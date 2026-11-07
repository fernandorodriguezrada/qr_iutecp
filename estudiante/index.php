<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/academico.php";

global $pdo;

function nivelRomano($n) {
    $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
    return $map[$n] ?? '';
}

redirigirSiNoAutenticado();

if ($_SESSION["rol"] !== "estudiante") {
    header("Location: /index.php");
    exit();
}

$estudiante_id = (int) $_SESSION["usuario_id"];
$periodo = obtenerPeriodoActual();
$semana_actual = obtenerSemanaActual($estudiante_id);

$stmt = $pdo->prepare("
    SELECT c.id, c.nombre, c.codigo, c.seccion, c.horario, c.aula, c.trayecto,
           cr.nombre as carrera_nombre, cr.nivel as carrera_nivel,
           u.nombre_completo as profesor_nombre
    FROM inscripciones i
    JOIN cursos c ON c.id = i.curso_id
    JOIN carreras cr ON cr.id = c.carrera_id
    JOIN usuarios u ON u.id = c.profesor_id
    WHERE i.estudiante_id = ? AND i.periodo = ? AND c.activo = 1
    ORDER BY cr.nombre, c.nombre
");
$stmt->execute([$estudiante_id, $periodo]);
$cursos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT carrera_id FROM usuarios WHERE id = ?");
$stmt->execute([$estudiante_id]);
$carrera_id = (int)$stmt->fetchColumn();

$carrera_nombre = '';
if ($carrera_id > 0) {
    $st = $pdo->prepare("SELECT nombre FROM carreras WHERE id = ?");
    $st->execute([$carrera_id]);
    $carrera_nombre = (string)$st->fetchColumn();
}

$stmt_cat = $pdo->prepare("SELECT id, codigo, nombre, trayecto, tipo FROM cursos WHERE carrera_id = ? AND activo = 1 AND codigo NOT IN ('ELEINF', 'ELEADM') ORDER BY trayecto, nombre");
$stmt_cat->execute([$carrera_id]);
$catalogo = $stmt_cat->fetchAll();

$stmt_cal = $pdo->prepare("SELECT c.codigo, cal.estatus FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ?");
$stmt_cal->execute([$estudiante_id]);
$cal_map_by_codigo = [];
while ($r = $stmt_cal->fetch()) {
    $cod = $r['codigo'];
    $est = $r['estatus'];
    if (!isset($cal_map_by_codigo[$cod]) || $est === 'Aprobada') {
        $cal_map_by_codigo[$cod] = $est;
    }
}

$stmt_ins = $pdo->prepare("SELECT curso_id FROM inscripciones WHERE estudiante_id = ? AND periodo = ?");
$stmt_ins->execute([$estudiante_id, $periodo]);
$inscritas_ahora = array_flip($stmt_ins->fetchAll(PDO::FETCH_COLUMN));

$stmt_prereq = $pdo->query("SELECT p.curso_id, p.requiere_codigo, p.nivel_requerido, p.creditos_minimos FROM prerrequisitos p JOIN cursos c ON c.id = p.curso_id WHERE c.carrera_id = $carrera_id AND c.activo = 1");
$prereq_map = [];
while ($r = $stmt_prereq->fetch()) {
    $prereq_map[$r['curso_id']][] = $r;
}

$niveles_ok = [];
$stmt_niv = $pdo->prepare("SELECT DISTINCT trayecto FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY trayecto");
$stmt_niv->execute([$carrera_id]);
foreach ($stmt_niv->fetchAll(PDO::FETCH_COLUMN) as $n) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cursos WHERE carrera_id = ? AND trayecto = ? AND activo = 1");
    $stmt->execute([$carrera_id, $n]);
    $total = (int)$stmt->fetchColumn();
    $stmt2 = $pdo->prepare("SELECT COUNT(DISTINCT c.id) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND c.carrera_id = ? AND c.trayecto = ? AND cal.estatus = 'Aprobada'");
    $stmt2->execute([$estudiante_id, $carrera_id, $n]);
    $aprobados = (int)$stmt2->fetchColumn();
    $niveles_ok[$n] = ($aprobados >= $total);
}

