# Sistema de Control de Asistencia QR - IUTECP

Sistema web universitario para el registro y gestión de asistencia estudiantil mediante códigos QR dinámicos, desarrollado para el **Instituto Universitario de Tecnología Elías Calixto Pompa (IUTECP)**.

---

## 📌 Ramas del Proyecto

* **`main`**: Rama de producción y demostración.
* **`dev`**: Rama de desarrollo activo para nuevas funcionalidades y pruebas locales antes de fusionar hacia `main`.

---

## 🚀 Arquitectura y Módulos

```text
qr_iutecp/
├── admin/          # Panel administrativo (gestión de usuarios, cursos, auditoría, reportes)
├── estudiante/     # Portal del estudiante (materias inscritas, catálogo de inscripción, escáner QR de asistencia)
├── profesor/       # Portal del profesor (generador de QR dinámicos con temporizador, registro de asistencias por semana)
├── includes/       # Configuraciones generales, sesiones, funciones auxiliares, PWA e integración QR
├── assets/         # Iconos de aplicación y librerías cliente (jsQR para decodificación de cámara)
├── sql/            # Respaldos y esquemas de la base de datos
└── seed/           # Archivos CSV para importación masiva de datos académicos
```

---

## 👥 Usuarios de Prueba y Credenciales

Todos los usuarios tienen la contraseña: **`12344321`**

### 1. Administrador
* **Usuario / Cédula:** `admin`
* **Panel:** `/admin/index.php`

### 2. Estudiante de Demostración
* **Nombre:** Fernando Rodríguez Rada
* **Cédula:** `31853086` *(o `31.853.086` / `V31853086`)*
* **Carrera:** Informática (TSU)
* **Semestre:** 5to Cuatrimestre (Sección 1D1, Sede Guatire)
* **Panel:** `/estudiante/index.php`

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

Este proyecto está configurado con **DDEV** (PHP 8.4 + MariaDB 11.8 + Nginx + Traefik).

```bash
# Iniciar el entorno de desarrollo
ddev start

# Ver el estado y URLs del proyecto
ddev status

# Acceder a la base de datos por terminal
ddev mysql

# Detener el entorno
ddev stop
```

### URLs Locales:
* **Con HTTPS (Certificado de confianza para cámara):** `https://qr-iutecp.ddev.site`
* **Localhost:** `http://localhost:8080`
* **Gestor de BD (Adminer):** `https://qr-iutecp.ddev.site:9101`
