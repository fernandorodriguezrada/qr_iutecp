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

$stmt = $pdo->prepare("
    SELECT c.id, c.nombre, c.codigo, c.seccion, c.horario, c.aula,
           cr.nombre as carrera_nombre
    FROM cursos c
    JOIN carreras cr ON c.carrera_id = cr.id
    WHERE c.profesor_id = ? AND c.activo = 1
    ORDER BY cr.nombre, c.nombre
");
$stmt->execute([$profesor_id]);
$cursos = $stmt->fetchAll();
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
        <h2 style="color:var(--primary);margin-bottom:5px">¡Hola, <?= htmlspecialchars(explode(' ', $_SESSION['nombre'])[0]) ?>!</h2>
        <p style="margin-bottom:15px;color:#666">Gestión de Aula IUTECP</p>

        <div class="card" id="form-generar-container" style="margin-bottom:15px;padding:15px 18px;">
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

        <div class="card hidden" id="qr-display-area" style="text-align:center;padding:15px 18px;margin-bottom:15px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid var(--border-color);padding-bottom:8px;">
                <div style="text-align:left;">
                    <strong id="active-materia-title" style="color:var(--primary);font-size:15px;display:block;">Materia</strong>
                    <span id="active-materia-sub" style="color:#666;font-size:12.5px;">Sec: - - Semana -</span>
                </div>
                <button type="button" class="btn btn-danger" style="padding:6px 10px;font-size:14px;line-height:1;display:inline-flex;align-items:center;justify-content:center;" onclick="cancelarQR()" title="Cancelar sesión de asistencia">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </div>

            <div id="qr-image" style="width:185px;height:185px;margin:0 auto 12px;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;background:#fff;padding:6px;">
            </div>

            <div class="timer" id="qr-timer" style="font-size:1.3rem;font-weight:bold;color:var(--primary);margin-bottom:12px">Expira en 05:00</div>

            <button class="btn btn-primary" style="width:100%;padding:10px;font-size:14px;" onclick="cerrarQR()">Ocultar Código</button>
        </div>
    </div>

    <div class="mobile-content hidden" id="tab-asistencia">
        <div id="asistencia-lista">
            <h2 style="color:var(--primary);margin-bottom:10px">Registro de Asistencias</h2>
            <p style="margin-bottom:20px;color:#666">Seleccione una materia y semana</p>
            <?php if (empty($cursos)): ?>
                <p style="color:#999;font-size:14px;">No tienes cursos asignados.</p>
            <?php else: ?>
            <?php foreach ($cursos as $c):
                $semana_inicio = 1;
                $semana_hoy = $semana_actual;
            ?>
            <details class="class-accordion">
                <summary><?= htmlspecialchars($c['nombre'] . ' - Sec: ' . ($c['seccion'] ?: '-') . ($c['horario'] ? ' (' . $c['horario'] . ')' : '')) ?></summary>
                <ul class="weeks-list" data-curso="<?= $c['id'] ?>">
                    <?php for ($s = 1; $s <= 12; $s++): ?>
                    <li class="clickable-week" data-curso="<?= $c['id'] ?>" data-semana="<?= $s ?>" onclick="verAsistencia(this)">
                        <span>Semana <?= $s ?></span>
                        <span class="status-pending" style="font-weight:bold">Pendiente</span>
                    </li>
                    <?php endfor; ?>
                </ul>
            </details>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div id="asistencia-detalle" class="hidden">
            <button class="btn" style="margin-bottom:15px;background:#eee;color:var(--text-dark);" onclick="volverClases()">⬅ Volver a clases</button>
            <h3 id="detalle-titulo" style="color:var(--primary);margin-bottom:12px;font-size:15px;line-height:1.35;word-break:break-word;">Materia - Semana</h3>
            <div id="detalle-container"></div>
        </div>
    </div>

    <div class="mobile-content hidden" id="tab-ajustes">
        <h2 style="color:var(--primary);margin-bottom:10px">Ajustes de Escaneo QR</h2>
        <p style="margin-bottom:20px;color:#666">Configuración de seguridad y expiración</p>
        <div class="card">
            <form id="ajustes-form" onsubmit="return guardarAjustes(event)">
                <label>Tiempo de expiración base (Minutos)</label>
                <input type="number" name="expiracion_minutos" id="cfg-expiracion" value="5" min="1" max="60" required />
                <label>Frecuencia de rotación dinámica (Segundos)</label>
                <input type="number" name="rotacion_segundos" id="cfg-rotacion" value="30" min="10" max="300" required />
                <label>Radio de validación GPS (Metros)</label>
                <input type="number" name="radio_gps" id="cfg-radio" value="50" min="10" max="500" required />
                <button class="btn btn-primary" style="width:100%;margin-top:10px">Guardar Ajustes</button>
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
        generarQR();
    }
}