$stmt_cred = $pdo->prepare("SELECT COALESCE(SUM(c.creditos), 0) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
$stmt_cred->execute([$estudiante_id]);
$total_creditos = (int)$stmt_cred->fetchColumn();

$cursos_disponibles = [];
$seen_codigos = [];

foreach ($catalogo as $c) {
    $codigo = $c['codigo'];
    $est = $cal_map_by_codigo[$codigo] ?? null;
    $inscrito = isset($inscritas_ahora[$c['id']]);

    if (isset($seen_codigos[$codigo])) {
        $idx = $seen_codigos[$codigo];
        if ($inscrito) {
            $cursos_disponibles[$idx]['estado'] = 'inscrita';
        } elseif ($est === 'Aprobada' && $cursos_disponibles[$idx]['estado'] !== 'inscrita') {
            $cursos_disponibles[$idx]['estado'] = 'aprobada';
        } elseif (($est === 'Reprobada' || $est === 'Remedial') && !in_array($cursos_disponibles[$idx]['estado'], ['inscrita', 'aprobada'])) {
            $cursos_disponibles[$idx]['estado'] = 'reprobada';
        }
        continue;
    }

    if ($inscrito) {
        $estado = 'inscrita';
    } elseif ($est === 'Aprobada') {
        $estado = 'aprobada';
    } elseif ($est === 'Reprobada' || $est === 'Remedial') {
        $estado = 'reprobada';
    } else {
        $todos_ok = true;
        foreach ($prereq_map[$c['id']] ?? [] as $pr) {
            $req = $pr['requiere_codigo'];
            $nivel = (int)$pr['nivel_requerido'];
            $cred = (int)$pr['creditos_minimos'];
            if ($req && ($cal_map_by_codigo[$req] ?? null) !== 'Aprobada') { $todos_ok = false; break; }
            if ($nivel && !($niveles_ok[$nivel] ?? false)) { $todos_ok = false; break; }
            if ($cred && $total_creditos < $cred) { $todos_ok = false; break; }
        }
        $estado = $todos_ok ? 'disponible' : 'bloqueada';
    }
    $seen_codigos[$codigo] = count($cursos_disponibles);
    $cursos_disponibles[] = [
        'id' => $c['id'],
        'codigo' => $c['codigo'],
        'nombre' => $c['nombre'],
        'trayecto' => (int)$c['trayecto'],
        'tipo' => $c['tipo'],
        'estado' => $estado,
    ];
}

$pass_msg = "";
$pass_error = "";
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "cambiar_password"
) {
    $actual = $_POST["actual"] ?? "";
    $nueva = $_POST["nueva"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";
    $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$estudiante_id]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($actual, $user["password_hash"])) {
        $pass_error = "La contraseña actual no es correcta.";
    } elseif (strlen($nueva) < 6) {
        $pass_error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva !== $confirmar) {
        $pass_error = "Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $upd = $pdo->prepare(
            "UPDATE usuarios SET password_hash = ?, password_temporal = FALSE WHERE id = ?",
        );
        $upd->execute([$hash, $estudiante_id]);
        registrarAuditoria(
            $pdo,
            $estudiante_id,
            "Cambio de contraseña",
            "Estudiante cambió su contraseña",
        );
        $pass_msg = "ok";
    }
    if ($pass_error) {
        header("Content-Type: application/json");
        echo json_encode(["ok" => false, "error" => $pass_error]);
        exit();
    }
    if ($pass_msg === "ok") {
        header("Content-Type: application/json");
        echo json_encode(["ok" => true]);
        exit();
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "inscribir_materia"
) {
    header("Content-Type: application/json");
    $curso_id = (int)($_POST["curso_id"] ?? 0);

    if (!$curso_id) {
        echo json_encode(["ok" => false, "error" => "Curso inválido"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripciones i JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND i.periodo = ? AND c.codigo = (SELECT codigo FROM cursos WHERE id = ?)");
    $stmt->execute([$estudiante_id, $periodo, $curso_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["ok" => false, "error" => "Ya estás inscrito en esta materia"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND c.codigo = (SELECT codigo FROM cursos WHERE id = ?) AND cal.estatus = 'Aprobada'");
    $stmt->execute([$estudiante_id, $curso_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["ok" => false, "error" => "Ya aprobaste esta materia"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT p.requiere_codigo FROM prerrequisitos p WHERE p.curso_id = ? AND p.requiere_codigo IS NOT NULL AND p.requiere_codigo != ''");
    $stmt->execute([$curso_id]);
    $reqs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($reqs)) {
        $placeholders = implode(',', array_fill(0, count($reqs), '?'));
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM (
                SELECT c2.codigo FROM calificaciones cal2
                JOIN inscripciones i2 ON i2.id = cal2.inscripcion_id
                JOIN cursos c2 ON c2.id = i2.curso_id
                WHERE i2.estudiante_id = ? AND c2.codigo IN ($placeholders) AND cal2.estatus = 'Aprobada'
                GROUP BY c2.codigo
            ) sub
        ");
        $stmt->execute(array_merge([$estudiante_id], $reqs));
        if ((int)$stmt->fetchColumn() < count($reqs)) {
            echo json_encode(["ok" => false, "error" => "No cumples los prerequisitos de esta materia"]);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO inscripciones (estudiante_id, curso_id, periodo) VALUES (?, ?, ?)");
        $stmt->execute([$estudiante_id, $curso_id, $periodo]);
        registrarAuditoria($pdo, $estudiante_id, "Inscripción manual", "Materia ID: $curso_id");
        echo json_encode(["ok" => true]);
    } catch (Exception $e) {
        echo json_encode(["ok" => false, "error" => "Error al inscribir: " . $e->getMessage()]);
    }
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <title>Estudiante - Sistema QR IUTECP</title>
    <link rel="stylesheet" href="/style.css?v=<?= filemtime(__DIR__ . '/../style.css') ?>" />
    <script src="/assets/js/jsqr.js"></script>
    <style>
        @keyframes pulse-cam {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
        .pulse-cam {
            animation: pulse-cam 1.5s ease-in-out infinite;
        }
        #modal-electivas *,
        #popup-confirm * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body>
<div class="mobile-container" style="max-width:480px;">
    <div class="mobile-header">
        <img src="/logoIUTECP_azul.png" alt="IUTECP" class="mobile-logo" />
        <div class="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
    <div id="side-menu" class="student-side-menu">
        <div class="menu-item" onclick="switchTab('guia');toggleMenu();">📘 Guía Rápida</div>
        <div class="menu-item" onclick="switchTab('password');toggleMenu();">🔑 Cambiar Contraseña</div>
        <div class="menu-item" onclick="location.href='/logout.php'" style="color:red">🚪 Cerrar Sesión</div>
    </div>

    <!-- Tab: Inicio -->
    <div class="mobile-content" id="tab-inicio">
        <h2 style="color:var(--primary);margin-bottom:10px">¡Hola, <?= htmlspecialchars(
            explode(" ", $_SESSION["nombre"])[0],
        ) ?>!</h2>
        <p style="margin-bottom:20px;color:#666">
            <?= htmlspecialchars($carrera_nombre) ?> | Periodo <?= htmlspecialchars($periodo) ?>
        </p>
        <div style="display:flex;border-bottom:2px solid var(--border-color);margin-bottom:15px;">
            <button class="sub-tab active" onclick="switchSubTab('mismaterias',this)" style="flex:1;padding:10px 0;border:none;background:none;font-weight:bold;color:var(--primary);cursor:pointer;text-align:center;border-bottom:3px solid var(--accent);margin-bottom:-2px;">Mis Materias</button>
            <button class="sub-tab" onclick="switchSubTab('inscribir',this)" style="flex:1;padding:10px 0;border:none;background:none;font-weight:bold;color:#888;cursor:pointer;text-align:center;border-bottom:3px solid transparent;margin-bottom:-2px;">Inscribir Materias</button>
        </div>

        <div id="sub-mismaterias">
        <?php if (empty($cursos)): ?>
            <div class="card" style="text-align:center;padding:30px;">
                <p style="color:#999;font-size:14px;">No tienes materias inscritas para el periodo <?= htmlspecialchars(
                    $periodo,
                ) ?>.</p>
            </div>
        <?php else: ?>
        <?php foreach ($cursos as $c): ?>
        <div style="font-size:13px;font-weight:bold;color:var(--primary);margin:15px 0 8px 0;"><?= htmlspecialchars(
            $c["profesor_nombre"],
        ) ?><?= $c["seccion"] ? " - " . htmlspecialchars($c["seccion"]) : "" ?><?= $c["trayecto"] ? " - Nivel " . nivelRomano((int)$c["trayecto"]) : "" ?></div>
        <details class="class-accordion">
            <summary style="font-size:14px;"><?= htmlspecialchars(
                $c["nombre"],
            ) ?></summary>
            <ul class="weeks-list" data-curso="<?= $c["id"] ?>">
                <?php for ($s = 1; $s <= 12; $s++): ?>
                <li>
                    <span>Semana <?= $s ?></span>
                    <span class="status-pending semana-status" data-curso="<?= $c[
                        "id"
                    ] ?>" data-semana="<?= $s ?>">Pendiente</span>
                </li>
                <?php endfor; ?>
            </ul>
        </details>
        <?php endforeach; ?>
        <?php endif; ?>
        </div>

        <div id="sub-inscribir" class="hidden"></div>
    </div>

    <!-- Tab: Escanear -->
    <div class="mobile-content hidden" id="tab-escanear" style="padding:0;">
        <div id="esc-container" style="margin:10px;border-radius:4px;border:1px solid #999;overflow:hidden;position:relative;flex:1;min-height:70vh;display:flex;flex-direction:column;">
            <div id="escanear-placeholder" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--accent);position:relative;overflow:hidden;opacity:1;">
                <div style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;">
                    <span style="position:absolute;top:8%;left:10%;transform:rotate(-15deg);font-size:3.5rem;opacity:0.4;user-select:none;">💻</span>
                    <span style="position:absolute;top:20%;left:80%;transform:rotate(20deg);font-size:3.5rem;opacity:0.4;user-select:none;">📊</span>
                    <span style="position:absolute;top:75%;left:8%;transform:rotate(-10deg);font-size:3.5rem;opacity:0.4;user-select:none;">🧮</span>
                    <span style="position:absolute;top:80%;left:82%;transform:rotate(15deg);font-size:3.5rem;opacity:0.4;user-select:none;">🩺</span>
                    <span style="position:absolute;top:45%;left:5%;transform:rotate(25deg);font-size:3.5rem;opacity:0.4;user-select:none;">🦴</span>
                    <span style="position:absolute;top:12%;left:45%;transform:rotate(10deg);font-size:3.5rem;opacity:0.4;user-select:none;">💼</span>
                    <span style="position:absolute;top:65%;left:75%;transform:rotate(-25deg);font-size:3.5rem;opacity:0.4;user-select:none;">👥</span>
                </div>
                <div id="esc-welcome" style="position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;transition:opacity 0.3s;">
                    <img src="/k-listo-foto.png" alt="K-Listo" style="width:200px;max-width:80%;margin-bottom:15px;" />
                    <p style="font-size:20px;font-weight:bold;color:var(--primary);text-align:center;margin:0 0 8px 0;">Escanea el código QR</p>
                    <p style="font-size:15px;color:var(--primary);text-align:center;margin:0 0 25px 0;line-height:1.5;max-width:320px;">
                        Espera a que tu profesor active el código en su pantalla.
                        Luego presiona el botón y apunta la cámara al QR.
                    </p>
                    <button class="btn btn-primary" onclick="iniciarCamara()" style="width:80%;max-width:280px;font-size:16px;padding:14px;">Activar Cámara</button>
                </div>
                <div id="esc-loading" style="display:none;position:relative;z-index:1;flex-direction:column;align-items:center;gap:15px;">
                    <span class="pulse-cam" style="font-size:56px;display:inline-block;">📷</span>
                    <p style="font-size:18px;font-weight:bold;color:var(--primary);margin:0;">Cargando cámara...</p>
                </div>
            </div>
            <div id="escanear-camara" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;z-index:5;background:#000;transition:opacity 0.4s;opacity:0;">
                <video id="camera-preview" autoplay playsinline style="width:100%;height:100%;position:absolute;top:0;left:0;object-fit:cover;"></video>
                <canvas id="camera-canvas" style="display:none;"></canvas>
                <button onclick="detenerCamara()" style="position:absolute;top:15px;left:15px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;z-index:10;">✕</button>
            </div>
        </div>
    </div>

    <!-- Tab: Historial -->
    <div class="mobile-content hidden" id="tab-historial">
        <h3 style="margin-bottom:20px;color:var(--primary)">Historial de Asistencias</h3>
        <ul class="historial-list" id="historial-lista">
            <li style="text-align:center;color:#999;padding:30px;">Cargando...</li>
        </ul>
    </div>

    <!-- Tab: Guía -->
    <div class="mobile-content hidden" id="tab-guia">
        <h3 style="color:var(--primary);margin-bottom:15px">Guía Rápida</h3>
        <p style="margin-bottom:15px;font-size:15px;line-height:1.5;">
            ¡Hola! Bienvenido a la app de Control de Asistencias QR del IUTECP.
            Registrar tu asistencia ahora es mucho más sencillo. Solo sigue estos pasos:
        </p>
        <div class="card" style="margin-bottom:15px;padding:15px;">
            <h4 style="color:var(--secondary)">1. Revisa tus clases</h4>
            <p style="font-size:14px;margin:10px 0;">Ve a la pestaña <b>Inicio</b> para consultar tus materias asignadas y cuántas semanas llevas asistiendo.</p>
        </div>
        <div class="card" style="margin-bottom:15px;padding:15px;">
            <h4 style="color:var(--secondary)">2. Escanea el Código QR</h4>
            <p style="font-size:14px;margin:10px 0;">Cuando estés en el aula con tu profesor, ve a la pestaña <b>Escanear</b>, apunta tu cámara al código y ¡listo! Asistencia guardada.</p>
        </div>
        <div class="card" style="padding:15px;">
            <h4 style="color:var(--secondary)">3. Revisa tu Historial</h4>
            <p style="font-size:14px;margin:10px 0;">En la pestaña <b>Historial</b> puedes ver todas tus asistencias registradas.</p>
        </div>
    </div>

    <!-- Tab: Password -->
    <div class="mobile-content hidden" id="tab-password">
        <h3 style="color:var(--primary);margin-bottom:15px">Cambiar Contraseña</h3>
        <div class="card">
            <form id="password-form" onsubmit="return cambiarPassword(event)">
                <label style="font-size:14px;display:block;margin-bottom:5px">Contraseña Actual</label>
                <input type="password" name="actual" required />
                <label style="font-size:14px;display:block;margin-bottom:5px">Nueva Contraseña</label>
                <input type="password" name="nueva" required minlength="6" />
                <label style="font-size:14px;display:block;margin-bottom:5px">Confirmar Nueva Contraseña</label>
                <input type="password" name="confirmar" required minlength="6" />
                <button class="btn btn-primary" style="width:100%">Actualizar</button>
            </form>
        </div>
    </div>

    <!-- Popups -->
    <div id="popup-exito" class="scan-popup">
        <div class="popup-content">
            <span class="check-icon">✓</span>
            <p class="popup-text" id="popup-text">¡Escaneo<br />Exitoso!</p>
        </div>
    </div>

    <div id="popup-error" class="scan-popup">
        <div class="popup-content">
            <span class="check-icon error">✕</span>
            <p class="popup-text" id="popup-error-text">Error</p>
        </div>
    </div>

    <div id="popup-confirm" class="scan-popup">
        <div class="popup-content" style="padding:25px;">
            <p id="confirm-text" style="font-size:16px;margin-bottom:20px;line-height:1.5;"></p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button class="btn" onclick="cerrarConfirm()" style="padding:10px 20px;">Cancelar</button>
                <button id="btn-confirm-action" class="btn btn-primary" onclick="ejecutarConfirm()" style="padding:10px 20px;">Sí, inscribir</button>
            </div>
        </div>
    </div>

    <div id="modal-electivas" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarElectivas()">
        <div class="popup-content" style="max-width:90%;width:380px;padding:20px;border-radius:8px;transform:none;transition:none;" onclick="event.stopPropagation()">
            <h4 style="color:var(--primary);margin:0 0 15px 0;">Selecciona una electiva</h4>
            <div id="electivas-lista" style="max-height:60vh;overflow-y:auto;margin-bottom:15px;"></div>
            <div id="electivas-sin-seleccion" style="display:none;">
                <p style="color:#999;font-size:13px;text-align:center;padding:10px;">Las electivas se desbloquean al aprobar Nivel I.</p>
            </div>
            <div style="display:flex;gap:8px;justify-content:center;">
                <button class="btn" onclick="cerrarElectivas()" style="padding:10px 20px;">Cerrar</button>
                <button id="btn-inscribir-electiva" class="btn btn-primary" onclick="mostrarConfirmElectiva()" style="padding:10px 20px;display:none;">Inscribir</button>
            </div>
        </div>
    </div>

    <button id="btn-scroll-top" onclick="volverArriba()" style="position:absolute;bottom:65px;right:12px;width:42px;height:42px;border-radius:50%;border:none;background:var(--primary);color:#fff;font-size:20px;cursor:pointer;z-index:500;display:none;box-shadow:0 2px 6px rgba(0,0,0,0.3);line-height:42px;text-align:center;padding:0;transition:opacity 0.4s ease;">↑</button>

    <div class="mobile-nav">
        <button class="active" onclick="switchTab('inicio',this)">Inicio</button>
        <button onclick="switchTab('escanear',this)">Escanear</button>
        <button onclick="switchTab('historial',this)">Historial</button>
    </div>
</div>

<script>
var camaraActiva = false;
var videoStream = null;
var scanInterval = null;

function switchTab(tab, btn) {
    document.querySelectorAll('.mobile-content').forEach(function(el) { el.classList.add('hidden'); el.style.display = ''; });
    var el = document.getElementById('tab-' + tab);
    el.classList.remove('hidden');
    if (tab === 'escanear') el.style.display = 'flex';
    if (btn) {
        document.querySelectorAll('.mobile-nav button').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
    }
    if (tab === 'inicio') cargarEstadoSemanas();
    if (tab === 'historial') cargarHistorial();
    if (tab !== 'escanear') detenerCamara();
}

function toggleMenu() {
    document.getElementById('side-menu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('side-menu');
    if (!menu || !menu.classList.contains('open')) return;
    if (!menu.contains(e.target) && !document.querySelector('.hamburger')?.contains(e.target)) {
        menu.classList.remove('open');
    }
});

function mostrarPopup(texto) {
    var p = document.getElementById('popup-exito');
    document.getElementById('popup-text').innerHTML = texto;
    p.classList.add('show');
    setTimeout(function() { p.classList.remove('show'); }, 2000);
}

function mostrarPopupError(texto) {
    var p = document.getElementById('popup-error');
    document.getElementById('popup-error-text').innerHTML = texto;
    p.classList.add('show');
    setTimeout(function() { p.classList.remove('show'); }, 2000);
}

function cargarEstadoSemanas() {
    document.querySelectorAll('.weeks-list').forEach(function(ul) {
        var cursoId = ul.getAttribute('data-curso');
        if (!cursoId) return;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../profesor/asistencia.php?accion=estado_semanas&curso_id=' + cursoId, true);
        xhr.onload = function() {
            if (xhr.status !== 200) return;
            try {
                var r = JSON.parse(xhr.responseText);
                if (!r.ok) return;
                r.semanas.forEach(function(s) {
                    var spans = document.querySelectorAll('.semana-status[data-curso="' + cursoId + '"][data-semana="' + s.semana + '"]');
                    spans.forEach(function(sp) {
                        var sesiones = s.sesiones || 0;
                        var misAsis = s.mis_asistencias || 0;
                        if (sesiones > 0) {
                            if (misAsis > 0) {
                                sp.className = 'status-present semana-status';
                                sp.textContent = misAsis + '/' + sesiones + ' sesiones';
                            } else {
                                sp.className = 'status-absent semana-status';
                                sp.textContent = 'Ausente (' + sesiones + ' sesiones)';
                            }
                        } else if (s.total_inscritos > 0) {
                            sp.className = 'status-absent semana-status';
                            sp.textContent = 'Ausente';
                        } else {
                            sp.className = 'status-pending semana-status';
                            sp.textContent = 'Pendiente';
                        }
                    });
                });
            } catch(e) {}
        };
        xhr.send();
    });
}

function cargarHistorial() {
    var ul = document.getElementById('historial-lista');
    ul.innerHTML = '<li style="text-align:center;color:#999;padding:30px;">Cargando...</li>';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'escanear_qr.php?accion=historial', true);
    xhr.onload = function() {
        if (xhr.status !== 200) { ul.innerHTML = '<li style="text-align:center;color:red;padding:30px;">Error al cargar</li>'; return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (!r.ok) { ul.innerHTML = '<li style="text-align:center;color:#999;padding:30px;">' + (r.error || 'Sin datos') + '</li>'; return; }
            if (r.registros.length === 0) { ul.innerHTML = '<li style="text-align:center;color:#999;padding:30px;">No hay registros de asistencia.</li>'; return; }
            var html = '';
            r.registros.forEach(function(reg) {
                html += '<li><strong>' + reg.curso + ' (' + reg.codigo + ')</strong><br />';
                html += '<span style="font-size:12px;color:#666;">' + reg.fecha + ' ' + reg.hora + ' - Semana ' + reg.semana + '</span></li>';
            });
            ul.innerHTML = html;
        } catch(e) { ul.innerHTML = '<li style="text-align:center;color:red;padding:30px;">Error al procesar</li>'; }
    };
    xhr.send();
}

var camaraTimer = null;
var camaraReady = false;
var cameraStreamResolve = null;

function iniciarCamara() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            alert('Por seguridad del navegador (Brave/Chrome), el acceso a la cámara requiere HTTPS o localhost.\n\nPara usar la cámara:\n• En la laptop: usa https://qr-iutecp.ddev.site o http://localhost:8080\n• En el teléfono: usa https://192.168.10.9:8443');
        } else {
            alert('Tu navegador no soporta acceso a la cámara o los permisos están bloqueados.');
        }
        return;
    }
    var welcome = document.getElementById('esc-welcome');
    var loading = document.getElementById('esc-loading');
    welcome.style.opacity = '0';
    setTimeout(function() {
        welcome.style.display = 'none';
        loading.style.display = 'flex';
    }, 300);
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function(stream) {
        camaraReady = true;
        videoStream = stream;
        var video = document.getElementById('camera-preview');
        video.srcObject = stream;
        video.play();
        escanearConCamara();
        if (camaraTimer) { clearTimeout(camaraTimer); camaraTimer = null; }
        transicionCamara();
    }).catch(function(err) {
        var loading = document.getElementById('esc-loading');
        var welcome = document.getElementById('esc-welcome');
        loading.style.display = 'none';
        welcome.style.display = 'flex';
        welcome.style.opacity = '1';
        if (camaraTimer) { clearTimeout(camaraTimer); camaraTimer = null; }
        alert('No se pudo acceder a la cámara: ' + err.message);
    });
    camaraTimer = setTimeout(function() {
        if (!camaraReady) return;
        transicionCamara();
    }, 3000);
}

function transicionCamara() {
    var ph = document.getElementById('escanear-placeholder');
    var cam = document.getElementById('escanear-camara');
    cam.style.display = 'block';
    void cam.offsetHeight;
    ph.style.transition = 'opacity 0.4s';
    ph.style.opacity = '0';
    cam.style.opacity = '1';
    setTimeout(function() {
        ph.style.display = 'none';
        camaraActiva = true;
    }, 400);
}

function detenerCamara() {
    if (camaraTimer) { clearTimeout(camaraTimer); camaraTimer = null; }
    if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
    if (videoStream) {
        videoStream.getTracks().forEach(function(t) { t.stop(); });
        videoStream = null;
    }
    camaraActiva = false;
    camaraReady = false;
    var cam = document.getElementById('escanear-camara');
    cam.style.opacity = '0';
    cam.style.display = 'none';
    var ph = document.getElementById('escanear-placeholder');
    ph.style.transition = '';
    ph.style.opacity = '1';
    ph.style.display = 'flex';
    var welcome = document.getElementById('esc-welcome');
    var loading = document.getElementById('esc-loading');
    loading.style.display = 'none';
    welcome.style.display = 'flex';
    welcome.style.opacity = '1';
}

function escanearConCamara() {
    var video = document.getElementById('camera-preview');
    var canvas = document.getElementById('camera-canvas');
    var ctx = canvas.getContext('2d');

    scanInterval = setInterval(function() {
        if (video.readyState !== video.HAVE_ENOUGH_DATA) return;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code && code.data) {
            detenerCamara();
            procesarCodigo(code.data);
        }
    }, 500);
}

function procesarCodigo(codigo) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'escanear_qr.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) {
                mostrarPopup('¡Escaneo<br />Exitoso!');
            } else {
                mostrarPopupError(r.error || 'Código<br />inválido');
            }
        } catch(e) { mostrarPopupError('Error al<br />procesar'); }
    };
    xhr.onerror = function() { mostrarPopupError('Error de<br />conexión'); };
    xhr.send('codigo_qr=' + encodeURIComponent(codigo));
}

