<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/academico.php";
global $pdo;

redirigirSiNoAutenticado();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

if (isset($_GET['accion']) && $_GET['accion'] === 'detalle') {
    $curso_id = (int)($_GET['curso_id'] ?? 0);
    $semana = (int)($_GET['semana'] ?? 1);
    if ($curso_id <= 0 || $semana < 1 || $semana > 12) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
        exit;
    }
    $periodo = !empty($_GET['periodo']) ? $_GET['periodo'] : obtenerPeriodoActual();

    $stmt = $pdo->prepare("
        SELECT u.nombre_completo, u.cedula, a.estado, a.fecha, a.hora_escaneo
        FROM inscripciones i
        JOIN usuarios u ON u.id = i.estudiante_id
        LEFT JOIN asistencias a ON a.inscripcion_id = i.id AND a.semana = ?
        WHERE i.curso_id = ? AND i.periodo = ?
        ORDER BY u.nombre_completo
    ");
    $stmt->execute([$semana, $curso_id, $periodo]);
    $estudiantes = $stmt->fetchAll();

    $stmt2 = $pdo->prepare("SELECT nombre, codigo FROM cursos WHERE id = ?");
    $stmt2->execute([$curso_id]);
    $curso = $stmt2->fetch();

    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'curso' => $curso ? $curso['nombre'] . ' (' . $curso['codigo'] . ')' : '',
        'semana' => $semana,
        'periodo' => $periodo,
        'estudiantes' => $estudiantes,
    ]);
    exit;
}

require_once __DIR__ . "/includes/header.php";

$periodo_selected = $_GET['periodo'] ?? '';
$carrera_selected = (int)($_GET['carrera'] ?? 0);
$search = trim($_GET['search'] ?? '');

$periodos = $pdo->query("SELECT DISTINCT periodo FROM inscripciones ORDER BY periodo DESC")->fetchAll(PDO::FETCH_COLUMN);
$current = obtenerPeriodoActual();
if (!in_array($current, $periodos)) {
    $periodos[] = $current;
}
rsort($periodos);

$carreras = $pdo->query("SELECT id, nombre FROM carreras ORDER BY nombre")->fetchAll();

$where_clauses = ["c.activo = 1"];
$params = [];

