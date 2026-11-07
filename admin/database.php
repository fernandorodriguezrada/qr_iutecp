<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

redirigirSiNoAutenticado();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$message = '';
$message_type = '';

// --- IMPORTAR SEED (modal) ---
$seed_resultados = [];

function seed_importar_profesores($pdo, $archivo) {
    $handle = fopen($archivo, "r");
    if (!$handle) return ["ok" => false, "error" => "No se pudo abrir el archivo"];
    $insertados = 0; $errores = []; $fila = 0; $cols = [];
    $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES (?, ?, ?, ?, 'profesor', ?, FALSE, TRUE) ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo), email = VALUES(email), carrera_id = VALUES(carrera_id)");
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
        $cedula = 'V' . preg_replace('/[^0-9]/', '', $data[$cols['cedula']] ?? ''); $nombre = trim($data[$cols['nombre'] ?? 1] ?? ''); $email = trim($data[$cols['email'] ?? 2] ?? ''); $carrera_id = intval($data[$cols['carrera'] ?? 3] ?? 0); $hash = password_hash(trim($data[$cols['password'] ?? 4] ?? '12345678'), PASSWORD_DEFAULT);
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

function seed_importar_estudiantes($pdo, $archivo) {
    $handle = fopen($archivo, "r");
    if (!$handle) return ["ok" => false, "error" => "No se pudo abrir el archivo"];
    $insertados = 0; $errores = []; $fila = 0; $cols = [];
    $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo) VALUES (?, ?, ?, ?, 'estudiante', ?, FALSE, TRUE) ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo), email = VALUES(email), carrera_id = VALUES(carrera_id)");
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
        $cedula = 'V' . preg_replace('/[^0-9]/', '', $data[$cols['cedula']] ?? ''); $nombre = trim($data[$cols['nombre'] ?? 1] ?? ''); $email = trim($data[$cols['email'] ?? 2] ?? ''); $carrera_id = intval($data[$cols['carrera'] ?? 3] ?? 0); $hash = password_hash(trim($data[$cols['password'] ?? 4] ?? '12345678'), PASSWORD_DEFAULT);
        if (empty($cedula) || empty($nombre)) { $errores[] = "Fila $fila: cédula o nombre vacío"; continue; }
        try { $stmt->execute([$cedula, $nombre, $email, $carrera_id, $hash]); $insertados++; }
        catch (Exception $e) { $errores[] = "Fila $fila: " . $e->getMessage(); }
    }
    fclose($handle);
    return ["ok" => true, "insertados" => $insertados, "errores" => $errores];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES["seed_profesores"]) && $_FILES["seed_profesores"]["error"] === UPLOAD_ERR_OK) {
        $seed_resultados[] = ["tipo" => "Profesores", "datos" => seed_importar_profesores($pdo, $_FILES["seed_profesores"]["tmp_name"])];
    }
    if (isset($_FILES["seed_estudiantes"]) && $_FILES["seed_estudiantes"]["error"] === UPLOAD_ERR_OK) {
        $seed_resultados[] = ["tipo" => "Estudiantes", "datos" => seed_importar_estudiantes($pdo, $_FILES["seed_estudiantes"]["tmp_name"])];
    }
    if (!empty($seed_resultados)) {
        $total_errores = 0;
        foreach ($seed_resultados as $r) {
            if ($r['datos']['ok']) $total_errores += count($r['datos']['errores'] ?? []);
        }
        if ($total_errores === 0) {
            registrarAuditoria($pdo, $_SESSION["usuario_id"], "Importar seed completo", implode(', ', array_map(fn($r) => $r['tipo'], $seed_resultados)));
        }
    }
}

