-- ==============================================================================
-- Migration: 001_initial_schema.sql
-- Description: Baseline schema definition, institutional careers, and initial admin
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Table structure for: migrations (migration checkpoint registry)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration_name` VARCHAR(255) NOT NULL UNIQUE,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 2. Table structure for: carreras
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `carreras` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `nivel` VARCHAR(50) NOT NULL,
    `codigo` VARCHAR(20),
    `tipo_periodo` ENUM('Trimestre','Cuatrimestre') NOT NULL DEFAULT 'Cuatrimestre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Institutional careers catalogue
INSERT INTO `carreras` (`id`, `nombre`, `nivel`, `codigo`, `tipo_periodo`) VALUES
(1, 'Enfermería', 'TSU', 'ENF-TSU', 'Cuatrimestre'),
(2, 'Enfermería', 'Licenciatura', 'ENF-LIC', 'Cuatrimestre'),
(3, 'Fisioterapia', 'TSU', 'FIS-TSU', 'Cuatrimestre'),
(4, 'Informática', 'TSU', 'INF-TSU', 'Cuatrimestre'),
(5, 'Diseño Gráfico', 'TSU', 'DIS-TSU', 'Trimestre'),
(6, 'Administración de Empresas', 'TSU', 'ADM-TSU', 'Cuatrimestre'),
(7, 'Administración de Empresas', 'Licenciatura', 'ADM-LIC', 'Cuatrimestre'),
(8, 'Contaduría Pública', 'TSU', 'CON-TSU', 'Cuatrimestre'),
(9, 'Contaduría Pública', 'Licenciatura', 'CON-LIC', 'Cuatrimestre'),
(10, 'Relaciones Industriales', 'TSU', 'RIN-TSU', 'Cuatrimestre'),
(11, 'Gastronomía', 'TSU', 'GAS-TSU', 'Trimestre')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- ------------------------------------------------------------------------------
-- 3. Table structure for: usuarios
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cedula` VARCHAR(20) NOT NULL UNIQUE,
    `username` VARCHAR(50) UNIQUE,
    `nombre_completo` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255),
    `carrera_id` INT DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `rol` ENUM('admin', 'profesor', 'estudiante') NOT NULL DEFAULT 'estudiante',
    `password_hash` VARCHAR(255) NOT NULL,
    `password_temporal` TINYINT(1) DEFAULT 1,
    `activo` TINYINT(1) DEFAULT 1,
    `es_nuevo_ingreso` TINYINT(1) DEFAULT 0,
    `creado_por` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `creado_por` (`creado_por`),
    KEY `carrera_id` (`carrera_id`),
    CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Initial Administrator user (username: admin, password: 12344321)
INSERT INTO `usuarios` (`id`, `cedula`, `username`, `nombre_completo`, `email`, `rol`, `password_hash`, `password_temporal`, `activo`) VALUES
(1, '00000000', 'admin', 'Administrador General', 'admin@iutecp.edu.ve', 'admin', '$2y$12$2iHQ3PuR/c4np5pbfxwp6eMef6rAtTH11kr9Jh7AXv8Rk0644n9OK', 0, 1)
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- ------------------------------------------------------------------------------
-- 4. Table structure for: configuracion
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(50) NOT NULL UNIQUE,
    `valor` VARCHAR(255) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('periodo_activo', '2026-III')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- ------------------------------------------------------------------------------
-- 5. Table structure for: periodos
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `periodos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(10) NOT NULL UNIQUE,
    `fecha_inicio` DATE NOT NULL,
    `fecha_inicio_nuevos` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `periodos` (`codigo`, `fecha_inicio`, `fecha_inicio_nuevos`) VALUES
('2026-III', '2026-09-07', '2026-09-14')
ON DUPLICATE KEY UPDATE `fecha_inicio` = VALUES(`fecha_inicio`);

-- ------------------------------------------------------------------------------
-- 6. Table structure for: cursos
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cursos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `codigo` VARCHAR(50),
    `carrera_id` INT,
    `profesor_id` INT NULL,
    `aula` VARCHAR(100),
    `seccion` VARCHAR(20),
    `horario` VARCHAR(100),
    `trayecto` INT NOT NULL DEFAULT 1,
    `creditos` INT NOT NULL DEFAULT 0,
    `tipo` ENUM('Obligatoria','Electiva') NOT NULL DEFAULT 'Obligatoria',
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `carrera_id` (`carrera_id`),
    KEY `profesor_id` (`profesor_id`),
    CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`),
    CONSTRAINT `cursos_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 7. Table structure for: inscripciones
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inscripciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `estudiante_id` INT NOT NULL,
    `curso_id` INT NOT NULL,
    `periodo` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_inscripcion` (`estudiante_id`,`curso_id`,`periodo`),
    KEY `curso_id` (`curso_id`),
    CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 8. Table structure for: asistencias
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `asistencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inscripcion_id` INT NOT NULL,
    `semana` INT NOT NULL,
    `fecha` DATE NOT NULL,
    `bloque_horario` VARCHAR(100) NOT NULL DEFAULT '',
    `hora_escaneo` TIME DEFAULT NULL,
    `estado` ENUM('presente','ausente','justificado','tarde') DEFAULT 'ausente',
    `justificacion` TEXT DEFAULT NULL,
    `qr_code` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_asistencia` (`inscripcion_id`,`semana`,`fecha`,`bloque_horario`),
    CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 9. Table structure for: sesiones_qr
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sesiones_qr` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `curso_id` INT NOT NULL,
    `profesor_id` INT NOT NULL,
    `codigo_qr` VARCHAR(100) NOT NULL UNIQUE,
    `fecha` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_expiracion` TIME NOT NULL,
    `semana` INT NOT NULL,
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `curso_id` (`curso_id`),
    KEY `profesor_id` (`profesor_id`),
    CONSTRAINT `sesiones_qr_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
    CONSTRAINT `sesiones_qr_ibfk_2` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 10. Table structure for: configuracion_profesor
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion_profesor` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `profesor_id` INT NOT NULL UNIQUE,
    `expiracion_minutos` INT NOT NULL DEFAULT 5,
    `rotacion_segundos` INT NOT NULL DEFAULT 30,
    `radio_gps` INT NOT NULL DEFAULT 50,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `configuracion_profesor_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 11. Table structure for: calificaciones
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `calificaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inscripcion_id` INT NOT NULL,
    `nota_final` DECIMAL(4,2) DEFAULT NULL,
    `estatus` ENUM('Aprobada','Reprobada','Remedial','Retirada') DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_calificacion` (`inscripcion_id`),
    CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 12. Table structure for: prerrequisitos
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prerrequisitos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `curso_id` INT NOT NULL,
    `requiere_codigo` VARCHAR(50) DEFAULT NULL,
    `nivel_requerido` INT DEFAULT NULL,
    `creditos_minimos` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `curso_id` (`curso_id`),
    CONSTRAINT `prerrequisitos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ------------------------------------------------------------------------------
-- 13. Table structure for: auditoria
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT DEFAULT NULL,
    `accion` VARCHAR(255) NOT NULL,
    `detalle` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `usuario_id` (`usuario_id`),
    CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

SET FOREIGN_KEY_CHECKS = 1;
