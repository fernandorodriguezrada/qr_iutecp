SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS qr_iutecp DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
USE qr_iutecp;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE,
    nombre_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    carrera_id INT,
    telefono VARCHAR(20),
    rol ENUM('admin', 'profesor', 'estudiante') NOT NULL DEFAULT 'estudiante',
    password_hash VARCHAR(255) NOT NULL,
    password_temporal BOOLEAN DEFAULT TRUE,
    activo BOOLEAN DEFAULT TRUE,
    creado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id),
    FOREIGN KEY (carrera_id) REFERENCES carreras(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE carreras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    nivel VARCHAR(50) NOT NULL,
    codigo VARCHAR(20),
    tipo_periodo ENUM('Trimestre','Cuatrimestre') NOT NULL DEFAULT 'Cuatrimestre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO carreras (nombre, nivel, codigo, tipo_periodo) VALUES
('Enfermería', 'TSU', 'ENF-TSU', 'Cuatrimestre'),
('Enfermería', 'Licenciatura', 'ENF-LIC', 'Cuatrimestre'),
('Fisioterapia', 'TSU', 'FIS-TSU', 'Cuatrimestre'),
('Informática', 'TSU', 'INF-TSU', 'Cuatrimestre'),
('Diseño Gráfico', 'TSU', 'DIS-TSU', 'Trimestre'),
('Administración de Empresas', 'TSU', 'ADM-TSU', 'Cuatrimestre'),
('Administración de Empresas', 'Licenciatura', 'ADM-LIC', 'Cuatrimestre'),
('Contaduría Pública', 'TSU', 'CON-TSU', 'Cuatrimestre'),
('Contaduría Pública', 'Licenciatura', 'CON-LIC', 'Cuatrimestre'),
('Relaciones Industriales', 'TSU', 'RIN-TSU', 'Cuatrimestre'),
('Gastronomía', 'TSU', 'GAS-TSU', 'Trimestre');

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50),
    carrera_id INT,
    profesor_id INT NULL,
    aula VARCHAR(100),
    seccion VARCHAR(20),
    horario VARCHAR(100),
    trayecto INT NOT NULL DEFAULT 1,
    creditos INT NOT NULL DEFAULT 0,
    tipo ENUM('Obligatoria','Electiva') NOT NULL DEFAULT 'Obligatoria',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id),
    FOREIGN KEY (carrera_id) REFERENCES carreras(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_id INT NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    estatus ENUM('Activa','Retirada') DEFAULT 'Activa',
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id),
    UNIQUE KEY unique_inscripcion (estudiante_id, curso_id, periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL UNIQUE,
    nota DECIMAL(5,2),
    estatus ENUM('Cursando','Aprobada','Reprobada','Remedial') NOT NULL DEFAULT 'Cursando',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    semana INT NOT NULL,
    fecha DATE NOT NULL,
    hora_escaneo TIME,
    estado ENUM('presente', 'ausente', 'justificado') DEFAULT 'ausente',
    qr_code VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id),
    UNIQUE KEY unique_asistencia (inscripcion_id, semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE sesiones_qr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    profesor_id INT NOT NULL,
    codigo_qr VARCHAR(100) UNIQUE NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_expiracion TIME NOT NULL,
    semana INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id),
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE configuracion_profesor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesor_id INT NOT NULL UNIQUE,
    expiracion_minutos INT NOT NULL DEFAULT 5,
    rotacion_segundos INT NOT NULL DEFAULT 30,
    radio_gps INT NOT NULL DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    detalle TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO usuarios (cedula, username, nombre_completo, email, rol, password_hash, password_temporal, activo)
VALUES (
    'admin',
    'iut.admin',
    'Administrador IUTECP',
    'soporte@iutecp.edu.ve',
    'admin',
    '$2y$10$0KOQZPwKxrg5Cu9anti/GOSaQJR9LGKwi53i91ZQ4aXbXbtCj.jRy',
    FALSE,
    TRUE
);

INSERT INTO configuracion (clave, valor, updated_at)
VALUES ('periodo_activo', '2026-II', NOW());

COMMIT;
