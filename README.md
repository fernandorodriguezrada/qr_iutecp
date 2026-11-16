# Sistema de Control de Asistencia QR - IUTECP

Sistema web universitario para el registro y gestión de asistencia estudiantil mediante códigos QR dinámicos, desarrollado para el **Instituto Universitario de Tecnología Elías Calixto Pompa (IUTECP)**.

---

## 📌 Ramas del Repositorio

* **`main`**: Rama estable de producción y demostración.
* **`dev`**: Rama de desarrollo activo e integración de features.
* **`feature/<nombre>`**: Ramas temporales de trabajo creadas por los desarrolladores a partir de `dev`.

---

## 🚀 Arquitectura del Proyecto (PDS Skeleton)

El proyecto sigue el estándar de diseño **PDS Skeleton** con una carpeta pública raíz (`public/`) aislada del código privado para máxima seguridad institucional:

```text
qr_iutecp/
├── bin/                          # Scripts de terminal y mantenimiento (CLI)
│   ├── migrate.php               # Ejecutor de migraciones DDL/SQL
│   ├── seed.php                  # Sembrador de datos de prueba institucional
│   └── setup_network.sh          # Configuración de IP estática/DHCP en Debian/Ubuntu
├── database/                     # Versionado de la base de datos
│   ├── migrations/               # Scripts SQL de esquema (001_initial_schema.sql)
│   └── seeds/                    # Datos iniciales y cuatrimestres
├── docs/                         # Documentación técnica y capturas
│   └── screenshots/
├── includes/                     # LÓGICA PRIVADA (Aislada fuera de la web)
│   ├── .htaccess                 # Bloqueo total directo (Require all denied)
│   ├── config.php                # Conexión PDO a MariaDB
│   ├── session.php               # Manejo de sesiones seguras
│   ├── functions.php             # Funciones auxiliares y formateo
│   ├── academico.php             # Lógica de cálculo de asistencias y periodos
│   └── phpqrcode/                # Librería de generación de códigos QR
├── public/                       # DOCUMENT ROOT ÚNICO (Servidor Web)
│   ├── assets/                   # Recursos estáticos (CSS, JS, imágenes, iconos)
│   │   ├── css/style.css
│   │   ├── img/
│   │   ├── icons/
│   │   └── js/jsqr.js
│   ├── admin/                    # Panel administrativo institucional
│   ├── estudiante/               # Portal del estudiante (asistencias, cámara QR)
│   ├── profesor/                 # Portal del profesor (generador de QR dinámicos)
│   ├── seed/                     # Importador de CSVs de prueba
│   ├── index.php                 # Página de inicio / Login institucional
│   ├── login.php                 # Procesamiento de autenticación
│   ├── logout.php                # Cierre de sesión seguro
│   ├── cambio_password.php       # Cambio obligatorio de clave
│   ├── manifest.php              # Manifiesto PWA dinámico
│   ├── sw.js                     # Service Worker PWA
│   ├── favicon.ico
│   └── .htaccess                 # Directivas web y reescritura
├── CHANGELOG.md                  # Historial de cambios siguiendo SemVer
├── CONTRIBUTING.md               # Guía Scrum y flujo de trabajo en equipo
└── README.md
```

---

## 👥 Usuarios de Prueba y Credenciales

Todos los usuarios tienen la contraseña predeterminada: **`12344321`**

### 1. Administrador
* **Usuario / Cédula:** `admin`
* **Ruta:** `/admin/index.php`

### 2. Estudiante de Demostración
* **Nombre:** Fernando Rodríguez Rada
* **Cédula:** `31853086` *(o `31.853.086` / `V31853086`)*
* **Carrera:** Informática (TSU)
* **Semestre:** 5to Cuatrimestre (Sección 1D1, Sede Guatire)
* **Ruta:** `/estudiante/index.php`

### 3. Profesores del 5to Cuatrimestre (Horario Oficial)
* **Rosangela Fernandez:** Cédula `11486263` *(Planificación de Proyecto - PPR1543)*
* **José Goncalvez:** Cédula `21134176` *(Inteligencia de Negocio - INE1532)*
* **Yajes Fuenmayor:** Cédula `21134177` *(Informática y Ética - ETI1522)*
* **Jose Angel Ed:** Cédula `4273815` *(Procesamiento Datos - PDA1542)*
* **José Chacón:** Cédula `18403975` *(Programación IV - PRO1554)*
* **Jhanan Ojeda:** Cédula `15564116` *(Investigación de Operaciones - IOP1543)*
* **Pedro Yanez:** Cédula `25327679` *(Redes II - RED1532)*
* **José Mejias:** Cédula `4273816` *(Auditoría de Sistemas Computarizado - ASC1532)*

---

## 💻 Entorno de Desarrollo Local (DDEV)

El proyecto utiliza **DDEV** (PHP 8.2 + MariaDB 11.8 + Nginx).

### Puesta en marcha rápida:
```bash
# 1. Iniciar los contenedores del entorno local
ddev start

# 2. Ejecutar las migraciones de la base de datos
ddev exec php bin/migrate.php

# 3. (Opcional) Cargar datos de prueba del 5to cuatrimestre
ddev exec php bin/seed.php
```

### URLs Locales:
* **Con HTTPS (Certificado de confianza para cámara y PWA):** `https://qr-iutecp.ddev.site`
* **Localhost:** `http://localhost:8080`
* **Gestor de BD (Adminer):** `https://qr-iutecp.ddev.site:9101`

### Comandos útiles:
```bash
ddev status      # Ver estado de los servicios
ddev mysql       # Conectar a la base de datos por terminal
ddev stop        # Detener contenedores
```
