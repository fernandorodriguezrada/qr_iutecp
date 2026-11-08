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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#262865" />
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
    <div id="side-menu" class="student-side-menu">
        <div class="menu-item" onclick="switchTab('password');toggleMenu();">🔑 Cambiar Contraseña</div>
        <div class="menu-item" onclick="switchTab('diagnostico');toggleMenu();">🛠️ Diagnóstico</div>
        <div class="menu-item" onclick="location.href='/logout.php'" style="color:red">🚪 Cerrar Sesión</div>
    </div>

    <!-- Tab: Generar QR -->
    <div class="mobile-content" id="tab-generar">
        <h2 style="color:var(--primary);margin-bottom:10px">¡Hola, <?= htmlspecialchars(explode(' ', $_SESSION['nombre'])[0]) ?>!</h2>
        <p style="margin-bottom:20px;color:#666">Gestión de Aula IUTECP</p>
        <div class="card" style="margin-bottom:20px">
            <label style="font-weight:bold;display:block;margin-bottom:10px;color:var(--primary);">Seleccionar Clase de Hoy:</label>
            <?php if (empty($cursos)): ?>
                <p style="color:#999;font-size:14px;">No tienes cursos asignados actualmente.</p>
            <?php else: ?>
            <select id="qr-curso" style="font-size:16px;padding:10px;margin-bottom:15px">
                <?php foreach ($cursos as $c): ?>
                <option value="<?= $c['id'] ?>" data-horario="<?= htmlspecialchars($c['horario']) ?>">
                    <?= htmlspecialchars($c['nombre'] . ' - ' . $c['carrera_nombre'] . ' (Sec: ' . ($c['seccion'] ?: '-') . ')') ?>
                </option>
                <?php endforeach; ?>
            </select>
            <label style="font-weight:bold;display:block;margin-bottom:10px;color:var(--primary);">Semana:</label>
            <select id="qr-semana" style="font-size:16px;padding:10px;margin-bottom:20px">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $i === $semana_actual ? 'selected' : '' ?>>Semana <?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-accent" style="width:100%;font-size:16px;padding:15px" onclick="generarQR()">
                Generar QR Dinámico
            </button>
            <?php endif; ?>
        </div>
        <div class="card hidden" id="qr-display-area" style="text-align:center;">
            <h3 style="color:var(--primary);margin-bottom:15px">Código QR de Asistencia</h3>
            <div id="qr-image" style="width:200px;height:200px;margin:0 auto 20px;border:5px solid var(--white);outline:2px solid var(--border-color);display:flex;align-items:center;justify-content:center;background:#f9f9f9;">
            </div>
            <div class="timer" id="qr-timer" style="font-size:1.5rem;font-weight:bold;color:var(--primary);margin-bottom:20px">Expira en 05:00</div>
            <p style="margin-bottom:20px;font-size:14px;color:#666">Escanea con tu dispositivo</p>
            <button class="btn btn-primary" onclick="cerrarQR()">Ocultar Código</button>
        </div>
    </div>

    <!-- Tab: Asistencias -->
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
            <h3 id="detalle-titulo" style="color:var(--primary);margin-bottom:15px">Materia - Semana</h3>
    <div class="card" style="padding:0;">
        <div id="detalle-container" style="padding:15px;"></div>
    </div>
        </div>
    </div>

    <!-- Tab: Ajustes -->
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

    <!-- Tab: Diagnóstico -->
    <div class="mobile-content hidden" id="tab-diagnostico">
        <h3 style="color:var(--primary);margin-bottom:15px">Diagnóstico de Sistema</h3>
        <p id="diag-info" style="margin-bottom:20px;color:#666">Cargando...</p>
    </div>

    <!-- Popups -->
    <div id="popup-exito" class="scan-popup">
        <div class="popup-content">
            <span class="check-icon">✓</span>
            <p class="popup-text" id="popup-text">¡Operación<br />Exitosa!</p>
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
    document.querySelectorAll('.mobile-content').forEach(function(el) { el.classList.add('hidden'); });
    document.getElementById('tab-' + tab).classList.remove('hidden');
    if (btn) {
        document.querySelectorAll('.mobile-nav button').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
    }
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
function generarQR() {
    var cursoId = document.getElementById('qr-curso').value;
    var semana = document.getElementById('qr-semana').value;
    if (!cursoId) { alert('Selecciona un curso'); return; }

    var btn = document.querySelector('#tab-generar .btn-accent');
    btn.disabled = true;
    btn.textContent = 'Generando...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'generar_qr.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = 'Generar QR Dinámico';
        if (xhr.status !== 200) { alert('Error al generar QR'); return; }
        try {
            var r = JSON.parse(xhr.responseText);
            if (!r.ok) { alert(r.error || 'Error'); return; }

            document.getElementById('qr-image').innerHTML = '<img src="' + r.qr_url + '" alt="QR" style="width:200px;height:200px;" />';
            var segundos = r.expiracion_segundos || 300;
            mostrarTemporizador(segundos);
            document.getElementById('qr-display-area').classList.remove('hidden');
        } catch(e) { alert('Error al procesar respuesta'); }
    };
    xhr.onerror = function() { btn.disabled = false; btn.textContent = 'Generar QR Dinámico'; alert('Error de conexión'); };
    xhr.send('curso_id=' + encodeURIComponent(cursoId) + '&semana=' + encodeURIComponent(semana));
}

