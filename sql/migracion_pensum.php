<?php
require_once __DIR__ . '/../includes/config.php';

$counts = [
    'columns_added' => 0,
    'carreras_updated' => 0,
    'cursos_updated' => 0,
    'calificaciones_table' => 0,
];

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM carreras LIKE 'tipo_periodo'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE carreras ADD COLUMN tipo_periodo ENUM('Trimestre','Cuatrimestre') NOT NULL DEFAULT 'Cuatrimestre' AFTER codigo");
        $counts['columns_added']++;
    }

    $colmap = [
        'trayecto' => "ALTER TABLE cursos ADD COLUMN trayecto INT NOT NULL DEFAULT 1 AFTER horario",
        'creditos' => "ALTER TABLE cursos ADD COLUMN creditos INT NOT NULL DEFAULT 0 AFTER trayecto",
        'tipo'     => "ALTER TABLE cursos ADD COLUMN tipo ENUM('Obligatoria','Electiva') NOT NULL DEFAULT 'Obligatoria' AFTER creditos",
    ];
    foreach ($colmap as $col => $sql) {
        $stmt = $pdo->query("SHOW COLUMNS FROM cursos LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec($sql);
            $counts['columns_added']++;
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS calificaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inscripcion_id INT NOT NULL UNIQUE,
        nota DECIMAL(5,2),
        estatus ENUM('Cursando','Aprobada','Reprobada','Remedial') NOT NULL DEFAULT 'Cursando',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci");
    $counts['calificaciones_table'] = 1;

    $stmtC = $pdo->prepare("UPDATE carreras SET tipo_periodo = ? WHERE codigo = ?");
    $cuatrimestre = ['ENF-TSU','ENF-LIC','FIS-TSU','INF-TSU','ADM-TSU','ADM-LIC','CON-TSU','CON-LIC','RIN-TSU'];
    $trimestre    = ['DIS-TSU','GAS-TSU'];
    foreach ($cuatrimestre as $cod) {
        $stmtC->execute(['Cuatrimestre', $cod]);
        $counts['carreras_updated'] += $stmtC->rowCount();
    }
    foreach ($trimestre as $cod) {
        $stmtC->execute(['Trimestre', $cod]);
        $counts['carreras_updated'] += $stmtC->rowCount();
    }

    $mapeo = [];

    $mapeo['ADM1133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ECI1121'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['EFD1121'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['IBA1133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING1142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LCO1142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LOG1142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT1143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TEI1143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CBO1231'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['EDA1243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING1242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LPR1263'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MAT1243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIN1243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['OSI1232'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SIN1243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DIC1221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['EUN1221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['ICO1221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['PIN1221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['TEA1221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['ELEINF'] = ['trayecto' => 2, 'creditos' => 0, 'tipo' => 'Electiva'];
    $mapeo['ACO1343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ADS1343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['AEM1343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['BDA1343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CMA1342'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EAM1321'] = ['trayecto' => 3, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['EGE1343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['LPR1363'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ADS1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EPR1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['LPR1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MDA1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SERCOM01'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['SOP1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TEI1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TEL1443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ASC1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CPA1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['IOP1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ISO1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['LPR1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PPR1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['RLO1543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAS1616'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['ADM2133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CON2143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EFD2121'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['IBA2133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING2142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LCO2142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT2143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TEI2143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CBO2231'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['CON2243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['COR2233'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ECO2242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING2242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT2243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIN2243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PCT2223'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DIC2221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['EUN2221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['ICO2221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['PIN2221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['TEA2221'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['ELEADM'] = ['trayecto' => 2, 'creditos' => 0, 'tipo' => 'Electiva'];
    $mapeo['AEM2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['APE2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CCO2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CON2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EAM2321'] = ['trayecto' => 3, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['EGE2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MER2343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PDS2322'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['AEF2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['APU2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DME2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EST2442'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['OIN2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PCP2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PDI2443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SERCOM02'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['DLA2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['IME2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MFI2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PPR2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['RIN2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SPA2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TAU2543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAS2616'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['CON8154'] = ['trayecto' => 1, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['EFD8121'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['FAD8123'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING8132'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IRI8133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['LCO8122'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT8143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIN8122'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['CON8243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['FEC8222'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IIN8243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING8232'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LOR8233'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MAT8243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PSG8233'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ATR8332'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EGE8343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['HSI8333'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING8332'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LLA8343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['OCT8333'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PSI8333'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ACC8443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['OMT8443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PLP8443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['RLA8433'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['RSP8433'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SERCOM08'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['SES8433'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SPE8433'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ADN8543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CAD8533'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ELECT8522'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Electiva'];
    $mapeo['ERH8533'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PES8522'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['PPL8543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PPR8522'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['RCO8543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAS86126'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['ADM7133'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CON7143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ECO7142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EFD7121'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['IBA7132'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING7142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LCO7142'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT7143'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CON7243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['COR7232'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['DME7242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ECO7242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ELECON'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Electiva'];
    $mapeo['IBA7232'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING7242'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MAT7243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIN7243'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['AEM7343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CCO7343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CIN7343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DLA7342'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EAM7321'] = ['trayecto' => 3, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['EST7343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MFI7343'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PDS7322'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['AEF7443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CAV7443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CCO7443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DTR7442'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EST7443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['FDA7442'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['PCP7443'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['AFI7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CFI7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EPR7542'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IOP7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PPR7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['RIN7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SPA7543'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAS7616'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['AUD51021'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['BIO51022'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EFD51021'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['ENB51129'] = ['trayecto' => 1, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['ING51042'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LCO51042'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MOR51044'] = ['trayecto' => 1, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['ANH52042'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EMI52129'] = ['trayecto' => 2, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['EPE52022'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IBA52022'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['NUD52021'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['PSG52042'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SAP52021'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['ESE53021'] = ['trayecto' => 3, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['ESP53129'] = ['trayecto' => 3, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['FAR53042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['FFP53042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MIN53042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MIP53042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SDE53042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EMI54129'] = ['trayecto' => 4, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['EMQ54129'] = ['trayecto' => 4, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['FFP54042'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SERCOM54020'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['AAE55063'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ECI55129'] = ['trayecto' => 5, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['EMQ55129'] = ['trayecto' => 5, 'creditos' => 9, 'tipo' => 'Obligatoria'];
    $mapeo['PRC56306'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['ANA61084'] = ['trayecto' => 1, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['EDS61022'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EFD61021'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['INF61022'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING61021'] = ['trayecto' => 1, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['LCO61022'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MFI61042'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['OFI61022'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['AMH62022'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ANA62084'] = ['trayecto' => 2, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['AUD62021'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['MFI62042'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['MIN62042'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['PFI62042'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SNE62043'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['VDF62084'] = ['trayecto' => 2, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['ASA63022'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ASN63044'] = ['trayecto' => 3, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['ESS63042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['FPE63022'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SDE63042'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['TIF63085'] = ['trayecto' => 3, 'creditos' => 5, 'tipo' => 'Obligatoria'];
    $mapeo['VDF63084'] = ['trayecto' => 3, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['ASA64022'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ASN64044'] = ['trayecto' => 4, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['SCR64043'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SERCOM64020'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['TIF64085'] = ['trayecto' => 4, 'creditos' => 5, 'tipo' => 'Obligatoria'];
    $mapeo['VDF64043'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ACR65022'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['FEJ65044'] = ['trayecto' => 5, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['GSS65044'] = ['trayecto' => 5, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['PDH65022'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['TIF65085'] = ['trayecto' => 5, 'creditos' => 5, 'tipo' => 'Obligatoria'];
    $mapeo['PRC66306'] = ['trayecto' => 6, 'creditos' => 6, 'tipo' => 'Obligatoria'];

    $mapeo['IGA4113'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['GAS4113'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['GAS4213'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['GAS4313'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['GAS4413'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['HGU4112'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ETP4122'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['PAB4213'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAA4313'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PGA4513'] = ['trayecto' => 5, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['IBA4113'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EFD4111'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['BCU4212'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Electiva'];
    $mapeo['EVB4312'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Electiva'];
    $mapeo['MLG4322'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ING4112'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING4212'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['FCO4221'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['PSB4213'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PSA4313'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PAS4666'] = ['trayecto' => 6, 'creditos' => 12, 'tipo' => 'Obligatoria'];
    $mapeo['ORI4121'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['CYD4223'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['AOV4323'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TMC4332'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['VYC4412'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['LCO4112'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['BNU4212'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['BAL4223'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TAL4313'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['HSA4232'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MAT4113'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['QAL4213'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIG4332'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DIM4413'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ERC4422'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EST4313'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['ADM4313'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SER4333'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];

    $mapeo['TDI3113'] = ['trayecto' => 1, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['TDI3123'] = ['trayecto' => 1, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['TFO3112'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IBA3113'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['LCO3112'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MAT3113'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['PDS3312'] = ['trayecto' => 1, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['TEI3113'] = ['trayecto' => 1, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TFC3112'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['CGR3213'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DBA3113'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['EFD3111'] = ['trayecto' => 2, 'creditos' => 1, 'tipo' => 'Obligatoria'];
    $mapeo['GEO3122'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['HAR3112'] = ['trayecto' => 2, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['ING3112'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['SIM3212'] = ['trayecto' => 2, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CGR3320'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DAV3123'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DED3213'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['FOT3223'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['HDI3122'] = ['trayecto' => 3, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['TIP3213'] = ['trayecto' => 3, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CGR3330'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['CRE3312'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Electiva'];
    $mapeo['DEA3313'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DGP3123'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MAQ3222'] = ['trayecto' => 4, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['MIN3113'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SIN3332'] = ['trayecto' => 4, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['SER3333'] = ['trayecto' => 4, 'creditos' => 0, 'tipo' => 'Obligatoria'];
    $mapeo['DEM3223'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['DID3333'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['EYA3111'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Obligatoria'];
    $mapeo['IVC3313'] = ['trayecto' => 5, 'creditos' => 4, 'tipo' => 'Obligatoria'];
    $mapeo['PPR3553'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TDE3322'] = ['trayecto' => 5, 'creditos' => 3, 'tipo' => 'Obligatoria'];
    $mapeo['TWE3322'] = ['trayecto' => 5, 'creditos' => 2, 'tipo' => 'Electiva'];
    $mapeo['PAS3666'] = ['trayecto' => 6, 'creditos' => 12, 'tipo' => 'Obligatoria'];

    $stmtU = $pdo->prepare("UPDATE cursos SET trayecto = ?, creditos = ?, tipo = ? WHERE codigo = ?");
    foreach ($mapeo as $codigo => $data) {
        $stmtU->execute([$data['trayecto'], $data['creditos'], $data['tipo'], $codigo]);
        $counts['cursos_updated'] += $stmtU->rowCount();
    }

    echo "✓ Migración completada\n";
    echo "Columnas agregadas/modificadas: {$counts['columns_added']}\n";
    echo "Carreras actualizadas: {$counts['carreras_updated']}\n";
    echo "Cursos actualizados: {$counts['cursos_updated']}\n";
    echo "Tabla calificaciones creada: " . ($counts['calificaciones_table'] ? 'Sí' : 'Ya existía') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
