<?php
require_once __DIR__ . "/includes/header.php";

$resultados = [];

function importar_csv_profesores($pdo, $archivo) {
    $handle = fopen($archivo, "r");
    if (!$handle) return ["ok" => false, "error" => "No se pudo abrir el archivo"];

    $insertados = 0; $errores = []; $fila = 0; $cols = [];
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo)
         VALUES (?, ?, ?, ?, 'profesor', ?, FALSE, TRUE)
         ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo), email = VALUES(email), carrera_id = VALUES(carrera_id)"
    );

    while (($data = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
        $fila++;
        if ($fila === 1) {
            $h = array_map('strtolower', array_map('trim', $data));
            $h[0] = preg_replace('/^\xEF\xBB\xBF/', '', $h[0]);
            $cols['cedula'] = array_search('cedula', $h);
            if ($cols['cedula'] === false) $cols['cedula'] = array_search('cédula', $h);
            $cols['nombre'] = array_search('nombre_completo', $h);
            if ($cols['nombre'] === false) $cols['nombre'] = array_search('nombre', $h);
            $cols['email'] = array_search('email', $h);
            $cols['carrera'] = array_search('carrera_id', $h);
            $cols['password'] = array_search('password', $h);
            if ($cols['cedula'] === false) { $errores[] = "Encabezado: no se encontró columna 'cedula'"; break; }
            continue;
        }
        $cedula = 'V' . preg_replace('/[^0-9]/', '', $data[$cols['cedula']] ?? '');
        $nombre = trim($data[$cols['nombre'] ?? 1] ?? '');
        $email = trim($data[$cols['email'] ?? 2] ?? '');
        $carrera_id = intval($data[$cols['carrera'] ?? 3] ?? 0);
        $hash = password_hash(trim($data[$cols['password'] ?? 4] ?? '12345678'), PASSWORD_DEFAULT);
        if (empty($cedula) || empty($nombre)) { $errores[] = "Fila $fila: cédula o nombre vacío"; continue; }
        try { $stmt->execute([$cedula, $nombre, $email, $carrera_id, $hash]); $insertados++; }
        catch (Exception $e) { $errores[] = "Fila $fila: " . $e->getMessage(); }
    }
    fclose($handle);

    $carreras = $pdo->query("SELECT DISTINCT carrera_id FROM usuarios WHERE rol='profesor' AND activo=1 ORDER BY carrera_id")->fetchAll(PDO::FETCH_COLUMN);
    $asignados = 0;
    foreach ($carreras as $cid) {
        $profs = $pdo->prepare("SELECT id FROM usuarios WHERE rol='profesor' AND activo=1 AND carrera_id = ? ORDER BY id");
        $profs->execute([$cid]); $profs = $profs->fetchAll(PDO::FETCH_COLUMN);
        if (empty($profs)) continue;
        $cursos = $pdo->prepare("SELECT id FROM cursos WHERE carrera_id = ? AND activo = 1 AND profesor_id IS NULL ORDER BY id");
        $cursos->execute([$cid]); $cursos = $cursos->fetchAll(PDO::FETCH_COLUMN);
        if (empty($cursos)) continue;
        $stmt_up = $pdo->prepare("UPDATE cursos SET profesor_id = ? WHERE id = ?");
        foreach ($cursos as $i => $cur_id) {
            $stmt_up->execute([$profs[$i % count($profs)], $cur_id]);
            $asignados++;
        }
    }
    if ($asignados > 0) {
        $errores[] = "[Info] $asignados cursos asignados a profesores automaticamente.";
    }

    return ["ok" => true, "insertados" => $insertados, "errores" => $errores];
}

