# 4. Generación y verificación de Certificados SSL
echo -e "${BLUE}[3/5] Configurando certificados SSL para HTTPS...${NC}"
SSL_DIR="$PROJECT_ROOT/deploy/ssl"
mkdir -p "$SSL_DIR"

if [ -f "$SSL_DIR/server.crt" ] && [ -f "$SSL_DIR/server.key" ]; then
    CERT_ISSUER=$(openssl x509 -in "$SSL_DIR/server.crt" -noout -issuer 2>/dev/null || true)
    echo -e "${GREEN}  ✓ Certificados SSL existentes encontrados en deploy/ssl/:${NC}"
    echo -e "    $CERT_ISSUER"
else
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
CN = $DOMAIN_DUCK

[v3_req]
subjectAltName = @alt_names
basicConstraints = CA:FALSE
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = $DOMAIN_DUCK
DNS.2 = $DOMAIN_EDU
DNS.3 = $DOMAIN_LOCAL
DNS.4 = localhost
IP.1 = $LOCAL_IP
IP.2 = 127.0.0.1
EOF

    openssl req -x509 -nodes -days 1825 -newkey rsa:2048 \
        -keyout "$SSL_DIR/server.key" \
        -out "$SSL_DIR/server.crt" \
        -config "$SSL_DIR/openssl.cnf" >/dev/null 2>&1

    rm -f "$SSL_DIR/openssl.cnf"
    echo -e "${GREEN}  ✓ Certificado SSL generado con éxito para:${NC}"
    echo -e "    - ${DOMAIN_DUCK}"
    echo -e "    - ${DOMAIN_EDU}"
    echo -e "    - ${DOMAIN_LOCAL}"
    echo -e "    - IP: ${LOCAL_IP}"
fi
