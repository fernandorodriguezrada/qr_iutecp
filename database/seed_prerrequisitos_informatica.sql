-- Seed prerequisitos para Informática (carrera_id = 4)
-- Cada fila es UNA condición AND

-- ============================================================
-- NIVEL 2 (trayecto 2)
-- ============================================================

-- CBO1231: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'CBO1231' AND c.carrera_id = 4 AND c.activo = 1;

-- EDA1243: LOG1142
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'LOG1142', NULL, NULL FROM cursos c WHERE c.codigo = 'EDA1243' AND c.carrera_id = 4 AND c.activo = 1;

-- ING1242: ING1142
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ING1142', NULL, NULL FROM cursos c WHERE c.codigo = 'ING1242' AND c.carrera_id = 4 AND c.activo = 1;

-- LPR1263: IBA1133 Y LOG1142
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'IBA1133', NULL, NULL FROM cursos c WHERE c.codigo = 'LPR1263' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'LOG1142', NULL, NULL FROM cursos c WHERE c.codigo = 'LPR1263' AND c.carrera_id = 4 AND c.activo = 1;

-- MAT1243: MAT1143
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MAT1143', NULL, NULL FROM cursos c WHERE c.codigo = 'MAT1243' AND c.carrera_id = 4 AND c.activo = 1;

-- MIN1243: TEI1143
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'TEI1143', NULL, NULL FROM cursos c WHERE c.codigo = 'MIN1243' AND c.carrera_id = 4 AND c.activo = 1;

-- OSI1232: ADM1133
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ADM1133', NULL, NULL FROM cursos c WHERE c.codigo = 'OSI1232' AND c.carrera_id = 4 AND c.activo = 1;

-- SIN1243: TEI1143 Y IBA1133
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'TEI1143', NULL, NULL FROM cursos c WHERE c.codigo = 'SIN1243' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'IBA1133', NULL, NULL FROM cursos c WHERE c.codigo = 'SIN1243' AND c.carrera_id = 4 AND c.activo = 1;

-- ============================================================
-- NIVEL 3 (trayecto 3)
-- ============================================================

-- ACO1343: IBA1133 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'IBA1133', NULL, NULL FROM cursos c WHERE c.codigo = 'ACO1343' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'ACO1343' AND c.carrera_id = 4 AND c.activo = 1;

-- ADS1343: OSI1232 Y SIN1243
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'OSI1232', NULL, NULL FROM cursos c WHERE c.codigo = 'ADS1343' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'SIN1243', NULL, NULL FROM cursos c WHERE c.codigo = 'ADS1343' AND c.carrera_id = 4 AND c.activo = 1;

-- AEM1343: ADM1133 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ADM1133', NULL, NULL FROM cursos c WHERE c.codigo = 'AEM1343' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'AEM1343' AND c.carrera_id = 4 AND c.activo = 1;

-- BDA1343: EDA1243
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'EDA1243', NULL, NULL FROM cursos c WHERE c.codigo = 'BDA1343' AND c.carrera_id = 4 AND c.activo = 1;

-- CMA1342: MAT1243 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MAT1243', NULL, NULL FROM cursos c WHERE c.codigo = 'CMA1342' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'CMA1342' AND c.carrera_id = 4 AND c.activo = 1;

-- EAM1321: Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'EAM1321' AND c.carrera_id = 4 AND c.activo = 1;

-- EGE1343: MAT1143 Y Nivel 2
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MAT1143', NULL, NULL FROM cursos c WHERE c.codigo = 'EGE1343' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'EGE1343' AND c.carrera_id = 4 AND c.activo = 1;

-- LPR1363: LPR1263
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'LPR1263', NULL, NULL FROM cursos c WHERE c.codigo = 'LPR1363' AND c.carrera_id = 4 AND c.activo = 1;

-- ============================================================
-- NIVEL 4 (trayecto 4)
-- ============================================================