function importar_csv_estudiantes($pdo, $archivo) {
    $handle = fopen($archivo, "r");
    if (!$handle) return ["ok" => false, "error" => "No se pudo abrir el archivo"];

    $insertados = 0; $errores = []; $fila = 0; $cols = [];
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo)
         VALUES (?, ?, ?, ?, 'estudiante', ?, FALSE, TRUE)
         ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo), email = VALUES(email), carrera_id = VALUES(carrera_id)"
    );

    while (($data = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
        $fila++;
        if ($fila === 1) {
            $h = array_map('strtolower', array_map('trim', $data));
            $h[0] = preg_replace('/^\xEF\xBB\xBF/', '', $h[0]);
            $cols['cedula'] = array_search('cedula', $h);
            if ($cols['cedula'] === false) $cols['cedula'] = array_search('cédula', $h);
            $cols['nombre'] = array_search('nombre_completo', $h);
            if ($cols['nombre'] === false) $cols['nombre'] = array_search('nombre', $h);
            $cols['email'] = array_search('email', $h);
            $cols['carrera'] = array_search('carrera_id', $h);
            $cols['password'] = array_search('password', $h);
            if ($cols['cedula'] === false) { $errores[] = "Encabezado: no se encontró columna 'cedula'"; break; }
            continue;
        }
        $cedula = 'V' . preg_replace('/[^0-9]/', '', $data[$cols['cedula']] ?? '');
        $nombre = trim($data[$cols['nombre'] ?? 1] ?? '');
        $email = trim($data[$cols['email'] ?? 2] ?? '');
        $carrera_id = intval($data[$cols['carrera'] ?? 3] ?? 0);
        $hash = password_hash(trim($data[$cols['password'] ?? 4] ?? '12345678'), PASSWORD_DEFAULT);
        if (empty($cedula) || empty($nombre)) { $errores[] = "Fila $fila: cédula o nombre vacío"; continue; }
        try { $stmt->execute([$cedula, $nombre, $email, $carrera_id, $hash]); $insertados++; }
        catch (Exception $e) { $errores[] = "Fila $fila: " . $e->getMessage(); }
    }
    fclose($handle);
    return ["ok" => true, "insertados" => $insertados, "errores" => $errores];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES["csv_profesores"]) && $_FILES["csv_profesores"]["error"] === UPLOAD_ERR_OK) {
        $resultados[] = ["tipo" => "Profesores", "datos" => importar_csv_profesores($pdo, $_FILES["csv_profesores"]["tmp_name"])];
    }
    if (isset($_FILES["csv_estudiantes"]) && $_FILES["csv_estudiantes"]["error"] === UPLOAD_ERR_OK) {
        $resultados[] = ["tipo" => "Estudiantes", "datos" => importar_csv_estudiantes($pdo, $_FILES["csv_estudiantes"]["tmp_name"])];
    }
    if (!empty($resultados)) {
        $total_errores = 0;
        foreach ($resultados as $r) {
            if ($r['datos']['ok']) $total_errores += count($r['datos']['errores'] ?? []);
        }
        if ($total_errores === 0) {
            registrarAuditoria($pdo, $_SESSION["usuario_id"], "Importar seed completo", implode(', ', array_map(fn($r) => $r['tipo'], $resultados)));
        }
    }
}
?>
<h2 style="color:var(--primary);margin-bottom:20px">📦 Importar Semilla (CSV)</h2>

<p style="font-size:13px;color:#666;margin-bottom:20px">
    Poblar la BD con datos de prueba. Solo para <strong>desarrollo/pruebas</strong>.
    <a href="/seed/ejemplo_profesores.csv" download style="color:var(--primary)">Ejemplo profesores</a> ·
    <a href="/seed/ejemplo_estudiantes.csv" download style="color:var(--primary)">Ejemplo estudiantes</a>
</p>

<?php foreach ($resultados as $r): ?>
    <?php $d = $r['datos']; ?>
    <?php if ($d['ok']): ?>
    <div style="background:#d4edda;color:#155724;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        <strong>✓ <?= $r['tipo'] ?></strong><br>
        Insertados: <strong><?= $d['insertados'] ?></strong>
        <?php if (!empty($d['errores'])): ?>
            <br>Errores: <strong><?= count($d['errores']) ?></strong>
            <ul style="margin:5px 0 0;font-size:12px;max-height:150px;overflow-y:auto;">
            <?php foreach (array_slice($d['errores'], 0, 20) as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">
        <strong>✗ <?= $r['tipo'] ?>:</strong> <?= htmlspecialchars($d['error']) ?>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card" style="margin-bottom:20px">
    <h3 style="color:var(--primary);margin-bottom:10px">1. Profesores</h3>
    <p style="font-size:12px;color:#666;margin-bottom:10px">Columnas: <code>cedula, nombre_completo, email, carrera_id, password</code></p>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="file" name="csv_profesores" accept=".csv" class="form-control" style="display:block;margin-bottom:8px">
        <button type="submit" class="btn btn-primary" style="font-size:13px">Subir Profesores</button>
    </form>
</div>

<div class="card">
    <h3 style="color:var(--primary);margin-bottom:10px">2. Estudiantes</h3>
    <p style="font-size:12px;color:#666;margin-bottom:10px">Columnas: <code>cedula, nombre_completo, email, carrera_id, password</code></p>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="file" name="csv_estudiantes" accept=".csv" class="form-control" style="display:block;margin-bottom:8px">
        <button type="submit" class="btn btn-primary" style="font-size:13px">Subir Estudiantes</button>
    </form>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
