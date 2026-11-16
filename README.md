### Puesta en marcha en un solo paso:
```bash
./deploy/setup_production.sh
```

El script se encarga automáticamente de:
1. Detectar la IP del servidor en la red local del IUTECP.
2. Configurar la resolución para el dominio dinámico **`https://asistencias-iutecp.duckdns.org`** y el institucional **`https://asistencias.iutecp.edu.ve`**.
3. Activar la difusión mDNS (`https://asistencias.local`) como respaldo para dispositivos móviles en la red local.
4. Generar certificados SSL multidominio (SAN) para que la cámara y el escáner QR funcionen bajo HTTPS seguro.
5. Levantar la pila de producción Docker (Nginx en puertos 80/443 + PHP 8.2-FPM + MariaDB 11.8).
6. Inicializar la base de datos con su esquema institucional limpio (`001_initial_schema.sql`) y la cuenta de administrador lista (`admin` / `12344321`).
