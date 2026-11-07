-- Limpia datos de prueba, mantiene estructura + admin + carreras + cursos
USE qr_iutecp;
START TRANSACTION;

DELETE FROM calificaciones;
DELETE FROM asistencias;
DELETE FROM sesiones_qr;
DELETE FROM configuracion_profesor;
DELETE FROM inscripciones;
DELETE FROM auditoria;
UPDATE cursos SET profesor_id = NULL;
DELETE FROM usuarios WHERE rol != 'admin';

ALTER TABLE usuarios AUTO_INCREMENT = 2;
ALTER TABLE cursos AUTO_INCREMENT = 314;
ALTER TABLE inscripciones AUTO_INCREMENT = 1;
ALTER TABLE asistencias AUTO_INCREMENT = 1;
ALTER TABLE sesiones_qr AUTO_INCREMENT = 1;
ALTER TABLE calificaciones AUTO_INCREMENT = 1;
ALTER TABLE configuracion_profesor AUTO_INCREMENT = 1;
ALTER TABLE auditoria AUTO_INCREMENT = 1;

COMMIT;
