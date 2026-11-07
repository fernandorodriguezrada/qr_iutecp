# Guía de Despliegue en InfinityFree

Esta guía explica paso a paso cómo desplegar el proyecto **Sistema QR IUTECP** en **InfinityFree** utilizando el archivo de base de datos de producción incluido en el repositorio.

---

## 📦 Archivo de Base de Datos para Producción

En la carpeta `sql/` del repositorio encontrarás el archivo preparado:
👉 **`sql/backup_completo_produccion.sql`**

Este archivo contiene:
* Todas las tablas y relaciones de llaves foráneas.
* Las 11 carreras oficiales del IUTECP (incluida Informática).
* Todos los profesores del horario oficial con contraseñas ya hasheadas en bcrypt (`12344321`).
* El estudiante Fernando Rodríguez con sus trayectos 1 al 4 aprobados, listo para inscribir materias del 5to cuatrimestre.
* El periodo activo configurado en `2026-III`.

---

## 🛠️ Pasos para el Despliegue

### Paso 1: Crear la Base de Datos en InfinityFree
1. Entra a tu panel de control de InfinityFree (**cPanel**).
2. Ve a la sección **MySQL Databases**.
3. Crea una nueva base de datos (por ejemplo, `epiz_XXXXXX_qriutecp`).
4. Anota los datos de conexión que te muestra InfinityFree:
   * **MySQL Hostname:** (ej. `sqlXXX.infinityfree.com`)
   * **MySQL Database Name:** (ej. `epiz_XXXXXX_qriutecp`)
   * **MySQL Username:** (ej. `epiz_XXXXXX`)
   * **MySQL Password:** (tu contraseña de cuenta vPanel)

---

### Paso 2: Importar la Base de Datos
1. En el cPanel de InfinityFree, haz clic en **phpMyAdmin**.
2. Selecciona la base de datos recién creada en la barra izquierda.
3. Ve a la pestaña **Importar** (Import).
4. Haz clic en **Seleccionar archivo** y elige `sql/backup_completo_produccion.sql`.
5. Haz clic en **Continuar** (Go).
6. Todas las tablas, profesores, materias y datos se importarán automáticamente.

---

### Paso 3: Configurar Credenciales en el Código
Abre el archivo `includes/config.php` y actualiza los valores con los datos de tu base de datos de InfinityFree:

```php
// En includes/config.php:
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Tu host de InfinityFree
define('DB_NAME', 'epiz_XXXXXX_qriutecp');     // Tu nombre de BD
define('DB_USER', 'epiz_XXXXXX');              // Tu usuario de BD
define('DB_PASS', 'TU_PASSWORD_AQUI');         // Tu contraseña
define('DB_CHARSET', 'utf8mb4');
```

---

### Paso 4: Subir los Archivos al Servidor
1. Conéctate vía **FTP** (FileZilla) con los datos FTP de tu cuenta InfinityFree (o usa el **Online File Manager** de cPanel).
2. Entra al directorio **`htdocs/`** de tu cuenta.
3. Sube todos los archivos y carpetas del proyecto dentro de `htdocs/`:
   * `admin/`
   * `estudiante/`
   * `profesor/`
   * `includes/`
   * `assets/`
   * `index.php`, `login.php`, `style.css`, etc.
   *(No es necesario subir la carpeta `.ddev/` ni `informes/`).*

---

### Paso 5: Permisos de Escritura para Códigos QR
Asegúrate de que la carpeta de caché de QR tenga permisos de escritura (chmod 755 o 777 en cPanel / FileZilla):
* `includes/phpqrcode/cache/`

---

### Paso 6: Verificación y Pruebas
1. Abre tu dominio de InfinityFree en el navegador (ej. `http://tu-subdominio.infinityfreeapp.com`).
2. Inicia sesión como:
   * **Administrador:** `admin` / `12344321`
   * **Estudiante:** `31853086` / `12344321`
   * **Profesor:** `18403975` / `12344321` (José Chacón - Programación IV)
3. ¡Listo! El sistema estará 100% operativo en línea.
