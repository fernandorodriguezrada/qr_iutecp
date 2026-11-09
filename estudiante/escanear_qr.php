<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

global $pdo;

redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'estudiante') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}

$estudiante_id = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'historial') {
    $stmt = $pdo->prepare("
        SELECT c.nombre, c.codigo, a.fecha, a.hora_escaneo, a.semana
        FROM asistencias a
        JOIN inscripciones i ON i.id = a.inscripcion_id
        JOIN cursos c ON c.id = i.curso_id
        WHERE i.estudiante_id = ?
        ORDER BY a.fecha DESC, a.hora_escaneo DESC
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
            'hora' => $r['hora_escaneo'] ? date('h:i A', strtotime($r['hora_escaneo'])) : '--:--',
            'semana' => (int)$r['semana'],
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
    SELECT sq.id, sq.curso_id, sq.semana, sq.hora_inicio, sq.hora_expiracion, sq.fecha, sq.activo,
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

$stmt = $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ? AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
$stmt->execute([$sesion['id']]);

$periodo_escaner = obtenerPeriodoActual();
$stmt = $pdo->prepare("SELECT id, semana, fecha FROM sesiones_qr WHERE curso_id = ? AND activo = 1 AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
$stmt->execute([$sesion['curso_id']]);
foreach ($stmt->fetchAll() as $expirada) {
    $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ?")->execute([$expirada['id']]);
    $insc = $pdo->prepare("SELECT i.id FROM inscripciones i WHERE i.curso_id = ? AND i.periodo = ?");
    $insc->execute([$sesion['curso_id'], $periodo_escaner]);
    foreach ($insc->fetchAll() as $i) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ?");
        $chk->execute([$i['id'], $expirada['semana'], $expirada['fecha']]);
        if ($chk->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, estado) VALUES (?, ?, ?, 'ausente')")->execute([$i['id'], $expirada['semana'], $expirada['fecha']]);
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
    WHERE inscripcion_id = ? AND semana = ? AND fecha = ?
");
$stmt->execute([$inscripcion['id'], $sesion['semana'], $fecha_actual]);
$asistencia_existente = $stmt->fetch();

if ($asistencia_existente) {
    if ($asistencia_existente['estado'] === 'presente') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'mensaje' => 'Ya tenías registrada esta asistencia']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE asistencias SET estado = 'presente', hora_escaneo = ?, qr_code = ? WHERE id = ?");
    $stmt->execute([$hora_actual, $codigo_qr, $asistencia_existente['id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, hora_escaneo, estado, qr_code) VALUES (?, ?, ?, ?, 'presente', ?)");
    $stmt->execute([$inscripcion['id'], $sesion['semana'], $fecha_actual, $hora_actual, $codigo_qr]);
}

registrarAuditoria($pdo, $estudiante_id, 'Escaneo QR', "Curso: {$sesion['curso_nombre']} ({$sesion['curso_codigo']}), Semana: {$sesion['semana']}");

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'mensaje' => 'Asistencia registrada exitosamente',
    'curso' => $sesion['curso_nombre'],
    'semana' => (int)$sesion['semana'],
]);
