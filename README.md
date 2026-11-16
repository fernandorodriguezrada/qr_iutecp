### Comandos útiles:
```bash
ddev status      # Ver estado de los servicios
ddev mysql       # Conectar a la base de datos por terminal
ddev stop        # Detener contenedores
```

---

## 🏛️ Despliegue Autónomo en Producción (Red Local IUTECP)

Para desplegar el sistema en una máquina o servidor dentro de la red del instituto **sin depender de internet externo ni de herramientas de desarrollo (DDEV)**:

### Requisitos previos en el servidor/PC institucional:
* Linux (Debian, Ubuntu Server o Desktop).
* Conectado a la red local (Ethernet o Wi-Fi) del IUTECP.

### Puesta en marcha en un solo paso:
```bash
# Clonar el repositorio y ejecutar el instalador todo-terreno:
./deploy/setup_production.sh
```

El script se encargará automáticamente de:
1. Detectar la IP del servidor en la red local.
2. Anunciar el dominio amigable `https://asistencias.local` en toda la red mediante mDNS.
3. Generar certificados SSL con Subject Alternative Name (SAN) para la IP y el dominio.
4. Levantar la pila de producción nativa (Nginx en puertos 80/443 + PHP 8.2-FPM + MariaDB 11.8).
5. Inicializar la base de datos limpia con su esquema institucional (`001_initial_schema.sql`) y cuenta de administrador (`admin` / `12344321`).
