<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

global $pdo;

redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'profesor') {
    header('Location: /index.php');
    exit;
}

$profesor_id = $_SESSION['usuario_id'];
$periodo = obtenerPeriodoActual();
$semana_actual = obtenerSemanaActual();

$pass_msg = '';
$pass_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_password') {
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$profesor_id]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($actual, $user['password_hash'])) {
        $pass_error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 6) {
        $pass_error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $pass_error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE usuarios SET password_hash = ?, password_temporal = FALSE WHERE id = ?");
        $upd->execute([$hash, $profesor_id]);
        registrarAuditoria($pdo, $profesor_id, 'Cambio de contraseña', 'Profesor cambió su contraseña');
        $pass_msg = 'ok';
    }
    if ($pass_error) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $pass_error]);
        exit;
    }
    if ($pass_msg === 'ok') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
}

if (!function_exists('nivelRomano')) {
    function nivelRomano($n) {
        $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
        return $romanos[(int)$n] ?? (string)$n;
    }
}

$stmt = $pdo->prepare("
    SELECT c.id, c.nombre, c.codigo, c.seccion, c.horario, c.aula, c.trayecto,
           cr.nombre as carrera_nombre
    FROM cursos c
    JOIN carreras cr ON c.carrera_id = cr.id
    WHERE c.profesor_id = ? AND c.activo = 1
    ORDER BY cr.nombre, c.trayecto, c.seccion, c.nombre
");
$stmt->execute([$profesor_id]);
$cursos = $stmt->fetchAll();

$secciones_disponibles = [];
$niveles_disponibles = [];
$bloques_cursos = [];

foreach ($cursos as $c) {
    $sec = $c['seccion'] ?: 'Sin sección';
    $tray = (int)($c['trayecto'] ?: 1);
    $niv = 'Nivel ' . nivelRomano($tray);

    if (!empty($c['seccion']) && !in_array($c['seccion'], $secciones_disponibles)) {
        $secciones_disponibles[] = $c['seccion'];
    }
    if (!empty($c['trayecto']) && !in_array((int)$c['trayecto'], $niveles_disponibles)) {
        $niveles_disponibles[] = (int)$c['trayecto'];
    }

    $bloque_key = $sec . '___' . $tray;
    if (!isset($bloques_cursos[$bloque_key])) {
        $bloques_cursos[$bloque_key] = [
            'seccion' => $sec,
            'trayecto' => $tray,
            'nivel' => $niv,
            'cursos' => []
        ];
    }
    $bloques_cursos[$bloque_key]['cursos'][] = $c;
}
sort($secciones_disponibles);
sort($niveles_disponibles);

$sesion_activa = null;
$qr_activo_segundos = 0;
$qr_tolerancia_segundos = 0;
$qr_data_url = '';

$stmt_activa = $pdo->prepare("
    SELECT sq.*, c.nombre as curso_nombre, c.seccion, car.nombre as carrera_nombre
    FROM sesiones_qr sq
    JOIN cursos c ON c.id = sq.curso_id
    JOIN carreras car ON car.id = c.carrera_id
    WHERE sq.profesor_id = ? AND sq.activo = 1
      AND sq.fecha = CURDATE()
      AND CONCAT(sq.fecha, ' ', sq.hora_expiracion) > NOW()
    ORDER BY sq.id DESC LIMIT 1
");
$stmt_activa->execute([$profesor_id]);
$sesion_activa = $stmt_activa->fetch();

if ($sesion_activa) {
    require_once __DIR__ . '/../includes/phpqrcode/qrlib.php';
    $cache_dir = __DIR__ . '/../includes/phpqrcode/cache/';
    $qr_filename = 'qr_' . $sesion_activa['id'] . '.png';
    $qr_filepath = $cache_dir . $qr_filename;
    if (!file_exists($qr_filepath)) {
        QRcode::png($sesion_activa['codigo_qr'], $qr_filepath, QR_ECLEVEL_L, 6);
    }
    if (file_exists($qr_filepath)) {
        $qr_data_url = 'data:image/png;base64,' . base64_encode(file_get_contents($qr_filepath));
    } else {
        $qr_data_url = '/includes/phpqrcode/cache/' . $qr_filename . '?v=' . time();
    }

    header('Content-Type: text/html; charset=UTF-8');
    $now = time();
    $exp_time = strtotime($sesion_activa['fecha'] . ' ' . $sesion_activa['hora_expiracion']);
    $tol_time = !empty($sesion_activa['hora_tolerancia']) ? strtotime($sesion_activa['fecha'] . ' ' . $sesion_activa['hora_tolerancia']) : $exp_time;

    $qr_activo_segundos = max(0, $exp_time - $now);
    $qr_tolerancia_segundos = max(0, $tol_time - $now);
}
?>
<!doctype html>
<html lang="es" class="mobile-app-html">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="theme-color" content="#262865" />
    <meta name="color-scheme" content="light" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="QR IUTECP" />
    <?php require_once __DIR__ . "/../includes/pwa_head.php"; ?>
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <title>Profesor - Sistema QR IUTECP</title>
    <link rel="stylesheet" href="/style.css?v=<?= filemtime(__DIR__ . '/../style.css') ?>" />
</head>
<body class="mobile-app-body">
<div class="mobile-container">
    <div class="mobile-header">
        <img src="/logoIUTECP_azul.png" alt="IUTECP" class="mobile-logo" />
        <div class="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
    <div id="ptr-indicator" class="ptr-indicator">
        <span id="ptr-icon" class="ptr-icon">↓</span>
        <span class="ptr-spinner"></span>
        <span id="ptr-text">Desliza para actualizar</span>
    </div>
    <div id="side-menu" class="student-side-menu">
        <div class="menu-item" onclick="switchTab('password');toggleMenu();">🔑 Cambiar Contraseña</div>
        <div class="menu-item" onclick="switchTab('diagnostico');toggleMenu();">🛠️ Diagnóstico</div>
        <div class="menu-item" onclick="location.href='/logout.php'" style="color:red">🚪 Cerrar Sesión</div>
    </div>

    <div class="mobile-content" id="tab-generar">
        <?php
        $nombre_prof = trim($_SESSION['nombre'] ?? '');
        if (stripos($nombre_prof, 'Prof.') === 0 || strcasecmp($nombre_prof, 'Profesor') === 0) {
            $saludo_prof = $nombre_prof;
        } else {
            $saludo_prof = 'Prof. ' . $nombre_prof;
        }
        ?>
        <h2 style="color:var(--primary);margin-bottom:5px">¡Hola, <?= htmlspecialchars($saludo_prof) ?>!</h2>
        <p style="margin-bottom:15px;color:#666">Gestión de Aula IUTECP</p>

        <div class="card <?= $sesion_activa ? 'hidden' : '' ?>" id="form-generar-container" style="margin-bottom:15px;padding:15px 18px;">
            <?php if (empty($cursos)): ?>
                <p style="color:#999;font-size:14px;text-align:center;padding:10px 0;">No tienes cursos asignados actualmente.</p>
            <?php else: ?>
                <label style="font-weight:bold;display:block;margin-bottom:6px;color:var(--primary);font-size:14px;">Seleccionar Clase de Hoy:</label>
                <select id="qr-curso" class="prof-select" onchange="actualizarInfoCurso()">
                    <?php foreach ($cursos as $c): ?>
                    <option value="<?= $c['id'] ?>"
                            data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                            data-carrera="<?= htmlspecialchars($c['carrera_nombre']) ?>"
                            data-seccion="<?= htmlspecialchars($c['seccion'] ?: '-') ?>"
                            data-aula="<?= htmlspecialchars($c['aula'] ?: 'Por asignar') ?>"
                            data-horario="<?= htmlspecialchars($c['horario'] ?: 'No especificado') ?>">
                        <?= htmlspecialchars($c['nombre'] . ' - Sec: ' . ($c['seccion'] ?: '-')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <div id="curso-info-line" style="font-size:13px;color:#666;margin:0 0 14px 0;line-height:1.4;">
                    <div id="line-carrera">Informática</div>
                    <div id="line-aula">Aula: -</div>
                    <div id="line-horario">Horario: -</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:flex-end;">
                    <div>
                        <label style="font-weight:bold;display:block;margin-bottom:6px;color:var(--primary);font-size:14px;">Semana:</label>
                        <select id="qr-semana" class="prof-select" style="margin-bottom:0;width:100%;height:38px;box-sizing:border-box;">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === $semana_actual ? 'selected' : '' ?>>Semana <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <button id="btn-generar-qr" class="btn btn-accent" style="width:100%;height:38px;box-sizing:border-box;margin:0;padding:0;font-size:14px;font-weight:bold;display:flex;align-items:center;justify-content:center;" onclick="handleAccionQR()">
                            Generar QR
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card <?= $sesion_activa ? '' : 'hidden' ?>" id="qr-display-area" style="text-align:center;padding:15px 18px;margin-bottom:15px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid var(--border-color);padding-bottom:8px;">
                <div style="text-align:left;">
                    <strong id="active-materia-title" style="color:var(--primary);font-size:15px;display:block;"><?= $sesion_activa ? htmlspecialchars($sesion_activa['curso_nombre']) : 'Materia' ?></strong>
                    <span id="active-materia-sub" style="color:#666;font-size:12.5px;"><?= $sesion_activa ? 'Sec: ' . htmlspecialchars($sesion_activa['seccion'] ?: '-') . ' - Semana ' . $sesion_activa['semana'] . (!empty($sesion_activa['bloque_horario']) ? ' (' . htmlspecialchars($sesion_activa['bloque_horario']) . ')' : '') : 'Sec: - - Semana -' ?></span>
                </div>
                <button type="button" class="btn btn-danger" style="padding:6px 10px;font-size:14px;line-height:1;display:inline-flex;align-items:center;justify-content:center;" onclick="cancelarQR()" title="Cancelar sesión de asistencia">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </div>

            <div id="qr-image" style="width:185px;height:185px;margin:0 auto 12px;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;background:#fff;padding:6px;">
                <?= $sesion_activa ? '<img src="' . $qr_data_url . '" alt="QR" style="width:100%;height:100%;object-fit:contain;" />' : '' ?>
            </div>

            <div class="timer" id="qr-timer" style="font-size:1.3rem;font-weight:bold;color:var(--primary);margin-bottom:12px">Expira en 05:00</div>

            <button class="btn btn-primary" style="width:100%;padding:10px;font-size:14px;" onclick="cerrarQR()">Ocultar Código</button>
        </div>
    </div>

    <div class="mobile-content hidden" id="tab-asistencia">
        <div id="asistencia-lista">
            <h2 style="color:var(--primary);margin-bottom:6px">Registro de Asistencias</h2>
            <p style="margin-bottom:15px;color:#666;">Seleccione una materia y semana</p>

            <div class="asistencia-search-toolbar" style="display:flex;gap:8px;margin-bottom:14px;align-items:stretch;">
                <div style="position:relative;flex:1;">
                    <input type="text" id="filtro-materia-input" placeholder="Buscar materia..." autocomplete="off"
                           style="width:100%;height:100%;padding:10px 32px 10px 12px;margin:0 !important;border:1px solid var(--border-color);border-radius:4px;font-size:15px;background:#fff;box-sizing:border-box;"
                           oninput="onBusquedaMateriaInput()" />
                    <span id="btn-limpiar-busqueda" onclick="limpiarBusquedaTexto()"
                          style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:14px;color:#999;cursor:pointer;display:none;padding:2px 4px;user-select:none;">✕</span>
                </div>
                <button type="button" class="btn" id="btn-abrir-filtros" onclick="abrirModalFiltros()"
                        style="display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:var(--bg-light);border:1px solid var(--border-color);border-radius:4px;color:var(--primary);font-size:14px;font-weight:bold;white-space:nowrap;cursor:pointer;">
                    <span>Filtros</span>
                    <span id="filtro-badge-activo" style="display:none;font-size:12px;background:var(--primary);color:#fff;border-radius:3px;padding:2px 6px;font-weight:bold;line-height:1.2;">0</span>
                </button>
            </div>

            <div id="active-filters-chips" style="display:none;gap:6px;flex-wrap:wrap;margin-bottom:12px;align-items:center;">
                <span style="font-size:13px;color:#666;font-weight:bold;">Filtros:</span>
                <span id="chip-nivel" class="filter-chip" style="display:none;"></span>
                <span id="chip-seccion" class="filter-chip" style="display:none;"></span>
                <span id="chip-semana" class="filter-chip" style="display:none;"></span>
                <button type="button" onclick="limpiarTodosFiltros()" style="background:none;border:none;color:#dc3545;font-size:13px;font-weight:bold;cursor:pointer;padding:2px 4px;text-decoration:underline;">Limpiar todo</button>
            </div>

            <div id="no-cursos-filtro" style="display:none;text-align:center;padding:28px 14px;color:#888;">
                <p style="font-size:14px;margin-bottom:8px;">No se encontraron materias con los filtros aplicados.</p>
                <button type="button" class="btn" onclick="limpiarTodosFiltros()" style="font-size:12.5px;padding:6px 14px;background:#eee;color:var(--text-dark);border-radius:4px;border:none;cursor:pointer;">Restablecer filtros</button>
            </div>

            <?php if (empty($cursos)): ?>
                <p style="color:#999;font-size:14px;">No tienes cursos asignados.</p>
            <?php else: ?>
                <div id="bloques-cursos-container">
                <?php foreach ($bloques_cursos as $bKey => $bloque): ?>
                    <div class="bloque-seccion-group" data-seccion="<?= htmlspecialchars($bloque['seccion']) ?>" data-trayecto="<?= $bloque['trayecto'] ?>" style="margin-bottom:16px;">
                        <div class="bloque-header" style="font-size:15px;font-weight:bold;color:var(--primary);margin:18px 0 10px 0;letter-spacing:0.3px;">
                            Sección <?= htmlspecialchars($bloque['seccion']) ?> - <?= htmlspecialchars($bloque['nivel']) ?>
                        </div>
                        <?php foreach ($bloque['cursos'] as $c): ?>
                        <details class="class-accordion curso-item-accordion"
                                 data-curso-id="<?= $c['id'] ?>"
                                 data-curso-nombre="<?= htmlspecialchars(mb_strtolower($c['nombre'])) ?>"
                                 data-curso-nombre-full="<?= htmlspecialchars($c['nombre']) ?>"
                                 data-seccion="<?= htmlspecialchars($c['seccion'] ?: '') ?>"
                                 data-horario="<?= htmlspecialchars($c['horario'] ?: '') ?>"
                                 data-trayecto="<?= (int)$c['trayecto'] ?>"
                                 style="margin-bottom:8px;">
                            <summary style="font-size:15px;">
                                <div style="flex:1;min-width:0;padding-right:8px;">
                                    <div class="curso-title" style="font-size:15px;font-weight:bold;color:var(--primary);line-height:1.3;"><?= htmlspecialchars($c['nombre']) ?></div>
                                    <?php if (!empty($c['horario'])): ?>
                                    <div style="font-size:13px;color:#666;font-weight:normal;margin-top:3px;">(<?= htmlspecialchars($c['horario']) ?>)</div>
                                    <?php endif; ?>
                                </div>
                            </summary>
                            <ul class="weeks-list" data-curso="<?= $c['id'] ?>">
                                <?php for ($s = 1; $s <= 12; $s++): ?>
                                <li class="clickable-week" data-curso="<?= $c['id'] ?>" data-semana="<?= $s ?>">
                                    <span>Semana <?= $s ?></span>
                                    <span class="status-pending status-slot" style="font-weight:bold">Pendiente</span>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </details>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div id="asistencia-detalle" class="hidden">
            <button class="btn" style="margin-bottom:15px;background:#eee;color:var(--text-dark);" onclick="volverClases()">⬅ Volver a clases</button>
            <h3 id="detalle-titulo" style="color:var(--primary);margin-bottom:12px;font-size:15px;line-height:1.35;word-break:break-word;">Materia - Semana</h3>
            <div id="detalle-container"></div>
        </div>
    </div>

    <div class="mobile-content hidden" id="tab-ajustes">
        <h2 style="color:var(--primary);margin-bottom:6px">Ajustes de Escaneo QR</h2>
        <p style="margin-bottom:15px;color:#666;">Configuración de tiempos de asistencia</p>
        <div class="card">
            <form id="ajustes-form" onsubmit="return guardarAjustes(event)">
                <div style="margin-bottom:15px;">
                    <label for="cfg-puntual" style="font-weight:bold;color:var(--primary);font-size:14px;display:block;margin-bottom:6px;">
                        Tiempo de llegada puntual (Minutos)
                    </label>
                    <input type="number" name="tiempo_puntual" id="cfg-puntual" class="prof-select" value="10" min="1" max="120" required style="margin-bottom:15px;" />
                </div>

                <div style="margin-bottom:20px;">
                    <label for="cfg-tarde" style="font-weight:bold;color:var(--primary);font-size:14px;display:block;margin-bottom:6px;">
                        Tiempo de llegada tarde (Minutos)
                    </label>
                    <input type="number" name="tiempo_tarde" id="cfg-tarde" class="prof-select" value="10" min="0" max="120" required style="margin-bottom:20px;" />
                </div>

                <button class="btn btn-primary" style="width:100%;padding:10px;font-size:14px;font-weight:bold;border-radius:4px;">Guardar Ajustes</button>
            </form>
        </div>
    </div>

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

    <div class="mobile-content hidden" id="tab-diagnostico">
        <h3 style="color:var(--primary);margin-bottom:15px">Diagnóstico de Sistema</h3>
        <p id="diag-info" style="margin-bottom:20px;color:#666">Cargando...</p>
    </div>

    <div id="popup-exito" class="scan-popup">
        <div class="popup-content">
            <span class="check-icon">✓</span>
            <p class="popup-text" id="popup-text">¡Operación<br />Exitosa!</p>
        </div>
    </div>

    <div id="popup-error" class="scan-popup error">
        <div class="popup-content">
            <span class="check-icon error">✕</span>
            <p class="popup-text" id="popup-error-text">¡Error al<br />Procesar!</p>
        </div>
    </div>

    <div id="popup-confirmar" class="scan-popup" onclick="if(event.target===this)cerrarConfirmacion()">
        <div class="popup-content" style="max-width:85%;width:320px;padding:30px 24px 22px;border-radius:8px;text-align:center;" onclick="event.stopPropagation()">
            <div id="confirm-circle-icon" class="check-icon" style="background:#dc3545;margin-bottom:15px;font-size:32px;line-height:60px;font-weight:bold;color:#fff;">!</div>
            <h4 id="confirm-titulo" class="popup-text" style="font-size:18px;margin:0 0 10px 0;line-height:1.3;"></h4>
            <p id="confirm-mensaje" style="font-size:13.5px;color:#666;margin:0 0 20px 0;line-height:1.45;"></p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button type="button" class="btn" onclick="cerrarConfirmacion()" style="flex:1;padding:10px 14px;font-size:13px;border-radius:4px;background:#e2e8f0;color:#334155;border:1px solid #cbd5e1;font-weight:600;">Cancelar</button>
                <button type="button" id="btn-confirm-aceptar" class="btn" style="flex:1;padding:10px 14px;font-size:13px;border-radius:4px;color:#fff;border:none;font-weight:600;"></button>
            </div>
        </div>
    </div>

    <div id="popup-justificar" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarJustificar()">
        <div class="popup-content" style="max-width:90%;width:380px;padding:20px;border-radius:8px;transform:none;transition:none;text-align:left;" onclick="event.stopPropagation()">
            <h4 id="justificar-titulo" style="color:var(--primary);margin:0 0 10px 0;font-size:15px;"></h4>
            <p id="justificar-estudiante" style="font-size:14px;color:#666;margin-bottom:12px;"></p>
            <textarea id="justificar-texto" style="width:100%;min-height:100px;padding:10px;border:1px solid var(--border-color);border-radius:4px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;display:none;" placeholder="Escribe la justificación..."></textarea>
            <div id="justificar-lectura" style="font-size:14px;color:#333;background:var(--bg-light);padding:12px;border-radius:4px;line-height:1.5;display:none;white-space:pre-wrap;"></div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                <button class="btn" onclick="cerrarJustificar()" style="padding:8px 16px;">Cerrar</button>
                <button id="btn-guardar-justificar" class="btn btn-primary" onclick="guardarJustificacion()" style="padding:8px 16px;display:none;">Guardar</button>
            </div>
        </div>
    </div>

    <div id="popup-bloque-horario" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarModalBloque()">
        <div class="popup-content" style="max-width:90%;width:380px;padding:20px;border-radius:8px;transform:none;transition:none;text-align:left;" onclick="event.stopPropagation()">
            <h4 style="color:var(--primary);margin:0 0 6px 0;font-size:16px;">Seleccionar Clase de Hoy</h4>
            <p style="font-size:13px;color:#666;margin:0 0 14px 0;">Esta materia tiene varios horarios asignados. Elige qué clase vas a dar:</p>
            <div id="bloques-lista" style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;"></div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn" onclick="cerrarModalBloque()" style="padding:8px 16px;background:#eee;color:var(--text-dark);">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarBloqueYGenerar()" style="padding:8px 16px;">Continuar</button>
            </div>
        </div>
    </div>

    <div id="popup-cambiar-estado" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarModalEstado()">
        <div class="popup-content" style="max-width:90%;width:380px;padding:22px;border-radius:8px;transform:none;transition:none;text-align:left;" onclick="event.stopPropagation()">
            <h4 style="color:var(--primary);margin:0 0 4px 0;font-size:16px;">Cambiar Estado de Asistencia</h4>
            <p id="cambiar-estado-estudiante" style="font-size:13.5px;color:#555;margin:0 0 14px 0;"></p>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                <label class="opcion-cambio-label" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:6px;cursor:pointer;background:#fff;" onclick="seleccionarOpcionEstado(this)">
                    <input type="radio" name="cambio_estado_radio" value="presente" style="width:18px !important;height:18px !important;min-width:18px;max-width:18px;margin:0 !important;padding:0 !important;flex-shrink:0;cursor:pointer;accent-color:var(--primary);">
                    <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                        <strong style="color:green;font-size:14px;line-height:1.2;">Presente</strong>
                        <span style="font-size:12px;color:#666;line-height:1.2;">Asistió a la clase</span>
                    </div>
                </label>
                <label class="opcion-cambio-label" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:6px;cursor:pointer;background:#fff;" onclick="seleccionarOpcionEstado(this)">
                    <input type="radio" name="cambio_estado_radio" value="tarde" style="width:18px !important;height:18px !important;min-width:18px;max-width:18px;margin:0 !important;padding:0 !important;flex-shrink:0;cursor:pointer;accent-color:var(--primary);">
                    <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                        <strong style="color:#e67e22;font-size:14px;line-height:1.2;">Tarde</strong>
                        <span style="font-size:12px;color:#666;line-height:1.2;">Llegó tarde a la clase</span>
                    </div>
                </label>
                <label class="opcion-cambio-label" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:6px;cursor:pointer;background:#fff;" onclick="seleccionarOpcionEstado(this)">
                    <input type="radio" name="cambio_estado_radio" value="ausente" style="width:18px !important;height:18px !important;min-width:18px;max-width:18px;margin:0 !important;padding:0 !important;flex-shrink:0;cursor:pointer;accent-color:var(--primary);">
                    <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                        <strong style="color:red;font-size:14px;line-height:1.2;">Ausente</strong>
                        <span style="font-size:12px;color:#666;line-height:1.2;">No asistió a la clase</span>
                    </div>
                </label>
            </div>

            <p style="font-size:12px;color:#666;margin:0 0 16px 0;line-height:1.4;">
                🔒 Este cambio manual quedará registrado en la bitácora del sistema con tu usuario y la fecha actual.
            </p>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn" onclick="cerrarModalEstado()" style="padding:8px 16px;background:#eee;color:var(--text-dark);">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()" style="padding:8px 16px;">Guardar Cambio</button>
            </div>
        </div>
    </div>

    <div id="popup-filtros-asistencia" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarModalFiltros()">
        <div class="popup-content" style="max-width:90%;width:350px;padding:22px;border-radius:8px;transform:none;transition:none;text-align:left;" onclick="event.stopPropagation()">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h4 style="color:var(--primary);margin:0;font-size:16px;">Filtrar Asistencias</h4>
                <button type="button" onclick="cerrarModalFiltros()" style="background:none;border:none;font-size:18px;color:#888;cursor:pointer;padding:0 4px;line-height:1;">✕</button>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-weight:bold;font-size:14px;color:var(--primary);display:block;margin-bottom:6px;">Nivel</label>
                <select id="filtro-modal-nivel" class="prof-select" style="margin-bottom:0;padding:10px 12px;font-size:15px;">
                    <option value="">Todos los niveles</option>
                    <?php foreach ($niveles_disponibles as $niv): ?>
                    <option value="<?= $niv ?>">Nivel <?= nivelRomano($niv) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-weight:bold;font-size:14px;color:var(--primary);display:block;margin-bottom:6px;">Sección</label>
                <select id="filtro-modal-seccion" class="prof-select" style="margin-bottom:0;padding:10px 12px;font-size:15px;">
                    <option value="">Todas las secciones</option>
                    <?php foreach ($secciones_disponibles as $sec): ?>
                    <option value="<?= htmlspecialchars($sec) ?>">Sección <?= htmlspecialchars($sec) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:bold;font-size:14px;color:var(--primary);display:block;margin-bottom:6px;">Semana</label>
                <select id="filtro-modal-semana" class="prof-select" style="margin-bottom:0;padding:10px 12px;font-size:15px;">
                    <option value="">Todas las semanas</option>
                    <?php for ($s = 1; $s <= 12; $s++): ?>
                    <option value="<?= $s ?>">Semana <?= $s ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn" onclick="limpiarFiltrosDesdeModal()" style="padding:8px 16px;background:#eee;color:var(--text-dark);">Limpiar</button>
                <button type="button" class="btn btn-primary" onclick="aplicarFiltrosDesdeModal()" style="padding:8px 16px;">Aplicar</button>
            </div>
        </div>
    </div>

    <div class="mobile-nav">
        <button class="active" onclick="switchTab('generar',this)">Generar QR</button>
        <button onclick="switchTab('asistencia',this)">Asistencias</button>
        <button onclick="switchTab('ajustes',this)">Ajustes</button>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    window.scrollTo(0, 0);
    document.querySelectorAll('.mobile-content').forEach(function(el) { el.classList.add('hidden'); });
    document.getElementById('tab-' + tab).classList.remove('hidden');
    if (btn) {
        document.querySelectorAll('.mobile-nav button').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
    }
    if (tab === 'generar') actualizarInfoCurso();
    if (tab === 'asistencia') cargarEstadoSemanas();
    if (tab === 'ajustes') cargarAjustes();
    if (tab === 'diagnostico') cargarDiagnostico();
}
function toggleMenu() {
    document.getElementById('side-menu').classList.toggle('open');
}
document.getElementById('side-menu')?.addEventListener('click', function(e) {
    if (e.target.tagName === 'DIV' && e.target.classList.contains('menu-item')) {
        this.classList.remove('open');
    }
});
document.addEventListener('click', function(e) {
    var menu = document.getElementById('side-menu');
    if (!menu || !menu.classList.contains('open')) return;
    if (!menu.contains(e.target) && !document.querySelector('.hamburger')?.contains(e.target)) {
        menu.classList.remove('open');
    }
});

var qrInterval = null;
var qrActivo = false;
var sesionActivaId = 0;
var codigoQRActivo = '';
var cursoQRActivo = 0;

function actualizarInfoCurso() {
    var select = document.getElementById('qr-curso');
    if (!select || !select.selectedOptions || !select.selectedOptions[0]) return;
    var opt = select.selectedOptions[0];

    var carrera = opt.getAttribute('data-carrera') || 'Carrera';
    var aula = opt.getAttribute('data-aula') || 'Por asignar';
    var horario = opt.getAttribute('data-horario') || 'No asignado';

    var elCarrera = document.getElementById('line-carrera');
    var elAula = document.getElementById('line-aula');
    var elHorario = document.getElementById('line-horario');

    if (elCarrera) elCarrera.textContent = carrera;
    if (elAula) elAula.textContent = 'Aula: ' + aula;
    if (elHorario) elHorario.textContent = 'Horario: ' + horario;
}

function handleAccionQR() {
    if (qrActivo) {
        var formContainer = document.getElementById('form-generar-container');
        if (formContainer) formContainer.classList.add('hidden');
        var qrArea = document.getElementById('qr-display-area');
        if (qrArea) qrArea.classList.remove('hidden');
    } else {
        iniciarFlujoGenerarQR();
    }
}

function iniciarFlujoGenerarQR() {
    var cursoSelect = document.getElementById('qr-curso');
    if (!cursoSelect) return;
    var opt = cursoSelect.selectedOptions[0];
    if (!opt) return;

    var horarioStr = opt.getAttribute('data-horario') || '';
    var bloques = horarioStr.split('/').map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });

    if (bloques.length > 1) {
        abrirModalBloque(bloques);
    } else {
        ejecutarGenerarQR(bloques[0] || '');
    }
}