-- ADS1443: ADS1343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ADS1343', NULL, NULL FROM cursos c WHERE c.codigo = 'ADS1443' AND c.carrera_id = 4 AND c.activo = 1;

-- EPR1443: EGE1343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'EGE1343', NULL, NULL FROM cursos c WHERE c.codigo = 'EPR1443' AND c.carrera_id = 4 AND c.activo = 1;

-- LPR1443: LPR1363
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'LPR1363', NULL, NULL FROM cursos c WHERE c.codigo = 'LPR1443' AND c.carrera_id = 4 AND c.activo = 1;

-- MDA1443: BDA1343 Y CMA1342
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'BDA1343', NULL, NULL FROM cursos c WHERE c.codigo = 'MDA1443' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CMA1342', NULL, NULL FROM cursos c WHERE c.codigo = 'MDA1443' AND c.carrera_id = 4 AND c.activo = 1;

-- SERCOM01: Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'SERCOM01' AND c.carrera_id = 4 AND c.activo = 1;

-- SOP1443: ACO1343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ACO1343', NULL, NULL FROM cursos c WHERE c.codigo = 'SOP1443' AND c.carrera_id = 4 AND c.activo = 1;

-- TEI1443: Nivel 2 Y Nivel 3
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 2, NULL FROM cursos c WHERE c.codigo = 'TEI1443' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'TEI1443' AND c.carrera_id = 4 AND c.activo = 1;

-- TEL1443: ACO1343
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'ACO1343', NULL, NULL FROM cursos c WHERE c.codigo = 'TEL1443' AND c.carrera_id = 4 AND c.activo = 1;

-- ============================================================
-- NIVEL 5 (trayecto 5)
-- ============================================================

-- ASC1543: Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'ASC1543' AND c.carrera_id = 4 AND c.activo = 1;

-- CPA1543: Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'CPA1543' AND c.carrera_id = 4 AND c.activo = 1;

-- IOP1543: CMA1342 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'CMA1342', NULL, NULL FROM cursos c WHERE c.codigo = 'IOP1543' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'IOP1543' AND c.carrera_id = 4 AND c.activo = 1;

-- ISO1543: Nivel 3 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 3, NULL FROM cursos c WHERE c.codigo = 'ISO1543' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'ISO1543' AND c.carrera_id = 4 AND c.activo = 1;

-- LPR1543: LPR1443
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'LPR1443', NULL, NULL FROM cursos c WHERE c.codigo = 'LPR1543' AND c.carrera_id = 4 AND c.activo = 1;

-- PPR1543: MIN1243 Y Nivel 4
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'MIN1243', NULL, NULL FROM cursos c WHERE c.codigo = 'PPR1543' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 4, NULL FROM cursos c WHERE c.codigo = 'PPR1543' AND c.carrera_id = 4 AND c.activo = 1;

-- RLO1543: SOP1443 Y TEL1443
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'SOP1443', NULL, NULL FROM cursos c WHERE c.codigo = 'RLO1543' AND c.carrera_id = 4 AND c.activo = 1;
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, 'TEL1443', NULL, NULL FROM cursos c WHERE c.codigo = 'RLO1543' AND c.carrera_id = 4 AND c.activo = 1;

-- ============================================================
-- NIVEL 6 (trayecto 6)
-- ============================================================

-- PAS1616: 99 créditos
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, NULL, 99 FROM cursos c WHERE c.codigo = 'PAS1616' AND c.carrera_id = 4 AND c.activo = 1;

-- ============================================================
-- ELECTIVAS DE INFORMÁTICA (trayecto 2)
-- ============================================================

-- DIC1221: Ninguno
-- EUN1221: Ninguno
-- ICO1221: Ninguno
-- TEA1221: Ninguno

-- PIN1221: Nivel 1
INSERT INTO prerrequisitos (curso_id, requiere_codigo, nivel_requerido, creditos_minimos)
SELECT c.id, NULL, 1, NULL FROM cursos c WHERE c.codigo = 'PIN1221' AND c.carrera_id = 4 AND c.activo = 1;
