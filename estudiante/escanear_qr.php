<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

global $pdo;

function obtenerResumenEstudiante(PDO $pdo, int $estudiante_id, string $periodo): array {
    $stmt_asist = $pdo->prepare("
        SELECT i.curso_id, a.inscripcion_id, a.estado, COUNT(*) as cantidad
        FROM asistencias a
        JOIN inscripciones i ON i.id = a.inscripcion_id
        WHERE i.estudiante_id = ? AND i.periodo = ?
        GROUP BY i.curso_id, a.inscripcion_id, a.estado
    ");
    $stmt_asist->execute([$estudiante_id, $periodo]);

    $global_counts = ['presente'=>0, 'tarde'=>0, 'ausente'=>0, 'justificado'=>0];
    $cursos_counts = [];

    while ($row = $stmt_asist->fetch()) {
        $c_id = (int)$row['curso_id'];
        $estado = (string)$row['estado'];
        $cant = (int)$row['cantidad'];
        if (isset($global_counts[$estado])) {
            $global_counts[$estado] += $cant;
        }
        if (!isset($cursos_counts[$c_id])) {
            $cursos_counts[$c_id] = ['presente'=>0, 'tarde'=>0, 'ausente'=>0, 'justificado'=>0];
        }
        if (isset($cursos_counts[$c_id][$estado])) {
            $cursos_counts[$c_id][$estado] += $cant;
        }
    }

    $global_tot = array_sum($global_counts);
    $global_asi = $global_counts['presente'] + $global_counts['tarde'] + $global_counts['justificado'];
    $global_porc = $global_tot > 0 ? round(($global_asi / $global_tot) * 100) : 100;

    $cursos_data = [];
    foreach ($cursos_counts as $c_id => $cnt) {
        $c_tot = array_sum($cnt);
        $c_asi = $cnt['presente'] + $cnt['tarde'] + $cnt['justificado'];
        $c_porc = $c_tot > 0 ? round(($c_asi / $c_tot) * 100) : null;
        $cursos_data[$c_id] = [
            'presente' => $cnt['presente'],
            'tarde' => $cnt['tarde'],
            'ausente' => $cnt['ausente'],
            'justificado' => $cnt['justificado'],
            'total' => $c_tot,
            'porcentaje' => $c_porc
        ];
    }

    return [
        'global' => [
            'presente' => $global_counts['presente'],
            'tarde' => $global_counts['tarde'],
            'ausente' => $global_counts['ausente'],
            'justificado' => $global_counts['justificado'],
            'total' => $global_tot,
            'asistidas' => $global_asi,
            'porcentaje' => $global_porc,
        ],
        'cursos' => $cursos_data
    ];
}


redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'estudiante') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}

$estudiante_id = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'resumen_data') {
    $periodo = obtenerPeriodoActual();
    $resumen = obtenerResumenEstudiante($pdo, $estudiante_id, $periodo);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'resumen' => $resumen]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'historial') {
    $stmt = $pdo->prepare("
        SELECT c.nombre, c.codigo, a.fecha, a.bloque_horario, a.hora_escaneo, a.semana, a.estado, a.justificacion
        FROM asistencias a
        JOIN inscripciones i ON i.id = a.inscripcion_id
        JOIN cursos c ON c.id = i.curso_id
        WHERE i.estudiante_id = ?
        ORDER BY a.fecha DESC, a.hora_escaneo DESC, a.id DESC
        LIMIT 50
    ");
    $stmt->execute([$estudiante_id]);
    $rows = $stmt->fetchAll();

    $registros = [];
    foreach ($rows as $r) {
        $registros[] = [
            'curso' => htmlspecialchars($r['nombre']),
            'codigo' => htmlspecialchars($r['codigo']),
            'fecha' => $r['fecha'],
            'bloque_horario' => htmlspecialchars($r['bloque_horario'] ?? ''),
            'hora' => $r['hora_escaneo'] ? date('h:i A', strtotime($r['hora_escaneo'])) : '--:--',
            'semana' => (int)$r['semana'],
            'estado' => $r['estado'] ?? 'presente',
            'justificacion' => !empty($r['justificacion']) ? htmlspecialchars($r['justificacion']) : null,
        ];
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'registros' => $registros]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$codigo_qr = trim($_POST['codigo_qr'] ?? '');

if (empty($codigo_qr)) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Código QR requerido']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT sq.id, sq.curso_id, sq.semana, sq.bloque_horario, sq.hora_inicio, sq.hora_tolerancia, sq.hora_expiracion, sq.fecha, sq.activo,
           c.nombre as curso_nombre, c.codigo as curso_codigo
    FROM sesiones_qr sq
    JOIN cursos c ON c.id = sq.curso_id
    WHERE sq.codigo_qr = ?
");
$stmt->execute([$codigo_qr]);
$sesion = $stmt->fetch();

if (!$sesion) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Código QR no válido']);
    exit;
}

if (!$sesion['activo']) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Este código QR ya no está activo']);
    exit;
}