function abrirModalBloque(bloques) {
    var dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    var diasAcc = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    var diaHoyNum = new Date().getDay();
    var diaHoy = dias[diaHoyNum];
    var diaHoyAcc = diasAcc[diaHoyNum];

    var matchIndex = 0;
    var preseleccionado = false;
    bloques.forEach(function(b, idx) {
        var bLower = b.toLowerCase();
        if (!preseleccionado && (bLower.indexOf(diaHoy) !== -1 || bLower.indexOf(diaHoyAcc) !== -1)) {
            matchIndex = idx;
            preseleccionado = true;
        }
    });

    var html = '';
    bloques.forEach(function(b, idx) {
        var isChecked = (idx === matchIndex) ? 'checked' : '';
        var isMatch = (idx === matchIndex && preseleccionado);
        var borderStyle = isChecked ? 'border: 2px solid var(--primary); background: #f0f4ff;' : 'border: 1px solid var(--border-color); background: var(--bg-light);';
        var badgeHoy = isMatch ? ' <span style="font-size:11px;background:var(--primary);color:#fff;padding:2px 6px;border-radius:4px;margin-left:6px;font-weight:normal;">Hoy</span>' : '';

        html += '<label class="opcion-bloque-label" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:6px;cursor:pointer;' + borderStyle + '" onclick="seleccionarOpcionBloque(this)">';
        html += '<input type="radio" name="radio_bloque_modal" value="' + encodeURIComponent(b) + '" ' + isChecked + ' style="margin:0;accent-color:var(--primary);width:18px;height:18px;flex-shrink:0;cursor:pointer;">';
        html += '<div style="flex:1;display:flex;align-items:center;line-height:1.2;">';
        html += '<strong style="color:var(--primary);font-size:14px;">' + b + '</strong>' + badgeHoy;
        html += '</div>';
        html += '</label>';
    });

    document.getElementById('bloques-lista').innerHTML = html;
    document.getElementById('popup-bloque-horario').classList.add('show');
}

