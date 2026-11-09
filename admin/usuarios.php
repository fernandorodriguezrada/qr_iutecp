<?php
require_once __DIR__ . "/includes/header.php";
global $pdo;

$mensaje = $_GET["msg"] ?? "";
$error = $_GET["error"] ?? "";

require_once __DIR__ . "/../includes/academico.php";

$periodo_actual = obtenerPeriodoActual();

$carreras = obtenerCarreras($pdo);
$profesores = $pdo
    ->query(
        "
    SELECT u.id, u.cedula, u.nombre_completo, u.email, u.carrera_id,
           c.nombre as carrera_nombre, c.nivel as carrera_nivel,
           (SELECT COUNT(*) FROM cursos WHERE profesor_id = u.id AND activo = 1) as total_cursos
    FROM usuarios u
    LEFT JOIN carreras c ON u.carrera_id = c.id
    WHERE u.rol = 'profesor' AND u.activo = 1
    ORDER BY u.nombre_completo
",
    )
    ->fetchAll();

$cursos_con_profesor = $pdo
    ->query(
        "
    SELECT c.id, c.nombre, c.codigo, c.seccion,
           cr.nombre as carrera_nombre,
           u.nombre_completo as profesor_nombre, u.id as profesor_id
    FROM cursos c
    JOIN carreras cr ON c.carrera_id = cr.id
    JOIN usuarios u ON c.profesor_id = u.id
    WHERE c.activo = 1 AND c.profesor_id IS NOT NULL
    ORDER BY cr.nombre, c.nombre
",
    )
    ->fetchAll();

$total_usuarios = $pdo
    ->query("SELECT COUNT(*) FROM usuarios WHERE rol != 'admin'")
    ->fetchColumn();
$total_cursos = $pdo
    ->query("SELECT COUNT(*) FROM cursos WHERE activo = 1")
    ->fetchColumn();

$estudiantes = $pdo
    ->query(
        "
    SELECT u.id, u.cedula, u.nombre_completo, c.nombre as carrera_nombre
    FROM usuarios u
    LEFT JOIN carreras c ON u.carrera_id = c.id
    WHERE u.rol = 'estudiante' AND u.activo = 1
    ORDER BY u.nombre_completo
",
    )
    ->fetchAll();

