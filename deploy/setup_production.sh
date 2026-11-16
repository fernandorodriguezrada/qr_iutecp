#!/usr/bin/env bash
# ==============================================================================
# SISTEMA DE ASISTENCIA QR - IUTECP
# Instalador Autónomo Todo-Terreno para Entorno de Producción Local
# ==============================================================================
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${BLUE}${BOLD}======================================================${NC}"
echo -e "${BLUE}${BOLD}  SISTEMA DE ASISTENCIA QR - IUTECP                   ${NC}"
echo -e "${BLUE}${BOLD}  Instalador de Producción Autónomo (Red Local)       ${NC}"
echo -e "${BLUE}${BOLD}======================================================${NC}"
echo ""

# 1. Comprobar permisos y herramientas necesarias
if [ "$(id -u)" -eq 0 ]; then
    SUDO=""
else
    if command -v sudo >/dev/null 2>&1; then
        SUDO="sudo"
    else
        echo -e "${RED}[ERROR] Este script requiere privilegios de administrador (sudo/root).${NC}"
        exit 1
    fi
fi

if ! command -v docker >/dev/null 2>&1; then
    echo -e "${YELLOW}[!] Docker no está instalado. Instalando Docker...${NC}"
    $SUDO apt-get update && $SUDO apt-get install -y docker.io docker-compose-plugin
    $SUDO systemctl enable --now docker
fi

# Detectar comando docker compose
if docker compose version >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    echo -e "${RED}[ERROR] No se encontró 'docker compose'.${NC}"
    exit 1
fi

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

# 2. Detección automática de la IP en la Red Local
echo -e "${BLUE}[1/5] Detectando interfaz e IP en la red local...${NC}"
LOCAL_IP=$(ip -4 route get 1.1.1.1 2>/dev/null | awk '{print $7}' | tr -d '\n' || true)

if [ -z "$LOCAL_IP" ]; then
    # Alternativa si no hay gateway/internet activo
    LOCAL_IP=$(hostname -I 2>/dev/null | awk '{print $1}' | tr -d '\n' || true)
fi

if [ -z "$LOCAL_IP" ]; then
    LOCAL_IP="127.0.0.1"
    echo -e "${YELLOW}  [AVISO] No se detectó interfaz de red activa. Usando 127.0.0.1.${NC}"
else
    echo -e "${GREEN}  ✓ Servidor detectado en la IP local: ${BOLD}$LOCAL_IP${NC}"
fi

DOMAIN="asistencias.local"

# 3. Configuración mDNS (Avahi) para resolver asistencias.local sin configurar routers
echo -e "${BLUE}[2/5] Configurando difusión mDNS (${DOMAIN})...${NC}"
if command -v avahi-daemon >/dev/null 2>&1; then
    if [ -f /etc/avahi/avahi-daemon.conf ]; then
        $SUDO sed -i 's/^#host-name=.*$/host-name=asistencias/' /etc/avahi/avahi-daemon.conf 2>/dev/null || true
        $SUDO sed -i 's/^host-name=.*$/host-name=asistencias/' /etc/avahi/avahi-daemon.conf 2>/dev/null || true
    fi
    $SUDO systemctl restart avahi-daemon 2>/dev/null || true
    echo -e "${GREEN}  ✓ Anunciando ${DOMAIN} en la red local mediante mDNS.${NC}"
else
    echo -e "${YELLOW}  [i] Instalando avahi-daemon para soporte .local automático...${NC}"
    $SUDO apt-get update -qq && $SUDO apt-get install -y -qq avahi-daemon >/dev/null 2>&1 || true
    $SUDO systemctl enable --now avahi-daemon 2>/dev/null || true
fi

# 4. Generación de Certificados SSL SAN (Para dominio local e IP)
echo -e "${BLUE}[3/5] Generando certificado SSL para HTTPS (habilitar cámara web)...${NC}"
SSL_DIR="$PROJECT_ROOT/deploy/ssl"
mkdir -p "$SSL_DIR"

if [ ! -f "$SSL_DIR/server.crt" ] || [ ! -f "$SSL_DIR/server.key" ]; then
    cat <<EOF > "$SSL_DIR/openssl.cnf"
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
x509_extensions = v3_req

[dn]
C = VE
ST = Miranda
L = Guatire
O = IUTECP
OU = Sistemas
CN = $DOMAIN

[v3_req]
subjectAltName = @alt_names
basicConstraints = CA:FALSE
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = $DOMAIN
DNS.2 = localhost
IP.1 = $LOCAL_IP
IP.2 = 127.0.0.1
EOF

    openssl req -x509 -nodes -days 1825 -newkey rsa:2048 \
        -keyout "$SSL_DIR/server.key" \
        -out "$SSL_DIR/server.crt" \
        -config "$SSL_DIR/openssl.cnf" >/dev/null 2>&1

    rm -f "$SSL_DIR/openssl.cnf"
    echo -e "${GREEN}  ✓ Certificado SSL generado con éxito (Válido para $DOMAIN y $LOCAL_IP).${NC}"
else
    echo -e "${GREEN}  ✓ Certificados SSL existentes encontrados en deploy/ssl/.${NC}"
fi

# 5. Despliegue de Contenedores Docker de Producción
echo -e "${BLUE}[4/5] Levantando pila de producción (MariaDB 11.8 + PHP 8.2-FPM + Nginx)...${NC}"

# Si DDEV está corriendo en esta máquina, detenerlo para liberar los puertos 80/443
if command -v ddev >/dev/null 2>&1 && ddev describe >/dev/null 2>&1; then
    echo -e "${YELLOW}  [i] Deteniendo DDEV para liberar puertos de red...${NC}"
    ddev stop >/dev/null 2>&1 || true
fi

$DOCKER_COMPOSE -f docker-compose.prod.yml up -d --build

# 6. Verificación de Salud
echo -e "${BLUE}[5/5] Verificando salud del sistema...${NC}"
sleep 5

HTTP_CODE=$(curl -s -k -o /dev/null -w "%{http_code}" "https://127.0.0.1/" || echo "000")

if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 302 ]; then
    echo -e "${GREEN}${BOLD}  ✓ ¡Sistema de producción operativo y respondiendo (HTTP $HTTP_CODE)!${NC}"
else
    echo -e "${YELLOW}  [!] El sistema está iniciando. Estado HTTP actual: $HTTP_CODE${NC}"
fi

echo ""
echo -e "${GREEN}${BOLD}======================================================${NC}"
echo -e "${GREEN}${BOLD}  ¡INSTALACIÓN DE PRODUCCIÓN COMPLETADA CON ÉXITO!    ${NC}"
echo -e "${GREEN}${BOLD}======================================================${NC}"
echo ""
echo -e "  🌐 ${BOLD}Enlaces de acceso en la red del IUTECP:${NC}"
echo -e "     • Nombre amigable: ${BOLD}https://${DOMAIN}${NC}"
echo -e "     • Por IP directa:  ${BOLD}https://${LOCAL_IP}${NC}"
echo ""
echo -e "  👤 ${BOLD}Credenciales predeterminadas de Administrador:${NC}"
echo -e "     • Usuario: ${BOLD}admin${NC}"
echo -e "     • Clave:   ${BOLD}12344321${NC}"
echo ""
echo -e "  📌 ${BOLD}Para detener el sistema:${NC}"
echo -e "     docker compose -f docker-compose.prod.yml down"
echo ""
echo -e "  📌 ${BOLD}Para reiniciar el sistema tras un reinicio de la PC:${NC}"
echo -e "     docker compose -f docker-compose.prod.yml start"
echo ""