function seleccionarOpcionBloque(labelEl) {
    document.querySelectorAll('#bloques-lista .opcion-bloque-label').forEach(function(l) {
        l.style.border = '1px solid var(--border-color)';
        l.style.background = 'var(--bg-light)';
    });
    labelEl.style.border = '2px solid var(--primary)';
    labelEl.style.background = '#f0f4ff';
    var radio = labelEl.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function cerrarModalBloque() {
    document.getElementById('popup-bloque-horario').classList.remove('show');
}

function confirmarBloqueYGenerar() {
    var radio = document.querySelector('input[name="radio_bloque_modal"]:checked');
    var bloque = radio ? decodeURIComponent(radio.value) : '';
    cerrarModalBloque();
    ejecutarGenerarQR(bloque);
}

function generarQR() {
    iniciarFlujoGenerarQR();
}

function ejecutarGenerarQR(bloqueHorario) {
    var cursoSelect = document.getElementById('qr-curso');
    if (!cursoSelect) return;
    var cursoId = cursoSelect.value;
    var semana = document.getElementById('qr-semana').value;
    if (!cursoId) { alert('Selecciona un curso'); return; }

    var opt = cursoSelect.selectedOptions[0];
    var nombreCurso = opt ? opt.getAttribute('data-nombre') : 'Materia';
    var seccion = opt ? opt.getAttribute('data-seccion') : '-';

    var btn = document.getElementById('btn-generar-qr');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Generando...';
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'generar_qr.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (btn) {
            btn.disabled = false;
        }
        if (xhr.status !== 200) {
            if (btn) btn.textContent = 'Generar QR';
            alert('Error al generar QR');
            return;
        }
        try {
            var r = JSON.parse(xhr.responseText);
            if (!r.ok) {
                if (btn) btn.textContent = 'Generar QR';
                alert(r.error || 'Error');
                return;
            }

            qrActivo = true;
            sesionActivaId = r.sesion_id || 0;
            codigoQRActivo = r.codigo || '';
            cursoQRActivo = cursoId;

            document.getElementById('qr-image').innerHTML = '<img src="' + r.qr_url + '" alt="QR" style="width:100%;height:100%;object-fit:contain;" />';

            document.getElementById('active-materia-title').textContent = nombreCurso;
            var bloqueStr = r.bloque_horario ? ' (' + r.bloque_horario + ')' : '';
            document.getElementById('active-materia-sub').textContent = 'Sec: ' + seccion + ' - Semana ' + semana + bloqueStr;

            var formContainer = document.getElementById('form-generar-container');
            if (formContainer) formContainer.classList.add('hidden');

            var qrArea = document.getElementById('qr-display-area');
            qrArea.classList.remove('hidden');

            var segundos = r.expiracion_segundos || 1200;
            var segTol = r.tolerancia_segundos || 600;
            mostrarTemporizador(segundos, segTol);
        } catch(e) {
            if (btn) btn.textContent = 'Generar QR';
            console.error('Respuesta recibida:', xhr.responseText);
            alert('Error al procesar respuesta: ' + (xhr.responseText ? xhr.responseText.replace(/<[^>]*>?/gm, ' ').trim().substring(0, 150) : 'Sin datos'));
        }
    };
    xhr.onerror = function() {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Generar QR';
        }
        alert('Error de conexión');
    };
    xhr.send('curso_id=' + encodeURIComponent(cursoId) + '&semana=' + encodeURIComponent(semana) + '&bloque_horario=' + encodeURIComponent(bloqueHorario || ''));
}

