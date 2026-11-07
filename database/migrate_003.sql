-- Migración 003: Periodos académicos + nuevo ingreso
-- Crea tabla de periodos con fechas de inicio y agrega es_nuevo_ingreso a usuarios

CREATE TABLE IF NOT EXISTS periodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    fecha_inicio DATE NOT NULL,
    fecha_inicio_nuevos DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE usuarios ADD COLUMN es_nuevo_ingreso TINYINT(1) DEFAULT 0 AFTER activo;

-- Poblar periodos desde las inscripciones existentes
INSERT IGNORE INTO periodos (codigo, fecha_inicio)
SELECT DISTINCT i.periodo, '2026-05-04'
FROM inscripciones i
WHERE i.periodo NOT IN (SELECT codigo FROM periodos);