if (!empty($periodo_selected)) {
    $where_clauses[] = "i.periodo = ?";
    $params[] = $periodo_selected;
}
if ($carrera_selected > 0) {
    $where_clauses[] = "c.carrera_id = ?";
    $params[] = $carrera_selected;
}
if ($search) {
    $where_clauses[] = "(c.nombre LIKE ? OR c.codigo LIKE ? OR ue.nombre_completo LIKE ? OR ue.cedula LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where_clauses);

$join_sql = "";
$join_params = [];
if (!empty($periodo_selected)) {
    $join_sql .= " AND i.periodo = ?";
    $join_params[] = $periodo_selected;
}

$stmt = $pdo->prepare("
    SELECT DISTINCT c.id, c.nombre, c.codigo, c.seccion,
           u.nombre_completo as profesor,
           cr.nombre as carrera_nombre
    FROM cursos c
    LEFT JOIN inscripciones i ON i.curso_id = c.id $join_sql
    LEFT JOIN usuarios ue ON ue.id = i.estudiante_id
    JOIN usuarios u ON u.id = c.profesor_id
    JOIN carreras cr ON cr.id = c.carrera_id
    WHERE $where_sql
    ORDER BY cr.nombre, c.nombre
");
$stmt->execute(array_merge($join_params, $params));
$cursos = $stmt->fetchAll();

$detail_periodo = !empty($periodo_selected) ? $periodo_selected : $current;
?>
<div id="admin-reports">
    <button id="btn-volver-semanas" class="btn hidden" style="margin-bottom:15px;background:#eee;color:var(--text-dark)" onclick="volverReportes()">
        ⬅ Volver a semanas
    </button>
    <h2 style="color:var(--primary);margin-bottom:20px">Reportes y Datos</h2>

    <div class="card" style="margin-bottom:15px">
        <div class="filters">
            <form method="GET" action="" style="display:contents" onkeydown="if(event.key==='Enter'&&event.target.tagName!=='TEXTAREA'){event.preventDefault();this.querySelector('button[type=submit]').click()}">
                <input type="text" name="search" placeholder="Buscar materia, código o estudiante..." value="<?= htmlspecialchars($search) ?>" />
                <select name="periodo">
                    <option value="">Todos los períodos</option>
                    <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p ?>" <?= $p === $periodo_selected ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="carrera">
                    <option value="">Todas las carreras</option>
                    <?php foreach ($carreras as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $carrera_selected ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <div id="admin-reports-list">
        <?php if (empty($cursos)): ?>
        <p style="padding:20px;text-align:center;color:#888">No hay cursos para este período.</p>
        <?php else: ?>
        <h3 style="color:var(--secondary);margin-bottom:15px;font-size:16px">
            <?= count($cursos) ?> curso(s) encontrados
        </h3>
        <div class="admin-accordions-grid">
            <?php foreach ($cursos as $curso): ?>
            <details class="class-accordion" open>
                <summary><?= htmlspecialchars($curso['nombre'] ?? '') ?><?= $curso['seccion'] ? ' - ' . htmlspecialchars($curso['seccion']) : '' ?></summary>
                <ul class="weeks-list">
                    <?php for ($w = 1; $w <= 12; $w++): ?>
                    <li class="clickable-week" onclick="viewReportDetalle(<?= $curso['id'] ?>, <?= $w ?>, '<?= htmlspecialchars($curso['nombre'] ?? '', ENT_QUOTES) ?>', '<?= $detail_periodo ?>')">
                        <span>Semana <?= $w ?></span>
                        <span style="color:var(--secondary);font-weight:bold">Ver detalle ></span>
                    </li>
                    <?php endfor; ?>
                </ul>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="admin-reports-detail" class="hidden">
        <h3 id="report-detail-title" style="color:var(--primary);margin-bottom:15px">Seleccione una semana</h3>
        <div style="overflow-x:auto;background:white;border:1px solid var(--border-color);border-radius:4px">
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody id="report-detail-body">
                    <tr><td colspan="5" style="text-align:center;color:#888">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function viewReportDetalle(cursoId, semana, cursoNombre, periodo) {
    document.getElementById('admin-reports-list').classList.add('hidden');
    document.getElementById('admin-reports-detail').classList.remove('hidden');
    document.getElementById('btn-volver-semanas').classList.remove('hidden');
    document.getElementById('report-detail-title').innerText = cursoNombre + ' - Semana ' + semana;
    document.getElementById('report-detail-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888">Cargando...</td></tr>';

    fetch('?accion=detalle&curso_id=' + cursoId + '&semana=' + semana + '&periodo=' + encodeURIComponent(periodo))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) {
                document.getElementById('report-detail-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:red">Error al cargar datos</td></tr>';
                return;
            }
            var html = '';
            data.estudiantes.forEach(function(e) {
                var estado = e.estado || 'pendiente';
                var color = estado === 'presente' ? 'green' : (estado === 'tarde' ? '#e67e22' : (estado === 'justificado' ? '#008b8b' : (estado === 'ausente' ? 'red' : '#888')));
                var fecha = e.fecha || '--';
                var hora = e.hora_escaneo ? e.hora_escaneo.substring(0, 5) : '--:--';
                html += '<tr>' +
                    '<td>' + e.cedula + '</td>' +
                    '<td>' + e.nombre_completo + '</td>' +
                    '<td>' + fecha + '</td>' +
                    '<td style="color:' + color + ';font-weight:bold">' + estado.charAt(0).toUpperCase() + estado.slice(1) + '</td>' +
                    '<td>' + hora + '</td>' +
                    '</tr>';
            });
            if (!html) {
                html = '<tr><td colspan="5" style="text-align:center;color:#888">Sin registros de asistencia</td></tr>';
            }
            document.getElementById('report-detail-body').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('report-detail-body').innerHTML = '<tr><td colspan="5" style="text-align:center;color:red">Error de conexión</td></tr>';
        });
}

function volverReportes() {
    document.getElementById('admin-reports-list').classList.remove('hidden');
    document.getElementById('admin-reports-detail').classList.add('hidden');
    document.getElementById('btn-volver-semanas').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