function mostrarTemporizador(segundos, segundosTolerancia) {
    if (qrInterval) clearInterval(qrInterval);
    var total = segundos;
    var tol = (typeof segundosTolerancia !== 'undefined') ? segundosTolerancia : 0;

    function actualizar() {
        var timerEl = document.getElementById('qr-timer');
        if (total <= 0) {
            timerEl.innerHTML = '<span style="color:#dc3545;">EXPIRADO</span>';
            qrActivo = false;
            var btn = document.getElementById('btn-generar-qr');
            if (btn) btn.textContent = 'Generar QR';

            if (qrInterval) clearInterval(qrInterval);
            if (cursoQRActivo) {
                var x = new XMLHttpRequest();
                x.open('GET', 'asistencia.php?accion=finalizar_sesion&curso_id=' + cursoQRActivo, true);
                x.send();
            }
            return;
        }

        var m = Math.floor(total / 60);
        var s = total % 60;
        var formatted = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

        if (tol > 0) {
            var mTol = Math.floor(tol / 60);
            var sTol = tol % 60;
            var fmtTol = String(mTol).padStart(2,'0') + ':' + String(sTol).padStart(2,'0');
            timerEl.innerHTML = '<span style="color:#2e7d32;">Puntual: ' + fmtTol + '</span><div style="font-size:12px;color:#666;font-weight:normal;margin-top:3px;">Cierre total en ' + formatted + '</div>';
            tol--;
        } else {
            timerEl.innerHTML = '<span style="color:#e67e22;">Tarde</span><div style="font-size:12px;color:#666;font-weight:normal;margin-top:3px;">Cierre total en ' + formatted + '</div>';
        }
        total--;
    }
    actualizar();
    qrInterval = setInterval(actualizar, 1000);
}

