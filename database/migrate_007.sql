ALTER TABLE prerrequisitos
  MODIFY COLUMN requiere_codigo VARCHAR(50) DEFAULT NULL,
  ADD COLUMN nivel_requerido TINYINT UNSIGNED DEFAULT NULL AFTER requiere_codigo,
  ADD COLUMN creditos_minimos SMALLINT UNSIGNED DEFAULT NULL AFTER nivel_requerido;
