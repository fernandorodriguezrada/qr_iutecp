<?php
require_once __DIR__ . '/includes/header.php';

$search = $_GET['search'] ?? '';
$filtro_carrera = $_GET['carrera'] ?? '';
$filtro_profesor = $_GET['profesor'] ?? '';

$where = ["cu.activo = 1"];
$params = [];

if ($search) {
    $where[] = "(cu.nombre LIKE ? OR cu.codigo LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filtro_carrera) {
    $where[] = "cu.carrera_id = ?";
    $params[] = $filtro_carrera;
}
if ($filtro_profesor) {
    $where[] = "cu.profesor_id = ?";
    $params[] = $filtro_profesor;
}

global $pdo;

$sql = "SELECT cu.id, cu.nombre, cu.codigo, cu.aula, cu.seccion, cu.horario,
               cu.carrera_id, ca.nombre as carrera_nombre, ca.nivel as carrera_nivel,
               u.nombre_completo as profesor_nombre, u.cedula as profesor_cedula,
               u.carrera_id as prof_carrera_id
        FROM cursos cu
        LEFT JOIN carreras ca ON cu.carrera_id = ca.id
        LEFT JOIN usuarios u ON cu.profesor_id = u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY cu.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cursos = $stmt->fetchAll();

$carreras = obtenerCarreras($pdo);
$profesores = $pdo->query("SELECT id, nombre_completo FROM usuarios WHERE rol='profesor' AND activo=1 ORDER BY nombre_completo")->fetchAll();

if (isset($_GET['accion']) && $_GET['accion'] === 'desactivar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE cursos SET activo = FALSE WHERE id = ?")->execute([$id]);
        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Desactivar curso', "ID: $id");
        header('Location: cursos_lista.php?msg=desactivado');
        exit;
    }
}

$mensaje = $_GET['msg'] ?? '';
?>
<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--primary);margin:0;">Cursos Registrados</h2>
        <a href="usuarios.php" style="font-size:14px;color:var(--primary);">⬅ Volver a Gestión</a>
    </div>

    <?php if ($mensaje === 'desactivado'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">Curso desactivado correctamente.</div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:15px;">
        <form method="GET" action="cursos_lista.php" onkeydown="if(event.key==='Enter'&&event.target.tagName!=='TEXTAREA'){event.preventDefault();this.querySelector('button[type=submit]').click()}">
            <div class="filters">
                <input type="text" name="search" placeholder="Buscar materia o código..." value="<?= htmlspecialchars($search) ?>" />
                <select name="carrera">
                    <option value="">Todas las carreras</option>
                    <?php foreach ($carreras as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtro_carrera == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre'] . ' (' . $c['nivel'] . ')') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="profesor">
                    <option value="">Todos los profesores</option>
                    <?php foreach ($profesores as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filtro_profesor == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre_completo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="cursos_lista.php" class="btn" style="background:#eee;color:var(--text-dark);text-decoration:none;text-align:center;">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x:auto;">
        <?php if (count($cursos) === 0): ?>
            <p style="color:#666;font-size:14px;text-align:center;padding:30px;">No se encontraron cursos.</p>
        <?php else: ?>
        <p style="margin-bottom:10px;font-size:13px;color:#666;">
            Mostrando <strong><?= count($cursos) ?></strong> resultado(s)
        </p>
        <table style="font-size:13px;">
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Código</th>
                    <th>Carrera</th>
                    <th>Profesor</th>
                    <th>Aula</th>
                    <th>Sección</th>
                    <th>Horario</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($c['codigo'] ?? '-') ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($c['carrera_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['profesor_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['aula'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['seccion'] ?? '-') ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($c['horario'] ?? '-') ?></td>
                    <td>
                        <a href="cursos_lista.php?accion=desactivar&id=<?= $c['id'] ?>&<?= http_build_query($_GET) ?>"
                           onclick="return confirm('¿Desactivar <?= htmlspecialchars(addslashes($c['nombre'])) ?>?')"
                           style="color:#dc3545;font-size:12px;">Desactivar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
