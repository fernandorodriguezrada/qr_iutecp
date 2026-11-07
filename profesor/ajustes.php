<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

global $pdo;

redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'profesor') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}

$profesor_id = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT expiracion_minutos, rotacion_segundos, radio_gps FROM configuracion_profesor WHERE profesor_id = ?");
    $stmt->execute([$profesor_id]);
    $cfg = $stmt->fetch();
    if (!$cfg) {
        $cfg = ['expiracion_minutos' => 5, 'rotacion_segundos' => 30, 'radio_gps' => 50];
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'config' => $cfg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expiracion = max(1, min(60, (int)($_POST['expiracion_minutos'] ?? 5)));
    $rotacion = max(10, min(300, (int)($_POST['rotacion_segundos'] ?? 30)));
    $radio = max(10, min(500, (int)($_POST['radio_gps'] ?? 50)));

    $stmt = $pdo->prepare("SELECT id FROM configuracion_profesor WHERE profesor_id = ?");
    $stmt->execute([$profesor_id]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare("UPDATE configuracion_profesor SET expiracion_minutos = ?, rotacion_segundos = ?, radio_gps = ? WHERE profesor_id = ?");
        $upd->execute([$expiracion, $rotacion, $radio, $profesor_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO configuracion_profesor (profesor_id, expiracion_minutos, rotacion_segundos, radio_gps) VALUES (?, ?, ?, ?)");
        $ins->execute([$profesor_id, $expiracion, $rotacion, $radio]);
    }

    registrarAuditoria($pdo, $profesor_id, 'Ajustes QR', "Exp:{$expiracion}min Rot:{$rotacion}seg GPS:{$radio}m");

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}
