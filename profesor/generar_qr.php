<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

global $pdo;

redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'profesor' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}

try {
    $profesor_id = (int)$_SESSION['usuario_id'];
    $curso_id = (int)($_POST['curso_id'] ?? 0);
    $semana = (int)($_POST['semana'] ?? 1);

    if ($curso_id <= 0 || $semana < 1 || $semana > 12) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT expiracion_minutos, tolerancia_minutos FROM configuracion_profesor WHERE profesor_id = ?");
    $stmt->execute([$profesor_id]);
    $cfg = $stmt->fetch();
    $expiracion_minutos = $cfg && !empty($cfg['expiracion_minutos']) ? (int)$cfg['expiracion_minutos'] : 20;
    $tolerancia_minutos = $cfg && !empty($cfg['tolerancia_minutos']) ? min($expiracion_minutos, (int)$cfg['tolerancia_minutos']) : 10;
    $expiracion_segundos = $expiracion_minutos * 60;
    $tolerancia_segundos = $tolerancia_minutos * 60;

    $bloque_horario = trim($_POST['bloque_horario'] ?? '');

    $stmt = $pdo->prepare("SELECT id, horario FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    $curso_row = $stmt->fetch();
    if (!$curso_row) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no encontrado o no asignado']);
        exit;
    }

    if (empty($bloque_horario) && !empty($curso_row['horario'])) {
        $bloques = array_map('trim', explode('/', $curso_row['horario']));
        $bloque_horario = $bloques[0] ?? '';
    }

    $codigo_qr = bin2hex(random_bytes(16));
    $fecha = date('Y-m-d');
    $now = time();
    $hora_inicio = date('H:i:s', $now);
    $hora_tolerancia = date('H:i:s', $now + $tolerancia_segundos);
    $hora_expiracion = date('H:i:s', $now + $expiracion_segundos);

    $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE profesor_id = ? AND activo = 1")->execute([$profesor_id]);

    $stmt = $pdo->prepare("INSERT INTO sesiones_qr (curso_id, profesor_id, codigo_qr, fecha, hora_inicio, hora_tolerancia, tolerancia_minutos, hora_expiracion, semana, bloque_horario, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$curso_id, $profesor_id, $codigo_qr, $fecha, $hora_inicio, $hora_tolerancia, $tolerancia_minutos, $hora_expiracion, $semana, $bloque_horario]);

    $sesion_id = $pdo->lastInsertId();

    $periodo_actual = obtenerPeriodoActual();
    $stmt = $pdo->prepare("SELECT id, semana, fecha, bloque_horario FROM sesiones_qr WHERE curso_id = ? AND activo = 1 AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
    $stmt->execute([$curso_id]);
    foreach ($stmt->fetchAll() as $expirada) {
        $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ?")->execute([$expirada['id']]);
        $insc = $pdo->prepare("SELECT i.id FROM inscripciones i WHERE i.curso_id = ? AND i.periodo = ?");
        $insc->execute([$curso_id, $periodo_actual]);
        foreach ($insc->fetchAll() as $i) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND bloque_horario = ?");
            $chk->execute([$i['id'], $expirada['semana'], $expirada['fecha'], $expirada['bloque_horario']]);
            if ($chk->fetchColumn() == 0) {
                $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado) VALUES (?, ?, ?, ?, 'ausente')")->execute([$i['id'], $expirada['semana'], $expirada['fecha'], $expirada['bloque_horario']]);
            }
        }
    }

    registrarAuditoria($pdo, $profesor_id, 'Generar QR', "Curso ID: $curso_id, Semana: $semana, Sesion QR ID: $sesion_id");

    require_once __DIR__ . '/../includes/phpqrcode/qrlib.php';
    ob_start();
    QRcode::png($codigo_qr, false, QR_ECLEVEL_L, 6);
    $img_bytes = ob_get_clean();
    $qr_url = 'data:image/png;base64,' . base64_encode($img_bytes);

    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'qr_url' => $qr_url,
        'codigo' => $codigo_qr,
        'bloque_horario' => $bloque_horario,
        'expiracion_segundos' => $expiracion_segundos,
        'tolerancia_segundos' => $tolerancia_segundos,
        'sesion_id' => $sesion_id,
    ]);
} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
