# Guía de Contribución y Flujo de Trabajo (Git & Scrum)

¡Bienvenidos al equipo de desarrollo del **Sistema de Asistencia QR - IUTECP**!  
Este documento establece los estándares de trabajo, ramas, commits y control de calidad que utilizaremos en el proyecto.

---

## 1. Estructura de Ramas

Trabajamos bajo una variante ágil de **GitHub Flow**:

* **`main` (Producción Estable):**
  * Código 100% probado, validado y desplegable en InfinityFree.
  * **Nadie programa ni comitea directamente en `main`**.
  * Solo recibe integraciones vía Pull Request desde `dev` al finalizar cada Sprint.
* **`dev` (Integración):**
  * Es la rama base donde se unen y prueban las funcionalidades antes de un release.
  * Todas las ramas de trabajo nacen de `dev` y se integran a `dev`.
* **`feature/<nombre-tarea>` (Ramas de trabajo temporales):**
  * Ramas individuales para cada tarea del Sprint.
  * Ejemplos: `feature/admin-exportar-excel`, `feature/admin-filtro-carrera`.

---

## 2. División de Responsabilidades por Módulos

Para evitar conflictos de código (*merge conflicts*), cada integrante se enfoca en su área designada:

| Integrante | Rol | Módulos / Archivos Asignados |
| :--- | :--- | :--- |
| **Fernando** | Scrum Master & Lead Frontend/QR | `estudiante/`, `profesor/`, `assets/js/`, `includes/academico.php` |
| **Alex** | Backend & Gestión Administrativa | `admin/usuarios.php`, `admin/usuarios_lista.php`, `admin/cursos_lista.php`, `admin/cargar_inscripciones.php` |
| **José** | Métricas & Reportes Administrativos | `admin/ausencias.php`, `admin/reportes.php`, `admin/index.php` |

> ⚠️ **Archivos Compartidos (`includes/config.php`, `includes/functions.php`, `style.css`):**  
> Si necesitas crear una función global o agregar una regla de estilo, avísalo en el grupo antes de tocarlo.

---

## 3. Nomenclatura Obligatoria de Commits (Conventional Commits)

Cada commit debe seguir estrictamente este formato en minúsculas y sin punto final:

```text
<tipo>(<alcance>): <descripción corta y clara>
```

### Tipos Permitidos:
* **`feat:`** Nueva funcionalidad para el usuario.  
  * *Ejemplo:* `feat(admin): agregar exportacion de ausencias a formato excel`
* **`fix:`** Corrección de un fallo o error.  
  * *Ejemplo:* `fix(admin): corregir calculo de inasistencias en panel general`
* **`refactor:`** Reestructuración de código sin cambiar su funcionalidad.  
  * *Ejemplo:* `refactor(admin): modularizar consulta de ausencias prolongadas`
* **`style:`** Ajustes visuales, CSS o maquetación institucional.  
  * *Ejemplo:* `style(admin): ajustar espaciado de tarjetas segun paleta`
* **`docs:`** Cambios en documentación o manuales.  
  * *Ejemplo:* `docs: actualizar pasos de instalacion en readme`
* **`chore:`** Tareas de mantenimiento, dependencias o `.gitignore`.  
  * *Ejemplo:* `chore: actualizar dependencias y reglas de gitignore`

### Alcances (`scope`) Comunes:
* `(admin)`, `(profesor)`, `(estudiante)`, `(qr)`, `(auth)`, `(db)`, `(ui)`.

---

## 4. Flujo de Trabajo Diario (Paso a Paso)

### Paso 1: Actualizar tu rama `dev` local
Antes de comenzar a programar cada día:
```bash
git checkout dev
git pull origin dev
```

### Paso 2: Crear tu rama de trabajo
```bash
git checkout -b feature/nombre-de-tu-tarea
```

### Paso 3: Desarrollar y realizar commits limpios
Realiza commits atómicos siguiendo la convención:
```bash
git add admin/archivo_modificado.php
git commit -m "feat(admin): descripcion del cambio realizado"
```

### Paso 4: Subir tu rama a GitHub
```bash
git push -u origin feature/nombre-de-tu-tarea
```

### Paso 5: Abrir un Pull Request (PR)
1. Ve al repositorio en GitHub.
2. Haz clic en **Compare & pull request**.
3. **IMPORTANTE:** La rama base debe ser **`base: dev`** $\leftarrow$ **`compare: feature/nombre-de-tu-tarea`**.
4. Asigna a **Fernando** como revisor (*Reviewer*).

---

## 5. Control de Calidad y Política de "Squash & Merge"

Para mantener el repositorio limpio y evitar historial saturado por micro-commits de desarrollo o asistencia de IA:
1. El Scrum Master revisará el código del Pull Request.
2. Al ser aprobado, se integrará utilizando **Squash and merge**, consolidando todos los commits de la rama en un único commit descriptivo y limpio en `dev`.
3. Una vez integrado, la rama `feature/...` se eliminará.

---

## 6. Versionado Semántico (SemVer) y Releases

Las versiones de la aplicación siguen el formato **`vMAYOR.MENOR.PARCHE`** (ejemplo: `v1.0.0`):
* **MAYOR:** Cambios estructurales grandes o incompatibles.
* **MENOR:** Nuevos módulos o características completadas al cierre de un Sprint.
* **PARCHE:** Correcciones rápidas de bugs (*hotfixes*).

Los lanzamientos a `main` se acompañan de su correspondiente registro en [CHANGELOG.md](CHANGELOG.md).
