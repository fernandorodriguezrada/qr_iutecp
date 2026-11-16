#!/bin/bash
# Configura acceso por red local: https://asistencias.local
# IMPORTANTE: el escaneo QR por cámara requiere HTTPS (getUserMedia),
# por eso se usa HTTPS y no el puerto HTTP 8080.

echo "=== 1/3 Configurando Avahi (mDNS) ==="
sudo sed -i 's/^#host-name=.*$/host-name=asistencias/' /etc/avahi/avahi-daemon.conf
sudo systemctl restart avahi-daemon
echo "  → asistencias.local anunciado en la red"

echo ""
echo "=== 2/3 Agregando a /etc/hosts ==="
if ! grep -q "asistencias.local" /etc/hosts; then
    echo "127.0.0.1 asistencias.local" | sudo tee -a /etc/hosts > /dev/null
    echo "  → asistencias.local agregado a /etc/hosts"
else
    echo "  → ya estaba en /etc/hosts"
fi

echo ""
echo "=== 3/3 Reiniciando DDEV ==="
ddev restart

echo ""
echo "✅ Listo. Probar desde otro dispositivo:"
echo "   https://asistencias.local"
echo ""
echo "   IMPORTANTE para el escaneo QR con cámara:"
echo "   • Accede SIEMPRE por HTTPS (la cámara no funciona en HTTP)."
echo "   • El dispositivo móvil debe confiar en el certificado local:"
echo "     abrir https://asistencias.local, aceptar la advertencia del"
echo "     certificado y, si el navegador lo pide, instalar la CA de mkcert"
echo "     (archivo ~/.local/share/mkcert/rootCA.pem)."
echo "   • En esta misma PC también funciona: https://asistencias.local"