function cambiarPassword(e) {
    e.preventDefault();
    var form = document.getElementById('password-form');
    var data = new FormData(form);
    data.append('accion', 'cambiar_password');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.onload = function() {
        if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) { mostrarPopup('¡Contraseña<br />Actualizada!'); form.reset(); }
            else { mostrarPopupError(r.error || 'Error al<br />cambiar'); }
        } catch(e) { mostrarPopupError('Error al<br />procesar'); }
    };
    xhr.send(data);
    return false;
}

var catalogo = <?= json_encode($cursos_disponibles) ?>;
var electivaSeleccionadaId = null;
var electivaSeleccionadaNombre = '';

function switchSubTab(tab, btn) {
    document.getElementById('sub-mismaterias').classList.toggle('hidden', tab !== 'mismaterias');
    document.getElementById('sub-inscribir').classList.toggle('hidden', tab !== 'inscribir');
    document.querySelectorAll('.sub-tab').forEach(function(b) {
        b.style.color = '#888';
        b.style.borderBottomColor = 'transparent';
    });
    if (btn) { btn.style.color = 'var(--primary)'; btn.style.borderBottomColor = 'var(--accent)'; }
    if (tab === 'inscribir') renderCatalogo();
}

function renderCatalogo() {
    var container = document.getElementById('sub-inscribir');
    if (catalogo.length === 0) {
        container.innerHTML = '<div class="card" style="text-align:center;padding:30px"><p style="color:#999;font-size:14px;">No hay materias disponibles.</p></div>';
        return;
    }
    var html = '';
    var romanos = ['', 'I', 'II', 'III', 'IV', 'V', 'VI'];

    var obligatorias = catalogo.filter(function(c) { return c.tipo !== 'Electiva'; });
    var electivas = catalogo.filter(function(c) { return c.tipo === 'Electiva'; });
    var tieneElectivaInscrita = electivas.some(function(c) { return c.estado === 'inscrita'; });
    var todasBloqueadas = electivas.every(function(c) { return c.estado === 'bloqueada'; });
    var tieneElectivaDisponible = electivas.some(function(c) { return c.estado === 'disponible'; });

    var niveles = [];
    obligatorias.forEach(function(c) {
        if (niveles.indexOf(c.trayecto) === -1) niveles.push(c.trayecto);
    });
    if (electivas.length > 0 && niveles.indexOf(2) === -1) niveles.push(2);
    niveles.sort(function(a,b) { return a-b; });

    if (niveles.length > 1) {
        html += '<div style="display:flex;gap:5px;margin-bottom:15px;flex-wrap:wrap;">';
        niveles.forEach(function(t) {
            html += '<button onclick="document.getElementById(\'nivel-' + t + '\').scrollIntoView({behavior:\'smooth\'})" style="flex:1;min-width:40px;padding:8px 0;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-light);color:var(--primary);font-weight:bold;font-size:14px;cursor:pointer;text-align:center;">' + romanos[t] + '</button>';
        });
        html += '</div>';
    }

    function renderCurso(c) {
        var badge = '';
        var disabled = false;
        if (c.estado === 'inscrita') {
            badge = '<span style="background:#5a7d5a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Inscrita</span>';
            disabled = true;
        } else if (c.estado === 'aprobada') {
            badge = '<span style="background:#4a6a8a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Aprobada</span>';
            disabled = true;
        } else if (c.estado === 'reprobada') {
            badge = '<span style="background:#8a4a4a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Reprobada</span>';
        } else if (c.estado === 'bloqueada') {
            badge = '<span style="background:var(--primary);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">🔒</span>';
            disabled = true;
        }
        html += '<div style="border:1px solid var(--border-color);border-radius:4px;margin-bottom:8px;' + (disabled ? 'opacity:0.6;' : '') + '">';
        html += '<div style="padding:12px 15px;background:var(--bg-light);display:flex;justify-content:space-between;align-items:center;font-weight:bold;color:var(--primary);font-size:14px;">';
        html += '<span>' + htmlspecialchars(c.nombre) + '</span> ' + badge + '</div>';
        html += '<div style="border-top:1px solid var(--border-color);padding:10px 15px;display:flex;gap:8px;align-items:center;">';
        html += '<span style="font-size:13px;color:#666;margin-right:auto;">' + c.codigo + '</span>';
        if (!disabled) {
            html += '<button class="btn btn-primary" onclick="inscribirMateria(' + c.id + ',this)" style="font-size:13px;padding:6px 14px;white-space:nowrap;margin:0;">Inscribir</button>';
        }
        html += '</div></div>';
    }

    function renderELEINF() {
        var badge = '';
        var disabled = false;
        var btnHtml = '';
        var titulo = 'ELECTIVAS';
        var codigoLabel = 'ELEINF';

        var electivaInscrita = electivas.find(function(c) { return c.estado === 'inscrita'; });
        if (electivaInscrita) {
            titulo = htmlspecialchars(electivaInscrita.nombre);
            codigoLabel = electivaInscrita.codigo;
            badge = '<span style="background:#5a7d5a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Inscrita</span>';
            disabled = true;
        } else if (!tieneElectivaDisponible) {
            badge = '<span style="background:var(--primary);color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">🔒</span>';
            disabled = true;
        } else {
            btnHtml = '<button class="btn btn-primary" onclick="abrirElectivas()" style="font-size:13px;padding:6px 14px;white-space:nowrap;margin:0;">Ver materias</button>';
        }

        html += '<div style="border:1px solid var(--border-color);border-radius:4px;margin-bottom:8px;' + (disabled ? 'opacity:0.6;' : '') + '">';
        html += '<div style="padding:12px 15px;background:var(--bg-light);display:flex;justify-content:space-between;align-items:center;font-weight:bold;color:var(--primary);font-size:14px;">';
        html += '<span>' + titulo + '</span> ' + badge + '</div>';
        html += '<div style="border-top:1px solid var(--border-color);padding:10px 15px;display:flex;gap:8px;align-items:center;">';
        html += '<span style="font-size:13px;color:#666;margin-right:auto;">' + codigoLabel + '</span>';
        if (btnHtml) html += btnHtml;
        html += '</div></div>';
    }

    var trayActual = 0;
    obligatorias.forEach(function(c) {
        if (c.trayecto !== trayActual) {
            if (trayActual === 2 && electivas.length > 0) renderELEINF();
            trayActual = c.trayecto;
            html += '<h5 id="nivel-' + c.trayecto + '" style="color:var(--primary);margin:15px 0 8px 0;scroll-margin-top:10px;">Nivel ' + (romanos[c.trayecto] || c.trayecto) + '</h5>';
        }
        renderCurso(c);
    });
    if (trayActual === 2 && electivas.length > 0) renderELEINF();
    if (trayActual < 2 && electivas.length > 0) {
        html += '<h5 id="nivel-2" style="color:var(--primary);margin:15px 0 8px 0;scroll-margin-top:10px;">Nivel II</h5>';
        renderELEINF();
    }

    container.innerHTML = html;
}

