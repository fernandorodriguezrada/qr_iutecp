<?php
require_once __DIR__ . '/includes/header.php';

$search = $_GET['search'] ?? '';
$filtro_rol = $_GET['rol'] ?? '';
$filtro_carrera = $_GET['carrera'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$where = ["u.rol != 'admin'"];
$params = [];

if ($search) {
    $where[] = "(u.nombre_completo LIKE ? OR u.cedula LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filtro_rol) {
    $where[] = "u.rol = ?";
    $params[] = $filtro_rol;
}
if ($filtro_carrera) {
    $where[] = "u.carrera_id = ?";
    $params[] = $filtro_carrera;
}
if ($filtro_estado === 'activo') {
    $where[] = "u.activo = 1";
} elseif ($filtro_estado === 'inactivo') {
    $where[] = "u.activo = 0";
}

$sql = "SELECT u.id, u.cedula, u.nombre_completo, u.email, u.rol, u.activo, u.password_temporal, u.es_nuevo_ingreso,
               u.carrera_id, c.nombre as carrera_nombre,
               u.created_at
        FROM usuarios u
        LEFT JOIN carreras c ON u.carrera_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY u.created_at DESC";

global $pdo;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$carreras = obtenerCarreras($pdo);

if (isset($_GET['accion'])) {
    $id = (int)($_GET['id'] ?? 0);
    if ($_GET['accion'] === 'desactivar' && $id > 0) {
        $pdo->prepare("UPDATE usuarios SET activo = FALSE WHERE id = ? AND rol != 'admin'")->execute([$id]);
        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Desactivar usuario', "ID: $id");
        header('Location: usuarios_lista.php?msg=desactivado');
        exit;
    }
    if ($_GET['accion'] === 'reactivar' && $id > 0) {
        $pdo->prepare("UPDATE usuarios SET activo = TRUE WHERE id = ? AND rol != 'admin'")->execute([$id]);
        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Reactivar usuario', "ID: $id");
        header('Location: usuarios_lista.php?msg=reactivado');
        exit;
    }
    if ($_GET['accion'] === 'toggle_nuevo' && $id > 0) {
        $stmt = $pdo->prepare("SELECT es_nuevo_ingreso FROM usuarios WHERE id = ? AND rol = 'estudiante'");
        $stmt->execute([$id]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            $nuevo_val = $val ? 0 : 1;
            $pdo->prepare("UPDATE usuarios SET es_nuevo_ingreso = ? WHERE id = ?")->execute([$nuevo_val, $id]);
            registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Toggle nuevo ingreso', "ID: $id -> " . ($nuevo_val ? 'nuevo' : 'regular'));
        }
        header('Location: usuarios_lista.php?msg=nuevo_toggle');
        exit;
    }
    if ($_GET['accion'] === 'eliminar' && $id > 0) {
        $stmt = $pdo->prepare("SELECT nombre_completo, rol FROM usuarios WHERE id = ? AND rol != 'admin'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $pdo->prepare("UPDATE cursos SET profesor_id = NULL WHERE profesor_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM configuracion_profesor WHERE profesor_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM sesiones_qr WHERE profesor_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM asistencias WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE estudiante_id = ?)")->execute([$id]);
            $pdo->prepare("DELETE FROM inscripciones WHERE estudiante_id = ?")->execute([$id]);
            $pdo->prepare("UPDATE auditoria SET usuario_id = NULL WHERE usuario_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol != 'admin'")->execute([$id]);
            registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Eliminar usuario', "ID: $id, Nombre: {$user['nombre_completo']}, Rol: {$user['rol']}");
        }
        header('Location: usuarios_lista.php?msg=eliminado');
        exit;
    }
}

