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

DOMAIN_DUCK="asistencias-iutecp.duckdns.org"
DOMAIN_EDU="asistencias.iutecp.edu.ve"
DOMAIN_LOCAL="asistencias.local"

# Sincronización automática de IP con DuckDNS si hay conexión
DUCK_CONF="$PROJECT_ROOT/deploy/.duckdns.env"
if [ -f "$DUCK_CONF" ]; then
    source "$DUCK_CONF"
    if [ -n "${DUCKDNS_TOKEN:-}" ] && [ -n "${DUCKDNS_DOMAIN:-}" ]; then
        echo -e "${BLUE}  Sincronizando ${DOMAIN_DUCK} con DuckDNS...${NC}"
        SYNC_RESP=$(curl -s --connect-timeout 5 "https://www.duckdns.org/update?domains=${DUCKDNS_DOMAIN}&token=${DUCKDNS_TOKEN}&ip=${LOCAL_IP}" || echo "TIMEOUT")
        if [ "$SYNC_RESP" = "OK" ]; then
            echo -e "${GREEN}  ✓ DuckDNS actualizado exitosamente hacia la IP: ${BOLD}${LOCAL_IP}${NC}"
        else
            echo -e "${YELLOW}  [i] Sincronización DuckDNS en la nube omitida o sin internet ($SYNC_RESP). (El DNS local resolverá la IP).${NC}"
        fi
    fi
fi