function abrirElectivas() {
    electivaSeleccionadaId = null;
    electivaSeleccionadaNombre = '';
    document.getElementById('btn-inscribir-electiva').style.display = 'none';

    var electivas = catalogo.filter(function(c) { return c.tipo === 'Electiva'; });
    var todasBloqueadas = electivas.every(function(c) { return c.estado === 'bloqueada'; });
    var listaHtml = '';

    if (todasBloqueadas) {
        listaHtml = '<p style="color:#999;font-size:14px;text-align:center;padding:20px;">Las electivas se desbloquean al aprobar Nivel I.</p>';
    } else {
        electivas.forEach(function(c) {
            var badge = '';
            var clickable = false;

            if (c.estado === 'inscrita') {
                badge = '<span style="background:#5a7d5a;color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;">Inscrita</span>';
            } else if (c.estado === 'aprobada') {
                badge = '<span style="background:#4a6a8a;color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;">Aprobada</span>';
            } else if (c.estado === 'reprobada') {
                badge = '<span style="background:#8a4a4a;color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;">Reprobada</span>';
            } else if (c.estado === 'bloqueada') {
                badge = '<span style="background:var(--primary);color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;">🔒</span>';
            } else {
                clickable = true;
            }

            var name = htmlspecialchars(c.nombre);
            listaHtml += '<div onclick="' + (clickable ? 'seleccionarElectiva(' + c.id + ',\'' + name.replace(/'/g, "\\'") + '\',this)' : '') + '" style="padding:12px;border:2px solid var(--border-color);border-radius:4px;margin-bottom:6px;cursor:' + (clickable ? 'pointer' : 'default') + ';background:var(--bg-light);display:flex;justify-content:space-between;align-items:center;">';
            listaHtml += '<div style="flex:1;min-width:0;"><div style="font-weight:bold;font-size:14px;color:var(--primary);text-align:left;">' + name + '</div>';
            listaHtml += '<div style="font-size:12px;color:#666;text-align:left;">' + c.codigo + '</div></div>';
            listaHtml += badge + '</div>';
        });
    }

    document.getElementById('electivas-lista').innerHTML = listaHtml;
    document.getElementById('modal-electivas').classList.add('show');
}

function cerrarElectivas() {
    document.getElementById('modal-electivas').classList.remove('show');
}

function seleccionarElectiva(id, nombre, el) {
    document.querySelectorAll('#electivas-lista > div').forEach(function(d) {
        d.style.borderColor = 'var(--border-color)';
        d.style.background = 'var(--bg-light)';
    });
    el.style.borderColor = 'var(--accent)';
    el.style.background = '#fffbe6';
    electivaSeleccionadaId = id;
    electivaSeleccionadaNombre = nombre;
    document.getElementById('btn-inscribir-electiva').style.display = '';
}

function mostrarConfirmElectiva() {
    if (!electivaSeleccionadaId) return;
    cerrarElectivas();
    document.getElementById('confirm-text').innerHTML = '¿Seguro que quieres inscribir<br /><strong>' + htmlspecialchars(electivaSeleccionadaNombre) + '</strong>?';
    document.getElementById('popup-confirm').classList.add('show');
}

function cerrarConfirm() {
    document.getElementById('popup-confirm').classList.remove('show');
}

function ejecutarConfirm() {
    var id = electivaSeleccionadaId;
    var nombre = electivaSeleccionadaNombre;
    cerrarConfirm();
    if (!id) return;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) {
                mostrarPopup('¡Inscrito<br />Exitosamente!');
                catalogo = catalogo.map(function(c) {
                    if (c.id === id) c.estado = 'inscrita';
                    return c;
                });
                renderCatalogo();
            } else {
                mostrarPopupError(r.error || 'Error al<br />inscribir');
            }
        } catch(e) { mostrarPopupError('Error al<br />procesar'); }
    };
    xhr.onerror = function() { mostrarPopupError('Error de<br />conexión'); };
    xhr.send('accion=inscribir_materia&curso_id=' + id);
    electivaSeleccionadaId = null;
    electivaSeleccionadaNombre = '';
}