function mostrarTemporizador(segundos) {
    if (qrInterval) clearInterval(qrInterval);
    var total = segundos;

    function actualizar() {
        if (total <= 0) {
            document.getElementById('qr-timer').textContent = 'EXPIRADO';
            if (qrInterval) clearInterval(qrInterval);
            var cursoId = document.getElementById('qr-curso').value;
            if (cursoId) {
                var x = new XMLHttpRequest();
                x.open('GET', 'asistencia.php?accion=finalizar_sesion&curso_id=' + cursoId, true);
                x.send();
            }
            return;
        }
        var m = Math.floor(total / 60);
        var s = total % 60;
        document.getElementById('qr-timer').textContent = 'Expira en ' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        total--;
    }
    actualizar();
    qrInterval = setInterval(actualizar, 1000);
}

function cerrarQR() {
    document.getElementById('qr-display-area').classList.add('hidden');
    if (qrInterval) clearInterval(qrInterval);
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
                        if (li.getAttribute('data-semana') == s.semana) {
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
                html += '<h4 style="color:var(--secondary);margin:15px 0 8px;font-size:14px;border-bottom:1px solid #ddd;padding-bottom:4px;">📅 ' + fechaLabel + '</h4>';
                html += '<table style="font-size:13px;margin-bottom:15px;"><thead><tr><th>N°</th><th>Estudiante</th><th>Cédula</th><th>Hora</th><th>Estatus</th></tr></thead><tbody>';
                ses.estudiantes.forEach(function(e, i) {
                    var color = e.estado === 'presente' ? 'green' : (e.estado === 'justificado' ? '#008b8b' : (e.estado === 'ausente' ? 'red' : '#999'));
                    var txtEstado = e.estado === 'justificado' ? 'Justificado' : (e.estado ? e.estado.charAt(0).toUpperCase() + e.estado.slice(1) : 'Pendiente');
                    var onclick = "abrirJustificar(" + e.inscripcion_id + ",'" + (e.nombre || '').replace(/'/g, "\\'") + "','" + e.estado + "','" + (e.justificacion || '').replace(/'/g, "\\'").replace(/\n/g, "\\n") + "','" + ses.fecha + "')";
                    html += '<tr><td>' + (i+1) + '</td><td>' + e.nombre + '</td><td>' + e.cedula + '</td><td>' + (e.hora || '--:--') + '</td><td style="color:' + color + ';font-weight:bold;cursor:pointer;" onclick="' + onclick + '">' + txtEstado + '</td></tr>';
                });
                html += '</tbody></table>';
            });
            if (!r.sesiones.length) {
                html = '<p style="color:#999;text-align:center;">No hay asistencias registradas esta semana.</p>';
            }
            container.innerHTML = html;
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

function abrirJustificar(inscripcionId, estudianteNombre, estado, justificacion, fecha) {
    inscripcionJustificar = inscripcionId;
    fechaJustificar = fecha;
    var popup = document.getElementById('popup-justificar');
    var textarea = document.getElementById('justificar-texto');
    var lectura = document.getElementById('justificar-lectura');
    var btnGuardar = document.getElementById('btn-guardar-justificar');
    var titulo = document.getElementById('justificar-titulo');
    var est = document.getElementById('justificar-estudiante');

    titulo.textContent = estado === 'justificado' ? 'Justificación' : 'Justificar inasistencia';
    est.textContent = estudianteNombre || '';

    if (estado === 'justificado') {
        textarea.style.display = 'none';
        lectura.style.display = 'block';
        lectura.textContent = justificacion || '(Sin texto)';
        btnGuardar.style.display = 'none';
    } else {
        textarea.style.display = 'block';
        lectura.style.display = 'none';
        textarea.value = justificacion || '';
        btnGuardar.style.display = '';
    }

    popup.classList.add('show');
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

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($cursos)): ?>
    cargarEstadoSemanas();
    <?php endif; ?>
    cargarAjustes();
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').catch(function(e){ console.log(e); });
    });
}
</script>
</body>
</html>
