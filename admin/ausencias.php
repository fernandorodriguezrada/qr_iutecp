<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

global $pdo;

redirigirSiNoAutenticado();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$periodo_actual = date('Y') . '-II';
$filtro_periodo = $_GET['periodo'] ?? $periodo_actual;
$filtro_carrera = $_GET['carrera'] ?? '';
$filtro_min_pct = (int)($_GET['min_pct'] ?? 0);
$filtro_nivel = (int)($_GET['nivel'] ?? 0);

$where = [];
$params = [$filtro_periodo];

$where[] = "i.periodo = ?";

if (!empty($filtro_carrera)) {
    $where[] = "u.carrera_id = ?";
    $params[] = $filtro_carrera;
}

if ($filtro_nivel > 0) {
    $where[] = "c.trayecto = ?";
    $params[] = $filtro_nivel;
}

$where_sql = implode(" AND ", $where);

$stmt = $pdo->prepare("
    SELECT 
        u.cedula,
        u.nombre_completo as estudiante,
        ca.nombre as carrera,
        c.nombre as curso,
        c.codigo,
        c.seccion,
        c.trayecto,
        i.estudiante_id,
        i.id as insc_id,
        COUNT(a.id) as total_sesiones,
        SUM(CASE WHEN a.estado = 'presente' THEN 1 ELSE 0 END) as presentes,
        SUM(CASE WHEN a.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
        SUM(CASE WHEN a.estado = 'justificado' THEN 1 ELSE 0 END) as justificados,
        ROUND((SUM(CASE WHEN a.estado != 'presente' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100) as pct_ausencia
    FROM inscripciones i
    JOIN usuarios u ON i.estudiante_id = u.id
    JOIN carreras ca ON u.carrera_id = ca.id
    JOIN cursos c ON i.curso_id = c.id
    LEFT JOIN asistencias a ON a.inscripcion_id = i.id
    WHERE $where_sql
    GROUP BY i.estudiante_id, i.curso_id, i.id
    HAVING COUNT(a.id) > 0 AND pct_ausencia >= ?
    ORDER BY ca.nombre, c.trayecto, c.nombre, u.nombre_completo
");
$stmt->execute(array_merge($params, [$filtro_min_pct]));
$rows = $stmt->fetchAll();

$just_map = [];
if (!empty($rows)) {
    $insc_ids = array_column($rows, 'insc_id');
    $placeholders = implode(',', array_fill(0, count($insc_ids), '?'));
    $st = $pdo->prepare("SELECT a.inscripcion_id, a.fecha, a.justificacion, c.nombre as curso, c.codigo, c.seccion FROM asistencias a JOIN inscripciones i ON i.id = a.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE a.inscripcion_id IN ($placeholders) AND a.justificacion IS NOT NULL AND a.justificacion != '' ORDER BY a.fecha");
    $st->execute($insc_ids);
    while ($j = $st->fetch()) {
        $just_map[$j['inscripcion_id']][] = ['f' => $j['fecha'], 't' => $j['justificacion'], 'm' => $j['curso'] . ' (' . $j['codigo'] . ')', 's' => $j['seccion']];
    }
}

$carreras = $pdo->query("SELECT id, CONCAT(nombre, ' (', nivel, ')') as label FROM carreras ORDER BY nombre")->fetchAll();

$niveles = $pdo->query("SELECT DISTINCT trayecto FROM cursos ORDER BY trayecto")->fetchAll(PDO::FETCH_COLUMN);

$cursos_lista = [];
foreach ($rows as $r) {
    $cursos_lista[$r['codigo']] = ['nombre' => $r['curso'], 'codigo' => $r['codigo']];
}
ksort($cursos_lista);

require_once __DIR__ . '/includes/header.php';
?>
<div id="admin-dashboard">
    <button class="btn" style="margin-bottom:15px;background:#eee;color:var(--text-dark)" onclick="window.location.href='/admin/index.php'">
        ⬅ Volver al Panel
    </button>
    <h2 style="color:var(--primary);margin-bottom:10px">Ausencias Prolongadas</h2>
    <p style="margin-bottom:20px;color:#666">Detección de patrones de inasistencia por estudiante y curso</p>

    <div class="card" style="margin-bottom:20px">
        <form method="get" class="filters" onkeydown="if(event.key==='Enter'){event.preventDefault();this.querySelector('button[type=submit]').click()}">
            <select name="periodo">
                <?php
                $periodos = $pdo->query("SELECT DISTINCT periodo FROM inscripciones ORDER BY periodo DESC")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($periodos as $p):
                ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= $filtro_periodo === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="carrera">
                <option value="">Todas las carreras</option>
                <?php foreach ($carreras as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filtro_carrera == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="nivel">
                <option value="0">Todos los niveles</option>
                <?php foreach ($niveles as $n): ?>
                <option value="<?= $n ?>" <?= $filtro_nivel === (int)$n ? 'selected' : '' ?>>Nivel <?= $n ?></option>
                <?php endforeach; ?>
            </select>
            <select name="min_pct">
                <option value="0" <?= $filtro_min_pct === 0 ? 'selected' : '' ?>>Mostrar todos</option>
                <option value="25" <?= $filtro_min_pct === 25 ? 'selected' : '' ?>>≥ 25% (en riesgo)</option>
                <option value="30" <?= $filtro_min_pct === 30 ? 'selected' : '' ?>>≥ 30% (crítico)</option>
                <option value="50" <?= $filtro_min_pct === 50 ? 'selected' : '' ?>>≥ 50% (muy crítico)</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>

    <?php
    $en_riesgo = 0;
    $criticos = 0;
    foreach ($rows as $r) {
        if ((int)$r['pct_ausencia'] >= 30) $criticos++;
        elseif ((int)$r['pct_ausencia'] >= 25) $en_riesgo++;
    }
    ?>

    <div class="dashboard-cards" style="margin-bottom:20px">
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">En Riesgo (25-29%)</p>
            <p style="font-size:24px;color:#e67e22;font-weight:bold;margin-top:5px"><?= $en_riesgo ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Críticos (≥30%)</p>
            <p style="font-size:24px;color:#dc3545;font-weight:bold;margin-top:5px"><?= $criticos ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Registros Analizados</p>
            <p style="font-size:24px;color:var(--primary);font-weight:bold;margin-top:5px"><?= count($rows) ?></p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Umbral Aplicado</p>
            <p style="font-size:20px;color:var(--primary);font-weight:bold;margin-top:5px"><?= $filtro_min_pct > 0 ? "≥ {$filtro_min_pct}%" : 'Todos' ?></p>
        </div>
    </div>

    <div class="card" style="margin-bottom:15px">
        <input type="text" id="search-ausencias" placeholder="Buscar por nombre o cédula..." oninput="filtrarAusencias(this.value)" style="width:100%;padding:12px;border:1px solid var(--border-color);border-radius:6px;font-size:14px;box-sizing:border-box;">
    </div>

    <div class="card">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:15px;">
            <h3 style="color:var(--primary);margin:0;">Detalle por Estudiante y Curso</h3>
            <div id="curso-chips" style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="btn btn-primary" style="padding:4px 10px;font-size:12px;" onclick="filtrarCurso('')">Todos</button>
                <?php foreach ($cursos_lista as $c): ?>
                <button class="btn" style="padding:4px 10px;font-size:12px;background:var(--bg-light);" data-codigo="<?= htmlspecialchars($c['codigo']) ?>" onclick="filtrarCurso('<?= htmlspecialchars($c['codigo']) ?>')"><?= htmlspecialchars($c['codigo']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Estudiante</th>
                        <th>Carrera</th>
                        <th>Nivel</th>
                        <th>Sec.</th>
                        <th>Total</th>
                        <th style="color:green">P</th>
                        <th style="color:#dc3545">A</th>
                        <th style="color:#008b8b">J</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) === 0): ?>
                    <tr><td colspan="10" style="text-align:center;color:#888;padding:20px">No se encontraron registros con los filtros seleccionados</td></tr>
                    <?php else: ?>
                    <?php foreach ($rows as $r):
                        $pct = (int)$r['pct_ausencia'];
                        $color = $pct >= 30 ? '#dc3545' : ($pct >= 25 ? '#e67e22' : 'green');
                        $justs = $just_map[$r['insc_id']] ?? [];
                    ?>
                    <tr class="ausencia-row" data-nombre="<?= htmlspecialchars($r['estudiante'])?>" data-cedula="<?= htmlspecialchars($r['cedula'])?>" data-curso="<?= htmlspecialchars($r['codigo'])?>">
                        <td><?= htmlspecialchars($r['cedula']) ?></td>
                        <td style="font-weight:bold"><?= htmlspecialchars($r['estudiante']) ?></td>
                        <td><?= htmlspecialchars($r['carrera']) ?></td>
                        <td>Nivel <?= (int)$r['trayecto'] ?></td>
                        <td><?= htmlspecialchars($r['seccion']) ?></td>
                        <td><?= $r['total_sesiones'] ?></td>
                        <td style="color:green;font-weight:bold"><?= $r['presentes'] ?></td>
                        <td style="color:#dc3545"><?= $r['ausencias'] ?></td>
                        <td style="cursor:pointer;color:#008b8b;font-weight:bold" onclick='abrirJustAdmin(<?= json_encode($justs) ?>)'><?= $r['justificados'] ?></td>
                        <td style="color:<?= $color ?>;font-weight:bold"><?= $pct ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="popup-just-admin" class="scan-popup" style="transition:none;" onclick="if(event.target===this)cerrarJustAdmin()">
    <div class="popup-content" style="max-width:90%;width:420px;padding:20px;border-radius:8px;transform:none;transition:none;text-align:left;" onclick="event.stopPropagation()">
        <h4 style="color:var(--primary);margin:0 0 15px 0;font-size:15px;">Justificaciones</h4>
        <div id="just-admin-lista"></div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
            <button class="btn" onclick="cerrarJustAdmin()" style="padding:8px 16px;">Cerrar</button>
        </div>
    </div>
</div>

<script>
function abrirJustAdmin(justs) {
    var container = document.getElementById('just-admin-lista');
    if (!justs || justs.length === 0) {
        container.innerHTML = '<p style="color:#999;font-size:14px;">Sin justificaciones registradas.</p>';
    } else {
        var html = '';
        justs.forEach(function(j) {
            var txt = document.createElement('div');
            txt.textContent = j.t;
            html += '<div style="margin-bottom:12px;padding:12px;background:var(--bg-light);border-radius:6px;">';
            html += '<div style="font-size:13px;color:var(--primary);font-weight:bold;margin-bottom:4px;">' + j.m + '</div>';
            html += '<div style="font-size:12px;color:#666;margin-bottom:2px;">Sección ' + j.s + ' &middot; ' + j.f + '</div>';
            html += '<div style="font-size:14px;color:#000;margin-top:6px;padding-top:6px;border-top:1px solid #ddd;">' + txt.innerHTML + '</div>';
            html += '</div>';
        });
        container.innerHTML = html;
    }
    document.getElementById('popup-just-admin').classList.add('show');
}

function cerrarJustAdmin() {
    document.getElementById('popup-just-admin').classList.remove('show');
}

var cursoActivo = '';

function filtrarAusencias(valor) {
    var q = valor.toLowerCase().trim();
    var filas = document.querySelectorAll('.ausencia-row');
    filas.forEach(function(f) {
        var nom = (f.getAttribute('data-nombre') || '').toLowerCase();
        var ced = (f.getAttribute('data-cedula') || '').toLowerCase();
        var cur = f.getAttribute('data-curso') || '';
        var coincideTexto = !q || nom.indexOf(q) !== -1 || ced.indexOf(q) !== -1;
        var coincideCurso = !cursoActivo || cur === cursoActivo;
        f.style.display = (coincideTexto && coincideCurso) ? '' : 'none';
    });
}

function filtrarCurso(codigo) {
    cursoActivo = codigo;
    document.querySelectorAll('#curso-chips .btn').forEach(function(b) {
        var esActivo = (b.getAttribute('data-codigo') || '') === codigo || (!codigo && !b.getAttribute('data-codigo'));
        b.className = esActivo ? 'btn btn-primary' : 'btn';
        b.style.cssText = esActivo ? 'padding:4px 10px;font-size:12px;' : 'padding:4px 10px;font-size:12px;background:var(--bg-light);';
    });
    var search = document.getElementById('search-ausencias');
    filtrarAusencias(search ? search.value : '');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