function inscribirMateria(cursoId, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = 'Inscribir';
        if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) {
                mostrarPopup('¡Inscrito<br />Exitosamente!');
                catalogo = catalogo.map(function(c) {
                    if (c.id === cursoId) c.estado = 'inscrita';
                    return c;
                });
                renderCatalogo();
            } else {
                mostrarPopupError(r.error || 'Error al<br />inscribir');
            }
        } catch(e) { mostrarPopupError('Error al<br />procesar'); }
    };
    xhr.onerror = function() { btn.disabled = false; btn.textContent = 'Inscribir'; mostrarPopupError('Error de<br />conexión'); };
    xhr.send('accion=inscribir_materia&curso_id=' + cursoId);
}

function htmlspecialchars(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($cursos)): ?>
    cargarEstadoSemanas();
    <?php endif; ?>
});

var timerScrollTop = null;

function volverArriba() {
    var el = document.querySelector('.mobile-content:not(.hidden)');
    if (el) el.scrollTo({ top: 0, behavior: 'smooth' });
}

(function() {
    var btn = document.getElementById('btn-scroll-top');
    var contenidos = document.querySelectorAll('.mobile-content');

    function mostrarBoton(conFade) {
        if (timerScrollTop) { clearTimeout(timerScrollTop); timerScrollTop = null; }
        if (conFade) {
            btn.style.display = '';
            btn.style.opacity = '0';
            void btn.offsetHeight;
            btn.style.opacity = '1';
        } else {
            btn.style.display = '';
            btn.style.opacity = '1';
        }
        timerScrollTop = setTimeout(function() {
            btn.style.opacity = '0';
            setTimeout(function() { if (btn.style.opacity === '0') btn.style.display = 'none'; }, 400);
        }, 3000);
    }

    function ocultarBoton() {
        if (timerScrollTop) { clearTimeout(timerScrollTop); timerScrollTop = null; }
        btn.style.display = 'none';
        btn.style.opacity = '0';
    }

    function verScroll() {
        var mostrar = false;
        contenidos.forEach(function(el) {
            if (!el.classList.contains('hidden') && el.scrollTop > 200) mostrar = true;
        });
        if (mostrar) {
            mostrarBoton(false);
        } else {
            ocultarBoton();
        }
    }

    contenidos.forEach(function(el) { el.addEventListener('scroll', verScroll); });
    var origSwitch = switchTab;
    window.switchTab = function(tab, btn2) {
        origSwitch(tab, btn2);
        setTimeout(verScroll, 50);
    };

    function ajustarAltura() {
        var c = document.querySelector('.mobile-container');
        if (c) c.style.height = window.innerHeight + 'px';
    }
    ajustarAltura();
    window.addEventListener('resize', ajustarAltura);
    if (window.visualViewport) window.visualViewport.addEventListener('resize', ajustarAltura);
})();
</script>
</body>
</html>