$seed_open = !empty($seed_resultados);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'optimize') {
        $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '" . DB_NAME . "'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        $optimized = 0;
        foreach ($tables as $table) {
            $o = $pdo->query("OPTIMIZE TABLE `$table`");
            $o->fetchAll();
            $optimized++;
        }
        $message = "Se optimizaron $optimized tablas correctamente.";
        $message_type = 'success';
        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Optimizar BD', "Optimizadas $optimized tablas");
    }

    if ($action === 'check_integrity') {
        $checks = [];

        $orphaned_inscripciones = $pdo->query("
            SELECT COUNT(*) FROM inscripciones i
            LEFT JOIN usuarios u ON i.estudiante_id = u.id
            WHERE u.id IS NULL
        ")->fetchColumn();
        if ($orphaned_inscripciones > 0) {
            $checks[] = "Inscripciones huérfanas: $orphaned_inscripciones";
        }

        $orphaned_cursos = $pdo->query("
            SELECT COUNT(*) FROM cursos c
            LEFT JOIN usuarios u ON c.profesor_id = u.id
            WHERE c.profesor_id IS NOT NULL AND u.id IS NULL
        ")->fetchColumn();
        if ($orphaned_cursos > 0) {
            $checks[] = "Cursos huérfanos: $orphaned_cursos";
        }

        $orphaned_asistencias = $pdo->query("
            SELECT COUNT(*) FROM asistencias a
            LEFT JOIN inscripciones i ON a.inscripcion_id = i.id
            WHERE i.id IS NULL
        ")->fetchColumn();
        if ($orphaned_asistencias > 0) {
            $checks[] = "Asistencias huérfanas: $orphaned_asistencias";
        }

        $orphaned_sesiones = $pdo->query("
            SELECT COUNT(*) FROM sesiones_qr s
            LEFT JOIN usuarios u ON s.profesor_id = u.id
            WHERE u.id IS NULL
        ")->fetchColumn();
        if ($orphaned_sesiones > 0) {
            $checks[] = "Sesiones QR huérfanas: $orphaned_sesiones";
        }

        if (empty($checks)) {
            $message = "Integridad referencial OK — no se encontraron registros huérfanos.";
        } else {
            $message = "Problemas encontrados:<br>" . implode("<br>", $checks);
            $message_type = 'warning';
        }
        $message_type = $message_type ?: 'success';
        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Verificar integridad BD', $message);
    }

    if ($action === 'clear_audit') {
        $pdo->exec("TRUNCATE TABLE auditoria");
        $message = "Registros de auditoría eliminados.";
        $message_type = 'success';
    }

    if ($action === 'update_periodo') {
        $codigo = $_POST['codigo'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_nuevos = $_POST['fecha_inicio_nuevos'] ?? '';
        if ($codigo && $fecha_inicio) {
            if ($fecha_nuevos) {
                $pdo->prepare("INSERT INTO periodos (codigo, fecha_inicio, fecha_inicio_nuevos) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE fecha_inicio = VALUES(fecha_inicio), fecha_inicio_nuevos = VALUES(fecha_inicio_nuevos)")->execute([$codigo, $fecha_inicio, $fecha_nuevos]);
            } else {
                $pdo->prepare("INSERT INTO periodos (codigo, fecha_inicio, fecha_inicio_nuevos) VALUES (?, ?, NULL) ON DUPLICATE KEY UPDATE fecha_inicio = VALUES(fecha_inicio), fecha_inicio_nuevos = NULL")->execute([$codigo, $fecha_inicio]);
            }
            $message = "Período $codigo actualizado.";
            $message_type = 'success';
            registrarAuditoria($pdo, $_SESSION['usuario_id'], "Actualizar período", "$codigo: inicio $fecha_inicio, nuevos $fecha_nuevos");
        }
    }
}

$periodos = $pdo->query("SELECT * FROM periodos ORDER BY codigo DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';

$tables = $pdo->query("SHOW TABLE STATUS FROM `db`")->fetchAll();

$audit_log = $pdo->query("
    SELECT a.*, u.nombre_completo
    FROM auditoria a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 100
")->fetchAll();

$db_size = $pdo->query("
    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.tables
    WHERE table_schema = 'db'
")->fetchColumn() ?: 0;

$total_administradores = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn();
$total_estudiantes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='estudiante' AND activo=1")->fetchColumn();
$total_profesores = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='profesor' AND activo=1")->fetchColumn();
?>
<div id="admin-dashboard">
    <h2 style="color:var(--primary);margin-bottom:10px">Base de Datos</h2>
    <p style="margin-bottom:20px;color:#666">Estado, mantenimiento y administración de la base de datos</p>

    <?php if ($message): ?>
    <div class="card" style="margin-bottom:20px;border-left:4px solid <?= $message_type === 'success' ? '#28a745' : '#e67e22' ?>;background:<?= $message_type === 'success' ? '#f0fff4' : '#fffbf0' ?>">
        <p style="margin:0;color:<?= $message_type === 'success' ? '#155724' : '#856404' ?>"><?= $message ?></p>
    </div>
    <?php endif; ?>

    <div class="dashboard-cards" style="margin-bottom:20px">
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Tamaño Total</p>
            <p style="font-size:24px;color:var(--primary);font-weight:bold;margin-top:5px"><?= $db_size ?> MB</p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Tablas</p>
            <p style="font-size:24px;color:var(--secondary);font-weight:bold;margin-top:5px"><?= count($tables) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Registros Auditoría</p>
            <p style="font-size:24px;color:#e67e22;font-weight:bold;margin-top:5px"><?= number_format($pdo->query("SELECT COUNT(*) FROM auditoria")->fetchColumn()) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Motor</p>
            <p style="font-size:18px;color:var(--primary);font-weight:bold;margin-top:5px">InnoDB</p>
        </div>
    </div>

    <div class="dashboard-cards" style="margin-bottom:20px">
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Administradores</p>
            <p style="font-size:24px;color:var(--primary);font-weight:bold;margin-top:5px"><?= number_format($total_administradores) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Estudiantes</p>
            <p style="font-size:24px;color:var(--secondary);font-weight:bold;margin-top:5px"><?= number_format($total_estudiantes) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Profesores</p>
            <p style="font-size:24px;color:var(--primary);font-weight:bold;margin-top:5px"><?= number_format($total_profesores) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Base de Datos</p>
            <a href="https://qr-iutecp.ddev.site:9101/?server=db&username=db&db=db" target="_blank" style="text-decoration:none;display:inline-block;margin-top:8px;padding:4px 12px;background:transparent;color:var(--primary);border:1px solid var(--border-color);border-radius:4px;font-weight:bold;font-size:14px">&gt;&gt;</a>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <h3 style="color:var(--primary);margin-bottom:15px">Estado de Tablas</h3>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th>Filas</th>
                        <th>Motor</th>
                        <th>Collation</th>
                        <th>Tamaño</th>
                        <th>Actualizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $t): ?>
                    <tr>
                        <td style="font-weight:bold"><?= htmlspecialchars($t['Name']) ?></td>
                        <td><?= number_format((int)$t['Rows']) ?></td>
                        <td><?= htmlspecialchars($t['Engine'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($t['Collation'] ?? '-') ?></td>
                        <td><?= round(($t['Data_length'] + $t['Index_length']) / 1024, 1) ?> KB</td>
                        <td><?= $t['Update_time'] ? date('d/m/Y h:i A', strtotime($t['Update_time'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <h3 style="color:var(--primary);margin-bottom:15px">Acciones</h3>
        <div style="display:flex;flex-wrap:wrap;gap:15px">
            <form method="post" style="margin:0" onsubmit="return confirm('¿Descargar respaldo de la base de datos?')">
                <input type="hidden" name="action" value="backup" />
                <button type="button" class="btn btn-primary" onclick="window.location.href='backup_db.php'" style="display:inline-flex;align-items:center;gap:8px">
                    ⬇️ Descargar Respaldo (.sql)
                </button>
            </form>
            <form method="post" style="margin:0" onsubmit="return confirm('¿Optimizar todas las tablas? Esto puede tomar unos segundos.')">
                <input type="hidden" name="action" value="optimize" />
                <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px">
                    🔧 Optimizar Tablas
                </button>
            </form>
            <form method="post" style="margin:0" onsubmit="return confirm('¿Verificar integridad referencial de los datos?')">
                <input type="hidden" name="action" value="check_integrity" />
                <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px">
                    ✅ Verificar Integridad
                </button>
            </form>
            <button type="button" class="btn btn-primary" data-open="modal-seed" style="display:inline-flex;align-items:center;gap:8px">
                🌱 Importar Semilla
            </button>
            <form method="post" style="margin:0" onsubmit="return confirm('¿Eliminar todos los registros de auditoría? Esta acción no se puede deshacer.')">
                <input type="hidden" name="action" value="clear_audit" />
                <button type="submit" class="btn btn-danger" style="display:inline-flex;align-items:center;gap:8px">
                    🗑️ Limpiar Auditoría
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Importar Semilla -->
    <div id="modal-seed" class="modal-overlay<?= $seed_open ? ' active' : '' ?>">
        <div class="modal-card">
            <div class="modal-header">
                <h3>🌱 Importar Semilla</h3>
                <button class="modal-close" data-close="modal-seed">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:#666;margin-bottom:15px">
                    Poblar la BD con datos de prueba. Solo para <strong>desarrollo/pruebas</strong>.
                    <a href="/seed/ejemplo_profesores.csv" download style="color:var(--primary)">Ejemplo profesores</a> ·
                    <a href="/seed/ejemplo_estudiantes.csv" download style="color:var(--primary)">Ejemplo estudiantes</a>
                </p>

                <?php foreach ($seed_resultados as $r): ?>
                    <?php $d = $r['datos']; ?>
                    <?php if ($d['ok']): ?>
                    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:10px;border:1px solid #c3e6cb;font-size:13px">
                        <strong>✓ <?= $r['tipo'] ?></strong> —
                        <?php if (isset($d['insertados'])): ?>Insertados: <?= $d['insertados'] ?><?php endif; ?>
                        <?php if (isset($d['actualizados'])): ?> · Actualizados: <?= $d['actualizados'] ?><?php endif; ?>
                        <?php if (isset($d['procesadas'])): ?>Procesadas: <?= $d['procesadas'] ?><?php endif; ?>
                        <?php if (!empty($d['errores'])): ?><br>Errores: <?= count($d['errores']) ?>
                            <ul style="margin:4px 0 0;font-size:11px;max-height:120px;overflow-y:auto;padding-left:18px">
                            <?php foreach (array_slice($d['errores'], 0, 20) as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:10px;border:1px solid #f5c6cb;font-size:13px">
                        <strong>✗ <?= $r['tipo'] ?>:</strong> <?= htmlspecialchars($d['error']) ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="border:1px solid var(--border-color);border-radius:6px;padding:12px">
                        <strong style="font-size:12px">1. Profesores</strong>
                        <p style="font-size:10px;color:#888;margin:4px 0 8px">cedula, nombre_completo, email, carrera_id, password</p>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="file" name="seed_profesores" accept=".csv" required style="display:block;font-size:11px;margin-bottom:6px;width:100%">
                            <button type="submit" class="btn btn-primary" style="font-size:11px;padding:4px 10px">Subir</button>
                        </form>
                    </div>
                    <div style="border:1px solid var(--border-color);border-radius:6px;padding:12px">
                        <strong style="font-size:12px">2. Estudiantes</strong>
                        <p style="font-size:10px;color:#888;margin:4px 0 8px">cedula, nombre_completo, email, carrera_id, password</p>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="file" name="seed_estudiantes" accept=".csv" required style="display:block;font-size:11px;margin-bottom:6px;width:100%">
                            <button type="submit" class="btn btn-primary" style="font-size:11px;padding:4px 10px">Subir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestión de Períodos -->
    <div class="card" style="margin-bottom:20px">
        <h3 style="color:var(--primary);margin-bottom:15px">📅 Gestión de Períodos</h3>
        <p style="font-size:13px;color:#666;margin-bottom:15px">
            Define las fechas de inicio de cada período. La semana actual se calcula automáticamente
            desde estas fechas. Los estudiantes con <code>es_nuevo_ingreso = 1</code> usan la fecha
            de "nuevos ingresos" si está definida.
        </p>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Inicio Regulares</th>
                        <th>Inicio Nuevo Ingreso</th>
                        <th>Semana Actual</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periodos as $p): ?>
                    <tr>
                        <form method="POST" action="" style="display:contents">
                            <input type="hidden" name="action" value="update_periodo" />
                            <input type="hidden" name="codigo" value="<?= htmlspecialchars($p['codigo']) ?>" />
                            <td style="font-weight:bold"><?= htmlspecialchars($p['codigo']) ?></td>
                            <td><input type="date" name="fecha_inicio" value="<?= htmlspecialchars($p['fecha_inicio']) ?>" required style="padding:4px;border:1px solid var(--border-color);border-radius:3px;font-size:12px" /></td>
                            <td><input type="date" name="fecha_inicio_nuevos" value="<?= htmlspecialchars($p['fecha_inicio_nuevos'] ?? '') ?>" style="padding:4px;border:1px solid var(--border-color);border-radius:3px;font-size:12px" /></td>
                            <td style="text-align:center;font-weight:bold"><?= obtenerSemanaActual() ?></td>
                            <td><button type="submit" class="btn btn-primary" style="font-size:11px;padding:4px 10px">Guardar</button></td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <form method="POST" action="" style="display:contents">
                            <input type="hidden" name="action" value="update_periodo" />
                            <td><input type="text" name="codigo" placeholder="2026-III" required style="padding:4px;border:1px solid var(--border-color);border-radius:3px;font-size:12px;width:80px" /></td>
                            <td><input type="date" name="fecha_inicio" required style="padding:4px;border:1px solid var(--border-color);border-radius:3px;font-size:12px" /></td>
                            <td><input type="date" name="fecha_inicio_nuevos" style="padding:4px;border:1px solid var(--border-color);border-radius:3px;font-size:12px" /></td>
                            <td></td>
                            <td><button type="submit" class="btn btn-primary" style="font-size:11px;padding:4px 10px">Crear</button></td>
                        </form>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 style="color:var(--primary);margin-bottom:15px">Auditoría Reciente <span style="font-size:12px;font-weight:normal;color:#666">(últimos 100 registros)</span></h3>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>IP</th>
                        <th>Fecha/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($audit_log) === 0): ?>
                    <tr><td colspan="6" style="text-align:center;color:#888">Sin registros de auditoría</td></tr>
                    <?php else: ?>
                    <?php foreach ($audit_log as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= htmlspecialchars($log['nombre_completo'] ?? 'Eliminado') ?></td>
                        <td><?= htmlspecialchars($log['accion']) ?></td>
                        <td style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($log['detalle'] ?? '') ?></td>
                        <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                        <td style="white-space:nowrap"><?= date('d/m/Y h:i A', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-open]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = document.getElementById(this.getAttribute('data-open'));
            if (modal) modal.classList.add('active');
        });
    });
    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = document.getElementById(this.getAttribute('data-close'));
            if (modal) modal.classList.remove('active');
        });
    });
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
