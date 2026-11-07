-- Seed prerequisitos para Administración (carrera_id = 6)
-- Cada fila es UNA condición AND

-- ============================================================
-- NIVEL 2 (trayecto 2)
-- ============================================================

-- CBO2231: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'CBO2231' AND c.carrera_id = 6 AND c.activo = 1;

-- CON2243: CON2143
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CON2143', NULL, NULL FROM cursos c WHERE c.codigo = 'CON2243' AND c.carrera_id = 6 AND c.activo = 1;

-- COR2233: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'COR2233' AND c.carrera_id = 6 AND c.activo = 1;

-- ECO2242: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'ECO2242' AND c.carrera_id = 6 AND c.activo = 1;

-- ING2242: ING2142
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ING2142', NULL, NULL FROM cursos c WHERE c.codigo = 'ING2242' AND c.carrera_id = 6 AND c.activo = 1;

-- MAT2243: MAT2143
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MAT2143', NULL, NULL FROM cursos c WHERE c.codigo = 'MAT2243' AND c.carrera_id = 6 AND c.activo = 1;

-- MIN2243: TEI2143
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'TEI2143', NULL, NULL FROM cursos c WHERE c.codigo = 'MIN2243' AND c.carrera_id = 6 AND c.activo = 1;

-- PCT2223: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'PCT2223' AND c.carrera_id = 6 AND c.activo = 1;

-- ============================================================
-- NIVEL 3 (trayecto 3)
-- ============================================================

-- AEM2343: ADM2133 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ADM2133', NULL, NULL FROM cursos c WHERE c.codigo = 'AEM2343' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'AEM2343' AND c.carrera_id = 6 AND c.activo = 1;

-- APE2343: ADM2133 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ADM2133', NULL, NULL FROM cursos c WHERE c.codigo = 'APE2343' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'APE2343' AND c.carrera_id = 6 AND c.activo = 1;

-- CCO2343: CON2243
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CON2243', NULL, NULL FROM cursos c WHERE c.codigo = 'CCO2343' AND c.carrera_id = 6 AND c.activo = 1;

-- CON2343: CON2243
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CON2243', NULL, NULL FROM cursos c WHERE c.codigo = 'CON2343' AND c.carrera_id = 6 AND c.activo = 1;

-- EAM2321: Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'EAM2321' AND c.carrera_id = 6 AND c.activo = 1;

-- EGE2343: MAT2143 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MAT2143', NULL, NULL FROM cursos c WHERE c.codigo = 'EGE2343' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'EGE2343' AND c.carrera_id = 6 AND c.activo = 1;

-- MER2343: Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'MER2343' AND c.carrera_id = 6 AND c.activo = 1;

-- PDS2322: Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'PDS2322' AND c.carrera_id = 6 AND c.activo = 1;

-- ============================================================
-- NIVEL 4 (trayecto 4)
-- ============================================================

-- AEF2443: CON2343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CON2343', NULL, NULL FROM cursos c WHERE c.codigo = 'AEF2443' AND c.carrera_id = 6 AND c.activo = 1;

-- APU2443: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'APU2443' AND c.carrera_id = 6 AND c.activo = 1;

-- DME2443: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'DME2443' AND c.carrera_id = 6 AND c.activo = 1;

-- EST2442: EGE2343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'EGE2343', NULL, NULL FROM cursos c WHERE c.codigo = 'EST2442' AND c.carrera_id = 6 AND c.activo = 1;

-- OIN2443: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'OIN2443' AND c.carrera_id = 6 AND c.activo = 1;

-- PCP2443: CON2343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CON2343', NULL, NULL FROM cursos c WHERE c.codigo = 'PCP2443' AND c.carrera_id = 6 AND c.activo = 1;

-- PDI2443: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'PDI2443' AND c.carrera_id = 6 AND c.activo = 1;

-- SERCOM02: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'SERCOM02' AND c.carrera_id = 6 AND c.activo = 1;

-- ============================================================
-- NIVEL 5 (trayecto 5)
-- ============================================================

-- DLA2543: Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'DLA2543' AND c.carrera_id = 6 AND c.activo = 1;

-- IME2543: Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'IME2543' AND c.carrera_id = 6 AND c.activo = 1;

-- MFI2543: Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'MFI2543' AND c.carrera_id = 6 AND c.activo = 1;

-- PPR2543: MIN2243 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MIN2243', NULL, NULL FROM cursos c WHERE c.codigo = 'PPR2543' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'PPR2543' AND c.carrera_id = 6 AND c.activo = 1;

-- RIN2543: OIN2443
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'OIN2443', NULL, NULL FROM cursos c WHERE c.codigo = 'RIN2543' AND c.carrera_id = 6 AND c.activo = 1;

-- SPA2543: OIN2443 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'OIN2443', NULL, NULL FROM cursos c WHERE c.codigo = 'SPA2543' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'SPA2543' AND c.carrera_id = 6 AND c.activo = 1;

-- TAU2543: AEF2443 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'AEF2443', NULL, NULL FROM cursos c WHERE c.codigo = 'TAU2543' AND c.carrera_id = 6 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'TAU2543' AND c.carrera_id = 6 AND c.activo = 1;

-- ============================================================
-- NIVEL 6 (trayecto 6)
-- ============================================================

-- PAS2616: 99 créditos
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, NULL, 99 FROM cursos c WHERE c.codigo = 'PAS2616' AND c.carrera_id = 6 AND c.activo = 1;

-- ============================================================
-- ELECTIVAS DE ADMINISTRACIÓN (trayecto 2)
-- ============================================================

-- DIC2221: Ninguno
-- EUN2221: Ninguno
-- ICO2221: Ninguno
-- TEA2221: Ninguno

-- PIN2221: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'PIN2221' AND c.carrera_id = 6 AND c.activo = 1;
