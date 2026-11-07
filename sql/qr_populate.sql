-- ============================================
-- qr_populate.sql
-- Datos de prueba — Pensum oficial IUTECP
-- Requiere: schema.sql importado previamente
-- Contraseña para todos los usuarios de prueba: 12345678
-- ============================================

START TRANSACTION;

-- Limpiar datos existentes (respetando FKs)
DELETE FROM auditoria;
DELETE FROM configuracion_profesor;
DELETE FROM sesiones_qr;
DELETE FROM asistencias;
DELETE FROM inscripciones;
DELETE FROM cursos;
DELETE FROM usuarios WHERE rol != 'admin';

-- Resetear auto_increment
ALTER TABLE usuarios AUTO_INCREMENT = 2;
ALTER TABLE cursos AUTO_INCREMENT = 1;
ALTER TABLE inscripciones AUTO_INCREMENT = 1;
ALTER TABLE asistencias AUTO_INCREMENT = 1;
ALTER TABLE sesiones_qr AUTO_INCREMENT = 1;
ALTER TABLE configuracion_profesor AUTO_INCREMENT = 1;
ALTER TABLE auditoria AUTO_INCREMENT = 1;

-- ============================
-- CURSOS DEL PENSUM
-- ============================

-- Enfermería (carrera_id=1)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('AUTODESARROLLO', 'AUD51021', 1, 1, 1, 'Obligatoria', TRUE),
('BIOQUÍMICA', 'BIO51022', 1, 1, 2, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD51021', 1, 1, 1, 'Obligatoria', TRUE),
('ENFERMERÍA BÁSICA', 'ENB51129', 1, 1, 9, 'Obligatoria', TRUE),
('INGLÉS INSTRUMENTAL', 'ING51042', 1, 1, 2, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO51042', 1, 1, 2, 'Obligatoria', TRUE),
('MORFOLOGÍA', 'MOR51044', 1, 1, 4, 'Obligatoria', TRUE),
('ANATOMÍA HUMANA', 'ANH52042', 1, 2, 2, 'Obligatoria', TRUE),
('ENFERMERÍA MATERNO INFANTIL I', 'EMI52129', 1, 2, 9, 'Obligatoria', TRUE),
('ÉTICA PROFESIONAL DE ENFERMERÍA', 'EPE52022', 1, 2, 2, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA52022', 1, 2, 1, 'Obligatoria', TRUE),
('NUTRICIÓN Y DIETÉTICA', 'NUD52021', 1, 2, 1, 'Obligatoria', TRUE),
('PSICOLOGÍA GENERAL', 'PSG52042', 1, 2, 2, 'Obligatoria', TRUE),
('SOCIOANTROPOLOGÍA', 'SAP52021', 1, 2, 1, 'Obligatoria', TRUE),
('ESTADÍSTICA DE SALUD Y EPIDEMIOLOGÍA', 'ESE53021', 1, 3, 1, 'Obligatoria', TRUE),
('ENFERMERÍA EN SALUD MENTAL Y PSIQUIATRÍA', 'ESP53129', 1, 3, 9, 'Obligatoria', TRUE),
('FARMACOLOGÍA', 'FAR53042', 1, 3, 2, 'Obligatoria', TRUE),
('FISIOLOGÍA Y FISIOPATOLOGÍA I', 'FFP53042', 1, 3, 2, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN53042', 1, 3, 2, 'Obligatoria', TRUE),
('MICROBIOLOGÍA Y PARASITOLOGÍA', 'MIP53042', 1, 3, 2, 'Obligatoria', TRUE),
('SALUD Y DESARROLLO ECONÓMICO-SOCIAL', 'SDE53042', 1, 3, 2, 'Obligatoria', TRUE),
('ENFERMERÍA MATERNO INFANTIL II', 'EMI54129', 1, 4, 9, 'Obligatoria', TRUE),
('ENFERMERÍA MÉDICO QUIRÚRGICO I', 'EMQ54129', 1, 4, 9, 'Obligatoria', TRUE),
('FISIOLOGÍA Y FISIOPATOLOGÍA II', 'FFP54042', 1, 4, 2, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SERCOM54020', 1, 4, 0, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE LA ATENCIÓN DE ENFERMERÍA', 'AAE55063', 1, 5, 3, 'Obligatoria', TRUE),
('ENFERMERÍA COMUNITARIA E INVESTIGACIÓN APLICADA', 'ECI55129', 1, 5, 9, 'Obligatoria', TRUE),
('ENFERMERÍA MÉDICO QUIRÚRGICO II', 'EMQ55129', 1, 5, 9, 'Obligatoria', TRUE),
('PRÁCTICAS CLÍNICAS', 'PRC56306', 1, 6, 6, 'Obligatoria', TRUE);

-- Fisioterapia (carrera_id=3)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('ANATOMÍA I (SISTEMA OSTEO-ARTICULAR)', 'ANA61084', 3, 1, 4, 'Obligatoria', TRUE),
('EDUCACIÓN PARA LA SALUD', 'EDS61022', 3, 1, 2, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD61021', 3, 1, 1, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'INF61022', 3, 1, 2, 'Obligatoria', TRUE),
('INGLÉS INSTRUMENTAL', 'ING61021', 3, 1, 1, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO61022', 3, 1, 2, 'Obligatoria', TRUE),
('MEDIOS FÍSICOS I', 'MFI61042', 3, 1, 2, 'Obligatoria', TRUE),
('ORIENTACIÓN EN FISIOTERAPIA', 'OFI61022', 3, 1, 2, 'Obligatoria', TRUE),
('ANÁLISIS DEL MOVIMIENTO HUMANO', 'AMH62022', 3, 2, 2, 'Obligatoria', TRUE),
('ANATOMÍA II (MIOLOGÍA)', 'ANA62084', 3, 2, 4, 'Obligatoria', TRUE),
('AUTODESARROLLO', 'AUD62021', 3, 2, 1, 'Obligatoria', TRUE),
('MEDIOS FÍSICOS II', 'MFI62042', 3, 2, 2, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN62042', 3, 2, 2, 'Obligatoria', TRUE),
('PRINCIPIOS FÍSICOS', 'PFI62042', 3, 2, 2, 'Obligatoria', TRUE),
('SISTEMA NERVIOSO', 'SNE62043', 3, 2, 3, 'Obligatoria', TRUE),
('VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA I', 'VDF62084', 3, 2, 4, 'Obligatoria', TRUE),
('ALTERACIONES DEL SISTEMA ÓSEO, ARTICULAR Y MUSCULAR I', 'ASA63022', 3, 3, 2, 'Obligatoria', TRUE),
('ALTERACIONES DEL SISTEMA NERVIOSO I', 'ASN63044', 3, 3, 4, 'Obligatoria', TRUE),
('ESTADÍSTICA DE SALUD', 'ESS63042', 3, 3, 2, 'Obligatoria', TRUE),
('FISIOTERAPIA EN PEDIATRÍA', 'FPE63022', 3, 3, 2, 'Obligatoria', TRUE),
('SALUD Y DESARROLLO ECONÓMICO SOCIAL', 'SDE63042', 3, 3, 2, 'Obligatoria', TRUE),
('TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA I', 'TIF63085', 3, 3, 5, 'Obligatoria', TRUE),
('VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA II', 'VDF63084', 3, 3, 4, 'Obligatoria', TRUE),
('ALTERACIONES DEL SISTEMA ÓSEO, ARTICULAR Y MUSCULAR II', 'ASA64022', 3, 4, 2, 'Obligatoria', TRUE),
('ALTERACIONES DEL SISTEMA NERVIOSO II', 'ASN64044', 3, 4, 4, 'Obligatoria', TRUE),
('SISTEMA CARDIO RESPIRATORIO', 'SCR64043', 3, 4, 3, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SERCOM64020', 3, 4, 0, 'Obligatoria', TRUE),
('TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA II', 'TIF64085', 3, 4, 5, 'Obligatoria', TRUE),
('VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA III', 'VDF64043', 3, 4, 3, 'Obligatoria', TRUE),
('ALTERACIONES CARDIO-RESPIRATORIAS', 'ACR65022', 3, 5, 2, 'Obligatoria', TRUE),
('FISIOLOGÍA DEL EJERCICIO', 'FEJ65044', 3, 5, 4, 'Obligatoria', TRUE),
('GERENCIA EN LOS SISTEMAS DE SALUD', 'GSS65044', 3, 5, 4, 'Obligatoria', TRUE),
('PSICOLOGÍA DEL DESARROLLO HUMANO', 'PDH65022', 3, 5, 2, 'Obligatoria', TRUE),
('TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA III', 'TIF65085', 3, 5, 5, 'Obligatoria', TRUE),
('PRÁCTICAS CLÍNICAS', 'PRC66306', 3, 6, 6, 'Obligatoria', TRUE);

-- Informática (carrera_id=4)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('ADMINISTRACIÓN', 'ADM1133', 4, 1, 3, 'Obligatoria', TRUE),
('EDUCACIÓN CIUDADANA', 'ECI1121', 4, 1, 1, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD1121', 4, 1, 1, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA1133', 4, 1, 3, 'Obligatoria', TRUE),
('INGLÉS I', 'ING1142', 4, 1, 2, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO1142', 4, 1, 2, 'Obligatoria', TRUE),
('LÓGICA', 'LOG1142', 4, 1, 2, 'Obligatoria', TRUE),
('MATEMÁTICA I', 'MAT1143', 4, 1, 3, 'Obligatoria', TRUE),
('TÉCNICAS DE ESTUDIO E INVESTIGACIÓN', 'TEI1143', 4, 1, 3, 'Obligatoria', TRUE),
('ELECTIVAS DE INFORMÁTICA', 'ELEINF', 4, 2, 0, 'Electiva', TRUE),
('CÁTEDRA BOLIVARIANA', 'CBO1231', 4, 2, 1, 'Obligatoria', TRUE),
('ESTRUCTURA DE DATOS', 'EDA1243', 4, 2, 3, 'Obligatoria', TRUE),
('INGLÉS II', 'ING1242', 4, 2, 2, 'Obligatoria', TRUE),
('LENGUAJE DE PROGRAMACIÓN I', 'LPR1263', 4, 2, 3, 'Obligatoria', TRUE),
('MATEMÁTICA II', 'MAT1243', 4, 2, 3, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN1243', 4, 2, 3, 'Obligatoria', TRUE),
('ORGANIZACIÓN Y SISTEMAS', 'OSI1232', 4, 2, 2, 'Obligatoria', TRUE),
('SISTEMAS DE INFORMACIÓN', 'SIN1243', 4, 2, 3, 'Obligatoria', TRUE),
('ARQUITECTURA DEL COMPUTADOR', 'ACO1343', 4, 3, 3, 'Obligatoria', TRUE),
('ANÁLISIS Y DISEÑO DE SISTEMAS I', 'ADS1343', 4, 3, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE EMPRESAS', 'AEM1343', 4, 3, 3, 'Obligatoria', TRUE),
('BASE DE DATOS', 'BDA1343', 4, 3, 3, 'Obligatoria', TRUE),
('CÁLCULO MATRICIAL', 'CMA1342', 4, 3, 2, 'Obligatoria', TRUE),
('EDUCACIÓN AMBIENTAL', 'EAM1321', 4, 3, 1, 'Obligatoria', TRUE),
('ESTADÍSTICA GENERAL', 'EGE1343', 4, 3, 3, 'Obligatoria', TRUE),
('LENGUAJE DE PROGRAMACIÓN II', 'LPR1363', 4, 3, 3, 'Obligatoria', TRUE),
('ANÁLISIS Y DISEÑO DE SISTEMAS II', 'ADS1443', 4, 4, 3, 'Obligatoria', TRUE),
('ESTADÍSTICA PROBABILÍSTICA', 'EPR1443', 4, 4, 3, 'Obligatoria', TRUE),
('LENGUAJE DE PROGRAMACIÓN III', 'LPR1443', 4, 4, 3, 'Obligatoria', TRUE),
('MODELO DE DATOS', 'MDA1443', 4, 4, 3, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SERCOM01', 4, 4, 0, 'Obligatoria', TRUE),
('SISTEMAS OPERATIVOS', 'SOP1443', 4, 4, 3, 'Obligatoria', TRUE),
('TÓPICOS ESPECIALES DE INFORMÁTICA', 'TEI1443', 4, 4, 3, 'Obligatoria', TRUE),
('TELEPROCESOS', 'TEL1443', 4, 4, 3, 'Obligatoria', TRUE),
('AUDITORÍA DE SISTEMA COMPUTARIZADO', 'ASC1543', 4, 5, 3, 'Obligatoria', TRUE),
('CONTABILIDAD Y PROCESOS ADMINISTRATIVOS', 'CPA1543', 4, 5, 3, 'Obligatoria', TRUE),
('INVESTIGACIÓN DE OPERACIONES', 'IOP1543', 4, 5, 3, 'Obligatoria', TRUE),
('INFORMÁTICA SOCIEDAD Y ORGANIZACIÓN', 'ISO1543', 4, 5, 3, 'Obligatoria', TRUE),
('LENGUAJE DE PROGRAMACIÓN IV', 'LPR1543', 4, 5, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN DE PROYECTOS', 'PPR1543', 4, 5, 3, 'Obligatoria', TRUE),
('REDES LOCALES', 'RLO1543', 4, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS1616', 4, 6, 6, 'Obligatoria', TRUE),
('DIVERSIDAD E IDENTIDAD CULTURAL', 'DIC1221', 4, 2, 1, 'Electiva', TRUE),
('EDITORIAL UNIVERSITARIA', 'EUN1221', 4, 2, 1, 'Electiva', TRUE),
('INTERPRETACIÓN CORAL', 'ICO1221', 4, 2, 1, 'Electiva', TRUE),
('PROTOCOLO INSTITUCIONAL', 'PIN1221', 4, 2, 1, 'Electiva', TRUE),
('TEATRO', 'TEA1221', 4, 2, 1, 'Electiva', TRUE);

-- Diseño Gráfico (carrera_id=5)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('TEORÍA DEL DISEÑO', 'TDI3113', 5, 1, 4, 'Obligatoria', TRUE),
('TALLER DE DISEÑO INTEGRAL', 'TDI3123', 5, 1, 4, 'Obligatoria', TRUE),
('TEORÍA Y FUNDAMENTOS DEL COLOR', 'TFC3112', 5, 2, 2, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN3113', 5, 4, 2, 'Obligatoria', TRUE),
('ECOLOGÍA Y AMBIENTE', 'EYA3111', 5, 5, 2, 'Obligatoria', TRUE),
('TEORÍA DE LA FORMA', 'TFO3112', 5, 1, 2, 'Obligatoria', TRUE),
('DIBUJO BÁSICO', 'DBA3113', 5, 2, 2, 'Obligatoria', TRUE),
('DIBUJO AVANZADO', 'DAV3123', 5, 3, 3, 'Obligatoria', TRUE),
('DISEÑO GRÁFICO PUBLICITARIO', 'DGP3123', 5, 4, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN DE PROYECTOS', 'PPR3553', 5, 5, 3, 'Obligatoria', TRUE),
('TÉCNICAS DE ESTUDIO', 'TEI3113', 5, 1, 3, 'Obligatoria', TRUE),
('HISTORIA DEL ARTE', 'HAR3112', 5, 2, 2, 'Obligatoria', TRUE),
('HISTORIA DEL DISEÑO', 'HDI3122', 5, 3, 2, 'Obligatoria', TRUE),
('CREATIVIDAD (ELECTIVA)', 'CRE3312', 5, 4, 2, 'Electiva', TRUE),
('TENDENCIAS WEB (ELECTIVA)', 'TWE3322', 5, 5, 2, 'Electiva', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO3112', 5, 1, 3, 'Obligatoria', TRUE),
('INGLÉS', 'ING3112', 5, 2, 3, 'Obligatoria', TRUE),
('DISEÑO EDITORIAL', 'DED3213', 5, 3, 3, 'Obligatoria', TRUE),
('DISEÑO EDITORIAL AVANZADO', 'DEA3313', 5, 4, 3, 'Obligatoria', TRUE),
('TENDENCIAS DEL DISEÑO', 'TDE3322', 5, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS3666', 5, 6, 12, 'Obligatoria', TRUE),
('PROB. DEL DESARROLLO SOCIOECONÓMICO', 'PDS3312', 5, 1, 2, 'Obligatoria', TRUE),
('SEMIÓTICA DE LA IMAGEN', 'SIM3212', 5, 2, 3, 'Obligatoria', TRUE),
('FOTOGRAFÍA', 'FOT3223', 5, 3, 3, 'Obligatoria', TRUE),
('SISTEMAS DE IMPRESIÓN', 'SIN3332', 5, 4, 2, 'Obligatoria', TRUE),
('IDENTIDAD VISUAL CORPORATIVA', 'IVC3313', 5, 5, 4, 'Obligatoria', TRUE),
('MATEMÁTICAS', 'MAT3113', 5, 1, 3, 'Obligatoria', TRUE),
('GEOMETRÍA', 'GEO3122', 5, 2, 3, 'Obligatoria', TRUE),
('TIPOGRAFÍA', 'TIP3213', 5, 3, 3, 'Obligatoria', TRUE),
('MAQUETERÍA', 'MAQ3222', 5, 4, 3, 'Obligatoria', TRUE),
('DISEÑO DE EMPAQUES', 'DEM3323', 5, 5, 3, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA3113', 5, 1, 2, 'Obligatoria', TRUE),
('COMPUTACIÓN GRÁFICA I', 'CGR3213', 5, 2, 3, 'Obligatoria', TRUE),
('COMPUTACIÓN GRÁFICA II', 'CGR3320', 5, 3, 3, 'Obligatoria', TRUE),
('COMPUTACIÓN GRÁFICA III', 'CGR3330', 5, 4, 3, 'Obligatoria', TRUE),
('DISEÑO DE INTERFACES DIGITALES', 'DID3333', 5, 5, 3, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTE', 'EFD3111', 5, 2, 1, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SER3333', 5, 4, 0, 'Obligatoria', TRUE);

-- Administración de Empresas (carrera_id=6)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('ADMINISTRACIÓN', 'ADM2133', 6, 1, 3, 'Obligatoria', TRUE),
('CONTABILIDAD I', 'CON2143', 6, 1, 3, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD2121', 6, 1, 1, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA2133', 6, 1, 3, 'Obligatoria', TRUE),
('INGLÉS I', 'ING2142', 6, 1, 2, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO2142', 6, 1, 2, 'Obligatoria', TRUE),
('MATEMÁTICA I', 'MAT2143', 6, 1, 3, 'Obligatoria', TRUE),
('TÉCNICAS DE ESTUDIO E INVESTIGACIÓN', 'TEI2143', 6, 1, 3, 'Obligatoria', TRUE),
('ELECTIVAS DE ADMINISTRACIÓN', 'ELEADM', 6, 2, 0, 'Electiva', TRUE),
('CÁTEDRA BOLIVARIANA', 'CBO2231', 6, 2, 1, 'Obligatoria', TRUE),
('CONTABILIDAD II', 'CON2243', 6, 2, 3, 'Obligatoria', TRUE),
('COMPORTAMIENTO ORGANIZACIONAL', 'COR2233', 6, 2, 3, 'Obligatoria', TRUE),
('ECONOMÍA', 'ECO2242', 6, 2, 2, 'Obligatoria', TRUE),
('INGLÉS II', 'ING2242', 6, 2, 2, 'Obligatoria', TRUE),
('MATEMÁTICA II', 'MAT2243', 6, 2, 3, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN2243', 6, 2, 3, 'Obligatoria', TRUE),
('PROBLEMÁTICA CONTEMPORANEA DE LA CIENCIA Y TECNOLOGÍA', 'PCT2223', 6, 2, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE EMPRESAS', 'AEM2343', 6, 3, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE PERSONAL', 'APE2343', 6, 3, 3, 'Obligatoria', TRUE),
('CONTABILIDAD DE COSTOS', 'CCO2343', 6, 3, 3, 'Obligatoria', TRUE),
('CONTABILIDAD III', 'CON2343', 6, 3, 3, 'Obligatoria', TRUE),
('EDUCACIÓN AMBIENTAL', 'EAM2321', 6, 3, 1, 'Obligatoria', TRUE),
('ESTADÍSTICA GENERAL', 'EGE2343', 6, 3, 3, 'Obligatoria', TRUE),
('MERCADEO', 'MER2343', 6, 3, 3, 'Obligatoria', TRUE),
('PROBLEMÁTICA DEL DESARROLLO SOCIAL Y ECONÓMICO', 'PDS2322', 6, 3, 2, 'Obligatoria', TRUE),
('ANÁLISIS DE ESTADOS FINANCIEROS', 'AEF2443', 6, 4, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN PÚBLICA', 'APU2443', 6, 4, 3, 'Obligatoria', TRUE),
('DERECHO MERCANTIL', 'DME2443', 6, 4, 3, 'Obligatoria', TRUE),
('ESTADÍSTICA I', 'EST2442', 6, 4, 2, 'Obligatoria', TRUE),
('ORGANIZACIÓN INDUSTRIAL', 'OIN2443', 6, 4, 3, 'Obligatoria', TRUE),
('PRESUPUESTO Y CONTROL PRESUPUESTARIO', 'PCP2443', 6, 4, 3, 'Obligatoria', TRUE),
('PROGRAMACIÓN Y DIAGRAMACIÓN', 'PDI2443', 6, 4, 3, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SERCOM02', 6, 4, 0, 'Obligatoria', TRUE),
('DERECHO LABORAL', 'DLA2543', 6, 5, 3, 'Obligatoria', TRUE),
('INVESTIGACIÓN DE MERCADO', 'IME2543', 6, 5, 3, 'Obligatoria', TRUE),
('MATEMÁTICA FINANCIERA', 'MFI2543', 6, 5, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN DE PROYECTOS', 'PPR2543', 6, 5, 3, 'Obligatoria', TRUE),
('RELACIONES INDUSTRIALES', 'RIN2543', 6, 5, 3, 'Obligatoria', TRUE),
('SISTEMAS Y PROCEDIMIENTOS ADMINISTRATIVOS', 'SPA2543', 6, 5, 3, 'Obligatoria', TRUE),
('TÉCNICAS DE AUDITORÍAS', 'TAU2543', 6, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS2616', 6, 6, 6, 'Obligatoria', TRUE),
('DIVERSIDAD E IDENTIDAD CULTURAL', 'DIC2221', 6, 2, 1, 'Electiva', TRUE),
('EDITORIAL UNIVERSITARIA', 'EUN2221', 6, 2, 1, 'Electiva', TRUE),
('INTERPRETACIÓN CORAL', 'ICO2221', 6, 2, 1, 'Electiva', TRUE),
('PROTOCOLO INSTITUCIONAL', 'PIN2221', 6, 2, 1, 'Electiva', TRUE),
('TEATRO', 'TEA2221', 6, 2, 1, 'Electiva', TRUE);

-- Contaduría Pública (carrera_id=8)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('ADMINISTRACIÓN', 'ADM7133', 8, 1, 3, 'Obligatoria', TRUE),
('CONTABILIDAD I', 'CON7143', 8, 1, 3, 'Obligatoria', TRUE),
('ECONOMÍA I', 'ECO7142', 8, 1, 2, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD7121', 8, 1, 1, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA7132', 8, 1, 2, 'Obligatoria', TRUE),
('INGLÉS I', 'ING7142', 8, 1, 2, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO7142', 8, 1, 2, 'Obligatoria', TRUE),
('MATEMÁTICA I', 'MAT7143', 8, 1, 3, 'Obligatoria', TRUE),
('CONTABILIDAD II', 'CON7243', 8, 2, 3, 'Obligatoria', TRUE),
('COMPORTAMIENTO ORGANIZACIONAL', 'COR7232', 8, 2, 2, 'Obligatoria', TRUE),
('DERECHO MERCANTIL', 'DME7242', 8, 2, 2, 'Obligatoria', TRUE),
('ECONOMÍA II', 'ECO7242', 8, 2, 2, 'Obligatoria', TRUE),
('ELECTIVA', 'ELECON', 8, 2, 1, 'Electiva', TRUE),
('INFORMÁTICA II', 'IBA7232', 8, 2, 2, 'Obligatoria', TRUE),
('INGLÉS II', 'ING7242', 8, 2, 2, 'Obligatoria', TRUE),
('MATEMÁTICA II', 'MAT7243', 8, 2, 3, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN7243', 8, 2, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE EMPRESAS', 'AEM7343', 8, 3, 3, 'Obligatoria', TRUE),
('CONTABILIDAD DE COSTOS I', 'CCO7343', 8, 3, 3, 'Obligatoria', TRUE),
('CONTABILIDAD INTERMEDIA', 'CIN7343', 8, 3, 3, 'Obligatoria', TRUE),
('DERECHO LABORAL', 'DLA7342', 8, 3, 2, 'Obligatoria', TRUE),
('EDUCACIÓN AMBIENTAL', 'EAM7321', 8, 3, 1, 'Obligatoria', TRUE),
('ESTADÍSTICA I', 'EST7343', 8, 3, 3, 'Obligatoria', TRUE),
('MATEMÁTICA FINANCIERA', 'MFI7343', 8, 3, 3, 'Obligatoria', TRUE),
('PROBLEMÁTICA DEL DESARROLLO SOCIAL Y ECONÓMICO', 'PDS7322', 8, 3, 2, 'Obligatoria', TRUE),
('ANÁLISIS DE ESTADOS FINANCIEROS', 'AEF7443', 8, 4, 3, 'Obligatoria', TRUE),
('CONTABILIDAD AVANZADA', 'CAV7443', 8, 4, 3, 'Obligatoria', TRUE),
('CONTABILIDAD DE COSTOS II', 'CCO7443', 8, 4, 3, 'Obligatoria', TRUE),
('DERECHO TRIBUTARIO', 'DTR7442', 8, 4, 2, 'Obligatoria', TRUE),
('ESTADÍSTICA II', 'EST7443', 8, 4, 3, 'Obligatoria', TRUE),
('FUNDAMENTOS DE LA AUDITORÍA', 'FDA7442', 8, 4, 2, 'Obligatoria', TRUE),
('PRESUPUESTO Y CONTROL PRESUPUESTARIO', 'PCP7443', 8, 4, 3, 'Obligatoria', TRUE),
('AUDITORÍA FINANCIERA', 'AFI7543', 8, 5, 3, 'Obligatoria', TRUE),
('CONTABILIDAD FISCAL', 'CFI7543', 8, 5, 3, 'Obligatoria', TRUE),
('ÉTICA PROFESIONAL', 'EPR7542', 8, 5, 2, 'Obligatoria', TRUE),
('INVESTIGACIÓN DE OPERACIONES', 'IOP7543', 8, 5, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN DE PROYECTOS', 'PPR7543', 8, 5, 3, 'Obligatoria', TRUE),
('RELACIONES INDUSTRIALES', 'RIN7543', 8, 5, 3, 'Obligatoria', TRUE),
('SISTEMAS Y PROCEDIMIENTOS ADMINISTRATIVOS', 'SPA7543', 8, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS7616', 8, 6, 6, 'Obligatoria', TRUE);

-- Relaciones Industriales (carrera_id=10)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('CONTABILIDAD I', 'CON8154', 10, 1, 4, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD8121', 10, 1, 1, 'Obligatoria', TRUE),
('FUNDAMENTOS DE ADMINISTRACIÓN', 'FAD8123', 10, 1, 3, 'Obligatoria', TRUE),
('INGLÉS I', 'ING8132', 10, 1, 2, 'Obligatoria', TRUE),
('INTRODUCCIÓN A LAS RELACIONES INDUSTRIALES', 'IRI8133', 10, 1, 3, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO8122', 10, 1, 2, 'Obligatoria', TRUE),
('MATEMÁTICA I', 'MAT8143', 10, 1, 3, 'Obligatoria', TRUE),
('METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN8122', 10, 1, 2, 'Obligatoria', TRUE),
('CONTABILIDAD II', 'CON8243', 10, 2, 3, 'Obligatoria', TRUE),
('FUNDAMENTOS DE ECONOMÍA', 'FEC8222', 10, 2, 2, 'Obligatoria', TRUE),
('INTRODUCCIÓN A LA INFORMÁTICA', 'IIN8243', 10, 2, 3, 'Obligatoria', TRUE),
('INGLÉS II', 'ING8232', 10, 2, 2, 'Obligatoria', TRUE),
('LEGISLACIÓN ORGANIZACIONAL', 'LOR8233', 10, 2, 3, 'Obligatoria', TRUE),
('MATEMÁTICA II', 'MAT8243', 10, 2, 3, 'Obligatoria', TRUE),
('PSICOLOGÍA GENERAL Y SOCIAL', 'PSG8233', 10, 2, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN TRIBUTARIA', 'ATR8332', 10, 3, 2, 'Obligatoria', TRUE),
('ESTADÍSTICA GENERAL', 'EGE8343', 10, 3, 3, 'Obligatoria', TRUE),
('HIGIENE Y SEGURIDAD INDUSTRIAL', 'HSI8333', 10, 3, 3, 'Obligatoria', TRUE),
('INGLÉS III', 'ING8332', 10, 3, 2, 'Obligatoria', TRUE),
('LEGISLACIÓN LABORAL', 'LLA8343', 10, 3, 3, 'Obligatoria', TRUE),
('ORGANIZACIÓN CIENTÍFICA DEL TRABAJO', 'OCT8333', 10, 3, 3, 'Obligatoria', TRUE),
('PSICOLOGÍA INDUSTRIAL', 'PSI8333', 10, 3, 3, 'Obligatoria', TRUE),
('ANÁLISIS Y CLASIFICACIÓN DE CARGOS', 'ACC8443', 10, 4, 3, 'Obligatoria', TRUE),
('ORGANIZACIÓN Y MÉTODOS DEL TRABAJO', 'OMT8443', 10, 4, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN Y PRESUPUESTO', 'PLP8443', 10, 4, 3, 'Obligatoria', TRUE),
('RELACIONES LABORALES', 'RLA8433', 10, 4, 3, 'Obligatoria', TRUE),
('RECLUTAMIENTO Y SELECCIÓN DE PERSONAL', 'RSP8433', 10, 4, 3, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SERCOM08', 10, 4, 0, 'Obligatoria', TRUE),
('SEGURIDAD SOCIAL', 'SES8433', 10, 4, 3, 'Obligatoria', TRUE),
('SUPERVISIÓN DE PERSONAL', 'SPE8433', 10, 4, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE NÓMINA', 'ADN8543', 10, 5, 3, 'Obligatoria', TRUE),
('CAPACITACIÓN Y ADIESTRAMIENTO', 'CAD8533', 10, 5, 3, 'Obligatoria', TRUE),
('ELECTIVA RELACIONES INDUSTRIALES', 'ELECT8522', 10, 5, 2, 'Electiva', TRUE),
('EVALUACIÓN DE LOS RECURSOS HUMANOS', 'ERH8533', 10, 5, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN ESTRATÉGICA DE RECURSOS HUMANOS', 'PES8522', 10, 5, 2, 'Obligatoria', TRUE),
('PROCEDIMIENTOS Y PRÁCTICAS LABORALES', 'PPL8543', 10, 5, 3, 'Obligatoria', TRUE),
('PLANIFICACIÓN DE PROYECTOS', 'PPR8522', 10, 5, 2, 'Obligatoria', TRUE),
('REGISTRO Y CONTROL', 'RCO8543', 10, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS86126', 10, 6, 6, 'Obligatoria', TRUE);

-- Gastronomía (carrera_id=11)
INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES
('INTRODUCCIÓN A LA GASTRONOMÍA', 'IGA4113', 11, 1, 3, 'Obligatoria', TRUE),
('GASTRONOMÍA I', 'GAS4113', 11, 2, 3, 'Obligatoria', TRUE),
('GASTRONOMÍA II', 'GAS4213', 11, 3, 3, 'Obligatoria', TRUE),
('GASTRONOMÍA III', 'GAS4313', 11, 4, 3, 'Obligatoria', TRUE),
('GASTRONOMÍA IV', 'GAS4413', 11, 5, 3, 'Obligatoria', TRUE),
('HISTORIA DE LA GASTRONOMÍA UNIVERSAL', 'HGU4112', 11, 1, 2, 'Obligatoria', TRUE),
('ETIQUETA Y PROTOCOLO', 'ETP4122', 11, 2, 2, 'Obligatoria', TRUE),
('PANADERÍA BÁSICA', 'PAB4213', 11, 3, 3, 'Obligatoria', TRUE),
('PANADERÍA AVANZADA', 'PAA4313', 11, 4, 3, 'Obligatoria', TRUE),
('PROYECTO GASTRONÓMICO', 'PGA4513', 11, 5, 4, 'Obligatoria', TRUE),
('INFORMÁTICA BÁSICA', 'IBA4113', 11, 1, 2, 'Obligatoria', TRUE),
('EDUCACIÓN FÍSICA Y DEPORTES', 'EFD4111', 11, 2, 1, 'Obligatoria', TRUE),
('BASES CULINARIAS (ELECTIVA I)', 'BCU4212', 11, 3, 2, 'Electiva', TRUE),
('EVENTOS Y BANQUETES (ELECTIVA II)', 'EVB4312', 11, 4, 2, 'Electiva', TRUE),
('MARCO LEGAL EN LA GASTRONOMÍA', 'MLG4322', 11, 5, 3, 'Obligatoria', TRUE),
('INGLÉS I', 'ING4112', 11, 1, 2, 'Obligatoria', TRUE),
('INGLÉS II', 'ING4212', 11, 2, 3, 'Obligatoria', TRUE),
('FRANCÉS EN LA COCINA', 'FCO4221', 11, 3, 2, 'Obligatoria', TRUE),
('PASTELERÍA BÁSICA', 'PSB4213', 11, 4, 3, 'Obligatoria', TRUE),
('PASTELERÍA AVANZADA', 'PSA4313', 11, 5, 3, 'Obligatoria', TRUE),
('PASANTÍAS', 'PAS4666', 11, 6, 12, 'Obligatoria', TRUE),
('ORIENTACIÓN', 'ORI4121', 11, 1, 2, 'Obligatoria', TRUE),
('CÁRNICOS Y DERIVADOS', 'CYD4223', 11, 2, 3, 'Obligatoria', TRUE),
('ALIMENTOS DE ORIGEN VEGETAL', 'AOV4323', 11, 3, 3, 'Obligatoria', TRUE),
('TÉCNICAS DE MIXOLOGÍA Y COCTELERÍA', 'TMC4332', 11, 4, 3, 'Obligatoria', TRUE),
('VINICULTURA Y CATA', 'VYC4412', 11, 5, 3, 'Obligatoria', TRUE),
('LENGUAJE Y COMUNICACIÓN', 'LCO4112', 11, 1, 3, 'Obligatoria', TRUE),
('BASES DE LA NUTRICIÓN', 'BNU4212', 11, 2, 3, 'Obligatoria', TRUE),
('BROMATOLOGÍA DE LOS ALIMENTOS', 'BAL4223', 11, 3, 3, 'Obligatoria', TRUE),
('TECNOLOGÍA DE LOS ALIMENTOS', 'TAL4313', 11, 4, 3, 'Obligatoria', TRUE),
('HIGIENE Y SEGURIDAD ALIMENTARIA', 'HSA4232', 11, 5, 3, 'Obligatoria', TRUE),
('MATEMÁTICAS', 'MAT4113', 11, 1, 3, 'Obligatoria', TRUE),
('QUÍMICA DE LOS ALIMENTOS', 'QAL4213', 11, 2, 3, 'Obligatoria', TRUE),
('MERMA DE LA INDUSTRIA GASTRONÓMICA', 'MIG4332', 11, 3, 3, 'Obligatoria', TRUE),
('DISEÑO E INGENIERÍA DEL MENÚ', 'DIM4413', 11, 4, 3, 'Obligatoria', TRUE),
('ESTANDARIZACIÓN DE RECETAS', 'ERC4422', 11, 5, 3, 'Obligatoria', TRUE),
('ESTADÍSTICA', 'EST4313', 11, 2, 3, 'Obligatoria', TRUE),
('ADMINISTRACIÓN DE EMPRESAS', 'ADM4313', 11, 3, 2, 'Obligatoria', TRUE),
('SERVICIO COMUNITARIO', 'SER4333', 11, 4, 0, 'Obligatoria', TRUE);


-- ============================
-- PROFESORES (20)
-- ============================
INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES
('V2010000000', 'Luis García Flores', 'profesor20@iutecp.edu.ve', 1, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000001', 'Ana Rodríguez Rivera', 'profesor20@iutecp.edu.ve', 3, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000002', 'Jorge Martínez Gómez', 'profesor20@iutecp.edu.ve', 4, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000003', 'Laura López Díaz', 'profesor20@iutecp.edu.ve', 5, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000004', 'Miguel Hernández Reyes', 'profesor20@iutecp.edu.ve', 6, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000005', 'Sofía González Morales', 'profesor20@iutecp.edu.ve', 8, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000006', 'Andrés Pérez Ortiz', 'profesor20@iutecp.edu.ve', 10, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000007', 'Isabella Sánchez Mendoza', 'profesor20@iutecp.edu.ve', 11, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000008', 'Manuel Ramírez Castillo', 'profesor20@iutecp.edu.ve', 1, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000009', 'Daniela Torres Moreno', 'profesor20@iutecp.edu.ve', 3, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000010', 'Francisco Flores Jiménez', 'profesor20@iutecp.edu.ve', 4, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000011', 'Camila Rivera Acosta', 'profesor20@iutecp.edu.ve', 5, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000012', 'Ricardo Gómez Medina', 'profesor20@iutecp.edu.ve', 6, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000013', 'Elena Díaz Molina', 'profesor20@iutecp.edu.ve', 8, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000014', 'Marcos Reyes Rivas', 'profesor20@iutecp.edu.ve', 10, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000015', 'Andrea Morales Quintero', 'profesor20@iutecp.edu.ve', 11, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000016', 'Alejandro Ortiz Cruz', 'profesor20@iutecp.edu.ve', 1, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000017', 'Verónica Mendoza Peña', 'profesor20@iutecp.edu.ve', 3, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000018', 'Javier Castillo Carrillo', 'profesor20@iutecp.edu.ve', 4, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V2010000019', 'Rosa Moreno Salazar', 'profesor20@iutecp.edu.ve', 5, 'profesor', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE);


-- ============================
-- ESTUDIANTES (200)
-- ============================
INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES
('V3010000000', 'Luis García González', 'estudiante0@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000001', 'Ana Rodríguez Pérez', 'estudiante1@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000002', 'Jorge Martínez Sánchez', 'estudiante2@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000003', 'Laura López Ramírez', 'estudiante3@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000004', 'Miguel Hernández Torres', 'estudiante4@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000005', 'Sofía González Flores', 'estudiante5@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000006', 'Andrés Pérez Rivera', 'estudiante6@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000007', 'Isabella Sánchez Gómez', 'estudiante7@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000008', 'Manuel Ramírez Díaz', 'estudiante8@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000009', 'Daniela Torres Reyes', 'estudiante9@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000010', 'Francisco Flores Morales', 'estudiante10@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000011', 'Camila Rivera Ortiz', 'estudiante11@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000012', 'Ricardo Gómez Mendoza', 'estudiante12@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000013', 'Elena Díaz Castillo', 'estudiante13@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000014', 'Marcos Reyes Moreno', 'estudiante14@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000015', 'Andrea Morales Jiménez', 'estudiante15@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000016', 'Alejandro Ortiz Acosta', 'estudiante16@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000017', 'Verónica Mendoza Medina', 'estudiante17@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000018', 'Javier Castillo Molina', 'estudiante18@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000019', 'Rosa Moreno Rivas', 'estudiante19@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000020', 'Víctor Jiménez Quintero', 'estudiante20@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000021', 'Silvia Acosta Cruz', 'estudiante21@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000022', 'Raúl Medina Peña', 'estudiante22@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000023', 'Adriana Molina Carrillo', 'estudiante23@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000024', 'Sergio Rivas Salazar', 'estudiante24@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000025', 'Alejandra Quintero Navarro', 'estudiante25@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000026', 'Alberto Cruz Paredes', 'estudiante26@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000027', 'Mónica Peña Rincón', 'estudiante27@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000028', 'Julián Carrillo Vargas', 'estudiante28@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000029', 'Beatriz Salazar Cordero', 'estudiante29@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000030', 'Adrián Navarro Arias', 'estudiante30@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000031', 'Martha Paredes Herrera', 'estudiante31@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000032', 'César Rincón Cabrera', 'estudiante32@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000033', 'Yolanda Vargas Uzcátegui', 'estudiante33@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000034', 'Nicolás Cordero Colmenares', 'estudiante34@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000035', 'Sandra Arias Pacheco', 'estudiante35@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000036', 'Samuel Herrera Briceno', 'estudiante36@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000037', 'Marisol Cabrera Chacón', 'estudiante37@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000038', 'Óscar Uzcátegui Henríquez', 'estudiante38@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000039', 'Belkis Colmenares Velásquez', 'estudiante39@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000040', 'Wilmer Pacheco García', 'estudiante40@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000041', 'Oriana Briceno Rodríguez', 'estudiante41@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000042', 'Saúl Chacón Martínez', 'estudiante42@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000043', 'Ana Henríquez López', 'estudiante43@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000044', 'Jairo Velásquez Hernández', 'estudiante44@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000045', 'Laura García González', 'estudiante45@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000046', 'Ángel Rodríguez Pérez', 'estudiante46@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000047', 'Sofía Martínez Sánchez', 'estudiante47@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000048', 'Carlos López Ramírez', 'estudiante48@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000049', 'Isabella Hernández Torres', 'estudiante49@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000050', 'Pedro González Flores', 'estudiante50@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000051', 'Daniela Pérez Rivera', 'estudiante51@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000052', 'José Sánchez Gómez', 'estudiante52@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000053', 'Camila Ramírez Díaz', 'estudiante53@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000054', 'Antonio Torres Reyes', 'estudiante54@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000055', 'Elena Flores Morales', 'estudiante55@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000056', 'David Rivera Ortiz', 'estudiante56@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000057', 'Andrea Gómez Mendoza', 'estudiante57@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000058', 'Rafael Díaz Castillo', 'estudiante58@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000059', 'Verónica Reyes Moreno', 'estudiante59@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000060', 'Héctor Morales Jiménez', 'estudiante60@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000061', 'Rosa Ortiz Acosta', 'estudiante61@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000062', 'Diego Mendoza Medina', 'estudiante62@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000063', 'Silvia Castillo Molina', 'estudiante63@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000064', 'Daniel Moreno Rivas', 'estudiante64@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000065', 'Adriana Jiménez Quintero', 'estudiante65@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000066', 'Pablo Acosta Cruz', 'estudiante66@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000067', 'Alejandra Medina Peña', 'estudiante67@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000068', 'Hugo Molina Carrillo', 'estudiante68@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000069', 'Mónica Rivas Salazar', 'estudiante69@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000070', 'Rubén Quintero Navarro', 'estudiante70@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000071', 'Beatriz Cruz Paredes', 'estudiante71@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000072', 'Fernando Peña Rincón', 'estudiante72@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000073', 'Martha Carrillo Vargas', 'estudiante73@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000074', 'Eduardo Salazar Cordero', 'estudiante74@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000075', 'Yolanda Navarro Arias', 'estudiante75@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000076', 'Gustavo Paredes Herrera', 'estudiante76@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000077', 'Sandra Rincón Cabrera', 'estudiante77@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000078', 'Arturo Vargas Uzcátegui', 'estudiante78@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000079', 'Marisol Cordero Colmenares', 'estudiante79@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000080', 'Marco Arias Pacheco', 'estudiante80@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000081', 'Belkis Herrera Briceno', 'estudiante81@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000082', 'Omar Cabrera Chacón', 'estudiante82@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000083', 'Oriana Uzcátegui Henríquez', 'estudiante83@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000084', 'Simón Colmenares Velásquez', 'estudiante84@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000085', 'Ana Pacheco García', 'estudiante85@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000086', 'Iván Briceno Rodríguez', 'estudiante86@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000087', 'Laura Chacón Martínez', 'estudiante87@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000088', 'Edgar Henríquez López', 'estudiante88@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000089', 'Sofía Velásquez Hernández', 'estudiante89@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000090', 'Moisés García González', 'estudiante90@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000091', 'Isabella Rodríguez Pérez', 'estudiante91@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000092', 'Elías Martínez Sánchez', 'estudiante92@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000093', 'Daniela López Ramírez', 'estudiante93@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000094', 'Luis Hernández Torres', 'estudiante94@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000095', 'Camila González Flores', 'estudiante95@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000096', 'Jorge Pérez Rivera', 'estudiante96@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000097', 'Elena Sánchez Gómez', 'estudiante97@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000098', 'Miguel Ramírez Díaz', 'estudiante98@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000099', 'Andrea Torres Reyes', 'estudiante99@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000100', 'Andrés Flores Morales', 'estudiante100@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000101', 'Verónica Rivera Ortiz', 'estudiante101@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000102', 'Manuel Gómez Mendoza', 'estudiante102@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000103', 'Rosa Díaz Castillo', 'estudiante103@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000104', 'Francisco Reyes Moreno', 'estudiante104@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000105', 'Silvia Morales Jiménez', 'estudiante105@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000106', 'Ricardo Ortiz Acosta', 'estudiante106@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000107', 'Adriana Mendoza Medina', 'estudiante107@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000108', 'Marcos Castillo Molina', 'estudiante108@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000109', 'Alejandra Moreno Rivas', 'estudiante109@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000110', 'Alejandro Jiménez Quintero', 'estudiante110@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000111', 'Mónica Acosta Cruz', 'estudiante111@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000112', 'Javier Medina Peña', 'estudiante112@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000113', 'Beatriz Molina Carrillo', 'estudiante113@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000114', 'Víctor Rivas Salazar', 'estudiante114@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000115', 'Martha Quintero Navarro', 'estudiante115@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000116', 'Raúl Cruz Paredes', 'estudiante116@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000117', 'Yolanda Peña Rincón', 'estudiante117@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000118', 'Sergio Carrillo Vargas', 'estudiante118@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000119', 'Sandra Salazar Cordero', 'estudiante119@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000120', 'Alberto Navarro Arias', 'estudiante120@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000121', 'Marisol Paredes Herrera', 'estudiante121@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000122', 'Julián Rincón Cabrera', 'estudiante122@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000123', 'Belkis Vargas Uzcátegui', 'estudiante123@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000124', 'Adrián Cordero Colmenares', 'estudiante124@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000125', 'Oriana Arias Pacheco', 'estudiante125@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000126', 'César Herrera Briceno', 'estudiante126@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000127', 'Ana Cabrera Chacón', 'estudiante127@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000128', 'Nicolás Uzcátegui Henríquez', 'estudiante128@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000129', 'Laura Colmenares Velásquez', 'estudiante129@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000130', 'Samuel Pacheco García', 'estudiante130@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000131', 'Sofía Briceno Rodríguez', 'estudiante131@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000132', 'Óscar Chacón Martínez', 'estudiante132@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000133', 'Isabella Henríquez López', 'estudiante133@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000134', 'Wilmer Velásquez Hernández', 'estudiante134@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000135', 'Daniela García González', 'estudiante135@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000136', 'Saúl Rodríguez Pérez', 'estudiante136@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000137', 'Camila Martínez Sánchez', 'estudiante137@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000138', 'Jairo López Ramírez', 'estudiante138@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000139', 'Elena Hernández Torres', 'estudiante139@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000140', 'Ángel González Flores', 'estudiante140@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000141', 'Andrea Pérez Rivera', 'estudiante141@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000142', 'Carlos Sánchez Gómez', 'estudiante142@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000143', 'Verónica Ramírez Díaz', 'estudiante143@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000144', 'Pedro Torres Reyes', 'estudiante144@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000145', 'Rosa Flores Morales', 'estudiante145@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000146', 'José Rivera Ortiz', 'estudiante146@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000147', 'Silvia Gómez Mendoza', 'estudiante147@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000148', 'Antonio Díaz Castillo', 'estudiante148@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000149', 'Adriana Reyes Moreno', 'estudiante149@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000150', 'David Morales Jiménez', 'estudiante150@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000151', 'Alejandra Ortiz Acosta', 'estudiante151@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000152', 'Rafael Mendoza Medina', 'estudiante152@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000153', 'Mónica Castillo Molina', 'estudiante153@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000154', 'Héctor Moreno Rivas', 'estudiante154@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000155', 'Beatriz Jiménez Quintero', 'estudiante155@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000156', 'Diego Acosta Cruz', 'estudiante156@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000157', 'Martha Medina Peña', 'estudiante157@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000158', 'Daniel Molina Carrillo', 'estudiante158@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000159', 'Yolanda Rivas Salazar', 'estudiante159@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000160', 'Pablo Quintero Navarro', 'estudiante160@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000161', 'Sandra Cruz Paredes', 'estudiante161@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000162', 'Hugo Peña Rincón', 'estudiante162@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000163', 'Marisol Carrillo Vargas', 'estudiante163@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000164', 'Rubén Salazar Cordero', 'estudiante164@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000165', 'Belkis Navarro Arias', 'estudiante165@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000166', 'Fernando Paredes Herrera', 'estudiante166@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000167', 'Oriana Rincón Cabrera', 'estudiante167@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000168', 'Eduardo Vargas Uzcátegui', 'estudiante168@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000169', 'Ana Cordero Colmenares', 'estudiante169@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000170', 'Gustavo Arias Pacheco', 'estudiante170@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000171', 'Laura Herrera Briceno', 'estudiante171@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000172', 'Arturo Cabrera Chacón', 'estudiante172@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000173', 'Sofía Uzcátegui Henríquez', 'estudiante173@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000174', 'Marco Colmenares Velásquez', 'estudiante174@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000175', 'Isabella Pacheco García', 'estudiante175@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000176', 'Omar Briceno Rodríguez', 'estudiante176@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000177', 'Daniela Chacón Martínez', 'estudiante177@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000178', 'Simón Henríquez López', 'estudiante178@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000179', 'Camila Velásquez Hernández', 'estudiante179@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000180', 'Iván García González', 'estudiante180@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000181', 'Elena Rodríguez Pérez', 'estudiante181@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000182', 'Edgar Martínez Sánchez', 'estudiante182@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000183', 'Andrea López Ramírez', 'estudiante183@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000184', 'Moisés Hernández Torres', 'estudiante184@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000185', 'Verónica González Flores', 'estudiante185@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000186', 'Elías Pérez Rivera', 'estudiante186@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000187', 'Rosa Sánchez Gómez', 'estudiante187@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000188', 'Luis Ramírez Díaz', 'estudiante188@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000189', 'Silvia Torres Reyes', 'estudiante189@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000190', 'Jorge Flores Morales', 'estudiante190@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000191', 'Adriana Rivera Ortiz', 'estudiante191@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000192', 'Miguel Gómez Mendoza', 'estudiante192@iutecp.edu.ve', 1, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000193', 'Alejandra Díaz Castillo', 'estudiante193@iutecp.edu.ve', 3, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000194', 'Andrés Reyes Moreno', 'estudiante194@iutecp.edu.ve', 4, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000195', 'Mónica Morales Jiménez', 'estudiante195@iutecp.edu.ve', 5, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000196', 'Manuel Ortiz Acosta', 'estudiante196@iutecp.edu.ve', 6, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000197', 'Beatriz Mendoza Medina', 'estudiante197@iutecp.edu.ve', 8, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000198', 'Francisco Castillo Molina', 'estudiante198@iutecp.edu.ve', 10, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE),
('V3010000199', 'Martha Moreno Rivas', 'estudiante199@iutecp.edu.ve', 11, 'estudiante', '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O', FALSE, TRUE);


-- ============================
-- ASIGNAR PROFESORES A CURSOS
-- ============================
UPDATE cursos SET profesor_id = 2 WHERE id = 1;
UPDATE cursos SET profesor_id = 2 WHERE id = 2;
UPDATE cursos SET profesor_id = 2 WHERE id = 3;
UPDATE cursos SET profesor_id = 2 WHERE id = 4;
UPDATE cursos SET profesor_id = 2 WHERE id = 5;
UPDATE cursos SET profesor_id = 2 WHERE id = 6;
UPDATE cursos SET profesor_id = 2 WHERE id = 7;
UPDATE cursos SET profesor_id = 2 WHERE id = 8;
UPDATE cursos SET profesor_id = 2 WHERE id = 9;
UPDATE cursos SET profesor_id = 2 WHERE id = 10;
UPDATE cursos SET profesor_id = 3 WHERE id = 30;
UPDATE cursos SET profesor_id = 3 WHERE id = 31;
UPDATE cursos SET profesor_id = 3 WHERE id = 32;
UPDATE cursos SET profesor_id = 3 WHERE id = 33;
UPDATE cursos SET profesor_id = 3 WHERE id = 34;
UPDATE cursos SET profesor_id = 3 WHERE id = 35;
UPDATE cursos SET profesor_id = 3 WHERE id = 36;
UPDATE cursos SET profesor_id = 3 WHERE id = 37;
UPDATE cursos SET profesor_id = 3 WHERE id = 38;
UPDATE cursos SET profesor_id = 3 WHERE id = 39;
UPDATE cursos SET profesor_id = 3 WHERE id = 40;
UPDATE cursos SET profesor_id = 3 WHERE id = 41;
UPDATE cursos SET profesor_id = 4 WHERE id = 65;
UPDATE cursos SET profesor_id = 4 WHERE id = 66;
UPDATE cursos SET profesor_id = 4 WHERE id = 67;
UPDATE cursos SET profesor_id = 4 WHERE id = 68;
UPDATE cursos SET profesor_id = 4 WHERE id = 69;
UPDATE cursos SET profesor_id = 4 WHERE id = 70;
UPDATE cursos SET profesor_id = 4 WHERE id = 71;
UPDATE cursos SET profesor_id = 4 WHERE id = 72;
UPDATE cursos SET profesor_id = 4 WHERE id = 73;
UPDATE cursos SET profesor_id = 4 WHERE id = 74;
UPDATE cursos SET profesor_id = 4 WHERE id = 75;
UPDATE cursos SET profesor_id = 4 WHERE id = 76;
UPDATE cursos SET profesor_id = 4 WHERE id = 77;
UPDATE cursos SET profesor_id = 4 WHERE id = 78;
UPDATE cursos SET profesor_id = 4 WHERE id = 79;
UPDATE cursos SET profesor_id = 4 WHERE id = 80;
UPDATE cursos SET profesor_id = 5 WHERE id = 112;
UPDATE cursos SET profesor_id = 5 WHERE id = 113;
UPDATE cursos SET profesor_id = 5 WHERE id = 114;
UPDATE cursos SET profesor_id = 5 WHERE id = 115;
UPDATE cursos SET profesor_id = 5 WHERE id = 116;
UPDATE cursos SET profesor_id = 5 WHERE id = 117;
UPDATE cursos SET profesor_id = 5 WHERE id = 118;
UPDATE cursos SET profesor_id = 5 WHERE id = 119;
UPDATE cursos SET profesor_id = 5 WHERE id = 120;
UPDATE cursos SET profesor_id = 5 WHERE id = 121;
UPDATE cursos SET profesor_id = 5 WHERE id = 122;
UPDATE cursos SET profesor_id = 5 WHERE id = 123;
UPDATE cursos SET profesor_id = 5 WHERE id = 124;
UPDATE cursos SET profesor_id = 6 WHERE id = 150;
UPDATE cursos SET profesor_id = 6 WHERE id = 151;
UPDATE cursos SET profesor_id = 6 WHERE id = 152;
UPDATE cursos SET profesor_id = 6 WHERE id = 153;
UPDATE cursos SET profesor_id = 6 WHERE id = 154;
UPDATE cursos SET profesor_id = 6 WHERE id = 155;
UPDATE cursos SET profesor_id = 6 WHERE id = 156;
UPDATE cursos SET profesor_id = 6 WHERE id = 157;
UPDATE cursos SET profesor_id = 6 WHERE id = 158;
UPDATE cursos SET profesor_id = 6 WHERE id = 159;
UPDATE cursos SET profesor_id = 6 WHERE id = 160;
UPDATE cursos SET profesor_id = 6 WHERE id = 161;
UPDATE cursos SET profesor_id = 6 WHERE id = 162;
UPDATE cursos SET profesor_id = 6 WHERE id = 163;
UPDATE cursos SET profesor_id = 6 WHERE id = 164;
UPDATE cursos SET profesor_id = 6 WHERE id = 165;
UPDATE cursos SET profesor_id = 6 WHERE id = 166;
UPDATE cursos SET profesor_id = 6 WHERE id = 167;
UPDATE cursos SET profesor_id = 6 WHERE id = 168;
UPDATE cursos SET profesor_id = 6 WHERE id = 169;
UPDATE cursos SET profesor_id = 6 WHERE id = 170;
UPDATE cursos SET profesor_id = 6 WHERE id = 171;
UPDATE cursos SET profesor_id = 6 WHERE id = 172;
UPDATE cursos SET profesor_id = 7 WHERE id = 196;
UPDATE cursos SET profesor_id = 7 WHERE id = 197;
UPDATE cursos SET profesor_id = 7 WHERE id = 198;
UPDATE cursos SET profesor_id = 7 WHERE id = 199;
UPDATE cursos SET profesor_id = 7 WHERE id = 200;
UPDATE cursos SET profesor_id = 7 WHERE id = 201;
UPDATE cursos SET profesor_id = 7 WHERE id = 202;
UPDATE cursos SET profesor_id = 7 WHERE id = 203;
UPDATE cursos SET profesor_id = 7 WHERE id = 204;
UPDATE cursos SET profesor_id = 7 WHERE id = 205;
UPDATE cursos SET profesor_id = 7 WHERE id = 206;
UPDATE cursos SET profesor_id = 7 WHERE id = 207;
UPDATE cursos SET profesor_id = 7 WHERE id = 208;
UPDATE cursos SET profesor_id = 7 WHERE id = 209;
UPDATE cursos SET profesor_id = 7 WHERE id = 210;
UPDATE cursos SET profesor_id = 7 WHERE id = 211;
UPDATE cursos SET profesor_id = 7 WHERE id = 212;
UPDATE cursos SET profesor_id = 7 WHERE id = 213;
UPDATE cursos SET profesor_id = 7 WHERE id = 214;
UPDATE cursos SET profesor_id = 7 WHERE id = 215;
UPDATE cursos SET profesor_id = 8 WHERE id = 236;
UPDATE cursos SET profesor_id = 8 WHERE id = 237;
UPDATE cursos SET profesor_id = 8 WHERE id = 238;
UPDATE cursos SET profesor_id = 8 WHERE id = 239;
UPDATE cursos SET profesor_id = 8 WHERE id = 240;
UPDATE cursos SET profesor_id = 8 WHERE id = 241;
UPDATE cursos SET profesor_id = 8 WHERE id = 242;
UPDATE cursos SET profesor_id = 8 WHERE id = 243;
UPDATE cursos SET profesor_id = 8 WHERE id = 244;
UPDATE cursos SET profesor_id = 8 WHERE id = 245;
UPDATE cursos SET profesor_id = 8 WHERE id = 246;
UPDATE cursos SET profesor_id = 8 WHERE id = 247;
UPDATE cursos SET profesor_id = 8 WHERE id = 248;
UPDATE cursos SET profesor_id = 8 WHERE id = 249;
UPDATE cursos SET profesor_id = 8 WHERE id = 250;
UPDATE cursos SET profesor_id = 8 WHERE id = 251;
UPDATE cursos SET profesor_id = 8 WHERE id = 252;
UPDATE cursos SET profesor_id = 8 WHERE id = 253;
UPDATE cursos SET profesor_id = 8 WHERE id = 254;
UPDATE cursos SET profesor_id = 8 WHERE id = 255;
UPDATE cursos SET profesor_id = 9 WHERE id = 275;
UPDATE cursos SET profesor_id = 9 WHERE id = 276;
UPDATE cursos SET profesor_id = 9 WHERE id = 277;
UPDATE cursos SET profesor_id = 9 WHERE id = 278;
UPDATE cursos SET profesor_id = 9 WHERE id = 279;
UPDATE cursos SET profesor_id = 9 WHERE id = 280;
UPDATE cursos SET profesor_id = 9 WHERE id = 281;
UPDATE cursos SET profesor_id = 9 WHERE id = 282;
UPDATE cursos SET profesor_id = 9 WHERE id = 283;
UPDATE cursos SET profesor_id = 9 WHERE id = 284;
UPDATE cursos SET profesor_id = 9 WHERE id = 285;
UPDATE cursos SET profesor_id = 9 WHERE id = 286;
UPDATE cursos SET profesor_id = 9 WHERE id = 287;
UPDATE cursos SET profesor_id = 9 WHERE id = 288;
UPDATE cursos SET profesor_id = 9 WHERE id = 289;
UPDATE cursos SET profesor_id = 9 WHERE id = 290;
UPDATE cursos SET profesor_id = 9 WHERE id = 291;
UPDATE cursos SET profesor_id = 9 WHERE id = 292;
UPDATE cursos SET profesor_id = 9 WHERE id = 293;
UPDATE cursos SET profesor_id = 9 WHERE id = 294;
UPDATE cursos SET profesor_id = 10 WHERE id = 11;
UPDATE cursos SET profesor_id = 10 WHERE id = 12;
UPDATE cursos SET profesor_id = 10 WHERE id = 13;
UPDATE cursos SET profesor_id = 10 WHERE id = 14;
UPDATE cursos SET profesor_id = 10 WHERE id = 15;
UPDATE cursos SET profesor_id = 10 WHERE id = 16;
UPDATE cursos SET profesor_id = 10 WHERE id = 17;
UPDATE cursos SET profesor_id = 10 WHERE id = 18;
UPDATE cursos SET profesor_id = 10 WHERE id = 19;
UPDATE cursos SET profesor_id = 10 WHERE id = 20;
UPDATE cursos SET profesor_id = 11 WHERE id = 42;
UPDATE cursos SET profesor_id = 11 WHERE id = 43;
UPDATE cursos SET profesor_id = 11 WHERE id = 44;
UPDATE cursos SET profesor_id = 11 WHERE id = 45;
UPDATE cursos SET profesor_id = 11 WHERE id = 46;
UPDATE cursos SET profesor_id = 11 WHERE id = 47;
UPDATE cursos SET profesor_id = 11 WHERE id = 48;
UPDATE cursos SET profesor_id = 11 WHERE id = 49;
UPDATE cursos SET profesor_id = 11 WHERE id = 50;
UPDATE cursos SET profesor_id = 11 WHERE id = 51;
UPDATE cursos SET profesor_id = 11 WHERE id = 52;
UPDATE cursos SET profesor_id = 11 WHERE id = 53;
UPDATE cursos SET profesor_id = 12 WHERE id = 81;
UPDATE cursos SET profesor_id = 12 WHERE id = 82;
UPDATE cursos SET profesor_id = 12 WHERE id = 83;
UPDATE cursos SET profesor_id = 12 WHERE id = 84;
UPDATE cursos SET profesor_id = 12 WHERE id = 85;
UPDATE cursos SET profesor_id = 12 WHERE id = 86;
UPDATE cursos SET profesor_id = 12 WHERE id = 87;
UPDATE cursos SET profesor_id = 12 WHERE id = 88;
UPDATE cursos SET profesor_id = 12 WHERE id = 89;
UPDATE cursos SET profesor_id = 12 WHERE id = 90;
UPDATE cursos SET profesor_id = 12 WHERE id = 91;
UPDATE cursos SET profesor_id = 12 WHERE id = 92;
UPDATE cursos SET profesor_id = 12 WHERE id = 93;
UPDATE cursos SET profesor_id = 12 WHERE id = 94;
UPDATE cursos SET profesor_id = 12 WHERE id = 95;
UPDATE cursos SET profesor_id = 12 WHERE id = 96;
UPDATE cursos SET profesor_id = 13 WHERE id = 125;
UPDATE cursos SET profesor_id = 13 WHERE id = 126;
UPDATE cursos SET profesor_id = 13 WHERE id = 127;
UPDATE cursos SET profesor_id = 13 WHERE id = 128;
UPDATE cursos SET profesor_id = 13 WHERE id = 129;
UPDATE cursos SET profesor_id = 13 WHERE id = 130;
UPDATE cursos SET profesor_id = 13 WHERE id = 131;
UPDATE cursos SET profesor_id = 13 WHERE id = 132;
UPDATE cursos SET profesor_id = 13 WHERE id = 133;
UPDATE cursos SET profesor_id = 13 WHERE id = 134;
UPDATE cursos SET profesor_id = 13 WHERE id = 135;
UPDATE cursos SET profesor_id = 13 WHERE id = 136;
UPDATE cursos SET profesor_id = 13 WHERE id = 137;
UPDATE cursos SET profesor_id = 14 WHERE id = 173;
UPDATE cursos SET profesor_id = 14 WHERE id = 174;
UPDATE cursos SET profesor_id = 14 WHERE id = 175;
UPDATE cursos SET profesor_id = 14 WHERE id = 176;
UPDATE cursos SET profesor_id = 14 WHERE id = 177;
UPDATE cursos SET profesor_id = 14 WHERE id = 178;
UPDATE cursos SET profesor_id = 14 WHERE id = 179;
UPDATE cursos SET profesor_id = 14 WHERE id = 180;
UPDATE cursos SET profesor_id = 14 WHERE id = 181;
UPDATE cursos SET profesor_id = 14 WHERE id = 182;
UPDATE cursos SET profesor_id = 14 WHERE id = 183;
UPDATE cursos SET profesor_id = 14 WHERE id = 184;
UPDATE cursos SET profesor_id = 14 WHERE id = 185;
UPDATE cursos SET profesor_id = 14 WHERE id = 186;
UPDATE cursos SET profesor_id = 14 WHERE id = 187;
UPDATE cursos SET profesor_id = 14 WHERE id = 188;
UPDATE cursos SET profesor_id = 14 WHERE id = 189;
UPDATE cursos SET profesor_id = 14 WHERE id = 190;
UPDATE cursos SET profesor_id = 14 WHERE id = 191;
UPDATE cursos SET profesor_id = 14 WHERE id = 192;
UPDATE cursos SET profesor_id = 14 WHERE id = 193;
UPDATE cursos SET profesor_id = 14 WHERE id = 194;
UPDATE cursos SET profesor_id = 14 WHERE id = 195;
UPDATE cursos SET profesor_id = 15 WHERE id = 216;
UPDATE cursos SET profesor_id = 15 WHERE id = 217;
UPDATE cursos SET profesor_id = 15 WHERE id = 218;
UPDATE cursos SET profesor_id = 15 WHERE id = 219;
UPDATE cursos SET profesor_id = 15 WHERE id = 220;
UPDATE cursos SET profesor_id = 15 WHERE id = 221;
UPDATE cursos SET profesor_id = 15 WHERE id = 222;
UPDATE cursos SET profesor_id = 15 WHERE id = 223;
UPDATE cursos SET profesor_id = 15 WHERE id = 224;
UPDATE cursos SET profesor_id = 15 WHERE id = 225;
UPDATE cursos SET profesor_id = 15 WHERE id = 226;
UPDATE cursos SET profesor_id = 15 WHERE id = 227;
UPDATE cursos SET profesor_id = 15 WHERE id = 228;
UPDATE cursos SET profesor_id = 15 WHERE id = 229;
UPDATE cursos SET profesor_id = 15 WHERE id = 230;
UPDATE cursos SET profesor_id = 15 WHERE id = 231;
UPDATE cursos SET profesor_id = 15 WHERE id = 232;
UPDATE cursos SET profesor_id = 15 WHERE id = 233;
UPDATE cursos SET profesor_id = 15 WHERE id = 234;
UPDATE cursos SET profesor_id = 15 WHERE id = 235;
UPDATE cursos SET profesor_id = 16 WHERE id = 256;
UPDATE cursos SET profesor_id = 16 WHERE id = 257;
UPDATE cursos SET profesor_id = 16 WHERE id = 258;
UPDATE cursos SET profesor_id = 16 WHERE id = 259;
UPDATE cursos SET profesor_id = 16 WHERE id = 260;
UPDATE cursos SET profesor_id = 16 WHERE id = 261;
UPDATE cursos SET profesor_id = 16 WHERE id = 262;
UPDATE cursos SET profesor_id = 16 WHERE id = 263;
UPDATE cursos SET profesor_id = 16 WHERE id = 264;
UPDATE cursos SET profesor_id = 16 WHERE id = 265;
UPDATE cursos SET profesor_id = 16 WHERE id = 266;
UPDATE cursos SET profesor_id = 16 WHERE id = 267;
UPDATE cursos SET profesor_id = 16 WHERE id = 268;
UPDATE cursos SET profesor_id = 16 WHERE id = 269;
UPDATE cursos SET profesor_id = 16 WHERE id = 270;
UPDATE cursos SET profesor_id = 16 WHERE id = 271;
UPDATE cursos SET profesor_id = 16 WHERE id = 272;
UPDATE cursos SET profesor_id = 16 WHERE id = 273;
UPDATE cursos SET profesor_id = 16 WHERE id = 274;
UPDATE cursos SET profesor_id = 17 WHERE id = 295;
UPDATE cursos SET profesor_id = 17 WHERE id = 296;
UPDATE cursos SET profesor_id = 17 WHERE id = 297;
UPDATE cursos SET profesor_id = 17 WHERE id = 298;
UPDATE cursos SET profesor_id = 17 WHERE id = 299;
UPDATE cursos SET profesor_id = 17 WHERE id = 300;
UPDATE cursos SET profesor_id = 17 WHERE id = 301;
UPDATE cursos SET profesor_id = 17 WHERE id = 302;
UPDATE cursos SET profesor_id = 17 WHERE id = 303;
UPDATE cursos SET profesor_id = 17 WHERE id = 304;
UPDATE cursos SET profesor_id = 17 WHERE id = 305;
UPDATE cursos SET profesor_id = 17 WHERE id = 306;
UPDATE cursos SET profesor_id = 17 WHERE id = 307;
UPDATE cursos SET profesor_id = 17 WHERE id = 308;
UPDATE cursos SET profesor_id = 17 WHERE id = 309;
UPDATE cursos SET profesor_id = 17 WHERE id = 310;
UPDATE cursos SET profesor_id = 17 WHERE id = 311;
UPDATE cursos SET profesor_id = 17 WHERE id = 312;
UPDATE cursos SET profesor_id = 17 WHERE id = 313;
UPDATE cursos SET profesor_id = 18 WHERE id = 21;
UPDATE cursos SET profesor_id = 18 WHERE id = 22;
UPDATE cursos SET profesor_id = 18 WHERE id = 23;
UPDATE cursos SET profesor_id = 18 WHERE id = 24;
UPDATE cursos SET profesor_id = 18 WHERE id = 25;
UPDATE cursos SET profesor_id = 18 WHERE id = 26;
UPDATE cursos SET profesor_id = 18 WHERE id = 27;
UPDATE cursos SET profesor_id = 18 WHERE id = 28;
UPDATE cursos SET profesor_id = 18 WHERE id = 29;
UPDATE cursos SET profesor_id = 19 WHERE id = 54;
UPDATE cursos SET profesor_id = 19 WHERE id = 55;
UPDATE cursos SET profesor_id = 19 WHERE id = 56;
UPDATE cursos SET profesor_id = 19 WHERE id = 57;
UPDATE cursos SET profesor_id = 19 WHERE id = 58;
UPDATE cursos SET profesor_id = 19 WHERE id = 59;
UPDATE cursos SET profesor_id = 19 WHERE id = 60;
UPDATE cursos SET profesor_id = 19 WHERE id = 61;
UPDATE cursos SET profesor_id = 19 WHERE id = 62;
UPDATE cursos SET profesor_id = 19 WHERE id = 63;
UPDATE cursos SET profesor_id = 19 WHERE id = 64;
UPDATE cursos SET profesor_id = 20 WHERE id = 97;
UPDATE cursos SET profesor_id = 20 WHERE id = 98;
UPDATE cursos SET profesor_id = 20 WHERE id = 99;
UPDATE cursos SET profesor_id = 20 WHERE id = 100;
UPDATE cursos SET profesor_id = 20 WHERE id = 101;
UPDATE cursos SET profesor_id = 20 WHERE id = 102;
UPDATE cursos SET profesor_id = 20 WHERE id = 103;
UPDATE cursos SET profesor_id = 20 WHERE id = 104;
UPDATE cursos SET profesor_id = 20 WHERE id = 105;
UPDATE cursos SET profesor_id = 20 WHERE id = 106;
UPDATE cursos SET profesor_id = 20 WHERE id = 107;
UPDATE cursos SET profesor_id = 20 WHERE id = 108;
UPDATE cursos SET profesor_id = 20 WHERE id = 109;
UPDATE cursos SET profesor_id = 20 WHERE id = 110;
UPDATE cursos SET profesor_id = 20 WHERE id = 111;
UPDATE cursos SET profesor_id = 21 WHERE id = 138;
UPDATE cursos SET profesor_id = 21 WHERE id = 139;
UPDATE cursos SET profesor_id = 21 WHERE id = 140;
UPDATE cursos SET profesor_id = 21 WHERE id = 141;
UPDATE cursos SET profesor_id = 21 WHERE id = 142;
UPDATE cursos SET profesor_id = 21 WHERE id = 143;
UPDATE cursos SET profesor_id = 21 WHERE id = 144;
UPDATE cursos SET profesor_id = 21 WHERE id = 145;
UPDATE cursos SET profesor_id = 21 WHERE id = 146;
UPDATE cursos SET profesor_id = 21 WHERE id = 147;
UPDATE cursos SET profesor_id = 21 WHERE id = 148;
UPDATE cursos SET profesor_id = 21 WHERE id = 149;


-- ============================
-- INSCRIPCIONES
-- ============================
INSERT INTO inscripciones (estudiante_id, curso_id, periodo) VALUES
(22, 1, '2026-II'),
(22, 2, '2026-II'),
(23, 32, '2026-II'),
(23, 33, '2026-II'),
(24, 69, '2026-II'),
(24, 70, '2026-II'),
(25, 118, '2026-II'),
(25, 119, '2026-II'),
(26, 158, '2026-II'),
(26, 159, '2026-II'),
(27, 206, '2026-II'),
(27, 207, '2026-II'),
(28, 248, '2026-II'),
(28, 249, '2026-II'),
(29, 289, '2026-II'),
(29, 290, '2026-II'),
(30, 17, '2026-II'),
(30, 18, '2026-II'),
(31, 48, '2026-II'),
(31, 49, '2026-II'),
(32, 85, '2026-II'),
(32, 86, '2026-II'),
(33, 134, '2026-II'),
(33, 135, '2026-II'),
(34, 174, '2026-II'),
(34, 175, '2026-II'),
(35, 222, '2026-II'),
(35, 223, '2026-II'),
(36, 264, '2026-II'),
(36, 265, '2026-II'),
(37, 305, '2026-II'),
(37, 306, '2026-II'),
(38, 4, '2026-II'),
(38, 5, '2026-II'),
(39, 64, '2026-II'),
(39, 30, '2026-II'),
(40, 101, '2026-II'),
(40, 102, '2026-II'),
(41, 112, '2026-II'),
(41, 113, '2026-II'),
(42, 190, '2026-II'),
(42, 191, '2026-II'),
(43, 198, '2026-II'),
(43, 199, '2026-II'),
(44, 241, '2026-II'),
(44, 242, '2026-II'),
(45, 282, '2026-II'),
(45, 283, '2026-II'),
(46, 20, '2026-II'),
(46, 21, '2026-II'),
(47, 45, '2026-II'),
(47, 46, '2026-II'),
(48, 70, '2026-II'),
(48, 71, '2026-II'),
(49, 128, '2026-II'),
(49, 129, '2026-II'),
(50, 160, '2026-II'),
(50, 161, '2026-II'),
(51, 214, '2026-II'),
(51, 215, '2026-II'),
(52, 257, '2026-II'),
(52, 258, '2026-II'),
(53, 298, '2026-II'),
(53, 299, '2026-II'),
(54, 7, '2026-II'),
(54, 8, '2026-II'),
(55, 61, '2026-II'),
(55, 62, '2026-II'),
(56, 86, '2026-II'),
(56, 87, '2026-II'),
(57, 144, '2026-II'),
(57, 145, '2026-II'),
(58, 176, '2026-II'),
(58, 177, '2026-II'),
(59, 230, '2026-II'),
(59, 231, '2026-II'),
(60, 273, '2026-II'),
(60, 274, '2026-II'),
(61, 275, '2026-II'),
(61, 276, '2026-II'),
(62, 23, '2026-II'),
(62, 24, '2026-II'),
(63, 42, '2026-II'),
(63, 43, '2026-II'),
(64, 102, '2026-II'),
(64, 103, '2026-II'),
(65, 122, '2026-II'),
(65, 123, '2026-II'),
(66, 192, '2026-II'),
(66, 193, '2026-II'),
(67, 206, '2026-II'),
(67, 207, '2026-II'),
(68, 250, '2026-II'),
(68, 251, '2026-II'),
(69, 291, '2026-II'),
(69, 292, '2026-II'),
(70, 10, '2026-II'),
(70, 11, '2026-II'),
(71, 58, '2026-II'),
(71, 59, '2026-II'),
(72, 71, '2026-II'),
(72, 72, '2026-II'),
(73, 138, '2026-II'),
(73, 139, '2026-II'),
(74, 162, '2026-II'),
(74, 163, '2026-II'),
(75, 222, '2026-II'),
(75, 223, '2026-II'),
(76, 266, '2026-II'),
(76, 267, '2026-II'),
(77, 307, '2026-II'),
(77, 308, '2026-II'),
(78, 26, '2026-II'),
(78, 27, '2026-II'),
(79, 39, '2026-II'),
(79, 40, '2026-II'),
(80, 87, '2026-II'),
(80, 88, '2026-II'),
(81, 116, '2026-II'),
(81, 117, '2026-II'),
(82, 178, '2026-II'),
(82, 179, '2026-II'),
(83, 198, '2026-II'),
(83, 199, '2026-II'),
(84, 243, '2026-II'),
(84, 244, '2026-II'),
(85, 284, '2026-II'),
(85, 285, '2026-II'),
(86, 13, '2026-II'),
(86, 14, '2026-II'),
(87, 55, '2026-II'),
(87, 56, '2026-II'),
(88, 103, '2026-II'),
(88, 104, '2026-II'),
(89, 132, '2026-II'),
(89, 133, '2026-II'),
(90, 194, '2026-II'),
(90, 195, '2026-II'),
(91, 214, '2026-II'),
(91, 215, '2026-II'),
(92, 259, '2026-II'),
(92, 260, '2026-II'),
(93, 300, '2026-II'),
(93, 301, '2026-II'),
(94, 29, '2026-II'),
(94, 1, '2026-II'),
(95, 36, '2026-II'),
(95, 37, '2026-II'),
(96, 72, '2026-II'),
(96, 73, '2026-II'),
(97, 148, '2026-II'),
(97, 149, '2026-II'),
(98, 164, '2026-II'),
(98, 165, '2026-II'),
(99, 230, '2026-II'),
(99, 231, '2026-II'),
(100, 236, '2026-II'),
(100, 237, '2026-II'),
(101, 277, '2026-II'),
(101, 278, '2026-II'),
(102, 16, '2026-II'),
(102, 17, '2026-II'),
(103, 52, '2026-II'),
(103, 53, '2026-II'),
(104, 88, '2026-II'),
(104, 89, '2026-II'),
(105, 126, '2026-II'),
(105, 127, '2026-II'),
(106, 180, '2026-II'),
(106, 181, '2026-II'),
(107, 206, '2026-II'),
(107, 207, '2026-II'),
(108, 252, '2026-II'),
(108, 253, '2026-II'),
(109, 293, '2026-II'),
(109, 294, '2026-II'),
(110, 3, '2026-II'),
(110, 4, '2026-II'),
(111, 33, '2026-II'),
(111, 34, '2026-II'),
(112, 104, '2026-II'),
(112, 105, '2026-II'),
(113, 142, '2026-II'),
(113, 143, '2026-II'),
(114, 150, '2026-II'),
(114, 151, '2026-II'),
(115, 222, '2026-II'),
(115, 223, '2026-II'),
(116, 268, '2026-II'),
(116, 269, '2026-II'),
(117, 309, '2026-II'),
(117, 310, '2026-II'),
(118, 19, '2026-II'),
(118, 20, '2026-II'),
(119, 49, '2026-II'),
(119, 50, '2026-II'),
(120, 73, '2026-II'),
(120, 74, '2026-II'),
(121, 120, '2026-II'),
(121, 121, '2026-II'),
(122, 166, '2026-II'),
(122, 167, '2026-II'),
(123, 198, '2026-II'),
(123, 199, '2026-II'),
(124, 245, '2026-II'),
(124, 246, '2026-II'),
(125, 286, '2026-II'),
(125, 287, '2026-II'),
(126, 6, '2026-II'),
(126, 7, '2026-II'),
(127, 30, '2026-II'),
(127, 31, '2026-II'),
(128, 89, '2026-II'),
(128, 90, '2026-II'),
(129, 136, '2026-II'),
(129, 137, '2026-II'),
(130, 182, '2026-II'),
(130, 183, '2026-II'),
(131, 214, '2026-II'),
(131, 215, '2026-II'),
(132, 261, '2026-II'),
(132, 262, '2026-II'),
(133, 302, '2026-II'),
(133, 303, '2026-II'),
(134, 22, '2026-II'),
(134, 23, '2026-II'),
(135, 46, '2026-II'),
(135, 47, '2026-II'),
(136, 105, '2026-II'),
(136, 106, '2026-II'),
(137, 114, '2026-II'),
(137, 115, '2026-II'),
(138, 152, '2026-II'),
(138, 153, '2026-II'),
(139, 230, '2026-II'),
(139, 231, '2026-II'),
(140, 238, '2026-II'),
(140, 239, '2026-II'),
(141, 279, '2026-II'),
(141, 280, '2026-II'),
(142, 9, '2026-II'),
(142, 10, '2026-II'),
(143, 62, '2026-II'),
(143, 63, '2026-II'),
(144, 74, '2026-II'),
(144, 75, '2026-II'),
(145, 130, '2026-II'),
(145, 131, '2026-II'),
(146, 168, '2026-II'),
(146, 169, '2026-II'),
(147, 206, '2026-II'),
(147, 207, '2026-II'),
(148, 254, '2026-II'),
(148, 255, '2026-II'),
(149, 295, '2026-II'),
(149, 296, '2026-II'),
(150, 25, '2026-II'),
(150, 26, '2026-II'),
(151, 43, '2026-II'),
(151, 44, '2026-II'),
(152, 90, '2026-II'),
(152, 91, '2026-II'),
(153, 146, '2026-II'),
(153, 147, '2026-II'),
(154, 184, '2026-II'),
(154, 185, '2026-II'),
(155, 222, '2026-II'),
(155, 223, '2026-II'),
(156, 270, '2026-II'),
(156, 271, '2026-II'),
(157, 311, '2026-II'),
(157, 312, '2026-II'),
(158, 12, '2026-II'),
(158, 13, '2026-II'),
(159, 59, '2026-II'),
(159, 60, '2026-II'),
(160, 106, '2026-II'),
(160, 107, '2026-II'),
(161, 124, '2026-II'),
(161, 125, '2026-II'),
(162, 154, '2026-II'),
(162, 155, '2026-II'),
(163, 198, '2026-II'),
(163, 199, '2026-II'),
(164, 247, '2026-II'),
(164, 248, '2026-II'),
(165, 288, '2026-II'),
(165, 289, '2026-II'),
(166, 28, '2026-II'),
(166, 29, '2026-II'),
(167, 40, '2026-II'),
(167, 41, '2026-II'),
(168, 75, '2026-II'),
(168, 76, '2026-II'),
(169, 140, '2026-II'),
(169, 141, '2026-II'),
(170, 170, '2026-II'),
(170, 171, '2026-II'),
(171, 214, '2026-II'),
(171, 215, '2026-II'),
(172, 263, '2026-II'),
(172, 264, '2026-II'),
(173, 304, '2026-II'),
(173, 305, '2026-II'),
(174, 15, '2026-II'),
(174, 16, '2026-II'),
(175, 56, '2026-II'),
(175, 57, '2026-II'),
(176, 91, '2026-II'),
(176, 92, '2026-II'),
(177, 118, '2026-II'),
(177, 119, '2026-II'),
(178, 186, '2026-II'),
(178, 187, '2026-II'),
(179, 230, '2026-II'),
(179, 231, '2026-II'),
(180, 240, '2026-II'),
(180, 241, '2026-II'),
(181, 281, '2026-II'),
(181, 282, '2026-II'),
(182, 2, '2026-II'),
(182, 3, '2026-II'),
(183, 37, '2026-II'),
(183, 38, '2026-II'),
(184, 107, '2026-II'),
(184, 108, '2026-II'),
(185, 134, '2026-II'),
(185, 135, '2026-II'),
(186, 156, '2026-II'),
(186, 157, '2026-II'),
(187, 206, '2026-II'),
(187, 207, '2026-II'),
(188, 256, '2026-II'),
(188, 257, '2026-II'),
(189, 297, '2026-II'),
(189, 298, '2026-II'),
(190, 18, '2026-II'),
(190, 19, '2026-II'),
(191, 53, '2026-II'),
(191, 54, '2026-II'),
(192, 76, '2026-II'),
(192, 77, '2026-II'),
(193, 112, '2026-II'),
(193, 113, '2026-II'),
(194, 172, '2026-II'),
(194, 173, '2026-II'),
(195, 222, '2026-II'),
(195, 223, '2026-II'),
(196, 272, '2026-II'),
(196, 273, '2026-II'),
(197, 313, '2026-II'),
(197, 275, '2026-II'),
(198, 5, '2026-II'),
(198, 6, '2026-II'),
(199, 34, '2026-II'),
(199, 35, '2026-II'),
(200, 92, '2026-II'),
(200, 93, '2026-II'),
(201, 128, '2026-II'),
(201, 129, '2026-II'),
(202, 188, '2026-II'),
(202, 189, '2026-II'),
(203, 198, '2026-II'),
(203, 199, '2026-II'),
(204, 249, '2026-II'),
(204, 250, '2026-II'),
(205, 290, '2026-II'),
(205, 291, '2026-II'),
(206, 21, '2026-II'),
(206, 22, '2026-II'),
(207, 50, '2026-II'),
(207, 51, '2026-II'),
(208, 108, '2026-II'),
(208, 109, '2026-II'),
(209, 144, '2026-II'),
(209, 145, '2026-II'),
(210, 158, '2026-II'),
(210, 159, '2026-II'),
(211, 214, '2026-II'),
(211, 215, '2026-II'),
(212, 265, '2026-II'),
(212, 266, '2026-II'),
(213, 306, '2026-II'),
(213, 307, '2026-II'),
(214, 8, '2026-II'),
(214, 9, '2026-II'),
(215, 31, '2026-II'),
(215, 32, '2026-II'),
(216, 77, '2026-II'),
(216, 78, '2026-II'),
(217, 122, '2026-II'),
(217, 123, '2026-II'),
(218, 174, '2026-II'),
(218, 175, '2026-II'),
(219, 230, '2026-II'),
(219, 231, '2026-II'),
(220, 242, '2026-II'),
(220, 243, '2026-II'),
(221, 283, '2026-II'),
(221, 284, '2026-II');

-- Período activo por defecto
INSERT INTO configuracion (clave, valor) VALUES ('periodo_activo', '2026-II')
ON DUPLICATE KEY UPDATE valor = '2026-II';

COMMIT;