function cerrarQR() {
    var qrArea = document.getElementById('qr-display-area');
    if (qrArea) qrArea.classList.add('hidden');

    var formContainer = document.getElementById('form-generar-container');
    if (formContainer) formContainer.classList.remove('hidden');

    var btn = document.getElementById('btn-generar-qr');
    if (btn) {
        if (qrActivo) {
            btn.textContent = 'Ver QR Activo';
        } else {
            btn.textContent = 'Generar QR';
        }
    }
}

var confirmCallback = null;

function mostrarConfirmacion(titulo, mensaje, textoBtn, esPeligroso, onAceptar) {
    document.getElementById('confirm-titulo').innerHTML = titulo || 'Confirmación';
    document.getElementById('confirm-mensaje').innerHTML = mensaje;
    var icon = document.getElementById('confirm-circle-icon');
    if (icon) {
        if (esPeligroso) {
            icon.style.background = '#dc3545';
            icon.textContent = '!';
        } else {
            icon.style.background = 'var(--secondary)';
            icon.textContent = '?';
        }
    }
    var btn = document.getElementById('btn-confirm-aceptar');
    btn.textContent = textoBtn || 'Aceptar';
    btn.style.backgroundColor = esPeligroso ? '#dc3545' : 'var(--primary)';
    confirmCallback = onAceptar;
    document.getElementById('popup-confirmar').classList.add('show');
}

function cerrarConfirmacion() {
    document.getElementById('popup-confirmar').classList.remove('show');
    confirmCallback = null;
}

document.getElementById('btn-confirm-aceptar')?.addEventListener('click', function() {
    var cb = confirmCallback;
    cerrarConfirmacion();
    if (cb) cb();
});

function mostrarPopupError(texto) {
    var p = document.getElementById('popup-error');
    if (!p) return;
    document.getElementById('popup-error-text').innerHTML = texto;
    p.classList.add('show');
    setTimeout(function() { p.classList.remove('show'); }, 2000);
}

function cancelarQR() {
    mostrarConfirmacion(
        'Cancelar sesión de asistencia',
        '¿Deseas cancelar esta sesión de asistencia? El código QR quedará invalidado inmediatamente.',
        'Sí, cancelar',
        true,
        function() {
            if (qrInterval) clearInterval(qrInterval);
            qrActivo = false;

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'asistencia.php?accion=cancelar_sesion&curso_id=' + cursoQRActivo + '&sesion_id=' + sesionActivaId + '&codigo_qr=' + encodeURIComponent(codigoQRActivo), true);
            xhr.onload = function() {
                sesionActivaId = 0;
                codigoQRActivo = '';
                cursoQRActivo = 0;

                var qrArea = document.getElementById('qr-display-area');
                if (qrArea) qrArea.classList.add('hidden');

                var formContainer = document.getElementById('form-generar-container');
                if (formContainer) formContainer.classList.remove('hidden');

                var btn = document.getElementById('btn-generar-qr');
                if (btn) btn.textContent = 'Generar QR';

                cargarEstadoSemanas();
                mostrarPopup('QR cancelado');
            };
            xhr.send();
        }
    );
}



function mostrarPopup(texto) {
    var p = document.getElementById('popup-exito');
    document.getElementById('popup-text').innerHTML = texto;
    p.classList.add('show');
    setTimeout(function() { p.classList.remove('show'); }, 2000);
}

function cargarEstadoSemanas() {
    document.querySelectorAll('.weeks-list').forEach(function(ul) {
        var cursoId = ul.getAttribute('data-curso');
        var items = ul.querySelectorAll('.clickable-week');
        var semanas = [];
        items.forEach(function(li) { semanas.push(li.getAttribute('data-semana')); });
        if (!cursoId || semanas.length === 0) return;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'asistencia.php?accion=estado_semanas&curso_id=' + cursoId, true);
        xhr.onload = function() {
            if (xhr.status !== 200) return;
            try {
                var r = JSON.parse(xhr.responseText);
                if (!r.ok) return;
                r.semanas.forEach(function(s) {
                    items.forEach(function(li) {
                        if (parseInt(li.getAttribute('data-semana')) === parseInt(s.semana)) {
                            var targetSlot = li.querySelector('.status-slot') || li.querySelector('.btn-ver-asistencia') || li.querySelector('span:last-child');
                            var sesiones = s.sesiones || 0;
                            if (sesiones > 0) {
                                targetSlot.outerHTML = '<button type="button" class="btn btn-ver-asistencia" onclick="event.stopPropagation(); verAsistenciaDesdeBtn(this)" class="btn btn-ver-asistencia" style="padding:6px 14px;font-size:13.5px;background:var(--primary);color:#fff;border-radius:4px;border:none;font-weight:bold;cursor:pointer;">Ver asistencias</button>';
                            } else {
                                targetSlot.outerHTML = '<span class="status-pending status-slot" style="font-weight:bold;color:gray;font-style:italic;">Pendiente</span>';
                            }
                        }
                    });
                });
            } catch(e) {}
        };
        xhr.send();
    });
}

var cursoIdActual = 0;
var semanaActual = 0;

function verAsistenciaDesdeBtn(btn) {
    var li = btn.closest('li.clickable-week');
    if (li) verAsistencia(li);
}