$cursos_asignar = $pdo
    ->query(
        "
    SELECT c.id, c.nombre, c.codigo, c.seccion, c.horario, c.aula,
           cr.nombre as carrera_nombre,
           u.nombre_completo as profesor_nombre
    FROM cursos c
    JOIN carreras cr ON c.carrera_id = cr.id
    LEFT JOIN usuarios u ON c.profesor_id = u.id
    WHERE c.activo = 1
    ORDER BY cr.nombre, c.nombre
",
    )
    ->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {
    if ($_POST["accion"] === "crear_usuario") {
        $cedula = "V" . limpiarInput($_POST["cedula"] ?? "");
        $nombre = limpiarInput($_POST["nombre_completo"] ?? "");
        $email = limpiarInput($_POST["email"] ?? "");
        $telefono = limpiarInput($_POST["telefono"] ?? "");
        $rol = limpiarInput($_POST["rol"] ?? "");
        $carrera_id = $_POST["carrera_id"] ?? null;
        if ($carrera_id === "") {
            $carrera_id = null;
        }

        if (empty($cedula) || empty($nombre) || empty($rol)) {
            $error = "campos_requeridos";
        } elseif (
            ($rol === "profesor" || $rol === "estudiante") &&
            empty($carrera_id)
        ) {
            $error = "carrera_requerida";
        } else {
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ?");
            $check->execute([$cedula]);
            if ($check->fetch()) {
                $error = "cedula_existe";
            } else {
                $username =
                    $rol === "admin" ? generarUsername($pdo, $nombre) : null;
                $password_temp = generarPasswordTemporal(
                    $nombre,
                    date("Y-m-d"),
                );
                $hash = password_hash($password_temp, PASSWORD_DEFAULT);
                $es_nuevo = ($_POST['es_nuevo_ingreso'] ?? '') === '1' ? 1 : 0;
                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (cedula, username, nombre_completo, email, telefono, carrera_id, rol, password_hash, password_temporal, es_nuevo_ingreso, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?, ?)",
                );
                $stmt->execute([
                    $cedula,
                    $username,
                    $nombre,
                    $email,
                    $telefono,
                    $carrera_id,
                    $rol,
                    $hash,
                    $es_nuevo,
                    $_SESSION["usuario_id"],
                ]);
                $nuevo_id = $pdo->lastInsertId();
                $detalle = "Cédula: $cedula, Nombre: $nombre, Rol: $rol";
                if ($carrera_id) {
                    $detalle .= ", Carrera ID: $carrera_id";
                }
                registrarAuditoria(
                    $pdo,
                    $_SESSION["usuario_id"],
                    "Crear usuario",
                    $detalle,
                );
                if ($rol === "estudiante" && $carrera_id) {
                    $stmt_cursos = $pdo->prepare(
                        "SELECT id FROM cursos WHERE carrera_id = ? AND activo = 1",
                    );
                    $stmt_cursos->execute([$carrera_id]);
                    $cursos_ids = $stmt_cursos->fetchAll(PDO::FETCH_COLUMN);
                    $periodo = obtenerPeriodoActual();
                    $stmt_ins = $pdo->prepare(
                        "INSERT INTO inscripciones (estudiante_id, curso_id, periodo) VALUES (?, ?, ?)",
                    );
                    foreach ($cursos_ids as $curso_id) {
                        $stmt_ins->execute([$nuevo_id, $curso_id, $periodo]);
                    }
                    registrarAuditoria(
                        $pdo,
                        $_SESSION["usuario_id"],
                        "Auto-inscribir estudiante",
                        "Usuario ID: $nuevo_id, Cursos: " .
                            count($cursos_ids) .
                            ", Periodo: $periodo",
                    );
                }
                $_SESSION["flash_password"] = $password_temp;
                header(
                    "Location: usuarios.php?msg=creado&cedula=" .
                        urlencode($cedula),
                );
                exit();
            }
        }
        if ($error) {
            header("Location: usuarios.php?error=" . $error);
            exit();
        }
    }

    if ($_POST["accion"] === "inscribir") {
        $estudiante_id = (int) ($_POST["estudiante_id"] ?? 0);
        $curso_id = (int) ($_POST["curso_id"] ?? 0);
        $periodo = limpiarInput($_POST["periodo"] ?? "");

        if ($estudiante_id <= 0 || $curso_id <= 0 || empty($periodo)) {
            $error = "campos_requeridos_inscribir";
        } else {
            $check_e = $pdo->prepare(
                "SELECT id FROM usuarios WHERE id = ? AND rol = 'estudiante' AND activo = 1",
            );
            $check_e->execute([$estudiante_id]);
            $check_c = $pdo->prepare(
                "SELECT id FROM cursos WHERE id = ? AND activo = 1",
            );
            $check_c->execute([$curso_id]);
            $curso_row = $check_c->fetch();
            if (!$check_e->fetch()) {
                $error = "estudiante_no_existe";
            } elseif (!$curso_row) {
                $error = "curso_no_existe";
            } else {
                $check_carrera = $pdo->prepare("SELECT carrera_id FROM usuarios WHERE id = ?");
                $check_carrera->execute([$estudiante_id]);
                $carrera_est = (int)$check_carrera->fetchColumn();
                $carrera_curso = (int)$curso_row['carrera_id'];
                if ($carrera_est > 0 && $carrera_curso > 0 && $carrera_est !== $carrera_curso) {
                    $error = "carrera_no_coincide";
                } else {
                $check_i = $pdo->prepare(
                    "SELECT i.id FROM inscripciones i JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND i.periodo = ? AND c.codigo = (SELECT codigo FROM cursos WHERE id = ?)",
                );
                $check_i->execute([$estudiante_id, $periodo, $curso_id]);
                if ($check_i->fetch()) {
                    $error = "ya_inscrito";
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO inscripciones (estudiante_id, curso_id, periodo) VALUES (?, ?, ?)",
                    );
                    $stmt->execute([$estudiante_id, $curso_id, $periodo]);
                    registrarAuditoria(
                        $pdo,
                        $_SESSION["usuario_id"],
                        "Inscribir estudiante",
                        "Estudiante ID: $estudiante_id, Curso ID: $curso_id, Periodo: $periodo",
                    );
                    header("Location: usuarios.php?msg=inscrito");
                    exit();
                }
            }
        }
    }
        if ($error) {
            header("Location: usuarios.php?error=" . $error);
            exit();
        }
    }

    if ($_POST["accion"] === "asignar_curso") {
        $cursos_ids = isset($_POST["cursos_ids"]) ? array_map('intval', (array)$_POST["cursos_ids"]) : (($_POST["curso_id"] ?? 0) ? [(int)$_POST["curso_id"]] : []);
        $profesor_id = (int) ($_POST["profesor_id"] ?? 0);
        $seccion = trim($_POST["seccion"] ?? "");
        if (empty($cursos_ids) || $profesor_id <= 0) {
            $error = "campos_requeridos_asignar";
        } else {
            $check2 = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND rol = 'profesor' AND activo = 1");
            $check2->execute([$profesor_id]);
            if (!$check2->fetch()) {
                $error = "profesor_no_existe";
            } else {
                $asignados = 0;
                $errores_asignar = [];
                foreach ($cursos_ids as $curso_id) {
                    $check = $pdo->prepare("SELECT id, nombre FROM cursos WHERE id = ? AND activo = 1");
                    $check->execute([$curso_id]);
                    $curso = $check->fetch();
                    if (!$curso) {
                        $errores_asignar[] = "Curso ID $curso_id no existe";
                        continue;
                    }
                    if ($seccion) {
                        $pdo->prepare("UPDATE cursos SET profesor_id = ?, seccion = ? WHERE id = ?")->execute([$profesor_id, $seccion, $curso_id]);
                    } else {
                        $pdo->prepare("UPDATE cursos SET profesor_id = ? WHERE id = ?")->execute([$profesor_id, $curso_id]);
                    }
                    $asignados++;
                }
                if ($asignados > 0) {
                    registrarAuditoria($pdo, $_SESSION["usuario_id"], "Asignar curso", "Profesor ID: $profesor_id, Cursos: " . implode(',', $cursos_ids) . ($seccion ? ", Seccion: $seccion" : ""));
                }
                $msg = $asignados > 0 ? "curso_asignado" : "";
                $error_extra = !empty($errores_asignar) ? "&err_asignar=" . urlencode(implode(' | ', $errores_asignar)) : "";
                if ($msg) {
                    header("Location: usuarios.php?msg=$msg&asignados=$asignados" . $error_extra);
                } else {
                    $error = "asignacion_fallida";
                }
                if ($msg) { exit(); }
            }
        }
        if ($error) {
            header("Location: usuarios.php?error=" . $error);
            exit();
        }
    }

    if ($_POST["accion"] === "crear_curso") {
        $nombre = limpiarInput($_POST["nombre"] ?? "");
        $codigo = limpiarInput($_POST["codigo"] ?? "");
        $carrera_id = $_POST["carrera_id"] ?? null;
        $profesor_id = $_POST["profesor_id"] ?? null;
        $aula = limpiarInput($_POST["aula"] ?? "");
        $seccion = limpiarInput($_POST["seccion"] ?? "");
        $horario = limpiarInput($_POST["horario"] ?? "");
        if ($carrera_id === "") {
            $carrera_id = null;
        }
        if ($profesor_id === "") {
            $profesor_id = null;
        }

        if (empty($nombre) || empty($profesor_id) || empty($carrera_id)) {
            $error = "campos_requeridos_curso";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO cursos (nombre, codigo, carrera_id, profesor_id, aula, seccion, horario) VALUES (?, ?, ?, ?, ?, ?, ?)",
            );
            $stmt->execute([
                $nombre,
                $codigo,
                $carrera_id,
                $profesor_id,
                $aula,
                $seccion,
                $horario,
            ]);
            $detalle = "Curso: $nombre, Carrera ID: $carrera_id, Profesor ID: $profesor_id, Aula: $aula";
            registrarAuditoria(
                $pdo,
                $_SESSION["usuario_id"],
                "Crear curso",
                $detalle,
            );
            header("Location: usuarios.php?msg=curso_creado");
            exit();
        }
        if ($error) {
            header("Location: usuarios.php?error=" . $error);
            exit();
        }
    }

    if ($_POST["accion"] === "desasignar_curso") {
        $curso_id = $_POST["curso_id"] ?? null;
        if ($curso_id) {
            $stmt = $pdo->prepare(
                "UPDATE cursos SET profesor_id = NULL WHERE id = ?",
            );
            $stmt->execute([$curso_id]);
            registrarAuditoria(
                $pdo,
                $_SESSION["usuario_id"],
                "Desasignar curso",
                "Curso ID: $curso_id",
            );
            header("Location: usuarios.php?msg=curso_desasignado");
            exit();
        }
        header("Location: usuarios.php?error=seleccione_curso");
        exit();
    }

    if ($_POST["accion"] === "desasignar_todos") {
        $profesor_id = $_POST["profesor_id"] ?? null;
        if ($profesor_id) {
            $count = $pdo->prepare(
                "SELECT COUNT(*) FROM cursos WHERE profesor_id = ? AND activo = 1",
            );
            $count->execute([$profesor_id]);
            $total = $count->fetchColumn();
            $stmt = $pdo->prepare(
                "UPDATE cursos SET profesor_id = NULL WHERE profesor_id = ? AND activo = 1",
            );
            $stmt->execute([$profesor_id]);
            registrarAuditoria(
                $pdo,
                $_SESSION["usuario_id"],
                "Desasignar todos los cursos",
                "Profesor ID: $profesor_id, Cursos: $total",
            );
            header("Location: usuarios.php?msg=todos_desasignados");
            exit();
        }
        header("Location: usuarios.php?error=seleccione_profesor");
        exit();
    }
}
?>
<style>
.management-grid .card {
    text-align: center;
}
.management-grid .card h3 {
    margin-bottom: 15px;
    color: var(--primary);
    border-bottom: 2px solid var(--accent);
    padding-bottom: 8px;
}
.management-grid .card p {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}
.management-grid .card .btn {
    width: 100%;
}
.accordion-card {
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    margin-bottom: 10px;
}
.class-accordion summary {
    padding: 12px 20px;
    font-weight: bold;
    background: var(--bg-light);
    color: var(--primary);
    cursor: pointer;
    user-select: none;
    font-size: 14px;
    line-height: 16px;
}
.class-accordion summary:hover {
    background: #e8e8e8;
}
.accordion-body {
    padding: 20px;
    border-top: 1px solid var(--border-color);
    display: none;
}
.accordion-body.open {
    display: block;
}
.accordion-body .btn-link {
    display: inline-block;
    padding: 12px 24px;
    background: var(--primary);
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    transition: opacity 0.2s;
}
.accordion-body .btn-link:hover {
    opacity: 0.85;
}
.accordion-body .stats-mini {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    color: #666;
    font-size: 14px;
}
.accordion-body .stats-mini strong {
    color: var(--primary);
}
.management-grid details.class-accordion {
    display: flex;
    flex-direction: column;
}
.management-grid details.class-accordion[open] {
    flex: 1;
}
.management-grid details.class-accordion > div {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.field-row {
    display: flex;
    flex-direction: column;
    height: 62px;
    margin-bottom: 5px;
    overflow: hidden;
}
.field-row label {
    flex-shrink: 0;
    height: 16px;
    line-height: 16px;
    margin-bottom: 3px;
    font-size: 14px;
    overflow: hidden;
}
.field-row select,
.field-row input[type="text"],
.field-row input[type="email"],
.field-row input[type="password"],
.field-row select {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    box-sizing: border-box;
    flex: 1;
    min-height: 0;
    border: 1px solid #ccc;
    margin: 0;
}
.cedula-wrapper input {
    flex: 1;
    padding: 10px;
    font-size: 14px;
    box-sizing: border-box;
    border: 1px solid var(--border-color);
    border-left: none;
    border-radius: 0 4px 4px 0;
    margin: 0;
}
.field-row select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    overflow: hidden;
}
.cedula-wrapper {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}
.cedula-wrapper span {
    width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 4px 0 0 4px;
    font-weight: bold;
    color: var(--primary);
    font-size: 14px;
    box-sizing: border-box;
    flex-shrink: 0;
}
.cedula-wrapper input {
    flex: 1;
    padding: 10px;
    font-size: 14px;
    box-sizing: border-box;
    border: 1px solid var(--border-color);
    border-left: none;
    border-radius: 0 4px 4px 0;
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-overlay.active {
    display: flex;
}
.modal-card {
    background: var(--white);
    border-radius: 8px;
    max-width: 520px;
    width: 100%;
    position: relative;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    max-height: 90vh;
    overflow-y: auto;
}
.modal-card .modal-header {
    padding: 20px 24px 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.modal-card .modal-header h3 {
    color: var(--primary);
    border-bottom: 2px solid var(--accent);
    padding-bottom: 8px;
    margin: 0 0 15px 0;
    flex: 1;
}
.modal-card .modal-close {
    background: none;
    border: none;
    font-size: 1.8rem;
    cursor: pointer;
    color: #999;
    line-height: 1;
    padding: 0 0 0 10px;
}
.modal-card .modal-close:hover {
    color: #333;
}
.modal-card .modal-body {
    padding: 0 24px 20px;
}
.modal-card label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
    color: var(--text-dark);
}
.modal-card input,
.modal-card select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}
.modal-card input:focus,
.modal-card select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 5px rgba(255, 224, 1, 0.5);
}
.modal-card .btn {
    width: 100%;
    margin-top: 5px;
}
</style>


