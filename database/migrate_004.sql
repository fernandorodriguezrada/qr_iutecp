-- Migración 004: Asistencia por sesión (misma semana, días distintos)
-- Permite múltiples registros de asistencia por (inscripcion_id, semana)
-- usando la fecha como discriminante

ALTER TABLE asistencias ADD UNIQUE INDEX unique_asistencia_nueva (inscripcion_id, semana, fecha);
ALTER TABLE asistencias DROP INDEX unique_asistencia;
ALTER TABLE asistencias RENAME INDEX unique_asistencia_nueva TO unique_asistencia;
