<?php

$password_hash = password_hash('12345678', PASSWORD_DEFAULT);

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

$carreras_tsu = [1, 3, 4, 5, 6, 8, 10, 11];

$prof_csv = fopen(__DIR__ . '/profesores.csv', 'w');
fprintf($prof_csv, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($prof_csv, ['cedula', 'nombre_completo', 'email', 'carrera_id', 'password'], ',', '"', '');

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
    fputcsv($prof_csv, [$ced, $nc, $email, $carrera_id, '12345678'], ',', '"', '');
}
fclose($prof_csv);
echo "✓ seed/profesores.csv (20 profesores)\n";

$est_csv = fopen(__DIR__ . '/estudiantes.csv', 'w');
fprintf($est_csv, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($est_csv, ['cedula', 'nombre_completo', 'email', 'carrera_id', 'password'], ',', '"', '');

$estudiantes = [];
for ($i = 0; $i < 60; $i++) {
    $carrera_id = $carreras_tsu[$i % count($carreras_tsu)];
    $gen = $i % 2 === 0 ? 'm' : 'f';
    $np = $gen === 'm' ? $nombres_m : $nombres_f;
    $nombre = $np[$i % count($np)];
    $a1 = $apellidos[$i % count($apellidos)];
    $a2 = $apellidos[($i + 5) % count($apellidos)];
    $nc = "$nombre $a1 $a2";
    $ced = 'V30' . str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
    $email = 'estudiante' . $i . '@iutecp.edu.ve';
    fputcsv($est_csv, [$ced, $nc, $email, $carrera_id, '12345678'], ',', '"', '');
    $estudiantes[] = ['cedula' => $ced, 'carrera_id' => $carrera_id];
}
fclose($est_csv);
echo "✓ seed/estudiantes.csv (60 estudiantes)\n";

$ins_csv = fopen(__DIR__ . '/inscripciones.csv', 'w');
fprintf($ins_csv, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($ins_csv, ['cedula', 'codigo_materia', 'seccion', 'periodo'], ',', '"', '');

require_once __DIR__ . '/../includes/config.php';
$cursos_por_carrera = [];
foreach ($carreras_tsu as $cid) {
    $stmt = $pdo->prepare("SELECT codigo FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY codigo");
    $stmt->execute([$cid]);
    $cursos_por_carrera[$cid] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$periodo = '2026-II';
$cont = 0;
$carrera_cont = [];

foreach ($estudiantes as $e) {
    if (!isset($carrera_cont[$e['carrera_id']])) $carrera_cont[$e['carrera_id']] = 0;
    $idx = $carrera_cont[$e['carrera_id']]++;
    $seccion = ($idx < 4) ? '1D1' : '1D2';

    $cursos = $cursos_por_carrera[$e['carrera_id']] ?? [];
    $total = count($cursos);
    if ($total < 2) continue;

    $num_cursos = min(3 + ($cont % 3), $total);
    for ($j = 0; $j < $num_cursos; $j++) {
        $codigo = $cursos[($cont + $j) % $total];
        fputcsv($ins_csv, [$e['cedula'], $codigo, $seccion, $periodo], ',', '"', '');
    }
    $cont++;
}
fclose($ins_csv);
echo "✓ seed/inscripciones.csv (inscripciones con seccion)\n";

$notas_csv = fopen(__DIR__ . '/notas.csv', 'w');
fprintf($notas_csv, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($notas_csv, ['cedula', 'codigo_materia', 'seccion', 'periodo', 'nota'], ',', '"', '');

$cont = 0;
$carrera_cont = [];
foreach ($estudiantes as $e) {
    if (!isset($carrera_cont[$e['carrera_id']])) $carrera_cont[$e['carrera_id']] = 0;
    $idx = $carrera_cont[$e['carrera_id']]++;
    $seccion = ($idx < 4) ? '1D1' : '1D2';

    $cursos = $cursos_por_carrera[$e['carrera_id']] ?? [];
    $total = count($cursos);
    if ($total < 2) continue;

    $num_cursos = min(3 + ($cont % 3), $total);
    for ($j = 0; $j < $num_cursos; $j++) {
        $codigo = $cursos[($cont + $j) % $total];
        if ($cont % 5 !== 0) {
            $nota = rand(0, 100);
            fputcsv($notas_csv, [$e['cedula'], $codigo, $seccion, $periodo, $nota], ',', '"', '');
        }
    }
    $cont++;
}
fclose($notas_csv);
echo "✓ seed/notas.csv (notas — archivo de uso continuo)\n";

echo "\n--- Archivos generados en seed/ ---\n";