<div id="admin-management">
    <h2 style="color:var(--primary);margin-bottom:20px">Gestión de Entidades</h2>

    <?php if ($mensaje === "creado"): ?>
    <div style="background:#d4edda;color:#155724;padding:15px;border-radius:4px;margin-bottom:20px;border:1px solid #c3e6cb;">
        <strong>✓ Usuario creado exitosamente.</strong><br>
        <span style="font-size:14px;">
            Cédula: <strong><?= htmlspecialchars(
                $_GET["cedula"] ?? "",
            ) ?></strong><br>
            Contraseña temporal: <strong><?= htmlspecialchars(
                $_SESSION["flash_password"] ?? "",
            ) ?></strong>
        </span>
        <p style="font-size:12px;margin-top:5px;color:#856404;">⚠️ Anote esta contraseña, no se mostrará de nuevo.</p>
    </div>
    <?php unset($_SESSION["flash_password"]); ?>
    <?php elseif ($mensaje === "curso_creado"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        ✓ Curso creado exitosamente.
    </div>
    <?php elseif ($mensaje === "inscrito"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        ✓ Estudiante inscrito exitosamente.
    </div>
    <?php elseif ($mensaje === "curso_asignado"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        ✓ Curso asignado exitosamente.
    </div>
    <?php elseif ($error === "campos_requeridos"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Complete todos los campos obligatorios.</div>
    <?php elseif ($error === "carrera_requerida"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Debe seleccionar una carrera para estudiantes y profesores.</div>
    <?php elseif ($error === "cedula_existe"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Ya existe un usuario con esa cédula.</div>
    <?php elseif ($error === "campos_requeridos_curso"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Complete nombre del curso, carrera y profesor asignado.</div>
    <?php elseif ($error === "campos_requeridos_inscribir"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Seleccione un estudiante, un curso y un periodo.</div>
    <?php elseif ($error === "estudiante_no_existe"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">El estudiante seleccionado no existe o está inactivo.</div>
    <?php elseif ($error === "ya_inscrito"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">El estudiante ya está inscrito en esta materia para el periodo seleccionado.</div>
    <?php elseif ($error === "campos_requeridos_asignar"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Seleccione un curso y un profesor.</div>
    <?php elseif ($error === "curso_no_existe"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">El curso seleccionado no existe o está inactivo.</div>
    <?php elseif ($error === "profesor_no_existe"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">El profesor seleccionado no existe o está inactivo.</div>
    <?php elseif ($mensaje === "curso_desasignado"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">✓ Curso desasignado exitosamente.</div>
    <?php elseif ($mensaje === "todos_desasignados"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">✓ Todos los cursos desasignados exitosamente.</div>
    <?php elseif ($error === "seleccione_curso"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Seleccione un curso para desasignar.</div>
    <?php elseif ($error === "seleccione_profesor"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Seleccione un profesor para desasignar todos sus cursos.</div>
    <?php endif; ?>

    <div class="management-grid">
        <div class="card">
            <h3>Registrar Usuario</h3>
            <p>Crear un nuevo usuario (estudiante, profesor o administrativo) en el sistema.</p>
            <button class="btn btn-primary" data-open="modal-usuario">Nuevo Usuario</button>
        </div>
        <div class="card">
            <h3>Inscribir Estudiante</h3>
            <p>Inscribir un estudiante en una materia para el periodo <?= htmlspecialchars(
                $periodo_actual,
            ) ?>.</p>
            <button class="btn btn-primary" data-open="modal-inscribir">Inscribir Estudiante</button>
        </div>
        <div class="card">
            <h3>Asignar Curso</h3>
            <p>Asignar o reasignar un curso existente a un profesor.</p>
            <button class="btn btn-primary" data-open="modal-asignar">Asignar Curso</button>
        </div>
        <div class="card">
            <h3>Desasignar Curso</h3>
            <p>Desasignar un curso individualmente o todos los cursos de un profesor.</p>
            <button class="btn btn-primary" data-open="modal-desasignar">Desasignar Curso</button>
        </div>
        <div class="card">
            <h3>Registrar Curso</h3>
            <p>Agregar un curso asignado a una carrera y a un profesor.</p>
            <button class="btn btn-primary" data-open="modal-curso">Nuevo Curso</button>
        </div>
        <div class="card">
            <h3>Usuarios Registrados</h3>
            <p>Gestione, busque y filtre todos los usuarios del sistema.</p>
            <a href="usuarios_lista.php" class="btn btn-secondary">Ver Usuarios</a>
        </div>
        <div class="card">
            <h3>Cursos Registrados</h3>
            <p>Gestione, busque y filtre los cursos activos.</p>
            <a href="cursos_lista.php" class="btn btn-secondary">Ver Cursos</a>
        </div>
    </div>

    <div id="modal-usuario" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Crear Usuario</h3>
                <button class="modal-close" data-close="modal-usuario">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="usuarios.php">
                    <input type="hidden" name="accion" value="crear_usuario" />
                    <label>Nombre Completo *</label>
                    <input type="text" name="nombre_completo" id="input-nombre-completo" required placeholder="Ej. Juan Pérez" oninput="generarUsernameCampo()" />
                    <div id="username-field" style="display:none;">
                        <label>Usuario</label>
                        <input type="text" name="username" id="input-username" readonly style="background:#f5f5f5;cursor:default;" />
                    </div>
                    <label>Cédula (solo números) *</label>
                    <div style="display:flex;margin-bottom:15px;">
                        <span style="width:44px;display:flex;align-items:center;justify-content:center;background:#e9ecef;border:1px solid var(--border-color);border-right:none;border-radius:4px 0 0 4px;font-weight:bold;color:var(--primary);font-size:14px;flex-shrink:0;">V</span>
                        <input type="text" name="cedula" required pattern="[0-9]+" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="12345678" style="margin-bottom:0;border-radius:0 4px 4px 0;" />
                    </div>
                    <label>Email</label>
                    <input type="email" name="email" placeholder="correo@ejemplo.com" />
                    <label>Teléfono</label>
                    <input type="text" name="telefono" placeholder="0412-1234567" />
                    <label>Rol *</label>
                    <select name="rol" id="rol-select" required onchange="toggleCarreraField()">
                        <option value="">Seleccione...</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="profesor">Profesor</option>
                        <option value="admin">Administrativo</option>
                    </select>
                    <div id="carrera-field" style="display:none;">
                        <label>Carrera</label>
                        <select name="carrera_id">
                            <option value="">Seleccione una carrera...</option>
                            <?php foreach ($carreras as $c): ?>
                                <option value="<?= $c[
                                    "id"
                                ] ?>"><?= htmlspecialchars(
    $c["nombre"] . " (" . $c["nivel"] . ")",
) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="nuevo-ingreso-field" style="display:none;margin-bottom:15px">
                        <label style="display:flex;align-items:center;gap:8px;font-weight:normal;font-size:13px;cursor:pointer">
                            <input type="checkbox" name="es_nuevo_ingreso" value="1" />
                            <span>Es nuevo ingreso (inició este período)</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-asignar" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Asignar Curso a Profesor</h3>
                <button class="modal-close" data-close="modal-asignar">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="usuarios.php" id="form-asignar">
                    <input type="hidden" name="accion" value="asignar_curso" />
                    <label>Curso *</label>
                    <div style="display:flex;gap:8px;align-items:stretch;">
                        <div style="flex:1;">
                            <input type="text" class="select-filter" placeholder="Buscar curso..." data-target="asignar-curso" style="margin-bottom:4px;padding:5px;width:100%;border:1px solid var(--border-color);border-radius:3px;font-size:13px">
                            <select id="asignar-curso" style="margin-bottom:0;">
                                <option value="">Seleccione un curso...</option>
                                <?php foreach ($cursos_asignar as $c): ?>
                                    <option value="<?= $c["id"] ?>">
                                        <?= htmlspecialchars(
                                            $c["nombre"] .
                                                " (" . $c["codigo"] . ")" .
                                                ($c["seccion"] ? " - Sec: " . $c["seccion"] : "") .
                                                " [" . $c["carrera_nombre"] . "]" .
                                                ($c["profesor_nombre"] ? " — " . $c["profesor_nombre"] : ""),
                                        ) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" onclick="agregarCursoAsignar()" style="padding:8px 14px;border:1px solid var(--border-color);border-radius:4px;background:var(--primary);color:#fff;font-size:18px;font-weight:bold;cursor:pointer;line-height:1;">+</button>
                    </div>
                    <div id="cursos-a-asignar" style="margin:8px 0 15px;"></div>
                    <label>Profesor *</label>
                    <input type="text" class="select-filter" placeholder="Buscar profesor..." data-target="asignar-profesor" style="margin-bottom:4px;padding:5px;width:100%;border:1px solid var(--border-color);border-radius:3px;font-size:13px">
                    <select name="profesor_id" id="asignar-profesor" required>
                        <option value="">Seleccione un profesor...</option>
                        <?php foreach ($profesores as $p): ?>
                            <option value="<?= $p["id"] ?>">
                                <?= htmlspecialchars(
                                    $p["nombre_completo"] .
                                        ($p["total_cursos"] > 0 ? " (" . $p["total_cursos"] . " cursos)" : ""),
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Sección (opcional)</label>
                    <input type="text" name="seccion" placeholder="Ej. 1D1" style="padding:8px;width:100%;border:1px solid var(--border-color);border-radius:4px;font-size:14px" />
                    <button type="submit" class="btn btn-primary" style="margin-top:15px">Asignar Curso(s)</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-desasignar" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Desasignar Curso</h3>
                <button class="modal-close" data-close="modal-desasignar">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="usuarios.php" onsubmit="return confirm('¿Desasignar este curso?')" style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border-color)">
                    <input type="hidden" name="accion" value="desasignar_curso" />
                    <h4 style="color:var(--primary);margin-bottom:15px">Desasignar individualmente</h4>
                    <label>Curso *</label>
                    <input type="text" class="select-filter" placeholder="Buscar curso..." data-target="desasignar-curso" style="margin-bottom:4px;padding:5px;width:100%;border:1px solid var(--border-color);border-radius:3px;font-size:13px">
                    <select name="curso_id" id="desasignar-curso" required>
                        <option value="">Seleccione un curso...</option>
                        <?php foreach ($cursos_con_profesor as $c): ?>
                            <option value="<?= $c[
                                "id"
                            ] ?>"><?= htmlspecialchars(
    $c["nombre"] . " (" . $c["codigo"] . ")" . ($c["seccion"] ? " - Sec: " . $c["seccion"] : "") . " [" . $c["carrera_nombre"] . "] — " . $c["profesor_nombre"],
) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Desasignar Curso</button>
                </form>
                <form method="POST" action="usuarios.php" onsubmit="return confirm('¿Desasignar TODOS los cursos de este profesor? Esta acción no se puede deshacer.')">
                    <input type="hidden" name="accion" value="desasignar_todos" />
                    <h4 style="color:var(--primary);margin-bottom:15px">Desasignar todos los cursos</h4>
                    <label>Profesor *</label>
                    <input type="text" class="select-filter" placeholder="Buscar profesor..." data-target="desasignar-profesor" style="margin-bottom:4px;padding:5px;width:100%;border:1px solid var(--border-color);border-radius:3px;font-size:13px">
                    <select name="profesor_id" id="desasignar-profesor" required>
                        <option value="">Seleccione un profesor...</option>
                        <?php foreach ($profesores as $p): ?>
                            <?php if ($p["total_cursos"] > 0): ?>
                            <option value="<?= $p[
                                "id"
                            ] ?>"><?= htmlspecialchars(
    $p["nombre_completo"],
) ?> (<?= $p["total_cursos"] ?> cursos)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger">Desasignar Todos</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-inscribir" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Inscribir Estudiante en Materia</h3>
                <button class="modal-close" data-close="modal-inscribir">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="usuarios.php">
                    <input type="hidden" name="accion" value="inscribir" />
                    <label>Estudiante *</label>
                    <select name="estudiante_id" required>
                        <option value="">Seleccione un estudiante...</option>
                        <?php foreach ($estudiantes as $e): ?>
                            <option value="<?= $e["id"] ?>">
                                <?= htmlspecialchars($e["nombre_completo"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Materia *</label>
                    <select name="curso_id" required>
                        <option value="">Seleccione una materia...</option>
                        <?php foreach ($cursos_asignar as $c): ?>
                            <option value="<?= $c["id"] ?>">
                                <?= htmlspecialchars(
                                    $c["nombre"] . " (" . $c["codigo"] . ")",
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Periodo *</label>
                    <input type="text" name="periodo" value="<?= htmlspecialchars(
                        $periodo_actual,
                    ) ?>" required placeholder="Ej. I-2026" />
                    <p style="font-size:12px;color:#666;margin-bottom:15px;">Formato: 2026-I, 2026-II, 2026-III</p>
                    <button type="submit" class="btn btn-primary">Inscribir</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-curso" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Crear Curso</h3>
                <button class="modal-close" data-close="modal-curso">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="usuarios.php">
                    <input type="hidden" name="accion" value="crear_curso" />
                    <label>Materia *</label>
                    <input type="text" name="nombre" required placeholder="Ej. Programación I" />
                    <label>Código de Materia</label>
                    <input type="text" name="codigo" placeholder="Ej. INF-101" />
                    <label>Carrera *</label>
                    <select name="carrera_id" id="curso-carrera" required onchange="filtrarProfesores()">
                        <option value="">Seleccione una carrera...</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= $c[
                                "id"
                            ] ?>"><?= htmlspecialchars(
    $c["nombre"] . " (" . $c["nivel"] . ")",
) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Profesor Asignado *</label>
                    <select name="profesor_id" id="curso-profesor" required onchange="toggleCursoExtras()">
                        <option value="">Primero seleccione una carrera...</option>
                        <?php foreach ($profesores as $p): ?>
                            <option value="<?= $p[
                                "id"
                            ] ?>" data-carrera="<?= $p["carrera_id"] ?? "" ?>">
                                <?= htmlspecialchars($p["nombre_completo"]) ?>
                                <?= $p["carrera_nombre"]
                                    ? " - " .
                                        htmlspecialchars($p["carrera_nombre"])
                                    : "" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="curso-extras" style="display:none;">
                        <div style="display:flex;gap:12px;">
                            <div style="flex:1;">
                                <label>Aula</label>
                                <input type="text" name="aula" placeholder="Ej. Laboratorio A" />
                            </div>
                            <div style="flex:1;">
                                <label>Sección</label>
                                <input type="text" name="seccion" placeholder="Ej. 1D1" />
                            </div>
                        </div>
                        <label>Horario</label>
                        <input type="text" name="horario" placeholder="Ej. 08:00 - 10:00 AM" />
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Curso</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allProfOptions = document.getElementById('curso-profesor')
    ? Array.from(document.getElementById('curso-profesor').querySelectorAll('option:not([value=""])'))
    : [];
function toggleCarreraField() {
    const rol = document.getElementById('rol-select').value;
    const show = rol === 'estudiante' || rol === 'profesor';
    document.getElementById('carrera-field').style.display = show ? 'block' : 'none';
    document.getElementById('nuevo-ingreso-field').style.display = rol === 'estudiante' ? 'block' : 'none';
    document.getElementById('username-field').style.display =
        rol === 'admin' ? 'block' : 'none';
    if (rol === 'admin') generarUsernameCampo();
}
function generarUsernameCampo() {
    const nombre = document.getElementById('input-nombre-completo').value.trim();
    const usernameInput = document.getElementById('input-username');
    if (!nombre) { usernameInput.value = ''; return; }
    const parts = nombre.split(/\s+/).filter(Boolean);
    const firstLetter = (parts[0] || '').charAt(0).toLowerCase();
    const surname = (parts[1] || '').toLowerCase();
    usernameInput.value = 'iut.' + firstLetter + surname;
}
function filtrarProfesores() {
    const carreraId = document.getElementById('curso-carrera').value;
    const select = document.getElementById('curso-profesor');
    select.innerHTML = '<option value="">Seleccione un profesor...</option>';
    let found = false;
    allProfOptions.forEach(opt => {
        if (carreraId === '' || opt.getAttribute('data-carrera') === carreraId) {
            select.appendChild(opt.cloneNode(true));
            found = true;
        }
    });
    if (!found && carreraId !== '') {
        select.innerHTML = '<option value="">No hay profesores en esta carrera</option>';
    }
}
function toggleCursoExtras() {
    const prof = document.getElementById('curso-profesor').value;
    document.getElementById('curso-extras').style.display = prof ? 'block' : 'none';
}

var cursosAsignar = [];

function agregarCursoAsignar() {
    var select = document.getElementById('asignar-curso');
    var id = select.value;
    var text = select.options[select.selectedIndex]?.text;
    if (!id || !text) return;
    if (cursosAsignar.some(function(c) { return c.id === id; })) return;
    cursosAsignar.push({ id: id, text: text });
    renderCursosAsignar();
    select.value = '';
    document.querySelector('#modal-asignar .select-filter[data-target="asignar-curso"]').value = '';
    var allOpts = Array.from(select.options).filter(function(o) { return o.value; }).map(function(o) { return o.cloneNode(true); });
    while (select.options.length > 1) select.remove(1);
    allOpts.forEach(function(o) { select.add(o.cloneNode(true)); });
}

function renderCursosAsignar() {
    var container = document.getElementById('cursos-a-asignar');
    container.innerHTML = '';
    if (cursosAsignar.length === 0) return;
    var div = document.createElement('div');
    div.style.cssText = 'border:1px solid var(--border-color);border-radius:4px;';
    cursosAsignar.forEach(function(c, i) {
        var row = document.createElement('div');
        row.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:6px 10px;font-size:13px;' + (i > 0 ? 'border-top:1px solid var(--border-color);' : '');
        row.innerHTML = '<span>' + htmlspecialchars(c.text) + '</span>' +
            '<input type="hidden" name="cursos_ids[]" value="' + c.id + '" />' +
            '<button type="button" onclick="quitarCursoAsignar(' + i + ')" style="border:none;background:none;color:#dc3545;cursor:pointer;font-size:18px;line-height:1;padding:0;">&times;</button>';
        div.appendChild(row);
    });
    container.appendChild(div);
}

function quitarCursoAsignar(index) {
    cursosAsignar.splice(index, 1);
    renderCursosAsignar();
}

function htmlspecialchars(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

function validarFormAsignar(e) {
    if (cursosAsignar.length === 0) {
        e.preventDefault();
        alert('Agrega al menos un curso usando el botón +.');
    }
}

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
    document.querySelectorAll('.select-filter').forEach(function(input) {
        var select = document.getElementById(input.dataset.target);
        if (!select) return;
        var wrapper = document.createElement('div');
        wrapper.style.cssText = 'position:relative;';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        var dropdown = document.createElement('div');
        dropdown.style.cssText = 'display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--border-color);border-radius:4px;max-height:220px;overflow-y:auto;z-index:20;box-shadow:0 4px 12px rgba(0,0,0,0.12);';
        wrapper.appendChild(dropdown);
        input.setAttribute('autocomplete', 'off');

        var allOpts = Array.from(select.options).filter(function(o) { return o.value; });

        function mostrarResultados(q) {
            var items = q ? allOpts.filter(function(o) { return o.text.toLowerCase().includes(q); }) : [];
            if (items.length === 0 || !q) { dropdown.style.display = 'none'; return; }
            dropdown.innerHTML = items.map(function(o) {
                return '<div class="filter-item" data-value="' + o.value + '" style="padding:8px 10px;cursor:pointer;font-size:13px;border-bottom:1px solid #eee;">' + htmlspecialchars(o.text) + '</div>';
            }).join('');
            dropdown.style.display = 'block';
        }

        input.addEventListener('input', function() { mostrarResultados(this.value.toLowerCase()); });
        input.addEventListener('focus', function() { if (this.value) mostrarResultados(this.value.toLowerCase()); });
        input.addEventListener('blur', function() { setTimeout(function() { dropdown.style.display = 'none'; }, 200); });

        dropdown.addEventListener('mousedown', function(e) {
            var item = e.target.closest('.filter-item');
            if (!item) return;
            e.preventDefault();
            input.value = item.textContent;
            select.value = item.dataset.value;
            dropdown.style.display = 'none';
            input.dispatchEvent(new Event('change'));
        });
    });
    document.getElementById('form-asignar')?.addEventListener('submit', validarFormAsignar);
});
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