$mensaje = $_GET['msg'] ?? '';
?>
<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--primary);margin:0;">Usuarios Registrados</h2>
        <a href="usuarios.php" style="font-size:14px;color:var(--primary);">⬅ Volver a Gestión</a>
    </div>

    <?php if ($mensaje === 'desactivado'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">Usuario desactivado.</div>
    <?php elseif ($mensaje === 'reactivado'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">Usuario reactivado.</div>
    <?php elseif ($mensaje === 'eliminado'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">Usuario eliminado permanentemente.</div>
    <?php elseif ($mensaje === 'nuevo_toggle'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">Estado de nuevo ingreso actualizado.</div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:15px;">
        <form method="GET" action="usuarios_lista.php" onkeydown="if(event.key==='Enter'&&event.target.tagName!=='TEXTAREA'){event.preventDefault();this.querySelector('button[type=submit]').click()}">
            <div class="filters">
                <input type="text" name="search" placeholder="Buscar por nombre o cédula..." value="<?= htmlspecialchars($search) ?>" />
                <select name="rol">
                    <option value="">Todos los roles</option>
                    <option value="estudiante" <?= $filtro_rol === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                    <option value="profesor" <?= $filtro_rol === 'profesor' ? 'selected' : '' ?>>Profesor</option>
                    <option value="admin" <?= $filtro_rol === 'admin' ? 'selected' : '' ?>>Administrativo</option>
                </select>
                <select name="carrera">
                    <option value="">Todas las carreras</option>
                    <?php foreach ($carreras as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtro_carrera == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?= $filtro_estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                    <option value="inactivo" <?= $filtro_estado === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="usuarios_lista.php" class="btn" style="background:#eee;color:var(--text-dark);text-decoration:none;text-align:center;">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x:auto;">
        <?php if (count($usuarios) === 0): ?>
            <p style="color:#666;font-size:14px;text-align:center;padding:30px;">No se encontraron usuarios.</p>
        <?php else: ?>
        <p style="margin-bottom:10px;font-size:13px;color:#666;">
            Mostrando <strong><?= count($usuarios) ?></strong> resultado(s)
        </p>
        <table style="font-size:13px;">
            <thead>
                <tr>
                    <th>C.I</th>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Carrera</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Nuevo Ingreso</th>
                    <th>Contraseña</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['cedula']) ?></td>
                    <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                    <td><?= ucfirst($u['rol']) ?></td>
                    <td style="font-size:12px;"><?= $u['carrera_nombre'] ? htmlspecialchars($u['carrera_nombre']) : '-' ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                    <td>
                        <?php if ($u['activo']): ?>
                            <span style="color:green;font-weight:bold;">Activo</span>
                        <?php else: ?>
                            <span style="color:red;font-weight:bold;">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ($u['rol'] === 'estudiante'): ?>
                            <?php if ($u['es_nuevo_ingreso']): ?>
                                <span style="color:#d39e00;font-weight:bold;font-size:12px">🗝️ Sí</span>
                                <a href="usuarios_lista.php?accion=toggle_nuevo&id=<?= $u['id'] ?>&<?= http_build_query($_GET) ?>"
                                   style="font-size:11px;color:#888;display:block">(marcar regular)</a>
                            <?php else: ?>
                                <span style="color:#888;font-size:12px">No</span>
                                <a href="usuarios_lista.php?accion=toggle_nuevo&id=<?= $u['id'] ?>&<?= http_build_query($_GET) ?>"
                                   style="font-size:11px;color:var(--primary);display:block">(marcar nuevo)</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#ccc">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['password_temporal']): ?>
                            <span style="color:#d39e00;font-size:12px;">⏳ Temporal</span>
                        <?php else: ?>
                            <span style="color:green;font-size:12px;">✓ Cambiada</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['activo']): ?>
                        <a href="usuarios_lista.php?accion=desactivar&id=<?= $u['id'] ?>&<?= http_build_query($_GET) ?>"
                           onclick="return confirm('¿Desactivar a <?= htmlspecialchars(addslashes($u['nombre_completo'])) ?>?')"
                           style="color:#dc3545;font-size:12px;">Desactivar</a>
                        <?php else: ?>
                        <a href="usuarios_lista.php?accion=reactivar&id=<?= $u['id'] ?>&<?= http_build_query($_GET) ?>"
                           style="color:green;font-size:12px;">Reactivar</a>
                        <?php endif; ?>
                        &nbsp;|&nbsp;
                        <a href="usuarios_lista.php?accion=eliminar&id=<?= $u['id'] ?>&<?= http_build_query($_GET) ?>"
                           onclick="return confirm('⚠️ ¿ELIMINAR permanentemente a <?= htmlspecialchars(addslashes($u['nombre_completo'])) ?>?\n\nSe borrarán todos sus datos relacionados (inscripciones, asistencias, sesiones QR, etc.).\n\nEsta acción NO se puede deshacer.')"
                           style="color:#dc3545;font-size:12px;font-weight:bold;">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
