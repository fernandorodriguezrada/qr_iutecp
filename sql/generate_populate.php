<?php
/**
 * Genera qr_populate.sql con el pensum real del IUTECP
 *
 * Para carreras CON código real: se usa el código textual del pensum.
 * Para Diseño Gráfico y Gastronomía (sin códigos): se generan CCC+D+T+NN+CR
 *   donde D=3(DIS) o 4(GAS), T=trayecto, NN=correlativo, CR=créditos.
 */

$password_hash = '$2y$10$YbGw7dQwLNrhdRUJ0Cipqupj0c8C4ZmMeieyJF2Jv2uSRm.gVdH5O';

// ============================
// PENSUM: [nombre, codigo, trayecto, creditos, tipo]
// ============================
$pensum = [];

// --- INFORMÁTICA (carrera_id=4) ---
$pensum[4] = [
    ['ADMINISTRACIÓN', 'ADM1133', 1, 3, 'Obligatoria'],
    ['EDUCACIÓN CIUDADANA', 'ECI1121', 1, 1, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD1121', 1, 1, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', 'IBA1133', 1, 3, 'Obligatoria'],
    ['INGLÉS I', 'ING1142', 1, 2, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO1142', 1, 2, 'Obligatoria'],
    ['LÓGICA', 'LOG1142', 1, 2, 'Obligatoria'],
    ['MATEMÁTICA I', 'MAT1143', 1, 3, 'Obligatoria'],
    ['TÉCNICAS DE ESTUDIO E INVESTIGACIÓN', 'TEI1143', 1, 3, 'Obligatoria'],
    ['ELECTIVAS DE INFORMÁTICA', 'ELEINF', 2, 0, 'Electiva'],
    ['CÁTEDRA BOLIVARIANA', 'CBO1231', 2, 1, 'Obligatoria'],
    ['ESTRUCTURA DE DATOS', 'EDA1243', 2, 3, 'Obligatoria'],
    ['INGLÉS II', 'ING1242', 2, 2, 'Obligatoria'],
    ['LENGUAJE DE PROGRAMACIÓN I', 'LPR1263', 2, 3, 'Obligatoria'],
    ['MATEMÁTICA II', 'MAT1243', 2, 3, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN1243', 2, 3, 'Obligatoria'],
    ['ORGANIZACIÓN Y SISTEMAS', 'OSI1232', 2, 2, 'Obligatoria'],
    ['SISTEMAS DE INFORMACIÓN', 'SIN1243', 2, 3, 'Obligatoria'],
    ['ARQUITECTURA DEL COMPUTADOR', 'ACO1343', 3, 3, 'Obligatoria'],
    ['ANÁLISIS Y DISEÑO DE SISTEMAS I', 'ADS1343', 3, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE EMPRESAS', 'AEM1343', 3, 3, 'Obligatoria'],
    ['BASE DE DATOS', 'BDA1343', 3, 3, 'Obligatoria'],
    ['CÁLCULO MATRICIAL', 'CMA1342', 3, 2, 'Obligatoria'],
    ['EDUCACIÓN AMBIENTAL', 'EAM1321', 3, 1, 'Obligatoria'],
    ['ESTADÍSTICA GENERAL', 'EGE1343', 3, 3, 'Obligatoria'],
    ['LENGUAJE DE PROGRAMACIÓN II', 'LPR1363', 3, 3, 'Obligatoria'],
    ['ANÁLISIS Y DISEÑO DE SISTEMAS II', 'ADS1443', 4, 3, 'Obligatoria'],
    ['ESTADÍSTICA PROBABILÍSTICA', 'EPR1443', 4, 3, 'Obligatoria'],
    ['LENGUAJE DE PROGRAMACIÓN III', 'LPR1443', 4, 3, 'Obligatoria'],
    ['MODELO DE DATOS', 'MDA1443', 4, 3, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', 'SERCOM01', 4, 0, 'Obligatoria'],
    ['SISTEMAS OPERATIVOS', 'SOP1443', 4, 3, 'Obligatoria'],
    ['TÓPICOS ESPECIALES DE INFORMÁTICA', 'TEI1443', 4, 3, 'Obligatoria'],
    ['TELEPROCESOS', 'TEL1443', 4, 3, 'Obligatoria'],
    ['AUDITORÍA DE SISTEMA COMPUTARIZADO', 'ASC1543', 5, 3, 'Obligatoria'],
    ['CONTABILIDAD Y PROCESOS ADMINISTRATIVOS', 'CPA1543', 5, 3, 'Obligatoria'],
    ['INVESTIGACIÓN DE OPERACIONES', 'IOP1543', 5, 3, 'Obligatoria'],
    ['INFORMÁTICA SOCIEDAD Y ORGANIZACIÓN', 'ISO1543', 5, 3, 'Obligatoria'],
    ['LENGUAJE DE PROGRAMACIÓN IV', 'LPR1543', 5, 3, 'Obligatoria'],
    ['PLANIFICACIÓN DE PROYECTOS', 'PPR1543', 5, 3, 'Obligatoria'],
    ['REDES LOCALES', 'RLO1543', 5, 3, 'Obligatoria'],
    ['PASANTÍAS', 'PAS1616', 6, 6, 'Obligatoria'],
    ['DIVERSIDAD E IDENTIDAD CULTURAL', 'DIC1221', 2, 1, 'Electiva'],
    ['EDITORIAL UNIVERSITARIA', 'EUN1221', 2, 1, 'Electiva'],
    ['INTERPRETACIÓN CORAL', 'ICO1221', 2, 1, 'Electiva'],
    ['PROTOCOLO INSTITUCIONAL', 'PIN1221', 2, 1, 'Electiva'],
    ['TEATRO', 'TEA1221', 2, 1, 'Electiva'],
];

// --- ADMINISTRACIÓN DE EMPRESAS (carrera_id=6) ---
$pensum[6] = [
    ['ADMINISTRACIÓN', 'ADM2133', 1, 3, 'Obligatoria'],
    ['CONTABILIDAD I', 'CON2143', 1, 3, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD2121', 1, 1, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', 'IBA2133', 1, 3, 'Obligatoria'],
    ['INGLÉS I', 'ING2142', 1, 2, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO2142', 1, 2, 'Obligatoria'],
    ['MATEMÁTICA I', 'MAT2143', 1, 3, 'Obligatoria'],
    ['TÉCNICAS DE ESTUDIO E INVESTIGACIÓN', 'TEI2143', 1, 3, 'Obligatoria'],
    ['ELECTIVAS DE ADMINISTRACIÓN', 'ELEADM', 2, 0, 'Electiva'],
    ['CÁTEDRA BOLIVARIANA', 'CBO2231', 2, 1, 'Obligatoria'],
    ['CONTABILIDAD II', 'CON2243', 2, 3, 'Obligatoria'],
    ['COMPORTAMIENTO ORGANIZACIONAL', 'COR2233', 2, 3, 'Obligatoria'],
    ['ECONOMÍA', 'ECO2242', 2, 2, 'Obligatoria'],
    ['INGLÉS II', 'ING2242', 2, 2, 'Obligatoria'],
    ['MATEMÁTICA II', 'MAT2243', 2, 3, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN2243', 2, 3, 'Obligatoria'],
    ['PROBLEMÁTICA CONTEMPORANEA DE LA CIENCIA Y TECNOLOGÍA', 'PCT2223', 2, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE EMPRESAS', 'AEM2343', 3, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE PERSONAL', 'APE2343', 3, 3, 'Obligatoria'],
    ['CONTABILIDAD DE COSTOS', 'CCO2343', 3, 3, 'Obligatoria'],
    ['CONTABILIDAD III', 'CON2343', 3, 3, 'Obligatoria'],
    ['EDUCACIÓN AMBIENTAL', 'EAM2321', 3, 1, 'Obligatoria'],
    ['ESTADÍSTICA GENERAL', 'EGE2343', 3, 3, 'Obligatoria'],
    ['MERCADEO', 'MER2343', 3, 3, 'Obligatoria'],
    ['PROBLEMÁTICA DEL DESARROLLO SOCIAL Y ECONÓMICO', 'PDS2322', 3, 2, 'Obligatoria'],
    ['ANÁLISIS DE ESTADOS FINANCIEROS', 'AEF2443', 4, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN PÚBLICA', 'APU2443', 4, 3, 'Obligatoria'],
    ['DERECHO MERCANTIL', 'DME2443', 4, 3, 'Obligatoria'],
    ['ESTADÍSTICA I', 'EST2442', 4, 2, 'Obligatoria'],
    ['ORGANIZACIÓN INDUSTRIAL', 'OIN2443', 4, 3, 'Obligatoria'],
    ['PRESUPUESTO Y CONTROL PRESUPUESTARIO', 'PCP2443', 4, 3, 'Obligatoria'],
    ['PROGRAMACIÓN Y DIAGRAMACIÓN', 'PDI2443', 4, 3, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', 'SERCOM02', 4, 0, 'Obligatoria'],
    ['DERECHO LABORAL', 'DLA2543', 5, 3, 'Obligatoria'],
    ['INVESTIGACIÓN DE MERCADO', 'IME2543', 5, 3, 'Obligatoria'],
    ['MATEMÁTICA FINANCIERA', 'MFI2543', 5, 3, 'Obligatoria'],
    ['PLANIFICACIÓN DE PROYECTOS', 'PPR2543', 5, 3, 'Obligatoria'],
    ['RELACIONES INDUSTRIALES', 'RIN2543', 5, 3, 'Obligatoria'],
    ['SISTEMAS Y PROCEDIMIENTOS ADMINISTRATIVOS', 'SPA2543', 5, 3, 'Obligatoria'],
    ['TÉCNICAS DE AUDITORÍAS', 'TAU2543', 5, 3, 'Obligatoria'],
    ['PASANTÍAS', 'PAS2616', 6, 6, 'Obligatoria'],
    ['DIVERSIDAD E IDENTIDAD CULTURAL', 'DIC2221', 2, 1, 'Electiva'],
    ['EDITORIAL UNIVERSITARIA', 'EUN2221', 2, 1, 'Electiva'],
    ['INTERPRETACIÓN CORAL', 'ICO2221', 2, 1, 'Electiva'],
    ['PROTOCOLO INSTITUCIONAL', 'PIN2221', 2, 1, 'Electiva'],
    ['TEATRO', 'TEA2221', 2, 1, 'Electiva'],
];

// --- DISEÑO GRÁFICO (carrera_id=5) — SIN códigos reales ---
// Generados: CCC + 3 + T + NN + CR
function genCode($a, $t, $n, $c) { return $a . '3' . $t . $n . $c; }
$pensum[5] = [
    ['TEORÍA DEL DISEÑO', genCode('TDI',1,1,3), 1, 4, 'Obligatoria'],
    ['TALLER DE DISEÑO INTEGRAL', genCode('TDI',1,2,3), 1, 4, 'Obligatoria'],
    ['TEORÍA Y FUNDAMENTOS DEL COLOR', genCode('TFC',1,1,2), 2, 2, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', genCode('MIN',1,1,3), 4, 2, 'Obligatoria'],
    ['ECOLOGÍA Y AMBIENTE', genCode('EYA',1,1,1), 5, 2, 'Obligatoria'],
    ['TEORÍA DE LA FORMA', genCode('TFO',1,1,2), 1, 2, 'Obligatoria'],
    ['DIBUJO BÁSICO', genCode('DBA',1,1,3), 2, 2, 'Obligatoria'],
    ['DIBUJO AVANZADO', genCode('DAV',1,2,3), 3, 3, 'Obligatoria'],
    ['DISEÑO GRÁFICO PUBLICITARIO', genCode('DGP',1,2,3), 4, 3, 'Obligatoria'],
    ['PLANIFICACIÓN DE PROYECTOS', genCode('PPR',5,5,3), 5, 3, 'Obligatoria'],
    ['TÉCNICAS DE ESTUDIO', genCode('TEI',1,1,3), 1, 3, 'Obligatoria'],
    ['HISTORIA DEL ARTE', genCode('HAR',1,1,2), 2, 2, 'Obligatoria'],
    ['HISTORIA DEL DISEÑO', genCode('HDI',1,2,2), 3, 2, 'Obligatoria'],
    ['CREATIVIDAD (ELECTIVA)', genCode('CRE',3,1,2), 4, 2, 'Electiva'],
    ['TENDENCIAS WEB (ELECTIVA)', genCode('TWE',3,2,2), 5, 2, 'Electiva'],
    ['LENGUAJE Y COMUNICACIÓN', genCode('LCO',1,1,2), 1, 3, 'Obligatoria'],
    ['INGLÉS', genCode('ING',1,1,2), 2, 3, 'Obligatoria'],
    ['DISEÑO EDITORIAL', genCode('DED',2,1,3), 3, 3, 'Obligatoria'],
    ['DISEÑO EDITORIAL AVANZADO', genCode('DEA',3,1,3), 4, 3, 'Obligatoria'],
    ['TENDENCIAS DEL DISEÑO', genCode('TDE',3,2,2), 5, 3, 'Obligatoria'],
    ['PASANTÍAS', genCode('PAS',6,6,6), 6, 12, 'Obligatoria'],
    ['PROB. DEL DESARROLLO SOCIOECONÓMICO', genCode('PDS',3,1,2), 1, 2, 'Obligatoria'],
    ['SEMIÓTICA DE LA IMAGEN', genCode('SIM',2,1,2), 2, 3, 'Obligatoria'],
    ['FOTOGRAFÍA', genCode('FOT',2,2,3), 3, 3, 'Obligatoria'],
    ['SISTEMAS DE IMPRESIÓN', genCode('SIN',3,3,2), 4, 2, 'Obligatoria'],
    ['IDENTIDAD VISUAL CORPORATIVA', genCode('IVC',3,1,3), 5, 4, 'Obligatoria'],
    ['MATEMÁTICAS', genCode('MAT',1,1,3), 1, 3, 'Obligatoria'],
    ['GEOMETRÍA', genCode('GEO',1,2,2), 2, 3, 'Obligatoria'],
    ['TIPOGRAFÍA', genCode('TIP',2,1,3), 3, 3, 'Obligatoria'],
    ['MAQUETERÍA', genCode('MAQ',2,2,2), 4, 3, 'Obligatoria'],
    ['DISEÑO DE EMPAQUES', genCode('DEM',3,2,3), 5, 3, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', genCode('IBA',1,1,3), 1, 2, 'Obligatoria'],
    ['COMPUTACIÓN GRÁFICA I', genCode('CGR',2,1,3), 2, 3, 'Obligatoria'],
    ['COMPUTACIÓN GRÁFICA II', genCode('CGR',3,2,0), 3, 3, 'Obligatoria'],
    ['COMPUTACIÓN GRÁFICA III', genCode('CGR',3,3,0), 4, 3, 'Obligatoria'],
    ['DISEÑO DE INTERFACES DIGITALES', genCode('DID',3,3,3), 5, 3, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTE', genCode('EFD',1,1,1), 2, 1, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', genCode('SER',3,3,3), 4, 0, 'Obligatoria'],
];

// --- GASTRONOMÍA (carrera_id=11) — SIN códigos reales ---
function genCodeG($a, $t, $n, $c) { return $a . '4' . $t . $n . $c; }
$pensum[11] = [
    ['INTRODUCCIÓN A LA GASTRONOMÍA', genCodeG('IGA',1,1,3), 1, 3, 'Obligatoria'],
    ['GASTRONOMÍA I', genCodeG('GAS',1,1,3), 2, 3, 'Obligatoria'],
    ['GASTRONOMÍA II', genCodeG('GAS',2,1,3), 3, 3, 'Obligatoria'],
    ['GASTRONOMÍA III', genCodeG('GAS',3,1,3), 4, 3, 'Obligatoria'],
    ['GASTRONOMÍA IV', genCodeG('GAS',4,1,3), 5, 3, 'Obligatoria'],
    ['HISTORIA DE LA GASTRONOMÍA UNIVERSAL', genCodeG('HGU',1,1,2), 1, 2, 'Obligatoria'],
    ['ETIQUETA Y PROTOCOLO', genCodeG('ETP',1,2,2), 2, 2, 'Obligatoria'],
    ['PANADERÍA BÁSICA', genCodeG('PAB',2,1,3), 3, 3, 'Obligatoria'],
    ['PANADERÍA AVANZADA', genCodeG('PAA',3,1,3), 4, 3, 'Obligatoria'],
    ['PROYECTO GASTRONÓMICO', genCodeG('PGA',5,1,3), 5, 4, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', genCodeG('IBA',1,1,3), 1, 2, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', genCodeG('EFD',1,1,1), 2, 1, 'Obligatoria'],
    ['BASES CULINARIAS (ELECTIVA I)', genCodeG('BCU',2,1,2), 3, 2, 'Electiva'],
    ['EVENTOS Y BANQUETES (ELECTIVA II)', genCodeG('EVB',3,1,2), 4, 2, 'Electiva'],
    ['MARCO LEGAL EN LA GASTRONOMÍA', genCodeG('MLG',3,2,2), 5, 3, 'Obligatoria'],
    ['INGLÉS I', genCodeG('ING',1,1,2), 1, 2, 'Obligatoria'],
    ['INGLÉS II', genCodeG('ING',2,1,2), 2, 3, 'Obligatoria'],
    ['FRANCÉS EN LA COCINA', genCodeG('FCO',2,2,1), 3, 2, 'Obligatoria'],
    ['PASTELERÍA BÁSICA', genCodeG('PSB',2,1,3), 4, 3, 'Obligatoria'],
    ['PASTELERÍA AVANZADA', genCodeG('PSA',3,1,3), 5, 3, 'Obligatoria'],
    ['PASANTÍAS', genCodeG('PAS',6,6,6), 6, 12, 'Obligatoria'],
    ['ORIENTACIÓN', genCodeG('ORI',1,2,1), 1, 2, 'Obligatoria'],
    ['CÁRNICOS Y DERIVADOS', genCodeG('CYD',2,2,3), 2, 3, 'Obligatoria'],
    ['ALIMENTOS DE ORIGEN VEGETAL', genCodeG('AOV',3,2,3), 3, 3, 'Obligatoria'],
    ['TÉCNICAS DE MIXOLOGÍA Y COCTELERÍA', genCodeG('TMC',3,3,2), 4, 3, 'Obligatoria'],
    ['VINICULTURA Y CATA', genCodeG('VYC',4,1,2), 5, 3, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', genCodeG('LCO',1,1,2), 1, 3, 'Obligatoria'],
    ['BASES DE LA NUTRICIÓN', genCodeG('BNU',2,1,2), 2, 3, 'Obligatoria'],
    ['BROMATOLOGÍA DE LOS ALIMENTOS', genCodeG('BAL',2,2,3), 3, 3, 'Obligatoria'],
    ['TECNOLOGÍA DE LOS ALIMENTOS', genCodeG('TAL',3,1,3), 4, 3, 'Obligatoria'],
    ['HIGIENE Y SEGURIDAD ALIMENTARIA', genCodeG('HSA',2,3,2), 5, 3, 'Obligatoria'],
    ['MATEMÁTICAS', genCodeG('MAT',1,1,3), 1, 3, 'Obligatoria'],
    ['QUÍMICA DE LOS ALIMENTOS', genCodeG('QAL',2,1,3), 2, 3, 'Obligatoria'],
    ['MERMA DE LA INDUSTRIA GASTRONÓMICA', genCodeG('MIG',3,3,2), 3, 3, 'Obligatoria'],
    ['DISEÑO E INGENIERÍA DEL MENÚ', genCodeG('DIM',4,1,3), 4, 3, 'Obligatoria'],
    ['ESTANDARIZACIÓN DE RECETAS', genCodeG('ERC',4,2,2), 5, 3, 'Obligatoria'],
    ['ESTADÍSTICA', genCodeG('EST',3,1,3), 2, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE EMPRESAS', genCodeG('ADM',3,1,3), 3, 2, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', genCodeG('SER',3,3,3), 4, 0, 'Obligatoria'],
];

// --- ENFERMERÍA (carrera_id=1) ---
$pensum[1] = [
    ['AUTODESARROLLO', 'AUD51021', 1, 1, 'Obligatoria'],
    ['BIOQUÍMICA', 'BIO51022', 1, 2, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD51021', 1, 1, 'Obligatoria'],
    ['ENFERMERÍA BÁSICA', 'ENB51129', 1, 9, 'Obligatoria'],
    ['INGLÉS INSTRUMENTAL', 'ING51042', 1, 2, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO51042', 1, 2, 'Obligatoria'],
    ['MORFOLOGÍA', 'MOR51044', 1, 4, 'Obligatoria'],
    ['ANATOMÍA HUMANA', 'ANH52042', 2, 2, 'Obligatoria'],
    ['ENFERMERÍA MATERNO INFANTIL I', 'EMI52129', 2, 9, 'Obligatoria'],
    ['ÉTICA PROFESIONAL DE ENFERMERÍA', 'EPE52022', 2, 2, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', 'IBA52022', 2, 1, 'Obligatoria'],
    ['NUTRICIÓN Y DIETÉTICA', 'NUD52021', 2, 1, 'Obligatoria'],
    ['PSICOLOGÍA GENERAL', 'PSG52042', 2, 2, 'Obligatoria'],
    ['SOCIOANTROPOLOGÍA', 'SAP52021', 2, 1, 'Obligatoria'],
    ['ESTADÍSTICA DE SALUD Y EPIDEMIOLOGÍA', 'ESE53021', 3, 1, 'Obligatoria'],
    ['ENFERMERÍA EN SALUD MENTAL Y PSIQUIATRÍA', 'ESP53129', 3, 9, 'Obligatoria'],
    ['FARMACOLOGÍA', 'FAR53042', 3, 2, 'Obligatoria'],
    ['FISIOLOGÍA Y FISIOPATOLOGÍA I', 'FFP53042', 3, 2, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN53042', 3, 2, 'Obligatoria'],
    ['MICROBIOLOGÍA Y PARASITOLOGÍA', 'MIP53042', 3, 2, 'Obligatoria'],
    ['SALUD Y DESARROLLO ECONÓMICO-SOCIAL', 'SDE53042', 3, 2, 'Obligatoria'],
    ['ENFERMERÍA MATERNO INFANTIL II', 'EMI54129', 4, 9, 'Obligatoria'],
    ['ENFERMERÍA MÉDICO QUIRÚRGICO I', 'EMQ54129', 4, 9, 'Obligatoria'],
    ['FISIOLOGÍA Y FISIOPATOLOGÍA II', 'FFP54042', 4, 2, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', 'SERCOM54020', 4, 0, 'Obligatoria'],
    ['ADMINISTRACIÓN DE LA ATENCIÓN DE ENFERMERÍA', 'AAE55063', 5, 3, 'Obligatoria'],
    ['ENFERMERÍA COMUNITARIA E INVESTIGACIÓN APLICADA', 'ECI55129', 5, 9, 'Obligatoria'],
    ['ENFERMERÍA MÉDICO QUIRÚRGICO II', 'EMQ55129', 5, 9, 'Obligatoria'],
    ['PRÁCTICAS CLÍNICAS', 'PRC56306', 6, 6, 'Obligatoria'],
];

// --- FISIOTERAPIA (carrera_id=3) ---
$pensum[3] = [
    ['ANATOMÍA I (SISTEMA OSTEO-ARTICULAR)', 'ANA61084', 1, 4, 'Obligatoria'],
    ['EDUCACIÓN PARA LA SALUD', 'EDS61022', 1, 2, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD61021', 1, 1, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', 'INF61022', 1, 2, 'Obligatoria'],
    ['INGLÉS INSTRUMENTAL', 'ING61021', 1, 1, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO61022', 1, 2, 'Obligatoria'],
    ['MEDIOS FÍSICOS I', 'MFI61042', 1, 2, 'Obligatoria'],
    ['ORIENTACIÓN EN FISIOTERAPIA', 'OFI61022', 1, 2, 'Obligatoria'],
    ['ANÁLISIS DEL MOVIMIENTO HUMANO', 'AMH62022', 2, 2, 'Obligatoria'],
    ['ANATOMÍA II (MIOLOGÍA)', 'ANA62084', 2, 4, 'Obligatoria'],
    ['AUTODESARROLLO', 'AUD62021', 2, 1, 'Obligatoria'],
    ['MEDIOS FÍSICOS II', 'MFI62042', 2, 2, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN62042', 2, 2, 'Obligatoria'],
    ['PRINCIPIOS FÍSICOS', 'PFI62042', 2, 2, 'Obligatoria'],
    ['SISTEMA NERVIOSO', 'SNE62043', 2, 3, 'Obligatoria'],
    ['VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA I', 'VDF62084', 2, 4, 'Obligatoria'],
    ['ALTERACIONES DEL SISTEMA ÓSEO, ARTICULAR Y MUSCULAR I', 'ASA63022', 3, 2, 'Obligatoria'],
    ['ALTERACIONES DEL SISTEMA NERVIOSO I', 'ASN63044', 3, 4, 'Obligatoria'],
    ['ESTADÍSTICA DE SALUD', 'ESS63042', 3, 2, 'Obligatoria'],
    ['FISIOTERAPIA EN PEDIATRÍA', 'FPE63022', 3, 2, 'Obligatoria'],
    ['SALUD Y DESARROLLO ECONÓMICO SOCIAL', 'SDE63042', 3, 2, 'Obligatoria'],
    ['TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA I', 'TIF63085', 3, 5, 'Obligatoria'],
    ['VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA II', 'VDF63084', 3, 4, 'Obligatoria'],
    ['ALTERACIONES DEL SISTEMA ÓSEO, ARTICULAR Y MUSCULAR II', 'ASA64022', 4, 2, 'Obligatoria'],
    ['ALTERACIONES DEL SISTEMA NERVIOSO II', 'ASN64044', 4, 4, 'Obligatoria'],
    ['SISTEMA CARDIO RESPIRATORIO', 'SCR64043', 4, 3, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', 'SERCOM64020', 4, 0, 'Obligatoria'],
    ['TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA II', 'TIF64085', 4, 5, 'Obligatoria'],
    ['VALORACIÓN Y DIAGNÓSTICO EN FISIOTERAPIA III', 'VDF64043', 4, 3, 'Obligatoria'],
    ['ALTERACIONES CARDIO-RESPIRATORIAS', 'ACR65022', 5, 2, 'Obligatoria'],
    ['FISIOLOGÍA DEL EJERCICIO', 'FEJ65044', 5, 4, 'Obligatoria'],
    ['GERENCIA EN LOS SISTEMAS DE SALUD', 'GSS65044', 5, 4, 'Obligatoria'],
    ['PSICOLOGÍA DEL DESARROLLO HUMANO', 'PDH65022', 5, 2, 'Obligatoria'],
    ['TÉCNICAS DE INTERVENCIÓN EN FISIOTERAPIA III', 'TIF65085', 5, 5, 'Obligatoria'],
    ['PRÁCTICAS CLÍNICAS', 'PRC66306', 6, 6, 'Obligatoria'],
];

// --- CONTADURÍA PÚBLICA (carrera_id=8) ---
$pensum[8] = [
    ['ADMINISTRACIÓN', 'ADM7133', 1, 3, 'Obligatoria'],
    ['CONTABILIDAD I', 'CON7143', 1, 3, 'Obligatoria'],
    ['ECONOMÍA I', 'ECO7142', 1, 2, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD7121', 1, 1, 'Obligatoria'],
    ['INFORMÁTICA BÁSICA', 'IBA7132', 1, 2, 'Obligatoria'],
    ['INGLÉS I', 'ING7142', 1, 2, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO7142', 1, 2, 'Obligatoria'],
    ['MATEMÁTICA I', 'MAT7143', 1, 3, 'Obligatoria'],
    ['CONTABILIDAD II', 'CON7243', 2, 3, 'Obligatoria'],
    ['COMPORTAMIENTO ORGANIZACIONAL', 'COR7232', 2, 2, 'Obligatoria'],
    ['DERECHO MERCANTIL', 'DME7242', 2, 2, 'Obligatoria'],
    ['ECONOMÍA II', 'ECO7242', 2, 2, 'Obligatoria'],
    ['ELECTIVA', 'ELECON', 2, 1, 'Electiva'],
    ['INFORMÁTICA II', 'IBA7232', 2, 2, 'Obligatoria'],
    ['INGLÉS II', 'ING7242', 2, 2, 'Obligatoria'],
    ['MATEMÁTICA II', 'MAT7243', 2, 3, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN7243', 2, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE EMPRESAS', 'AEM7343', 3, 3, 'Obligatoria'],
    ['CONTABILIDAD DE COSTOS I', 'CCO7343', 3, 3, 'Obligatoria'],
    ['CONTABILIDAD INTERMEDIA', 'CIN7343', 3, 3, 'Obligatoria'],
    ['DERECHO LABORAL', 'DLA7342', 3, 2, 'Obligatoria'],
    ['EDUCACIÓN AMBIENTAL', 'EAM7321', 3, 1, 'Obligatoria'],
    ['ESTADÍSTICA I', 'EST7343', 3, 3, 'Obligatoria'],
    ['MATEMÁTICA FINANCIERA', 'MFI7343', 3, 3, 'Obligatoria'],
    ['PROBLEMÁTICA DEL DESARROLLO SOCIAL Y ECONÓMICO', 'PDS7322', 3, 2, 'Obligatoria'],
    ['ANÁLISIS DE ESTADOS FINANCIEROS', 'AEF7443', 4, 3, 'Obligatoria'],
    ['CONTABILIDAD AVANZADA', 'CAV7443', 4, 3, 'Obligatoria'],
    ['CONTABILIDAD DE COSTOS II', 'CCO7443', 4, 3, 'Obligatoria'],
    ['DERECHO TRIBUTARIO', 'DTR7442', 4, 2, 'Obligatoria'],
    ['ESTADÍSTICA II', 'EST7443', 4, 3, 'Obligatoria'],
    ['FUNDAMENTOS DE LA AUDITORÍA', 'FDA7442', 4, 2, 'Obligatoria'],
    ['PRESUPUESTO Y CONTROL PRESUPUESTARIO', 'PCP7443', 4, 3, 'Obligatoria'],
    ['AUDITORÍA FINANCIERA', 'AFI7543', 5, 3, 'Obligatoria'],
    ['CONTABILIDAD FISCAL', 'CFI7543', 5, 3, 'Obligatoria'],
    ['ÉTICA PROFESIONAL', 'EPR7542', 5, 2, 'Obligatoria'],
    ['INVESTIGACIÓN DE OPERACIONES', 'IOP7543', 5, 3, 'Obligatoria'],
    ['PLANIFICACIÓN DE PROYECTOS', 'PPR7543', 5, 3, 'Obligatoria'],
    ['RELACIONES INDUSTRIALES', 'RIN7543', 5, 3, 'Obligatoria'],
    ['SISTEMAS Y PROCEDIMIENTOS ADMINISTRATIVOS', 'SPA7543', 5, 3, 'Obligatoria'],
    ['PASANTÍAS', 'PAS7616', 6, 6, 'Obligatoria'],
];

// --- RELACIONES INDUSTRIALES (carrera_id=10) ---
$pensum[10] = [
    ['CONTABILIDAD I', 'CON8154', 1, 4, 'Obligatoria'],
    ['EDUCACIÓN FÍSICA Y DEPORTES', 'EFD8121', 1, 1, 'Obligatoria'],
    ['FUNDAMENTOS DE ADMINISTRACIÓN', 'FAD8123', 1, 3, 'Obligatoria'],
    ['INGLÉS I', 'ING8132', 1, 2, 'Obligatoria'],
    ['INTRODUCCIÓN A LAS RELACIONES INDUSTRIALES', 'IRI8133', 1, 3, 'Obligatoria'],
    ['LENGUAJE Y COMUNICACIÓN', 'LCO8122', 1, 2, 'Obligatoria'],
    ['MATEMÁTICA I', 'MAT8143', 1, 3, 'Obligatoria'],
    ['METODOLOGÍA DE LA INVESTIGACIÓN', 'MIN8122', 1, 2, 'Obligatoria'],
    ['CONTABILIDAD II', 'CON8243', 2, 3, 'Obligatoria'],
    ['FUNDAMENTOS DE ECONOMÍA', 'FEC8222', 2, 2, 'Obligatoria'],
    ['INTRODUCCIÓN A LA INFORMÁTICA', 'IIN8243', 2, 3, 'Obligatoria'],
    ['INGLÉS II', 'ING8232', 2, 2, 'Obligatoria'],
    ['LEGISLACIÓN ORGANIZACIONAL', 'LOR8233', 2, 3, 'Obligatoria'],
    ['MATEMÁTICA II', 'MAT8243', 2, 3, 'Obligatoria'],
    ['PSICOLOGÍA GENERAL Y SOCIAL', 'PSG8233', 2, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN TRIBUTARIA', 'ATR8332', 3, 2, 'Obligatoria'],
    ['ESTADÍSTICA GENERAL', 'EGE8343', 3, 3, 'Obligatoria'],
    ['HIGIENE Y SEGURIDAD INDUSTRIAL', 'HSI8333', 3, 3, 'Obligatoria'],
    ['INGLÉS III', 'ING8332', 3, 2, 'Obligatoria'],
    ['LEGISLACIÓN LABORAL', 'LLA8343', 3, 3, 'Obligatoria'],
    ['ORGANIZACIÓN CIENTÍFICA DEL TRABAJO', 'OCT8333', 3, 3, 'Obligatoria'],
    ['PSICOLOGÍA INDUSTRIAL', 'PSI8333', 3, 3, 'Obligatoria'],
    ['ANÁLISIS Y CLASIFICACIÓN DE CARGOS', 'ACC8443', 4, 3, 'Obligatoria'],
    ['ORGANIZACIÓN Y MÉTODOS DEL TRABAJO', 'OMT8443', 4, 3, 'Obligatoria'],
    ['PLANIFICACIÓN Y PRESUPUESTO', 'PLP8443', 4, 3, 'Obligatoria'],
    ['RELACIONES LABORALES', 'RLA8433', 4, 3, 'Obligatoria'],
    ['RECLUTAMIENTO Y SELECCIÓN DE PERSONAL', 'RSP8433', 4, 3, 'Obligatoria'],
    ['SERVICIO COMUNITARIO', 'SERCOM08', 4, 0, 'Obligatoria'],
    ['SEGURIDAD SOCIAL', 'SES8433', 4, 3, 'Obligatoria'],
    ['SUPERVISIÓN DE PERSONAL', 'SPE8433', 4, 3, 'Obligatoria'],
    ['ADMINISTRACIÓN DE NÓMINA', 'ADN8543', 5, 3, 'Obligatoria'],
    ['CAPACITACIÓN Y ADIESTRAMIENTO', 'CAD8533', 5, 3, 'Obligatoria'],
    ['ELECTIVA RELACIONES INDUSTRIALES', 'ELECT8522', 5, 2, 'Electiva'],
    ['EVALUACIÓN DE LOS RECURSOS HUMANOS', 'ERH8533', 5, 3, 'Obligatoria'],
    ['PLANIFICACIÓN ESTRATÉGICA DE RECURSOS HUMANOS', 'PES8522', 5, 2, 'Obligatoria'],
    ['PROCEDIMIENTOS Y PRÁCTICAS LABORALES', 'PPL8543', 5, 3, 'Obligatoria'],
    ['PLANIFICACIÓN DE PROYECTOS', 'PPR8522', 5, 2, 'Obligatoria'],
    ['REGISTRO Y CONTROL', 'RCO8543', 5, 3, 'Obligatoria'],
    ['PASANTÍAS', 'PAS86126', 6, 6, 'Obligatoria'],
];

function escape($s) { return str_replace("'", "''", $s); }

// ============================
// GENERAR SQL
// ============================

$sql = "-- ============================================
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
";

$carreras_nombre = [
    1 => 'Enfermería', 3 => 'Fisioterapia', 4 => 'Informática',
    5 => 'Diseño Gráfico', 6 => 'Administración de Empresas',
    8 => 'Contaduría Pública', 10 => 'Relaciones Industriales', 11 => 'Gastronomía',
];

$carreras_tsu = [1, 3, 4, 5, 6, 8, 10, 11];

// Track course IDs per carrera for inscripciones
$curso_ids_por_carrera = [];
$curso_id = 1;

foreach ($carreras_tsu as $cid) {
    $cursos = $pensum[$cid];
    $sql .= "\n-- {$carreras_nombre[$cid]} (carrera_id=$cid)\n";
    $sql .= "INSERT INTO cursos (nombre, codigo, carrera_id, trayecto, creditos, tipo, activo) VALUES\n";
    $parts = [];
    $ids = [];
    foreach ($cursos as $c) {
        $parts[] = "('" . escape($c[0]) . "', '{$c[1]}', $cid, {$c[2]}, {$c[3]}, '{$c[4]}', TRUE)";
        $ids[] = $curso_id;
        $curso_id++;
    }
    $sql .= implode(",\n", $parts) . ";\n";
    $curso_ids_por_carrera[$cid] = $ids;
}

$hash = $password_hash;
$nombres_m = [
    'Luis','Carlos','Jorge','Pedro','Miguel','José','Andrés','Antonio',
    'Manuel','David','Francisco','Rafael','Ricardo','Héctor','Marcos',
    'Diego','Alejandro','Daniel','Javier','Pablo','Víctor','Hugo',
    'Raúl','Rubén','Sergio','Fernando','Alberto','Eduardo','Julián','Gustavo',
    'Adrián','Arturo','César','Marco','Nicolás','Omar','Samuel','Simón',
    'Óscar','Iván','Wilmer','Edgar','Saúl','Moisés','Jairo','Elías','Ángel',
];
$nombres_f = [
    'María','Ana','Carmen','Laura','Julia','Sofía','Valentina','Isabella',
    'Gabriela','Daniela','Luciana','Camila','Victoria','Elena','Paula',
    'Andrea','Natalia','Verónica','Patricia','Rosa','Teresa','Silvia',
    'Lorena','Adriana','Mariana','Alejandra','Claudia','Mónica','Cristina',
    'Beatriz','Gloria','Martha','Nelly','Yolanda','Miriam','Sandra',
    'Liliana','Marisol','Xiomara','Belkis','Yusmari','Oriana',
];
$apellidos = [
    'García','Rodríguez','Martínez','López','Hernández','González',
    'Pérez','Sánchez','Ramírez','Torres','Flores','Rivera','Gómez',
    'Díaz','Reyes','Morales','Ortiz','Mendoza','Castillo','Moreno',
    'Jiménez','Acosta','Medina','Molina','Rivas','Quintero','Cruz',
    'Peña','Carrillo','Salazar','Navarro','Paredes','Rincón',
    'Vargas','Cordero','Arias','Herrera','Cabrera','Uzcátegui',
    'Colmenares','Pacheco','Briceno','Chacón','Henríquez','Velásquez',
];

$sql .= "\n\n-- ============================\n-- PROFESORES (20)\n-- ============================\n";
$sql .= "INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES\n";
$prof_data = [];
for ($i = 0; $i < 20; $i++) {
    $carrera_id = $carreras_tsu[$i % count($carreras_tsu)];
    $gen = $i % 2 === 0 ? 'm' : 'f';
    $np = $gen === 'm' ? $nombres_m : $nombres_f;
    $nombre = $np[$i % count($np)];
    $a1 = $apellidos[$i % count($apellidos)];
    $a2 = $apellidos[($i + 10) % count($apellidos)];
    $nc = "$nombre $a1 $a2";
    $ced = 'V20' . str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
    $email = 'profesor' . $i . '@iutecp.edu.ve';
    $prof_data[] = [$nc, $carrera_id, $ced];
}
$parts = [];
foreach ($prof_data as $p) {
    $parts[] = "('{$p[2]}', '" . escape($p[0]) . "', 'profesor{$i}@iutecp.edu.ve', {$p[1]}, 'profesor', '$hash', FALSE, TRUE)";
}
$sql .= implode(",\n", $parts) . ";\n";

$sql .= "\n\n-- ============================\n-- ESTUDIANTES (200)\n-- ============================\n";
$sql .= "INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES\n";
$est_data = [];
for ($i = 0; $i < 200; $i++) {
    $carrera_id = $carreras_tsu[$i % count($carreras_tsu)];
    $gen = $i % 2 === 0 ? 'm' : 'f';
    $np = $gen === 'm' ? $nombres_m : $nombres_f;
    $nombre = $np[$i % count($np)];
    $a1 = $apellidos[$i % count($apellidos)];
    $a2 = $apellidos[($i + 5) % count($apellidos)];
    $nc = "$nombre $a1 $a2";
    $ced = 'V30' . str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
    $email = 'estudiante' . $i . '@iutecp.edu.ve';
    $est_data[] = [$nc, $carrera_id, $ced, $email];
}
$parts = [];
foreach ($est_data as $e) {
    $parts[] = "('{$e[2]}', '" . escape($e[0]) . "', '{$e[3]}', {$e[1]}, 'estudiante', '$hash', FALSE, TRUE)";
}
$sql .= implode(",\n", $parts) . ";\n";

// ============================
// ASIGNAR PROFESORES Y CREAR INSCRIPCIONES
// ============================
$sql .= "\n\n-- ============================\n-- ASIGNAR PROFESORES A CURSOS\n-- ============================\n";

$prof_id = 2;
$carrera_prof_count = [];
foreach ($carreras_tsu as $cid) { $carrera_prof_count[$cid] = 0; }
foreach ($prof_data as $p) { $carrera_prof_count[$p[1]]++; }
$carrera_prof_used = [];
foreach ($carreras_tsu as $cid) { $carrera_prof_used[$cid] = 0; }

// Assign ALL courses to professors (round-robin)
foreach ($prof_data as $p) {
    $cid = $p[1];
    $ids = $curso_ids_por_carrera[$cid] ?? [];
    $total = count($ids);
    $n_profs = max(1, $carrera_prof_count[$cid]);
    $prof_idx = $carrera_prof_used[$cid];
    $carrera_prof_used[$cid]++;

    $per_prof = (int)ceil($total / $n_profs);
    $start = $prof_idx * $per_prof;
    $assign_ids = array_slice($ids, $start, $per_prof);
    foreach ($assign_ids as $curso_asignado_id) {
        $sql .= "UPDATE cursos SET profesor_id = $prof_id WHERE id = {$curso_asignado_id};\n";
    }
    $prof_id++;
}

$sql .= "\n\n-- ============================\n-- INSCRIPCIONES\n-- ============================\n";

// Build a list of course IDs that have professors (reuse same logic)
$cursos_con_prof_por_carrera = [];
$carrera_prof_used2 = [];
foreach ($carreras_tsu as $cid) { $carrera_prof_used2[$cid] = 0; }
foreach ($prof_data as $p) {
    $cid = $p[1];
    $ids = $curso_ids_por_carrera[$cid] ?? [];
    $total = count($ids);
    $n_profs = max(1, $carrera_prof_count[$cid]);
    $prof_idx = $carrera_prof_used2[$cid];
    $carrera_prof_used2[$cid]++;
    $per_prof = (int)ceil($total / $n_profs);
    $start = $prof_idx * $per_prof;
    $assign_ids = array_slice($ids, $start, $per_prof);
    $cursos_con_prof_por_carrera[$cid] = array_merge(
        $cursos_con_prof_por_carrera[$cid] ?? [],
        $assign_ids
    );
}

$sql .= "INSERT INTO inscripciones (estudiante_id, curso_id, periodo) VALUES\n";
$est_id = 22;
$insc_parts = [];
foreach ($est_data as $i => $e) {
    $eid = $est_id + $i;
    $cp = $cursos_con_prof_por_carrera[$e[1]] ?? [];
    $total_cp = count($cp);
    if ($total_cp >= 2) {
        // Each student gets 2 different courses from their carrera (round-robin)
        $c1 = $cp[($i * 2) % $total_cp];
        $c2 = $cp[($i * 2 + 1) % $total_cp];
        $insc_parts[] = "($eid, $c1, '2026-II')";
        if ($c1 !== $c2) {
            $insc_parts[] = "($eid, $c2, '2026-II')";
        }
    }
}
$sql .= implode(",\n", $insc_parts) . ";\n";

$sql .= "\n-- Período activo por defecto\n";
$sql .= "INSERT INTO configuracion (clave, valor) VALUES ('periodo_activo', '2026-II')\n";
$sql .= "ON DUPLICATE KEY UPDATE valor = '2026-II';\n";

$sql .= "\nCOMMIT;\n";

file_put_contents(__DIR__ . '/qr_populate.sql', $sql);
echo "Generated qr_populate.sql (" . count($insc_parts) . " inscripciones)\n";