function generarQR() {
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
            document.getElementById('active-materia-sub').textContent = 'Sec: ' + seccion + ' - Semana ' + semana;

            var formContainer = document.getElementById('form-generar-container');
            if (formContainer) formContainer.classList.add('hidden');

            var qrArea = document.getElementById('qr-display-area');
            qrArea.classList.remove('hidden');

            var segundos = r.expiracion_segundos || 300;
            mostrarTemporizador(segundos);
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
    xhr.send('curso_id=' + encodeURIComponent(cursoId) + '&semana=' + encodeURIComponent(semana));
}

function mostrarTemporizador(segundos) {
    if (qrInterval) clearInterval(qrInterval);
    var total = segundos;

    function actualizar() {
        if (total <= 0) {
            document.getElementById('qr-timer').textContent = 'EXPIRADO';
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
        var formatted = 'Expira en ' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        document.getElementById('qr-timer').textContent = formatted;
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
                            var span = li.querySelector('span:last-child');
                            var sesiones = s.sesiones || 0;
                            if (s.contador > 0) {
                                span.className = s.completas === s.contador ? 'status-present' : 'status-absent';
                                span.textContent = s.completas + '/' + s.contador + ' Presentes';
                                if (sesiones > 1) {
                                    span.textContent += ' (' + sesiones + ' sesiones)';
                                }
                            } else {
                                span.className = 'status-pending';
                                span.textContent = 'Pendiente';
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

function verAsistencia(el) {
    cursoIdActual = parseInt(el.getAttribute('data-curso'));
    semanaActual = parseInt(el.getAttribute('data-semana'));
    var cursoId = cursoIdActual;
    var semana = semanaActual;
    var cursoNombre = el.closest('details').querySelector('summary').textContent.trim();

    document.getElementById('detalle-titulo').textContent = cursoNombre + ' - Semana ' + semana;
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
                html += '<h4 style="color:var(--primary);margin:12px 0 10px;font-size:13.5px;border-bottom:1px solid #e0e0e0;padding-bottom:5px;">📅 ' + fechaLabel + '</h4>';

                ses.estudiantes.forEach(function(e, i) {
                    var color = e.estado === 'presente' ? 'green' : (e.estado === 'justificado' ? '#008b8b' : (e.estado === 'ausente' ? 'red' : '#999'));
                    var txtEstado = e.estado === 'justificado' ? 'Justificado' : (e.estado ? e.estado.charAt(0).toUpperCase() + e.estado.slice(1) : 'Pendiente');

                    html += '<details class="student-accordion">';
                    html += '<summary>';
                    html += '<span class="student-name">' + (i + 1) + '. ' + e.nombre + '</span>';
                    html += '<span class="student-status" style="color:' + color + ';">' + txtEstado + '</span>';
                    html += '</summary>';
                    html += '<div class="student-details-body">';
                    html += '<div class="detail-row"><span class="detail-label">Cédula</span><span class="detail-value">' + e.cedula + '</span></div>';
                    html += '<div class="detail-row"><span class="detail-label">Hora</span><span class="detail-value">' + (e.hora || '--:--') + '</span></div>';
                    if (e.estado === 'justificado') {
                        if (e.justificacion) {
                            html += '<div class="detail-row" style="flex-direction:column;align-items:flex-start;gap:4px;"><span class="detail-label">Motivo de justificación</span><span class="detail-value" style="font-weight:normal;color:#008b8b;font-style:italic;">' + e.justificacion + '</span></div>';
                        }
                        html += '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;padding-top:4px;">';
                        html += '<button type="button" class="btn btn-ver-just" data-inscripcion="' + e.inscripcion_id + '" data-nombre="' + encodeURIComponent(e.nombre || '') + '" data-justificacion="' + encodeURIComponent(e.justificacion || '') + '" data-fecha="' + encodeURIComponent(ses.fecha || '') + '" style="padding:5px 12px;font-size:12.5px;background:#eee;color:var(--text-dark);border:1px solid #ccc;border-radius:4px;">Ver</button>';
                        html += '<button type="button" class="btn btn-editar-just" data-inscripcion="' + e.inscripcion_id + '" data-nombre="' + encodeURIComponent(e.nombre || '') + '" data-justificacion="' + encodeURIComponent(e.justificacion || '') + '" data-fecha="' + encodeURIComponent(ses.fecha || '') + '" style="padding:5px 12px;font-size:12.5px;background:var(--secondary);color:#fff;border-radius:4px;">Editar</button>';
                        html += '<button type="button" class="btn btn-danger btn-eliminar-just" data-inscripcion="' + e.inscripcion_id + '" data-fecha="' + encodeURIComponent(ses.fecha || '') + '" style="padding:5px 12px;font-size:12.5px;border-radius:4px;">Quitar</button>';
                        html += '</div>';
                    } else {
                        html += '<div style="display:flex;justify-content:flex-end;margin-top:10px;padding-top:4px;">';
                        html += '<button type="button" class="btn btn-primary btn-crear-just" data-inscripcion="' + e.inscripcion_id + '" data-nombre="' + encodeURIComponent(e.nombre || '') + '" data-fecha="' + encodeURIComponent(ses.fecha || '') + '" style="padding:6px 14px;font-size:13px;border-radius:4px;">Justificar inasistencia</button>';
                        html += '</div>';
                    }
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
                        decodeURIComponent(this.getAttribute('data-fecha'))
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
                        decodeURIComponent(this.getAttribute('data-fecha'))
                    );
                };
            });
            container.querySelectorAll('.btn-eliminar-just').forEach(function(b) {
                b.onclick = function() {
                    eliminarJustificacion(
                        parseInt(this.getAttribute('data-inscripcion')),
                        decodeURIComponent(this.getAttribute('data-fecha'))
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
                        decodeURIComponent(this.getAttribute('data-fecha'))
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

var inscripcionJustificar = 0;
var fechaJustificar = '';

function abrirJustificar(inscripcionId, estudianteNombre, modo, justificacion, fecha) {
    inscripcionJustificar = inscripcionId;
    fechaJustificar = fecha;
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

function eliminarJustificacion(inscripcionId, fecha) {
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
            xhr.send('inscripcion_id=' + inscripcionId + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(fecha));
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
    xhr.send('inscripcion_id=' + inscripcionJustificar + '&semana=' + semanaActual + '&fecha=' + encodeURIComponent(fechaJustificar) + '&justificacion=' + encodeURIComponent(texto));
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

function cargarAjustes() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajustes.php', true);
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.ok && r.config) {
                document.getElementById('cfg-expiracion').value = r.config.expiracion_minutos;
                document.getElementById('cfg-rotacion').value = r.config.rotacion_segundos;
                document.getElementById('cfg-radio').value = r.config.radio_gps;
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
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').catch(function(e){ console.log(e); });
    });
}
</script>
</body>
</html>
