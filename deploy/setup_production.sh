# Si DDEV está corriendo en esta máquina, apagar su router global para liberar los puertos 80/443
if command -v ddev >/dev/null 2>&1; then
    echo -e "${YELLOW}  [i] Apagando router global de DDEV para liberar puertos 80 y 443...${NC}"
    ddev poweroff >/dev/null 2>&1 || true
fi