function verAsistencia(el) {
    cursoIdActual = parseInt(el.getAttribute('data-curso'));
    semanaActual = parseInt(el.getAttribute('data-semana'));
    var cursoId = cursoIdActual;
    var semana = semanaActual;

    var details = el.closest('details');
    var cursoNombre = details ? (details.getAttribute('data-curso-nombre-full') || details.querySelector('.curso-title')?.textContent.trim()) : 'Materia';
    var seccion = details ? details.getAttribute('data-seccion') : '';
    var horario = details ? details.getAttribute('data-horario') : '';

    var headerText = cursoNombre;
    if (seccion) headerText += ' - Sec: ' + seccion;
    if (horario) headerText += ' (' + horario + ')';
    headerText += ' - Semana ' + semana;

    document.getElementById('detalle-titulo').textContent = headerText;
    document.getElementById('asistencia-lista').classList.add('hidden');
    document.getElementById('asistencia-detalle').classList.remove('hidden');

    var container = document.getElementById('detalle-container');
    container.innerHTML = '<p style="text-align:center;padding:20px;color:#999;">Cargando...</p>';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'asistencia.php?accion=detalle&curso_id=' + cursoId + '&semana=' + semana, true);
    xhr.onload = function() {
        if (xhr.status !== 200) { container.innerHTML = '<p style="color:red;">Error al cargar</p>'; return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (!r.ok) { container.innerHTML = '<p style="color:#999;">' + (r.error || 'Sin datos') + '</p>'; return; }
            var html = '';
            r.sesiones.forEach(function(ses) {
                var fechaLabel = ses.fecha ? new Date(ses.fecha + 'T12:00:00').toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' }) : 'Sin asistencia registrada';
                var tituloSesion = ses.bloque_horario ? ses.bloque_horario + ' (' + fechaLabel + ')' : fechaLabel;
                html += '<h4 style="color:var(--primary);margin:14px 0 10px;font-size:15px;border-bottom:1px solid #e0e0e0;padding-bottom:5px;">📅 ' + tituloSesion + '</h4>';

                ses.estudiantes.forEach(function(e, i) {
                    var color = e.estado === 'presente' ? 'green' : (e.estado === 'tarde' ? '#e67e22' : (e.estado === 'justificado' ? '#008b8b' : (e.estado === 'ausente' ? 'red' : '#999')));
                    var txtEstado = e.estado === 'justificado' ? 'Justificado' : (e.estado === 'tarde' ? 'Tarde' : (e.estado ? e.estado.charAt(0).toUpperCase() + e.estado.slice(1) : 'Pendiente'));

                    html += '<details class="student-accordion">';
                    html += '<summary>';
                    html += '<span class="student-name">' + (i + 1) + '. ' + e.nombre + '</span>';
                    html += '<span class="student-status" style="color:' + color + ';">' + txtEstado + '</span>';
                    html += '</summary>';
                    html += '<div class="student-details-body">';
                    html += '<div class="detail-row"><span class="detail-label">Cédula</span><span class="detail-value">' + e.cedula + '</span></div>';
                    html += '<div class="detail-row"><span class="detail-label">Hora</span><span class="detail-value">' + (e.hora || '--:--') + '</span></div>';
                    var bloqueAttr = ' data-bloque="' + encodeURIComponent(ses.bloque_horario || '') + '"';
                    var commonAttrs = ' data-inscripcion="' + e.inscripcion_id + '" data-nombre="' + encodeURIComponent(e.nombre || '') + '" data-fecha="' + encodeURIComponent(ses.fecha || '') + '" data-estado="' + (e.estado || 'ausente') + '"' + bloqueAttr;
                    html += '<div class="detail-row" style="align-items:center;padding-top:5px;padding-bottom:2px;">';
                    html += '<span class="detail-label">Acción</span>';
                    html += '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">';
                    if (e.estado === 'justificado') {
                        html += '<button type="button" class="btn btn-ver-just"' + commonAttrs + ' data-justificacion="' + encodeURIComponent(e.justificacion || '') + '" style="padding:7px 14px;font-size:13.5px;font-weight:bold;background:#008b8b;color:#fff;border:none;border-radius:4px;">Ver</button>';
                        html += '<button type="button" class="btn btn-editar-just"' + commonAttrs + ' data-justificacion="' + encodeURIComponent(e.justificacion || '') + '" style="padding:7px 14px;font-size:13.5px;font-weight:bold;background:#008b8b;color:#fff;border:none;border-radius:4px;">Editar</button>';
                        html += '<span style="color:#cbd5e1;font-size:16px;user-select:none;margin:0 2px;">|</span>';
                    } else {
                        html += '<button type="button" class="btn btn-crear-just"' + commonAttrs + ' style="padding:7px 14px;font-size:13.5px;font-weight:bold;background:#008b8b;color:#fff;border:none;border-radius:4px;">Justificar</button>';
                        html += '<span style="color:#cbd5e1;font-size:16px;user-select:none;margin:0 2px;">|</span>';
                    }
                    html += '<button type="button" class="btn btn-cambiar-estado"' + commonAttrs + ' style="padding:7px 14px;font-size:13.5px;font-weight:bold;background:var(--primary);color:#fff;border:none;border-radius:4px;">Cambiar Estado</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</details>';
                });
            });
            if (!r.sesiones.length) {
                html = '<p style="color:#999;text-align:center;">No hay asistencias registradas esta semana.</p>';
            }
            container.innerHTML = html;
            container.querySelectorAll('.btn-ver-just').forEach(function(b) {
                b.onclick = function() {
                    abrirJustificar(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-nombre')),
                        'ver',
                        decodeURIComponent(this.getAttribute('data-justificacion')),
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
            container.querySelectorAll('.btn-editar-just').forEach(function(b) {
                b.onclick = function() {
                    abrirJustificar(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-nombre')),
                        'editar',
                        decodeURIComponent(this.getAttribute('data-justificacion')),
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
            container.querySelectorAll('.btn-eliminar-just').forEach(function(b) {
                b.onclick = function() {
                    eliminarJustificacion(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
            container.querySelectorAll('.btn-marcar-tarde').forEach(function(b) {
                b.onclick = function() {
                    marcarTardeManual(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
            container.querySelectorAll('.btn-crear-just').forEach(function(b) {
                b.onclick = function() {
                    abrirJustificar(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-nombre')),
                        'crear',
                        '',
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
            container.querySelectorAll('.btn-cambiar-estado').forEach(function(b) {
                b.onclick = function() {
                    abrirCambiarEstado(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-nombre')),
                        this.getAttribute('data-estado'),
                        decodeURIComponent(this.getAttribute('data-fecha')),
                        decodeURIComponent(this.getAttribute('data-bloque') || '')
                    );
                };
            });
        } catch(e) { container.innerHTML = '<p style="color:red;">Error al procesar</p>'; }
    };
    xhr.send();
}

function volverClases() {
    document.getElementById('asistencia-lista').classList.remove('hidden');
    document.getElementById('asistencia-detalle').classList.add('hidden');
}

var filtroTexto = '';
var filtroNivel = '';
var filtroSeccion = '';
var filtroSemana = '';

function onBusquedaMateriaInput() {
    var val = document.getElementById('filtro-materia-input').value;
    filtroTexto = val.trim().toLowerCase();

    var btnLimpiar = document.getElementById('btn-limpiar-busqueda');
    if (btnLimpiar) {
        btnLimpiar.style.display = filtroTexto.length > 0 ? 'block' : 'none';
    }

    aplicarFiltrosVisuales();
}

function limpiarBusquedaTexto() {
    var input = document.getElementById('filtro-materia-input');
    if (input) {
        input.value = '';
        input.focus();
    }
    filtroTexto = '';
    var btnLimpiar = document.getElementById('btn-limpiar-busqueda');
    if (btnLimpiar) btnLimpiar.style.display = 'none';
    aplicarFiltrosVisuales();
}

function abrirModalFiltros() {
    if (document.getElementById('filtro-modal-nivel')) document.getElementById('filtro-modal-nivel').value = filtroNivel;
    if (document.getElementById('filtro-modal-seccion')) document.getElementById('filtro-modal-seccion').value = filtroSeccion;
    if (document.getElementById('filtro-modal-semana')) document.getElementById('filtro-modal-semana').value = filtroSemana;
    var p = document.getElementById('popup-filtros-asistencia');
    if (p) p.classList.add('show');
}

function cerrarModalFiltros() {
    var p = document.getElementById('popup-filtros-asistencia');
    if (p) p.classList.remove('show');
}

function limpiarFiltrosDesdeModal() {
    if (document.getElementById('filtro-modal-nivel')) document.getElementById('filtro-modal-nivel').value = '';
    if (document.getElementById('filtro-modal-seccion')) document.getElementById('filtro-modal-seccion').value = '';
    if (document.getElementById('filtro-modal-semana')) document.getElementById('filtro-modal-semana').value = '';
    filtroNivel = '';
    filtroSeccion = '';
    filtroSemana = '';
    cerrarModalFiltros();
    aplicarFiltrosVisuales();
}

function aplicarFiltrosDesdeModal() {
    var elNiv = document.getElementById('filtro-modal-nivel');
    var elSec = document.getElementById('filtro-modal-seccion');
    var elSem = document.getElementById('filtro-modal-semana');

    filtroNivel = elNiv ? elNiv.value : '';
    filtroSeccion = elSec ? elSec.value : '';
    filtroSemana = elSem ? elSem.value : '';

    cerrarModalFiltros();
    aplicarFiltrosVisuales();
}

function limpiarTodosFiltros() {
    limpiarBusquedaTexto();
    filtroNivel = '';
    filtroSeccion = '';
    filtroSemana = '';
    if (document.getElementById('filtro-modal-nivel')) document.getElementById('filtro-modal-nivel').value = '';
    if (document.getElementById('filtro-modal-seccion')) document.getElementById('filtro-modal-seccion').value = '';
    if (document.getElementById('filtro-modal-semana')) document.getElementById('filtro-modal-semana').value = '';
    aplicarFiltrosVisuales();
}

function removerFiltroIndividual(tipo) {
    if (tipo === 'nivel') {
        filtroNivel = '';
        if (document.getElementById('filtro-modal-nivel')) document.getElementById('filtro-modal-nivel').value = '';
    }
    if (tipo === 'seccion') {
        filtroSeccion = '';
        if (document.getElementById('filtro-modal-seccion')) document.getElementById('filtro-modal-seccion').value = '';
    }
    if (tipo === 'semana') {
        filtroSemana = '';
        if (document.getElementById('filtro-modal-semana')) document.getElementById('filtro-modal-semana').value = '';
    }
    aplicarFiltrosVisuales();
}

function aplicarFiltrosVisuales() {
    var countFiltros = (filtroNivel ? 1 : 0) + (filtroSeccion ? 1 : 0) + (filtroSemana ? 1 : 0);
    var badgeActivo = document.getElementById('filtro-badge-activo');
    var btnFiltros = document.getElementById('btn-abrir-filtros');

    if (countFiltros > 0) {
        if (badgeActivo) {
            badgeActivo.textContent = countFiltros;
            badgeActivo.style.display = 'inline-block';
            badgeActivo.style.background = '#fff';
            badgeActivo.style.color = 'var(--primary)';
        }
        if (btnFiltros) {
            btnFiltros.style.background = 'var(--primary)';
            btnFiltros.style.color = '#fff';
            btnFiltros.style.borderColor = 'var(--primary)';
        }
    } else {
        if (badgeActivo) badgeActivo.style.display = 'none';
        if (btnFiltros) {
            btnFiltros.style.background = 'var(--bg-light)';
            btnFiltros.style.color = 'var(--primary)';
            btnFiltros.style.borderColor = 'var(--border-color)';
        }
    }

    var chipsContainer = document.getElementById('active-filters-chips');
    var chipNivel = document.getElementById('chip-nivel');
    var chipSeccion = document.getElementById('chip-seccion');
    var chipSemana = document.getElementById('chip-semana');

    if (chipsContainer) {
        if (hayFiltrosActivos) {
            chipsContainer.style.display = 'flex';

            if (filtroNivel && chipNivel) {
                var selectNiv = document.getElementById('filtro-modal-nivel');
                var txtNiv = selectNiv && selectNiv.options[selectNiv.selectedIndex] ? selectNiv.options[selectNiv.selectedIndex].text : 'Nivel ' + filtroNivel;
                chipNivel.innerHTML = txtNiv + ' <span class="chip-remove" onclick="removerFiltroIndividual(\'nivel\')">✕</span>';
                chipNivel.style.display = 'inline-flex';
            } else if (chipNivel) {
                chipNivel.style.display = 'none';
            }

            if (filtroSeccion && chipSeccion) {
                chipSeccion.innerHTML = 'Sec: ' + filtroSeccion + ' <span class="chip-remove" onclick="removerFiltroIndividual(\'seccion\')">✕</span>';
                chipSeccion.style.display = 'inline-flex';
            } else if (chipSeccion) {
                chipSeccion.style.display = 'none';
            }

            if (filtroSemana && chipSemana) {
                chipSemana.innerHTML = 'Semana ' + filtroSemana + ' <span class="chip-remove" onclick="removerFiltroIndividual(\'semana\')">✕</span>';
                chipSemana.style.display = 'inline-flex';
            } else if (chipSemana) {
                chipSemana.style.display = 'none';
            }
        } else {
            chipsContainer.style.display = 'none';
            if (chipNivel) chipNivel.style.display = 'none';
            if (chipSeccion) chipSeccion.style.display = 'none';
            if (chipSemana) chipSemana.style.display = 'none';
        }
    }

    var totalVisibles = 0;
    var bloques = document.querySelectorAll('.bloque-seccion-group');
    bloques.forEach(function(bloque) {
        var bloqueSec = bloque.getAttribute('data-seccion') || '';
        var bloqueTray = bloque.getAttribute('data-trayecto') || '';

        var matchNivel = (filtroNivel === '' || bloqueTray === filtroNivel);
        var matchSeccion = (filtroSeccion === '' || bloqueSec === filtroSeccion);

        var cursosEnBloque = bloque.querySelectorAll('.curso-item-accordion');
        var cursosVisiblesEnBloque = 0;

        cursosEnBloque.forEach(function(item) {
            var cursoNombre = item.getAttribute('data-curso-nombre') || '';
            var matchTexto = (filtroTexto === '' || cursoNombre.indexOf(filtroTexto) !== -1);

            var weeks = item.querySelectorAll('.clickable-week');
            weeks.forEach(function(w) {
                var sem = w.getAttribute('data-semana');
                if (filtroSemana === '' || sem === filtroSemana) {
                    w.style.display = '';
                } else {
                    w.style.display = 'none';
                }
            });

            if (matchNivel && matchSeccion && matchTexto) {
                item.style.display = '';
                cursosVisiblesEnBloque++;
                totalVisibles++;
                if (filtroSemana !== '' || (filtroTexto !== '' && filtroTexto.length >= 3)) {
                    item.open = true;
                }
            } else {
                item.style.display = 'none';
            }
        });

        if (cursosVisiblesEnBloque > 0) {
            bloque.style.display = '';
        } else {
            bloque.style.display = 'none';
        }
    });

    var noCursosMsg = document.getElementById('no-cursos-filtro');
    if (noCursosMsg) {
        noCursosMsg.style.display = totalVisibles === 0 ? 'block' : 'none';
    }
}

var cambioEstadoInscripcion = 0;
var cambioEstadoFecha = '';
var cambioEstadoBloque = '';
var cambioEstadoActual = '';

function seleccionarOpcionEstado(labelEl) {
    var radio = labelEl.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
    actualizarEstilosRadiosEstado();
}

function actualizarEstilosRadiosEstado() {
    var labels = document.querySelectorAll('#popup-cambiar-estado .opcion-cambio-label');
    labels.forEach(function(l) {
        var r = l.querySelector('input[type="radio"]');
        if (r && r.checked) {
            l.style.borderColor = 'var(--primary)';
            l.style.background = '#f0f4ff';
        } else {
            l.style.borderColor = '#cbd5e1';
            l.style.background = '#fff';
        }
    });
}

function abrirCambiarEstado(inscripcionId, nombre, estadoActual, fecha, bloque) {
    cambioEstadoInscripcion = inscripcionId;
    cambioEstadoFecha = fecha;
    cambioEstadoBloque = bloque || '';
    cambioEstadoActual = estadoActual || 'ausente';

    var txtEstado = cambioEstadoActual === 'justificado' ? 'Justificado' : (cambioEstadoActual === 'tarde' ? 'Tarde' : (cambioEstadoActual ? cambioEstadoActual.charAt(0).toUpperCase() + cambioEstadoActual.slice(1) : 'Pendiente'));
    document.getElementById('cambiar-estado-estudiante').innerHTML = '<strong>' + nombre + '</strong><br><span style="font-size:12.5px;color:#666;">Estado actual: <strong>' + txtEstado + '</strong></span>';

    var radios = document.getElementsByName('cambio_estado_radio');
    radios.forEach(function(r) {
        r.checked = (r.value === cambioEstadoActual);
    });
    actualizarEstilosRadiosEstado();

    document.getElementById('popup-cambiar-estado').classList.add('show');
}

function cerrarModalEstado() {
    document.getElementById('popup-cambiar-estado').classList.remove('show');
}

function confirmarCambioEstado() {
    var radios = document.getElementsByName('cambio_estado_radio');
    var seleccionado = '';
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) { seleccionado = radios[i].value; break; }
    }

    if (!seleccionado) {
        cerrarModalEstado();
        return;
    }

    if (seleccionado === cambioEstadoActual) {
        cerrarModalEstado();
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'asistencia.php?accion=cambiar_estado&curso_id=' + cursoIdActual, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        cerrarModalEstado();
        if (xhr.status === 200) {
            try {
                var r = JSON.parse(xhr.responseText);
                if (r.ok) {
                    mostrarPopup('Estado<br />actualizado');
                    var cursoEl = document.querySelector('[data-curso="' + cursoIdActual + '"][data-semana="' + semanaActual + '"]');
                    if (cursoEl) verAsistencia(cursoEl);
                    return;
                }
            } catch(e) {}
        }
        mostrarPopupError('Error al<br />actualizar');
    };
    xhr.onerror = function() {
        cerrarModalEstado();
        mostrarPopupError('Error de<br />conexión');
    };
    xhr.send('inscripcion_id=' + cambioEstadoInscripcion + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(cambioEstadoFecha) + '&bloque_horario=' + encodeURIComponent(cambioEstadoBloque) + '&nuevo_estado=' + encodeURIComponent(seleccionado));
}

var inscripcionJustificar = 0;
var fechaJustificar = '';
var bloqueJustificar = '';

function abrirJustificar(inscripcionId, estudianteNombre, modo, justificacion, fecha, bloque) {
    inscripcionJustificar = inscripcionId;
    fechaJustificar = fecha;
    bloqueJustificar = bloque || '';
    var popup = document.getElementById('popup-justificar');
    var textarea = document.getElementById('justificar-texto');
    var lectura = document.getElementById('justificar-lectura');
    var btnGuardar = document.getElementById('btn-guardar-justificar');
    var titulo = document.getElementById('justificar-titulo');
    var est = document.getElementById('justificar-estudiante');

    est.textContent = estudianteNombre || '';

    if (modo === 'ver') {
        titulo.textContent = 'Ver justificación';
        textarea.style.display = 'none';
        lectura.style.display = 'block';
        lectura.textContent = justificacion || '(Sin justificación registrada)';
        btnGuardar.style.display = 'none';
    } else if (modo === 'editar') {
        titulo.textContent = 'Editar justificación';
        textarea.style.display = 'block';
        textarea.value = justificacion || '';
        lectura.style.display = 'none';
        btnGuardar.style.display = '';
        btnGuardar.textContent = 'Guardar';
    } else {
        titulo.textContent = 'Justificar inasistencia';
        textarea.style.display = 'block';
        textarea.value = '';
        lectura.style.display = 'none';
        btnGuardar.style.display = '';
        btnGuardar.textContent = 'Guardar';
    }

    popup.classList.add('show');
}

function eliminarJustificacion(inscripcionId, fecha, bloque) {
    mostrarConfirmacion(
        'Eliminar justificación',
        '¿Deseas eliminar la justificación? El estudiante volverá a estar en estado Ausente.',
        'Sí, eliminar',
        true,
        function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'asistencia.php?accion=eliminar_justificacion&curso_id=' + cursoIdActual, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.ok) {
                        mostrarPopup('Justificación<br />eliminada');
                        var cursoEl = document.querySelector('[data-curso="' + cursoIdActual + '"][data-semana="' + semanaActual + '"]');
                        if (cursoEl) verAsistencia(cursoEl);
                    } else {
                        mostrarPopupError(r.error || 'Error al<br />eliminar');
                    }
                } catch(e) { mostrarPopupError('Error al<br />procesar'); }
            };
            xhr.onerror = function() { mostrarPopupError('Error de<br />conexión'); };
            xhr.send('inscripcion_id=' + inscripcionId + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(fecha) + '&bloque_horario=' + encodeURIComponent(bloque || ''));
        }
    );
}

function cerrarJustificar() {
    document.getElementById('popup-justificar').classList.remove('show');
}

function guardarJustificacion() {
    var texto = document.getElementById('justificar-texto').value.trim();
    if (!texto) { mostrarPopupError('Escribe una<br />justificación'); return; }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'asistencia.php?accion=justificar&curso_id=' + cursoIdActual, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status !== 200) { mostrarPopupError('Error de<br />conexión'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) {
                cerrarJustificar();
                mostrarPopup('Justificación<br />Guardada');
                var cursoEl = document.querySelector('[data-curso="' + cursoIdActual + '"][data-semana="' + semanaActual + '"]');
                if (cursoEl) verAsistencia(cursoEl);
            } else {
                mostrarPopupError(r.error || 'Error al<br />guardar');
            }
        } catch(e) { mostrarPopupError('Error al<br />procesar'); }
    };
    xhr.onerror = function() { mostrarPopupError('Error de<br />conexión'); };
    xhr.send('inscripcion_id=' + inscripcionJustificar + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(fechaJustificar) + '&bloque_horario=' + encodeURIComponent(bloqueJustificar) + '&justificacion=' + encodeURIComponent(texto));
}

function guardarAjustes(e) {
    e.preventDefault();
    var form = document.getElementById('ajustes-form');
    var data = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajustes.php', true);
    xhr.onload = function() {
        if (xhr.status !== 200) { alert('Error al guardar'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) { mostrarPopup('¡Ajustes<br />Guardados!'); }
            else { alert(r.error || 'Error'); }
        } catch(e) { alert('Error al procesar respuesta'); }
    };
    xhr.send(data);
    return false;
}

function marcarTardeManual(inscripcionId, fecha, bloque) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'asistencia.php?accion=marcar_tarde&curso_id=' + cursoIdActual, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            mostrarPopup('Marcado<br />como Tarde');
            var cursoEl = document.querySelector('[data-curso="' + cursoIdActual + '"][data-semana="' + semanaActual + '"]');
            if (cursoEl) verAsistencia(cursoEl);
        } else {
            mostrarPopupError('Error al<br />marcar');
        }
    };
    xhr.send('inscripcion_id=' + inscripcionId + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(fecha) + '&bloque_horario=' + encodeURIComponent(bloque || ''));
}

function cargarAjustes() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajustes.php', true);
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok && r.config) {
                var puntualEl = document.getElementById('cfg-puntual');
                var tardeEl = document.getElementById('cfg-tarde');
                if (puntualEl && typeof r.config.tiempo_puntual !== 'undefined') {
                    puntualEl.value = r.config.tiempo_puntual;
                }
                if (tardeEl && typeof r.config.tiempo_tarde !== 'undefined') {
                    tardeEl.value = r.config.tiempo_tarde;
                }
            }
        } catch(e) {}
    };
    xhr.send();
}

function cargarDiagnostico() {
    var info = document.getElementById('diag-info');
    info.innerHTML = '<div style="margin-bottom:10px;color:green;">✅ Cámara: ' + (navigator.mediaDevices && navigator.mediaDevices.getUserMedia ? 'Disponible' : 'No disponible') + '</div>'
        + '<div style="margin-bottom:10px;color:green;">✅ Conexión: ' + (navigator.onLine ? 'En línea' : 'Sin conexión') + '</div>'
        + '<div style="margin-bottom:10px;color:#666;">ℹ️ URL actual: ' + window.location.href + '</div>'
        + '<div style="margin-bottom:10px;color:#666;">ℹ️ Protocolo: ' + window.location.protocol + '</div>';
}

function cambiarPassword(e) {
    e.preventDefault();
    var form = document.getElementById('password-form');
    var data = new FormData(form);
    data.append('accion', 'cambiar_password');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.onload = function() {
        if (xhr.status !== 200) { alert('Error'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok) { mostrarPopup('¡Contraseña<br />Actualizada!'); form.reset(); }
            else { alert(r.error || 'Error'); }
        } catch(e) { alert('Error al procesar respuesta'); }
    };
    xhr.send(data);
    return false;
}

function initPullToRefresh() {
    var container = document.querySelector('.mobile-container');
    var indicator = document.getElementById('ptr-indicator');
    var ptrIcon = document.getElementById('ptr-icon');
    var ptrText = document.getElementById('ptr-text');
    if (!container || !indicator) return;

    var startY = 0;
    var pullDistance = 0;
    var isPulling = false;
    var activeContent = null;

    container.addEventListener('touchstart', function(e) {
        activeContent = document.querySelector('.mobile-content:not(.hidden)');
        if (!activeContent) return;
        if (activeContent.scrollTop <= 0) {
            startY = e.touches[0].clientY;
            isPulling = true;
            pullDistance = 0;
        } else {
            isPulling = false;
        }
    }, { passive: true });

    container.addEventListener('touchmove', function(e) {
        if (!isPulling || !activeContent) return;
        if (activeContent.scrollTop > 0) {
            isPulling = false;
            indicator.classList.remove('pulling');
            indicator.style.transform = '';
            activeContent.style.transform = '';
            return;
        }
        var currentY = e.touches[0].clientY;
        var diff = currentY - startY;
        if (diff > 0) {
            pullDistance = Math.min(diff * 0.45, 75);
            if (pullDistance > 8) {
                indicator.classList.add('pulling');
                indicator.style.transform = 'translateY(' + pullDistance + 'px)';
                activeContent.style.transition = 'none';
                activeContent.style.transform = 'translateY(' + pullDistance + 'px)';
                if (pullDistance >= 46) {
                    ptrIcon.classList.add('flip');
                    ptrText.textContent = 'Suelta para actualizar';
                } else {
                    ptrIcon.classList.remove('flip');
                    ptrText.textContent = 'Desliza para actualizar';
                }
            }
        } else {
            pullDistance = 0;
            indicator.classList.remove('pulling');
            indicator.style.transform = '';
            activeContent.style.transform = '';
        }
    }, { passive: true });

    container.addEventListener('touchend', function() {
        if (!isPulling) return;
        isPulling = false;
        indicator.classList.remove('pulling');
        if (pullDistance >= 46) {
            indicator.classList.add('refreshing');
            indicator.style.transform = 'translateY(50px)';
            if (activeContent) {
                activeContent.style.transition = 'transform 0.2s ease';
                activeContent.style.transform = 'translateY(50px)';
            }
            ptrText.textContent = 'Actualizando...';
            setTimeout(function() {
                window.location.reload();
            }, 350);
        } else {
            indicator.style.transform = '';
            if (activeContent) {
                activeContent.style.transition = 'transform 0.2s ease';
                activeContent.style.transform = '';
                setTimeout(function() {
                    if (activeContent) activeContent.style.transition = '';
                }, 200);
            }
        }
        pullDistance = 0;
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($cursos)): ?>
    cargarEstadoSemanas();
    <?php endif; ?>
    actualizarInfoCurso();
    cargarAjustes();
    initPullToRefresh();
    <?php if ($sesion_activa): ?>
    qrActivo = true;
    sesionActivaId = <?= (int)$sesion_activa['id'] ?>;
    codigoQRActivo = <?= json_encode($sesion_activa['codigo_qr']) ?>;
    cursoQRActivo = <?= (int)$sesion_activa['curso_id'] ?>;
    var btn = document.getElementById('btn-generar-qr');
    if (btn) btn.textContent = 'Ver QR Activo';
    mostrarTemporizador(<?= (int)$qr_activo_segundos ?>, <?= (int)$qr_tolerancia_segundos ?>);
    <?php endif; ?>
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').catch(function(e){ console.log(e); });
    });
}
</script>
</body>
</html>