$hora_actual = date('H:i:s');
$fecha_actual = date('Y-m-d');

if ($sesion['fecha'] !== $fecha_actual) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Este QR es de otra fecha']);
    exit;
}

if ($hora_actual < $sesion['hora_inicio'] || $hora_actual > $sesion['hora_expiracion']) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'El código QR ha expirado o aún no está vigente']);
    exit;
}

$periodo_escaner = obtenerPeriodoActual();
$stmt = $pdo->prepare("SELECT id, semana, fecha, bloque_horario FROM sesiones_qr WHERE curso_id = ? AND id != ? AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
$stmt->execute([$sesion['curso_id'], $sesion['id']]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $expirada) {
    $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ?")->execute([$expirada['id']]);
    $insc = $pdo->prepare("SELECT i.id FROM inscripciones i WHERE i.curso_id = ? AND i.periodo = ?");
    $insc->execute([$sesion['curso_id'], $periodo_escaner]);
    foreach ($insc->fetchAll(PDO::FETCH_ASSOC) as $i) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
        $chk->execute([$i['id'], $expirada['semana'], $expirada['fecha'], $expirada['bloque_horario'], $expirada['bloque_horario']]);
        if ($chk->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado) VALUES (?, ?, ?, ?, 'ausente')")->execute([$i['id'], $expirada['semana'], $expirada['fecha'], $expirada['bloque_horario']]);
        }
    }
}

$periodo = obtenerPeriodoActual();
$stmt = $pdo->prepare("
    SELECT i.id, i.curso_id
    FROM inscripciones i
    WHERE i.estudiante_id = ? AND i.curso_id = ? AND i.periodo = ?
");
$stmt->execute([$estudiante_id, $sesion['curso_id'], $periodo]);
$inscripcion = $stmt->fetch();

if (!$inscripcion) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'No estás inscrito en esta materia para el periodo actual']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, estado FROM asistencias
    WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))
");
$stmt->execute([$inscripcion['id'], $sesion['semana'], $fecha_actual, $sesion['bloque_horario'], $sesion['bloque_horario']]);
$asistencia_existente = $stmt->fetch();

$nuevo_estado = 'presente';
if (!empty($sesion['hora_tolerancia']) && $hora_actual > $sesion['hora_tolerancia']) {
    $nuevo_estado = 'tarde';
}

if ($asistencia_existente) {
    if ($asistencia_existente['estado'] === 'presente') {
        $resumen = obtenerResumenEstudiante($pdo, $estudiante_id, $periodo);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'mensaje' => 'Ya tenías registrada tu asistencia (Presente)', 'estado' => 'presente', 'curso_id' => (int)$sesion['curso_id'], 'semana' => (int)$sesion['semana'], 'resumen' => $resumen]);
        exit;
    }
    if ($asistencia_existente['estado'] === 'tarde' && $nuevo_estado === 'tarde') {
        $resumen = obtenerResumenEstudiante($pdo, $estudiante_id, $periodo);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'mensaje' => 'Ya tenías registrada tu asistencia (Tarde)', 'estado' => 'tarde', 'curso_id' => (int)$sesion['curso_id'], 'semana' => (int)$sesion['semana'], 'resumen' => $resumen]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE asistencias SET estado = ?, hora_escaneo = ?, qr_code = ?, bloque_horario = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $hora_actual, $codigo_qr, $sesion['bloque_horario'], $asistencia_existente['id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, hora_escaneo, estado, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$inscripcion['id'], $sesion['semana'], $fecha_actual, $sesion['bloque_horario'], $hora_actual, $nuevo_estado, $codigo_qr]);
}

registrarAuditoria($pdo, $estudiante_id, 'Escaneo QR', "Curso: {$sesion['curso_nombre']} ({$sesion['curso_codigo']}), Semana: {$sesion['semana']}, Estado: $nuevo_estado");

$mensaje_exito = ($nuevo_estado === 'tarde') 
    ? '¡Asistencia registrada (Tarde)!' 
    : '¡Asistencia registrada exitosamente!';

$resumen = obtenerResumenEstudiante($pdo, $estudiante_id, $periodo);
header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'mensaje' => $mensaje_exito,
    'estado' => $nuevo_estado,
    'curso' => $sesion['curso_nombre'],
    'curso_id' => (int)$sesion['curso_id'],
    'semana' => (int)$sesion['semana'],
    'resumen' => $resumen
]);
