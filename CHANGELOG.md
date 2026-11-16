# Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y este proyecto se adhiere a [Semantic Versioning (SemVer)](https://semver.org/lang/es/).

---

## [1.0.0] - 2026-11-15 (Baseline de Producción)

### Añadido
- **Módulo Estudiante (PWA Móvil):**
  - Interfaz móvil optimizada como Progressive Web App (PWA) con diseño institucional (`#262865` y `#ffe001`).
  - Escáner de códigos QR en tiempo real para registro de asistencia.
  - Bloques de métricas rápidas en resumen académico: **Materias inscritas**, **Días en racha** e **Índice de puntualidad**.
  - Visualización del historial de asistencias desglosado por semanas (1 a 12) y estados (Presente, Tarde, Ausente, Justificado).
  - Gestor de cambio de contraseña para usuarios con clave temporal.
- **Módulo Profesor:**
  - Generador de códigos QR dinámicos con configuración personalizada de tiempos de llegada puntual y con retraso.
  - Visualización y conteo en tiempo real de alumnos que escanean en el aula.
  - Cierre automático de sesiones de asistencia y marcado de ausentes al expirar el tiempo de la clase.
  - Módulo de justificación manual de inasistencias con registro de motivos y auditoría.
- **Módulo Administrativo:**
  - Panel de control con balance semanal de asistencias vs. inasistencias y gráfico analítico por días.
  - Módulo de detección de **Ausencias Prolongadas** con filtros por carrera, nivel y porcentajes de inasistencia (en riesgo ≥25% y crítico ≥30%).
  - Carga masiva de inscripciones académicas y sincronización con el pénsum del IUTECP.
  - Gestión integral de usuarios (estudiantes, profesores, administradores) con control de estado y nuevo ingreso.
- **Base de Datos y Seguridad:**
  - Migración con datos reales para el 5.° cuatrimestre (cohorte de Informática 1D1, 2026-III).
  - Manejo de roles estricto, protección de sesiones y registro de auditoría de acciones sensibles.
  - Zona horaria configurada en `America/Caracas` (UTC-4) con soporte bimodal para desarrollo local (DDEV) y producción (InfinityFree).

### Modificado
- Desacoplamiento de la autoinscripción de materias del portal del estudiante, alineando la arquitectura a la importación oficial desde **Terna.net**.
- Separación de la métrica de asistencia real frente a la puntualidad estricta en el panel administrativo.
- Limpieza integral de código y comentarios superfluos a lo largo de toda la aplicación.
