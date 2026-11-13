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
    $stmt = $pdo->prepare("SELECT expiracion_minutos, tolerancia_minutos FROM configuracion_profesor WHERE profesor_id = ?");
    $stmt->execute([$profesor_id]);
    $cfg = $stmt->fetch();
    if (!$cfg) {
        $cfg = ['expiracion_minutos' => 20, 'tolerancia_minutos' => 10];
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'config' => $cfg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expiracion = max(1, min(120, (int)($_POST['expiracion_minutos'] ?? 20)));
    $tolerancia = max(1, min($expiracion, (int)($_POST['tolerancia_minutos'] ?? 10)));
    $rotacion = 30;
    $radio = 50;

    $stmt = $pdo->prepare("SELECT id FROM configuracion_profesor WHERE profesor_id = ?");
    $stmt->execute([$profesor_id]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare("UPDATE configuracion_profesor SET expiracion_minutos = ?, tolerancia_minutos = ? WHERE profesor_id = ?");
        $upd->execute([$expiracion, $tolerancia, $profesor_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO configuracion_profesor (profesor_id, expiracion_minutos, tolerancia_minutos, rotacion_segundos, radio_gps) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$profesor_id, $expiracion, $tolerancia, $rotacion, $radio]);
    }

    registrarAuditoria($pdo, $profesor_id, 'Ajustes QR', "Puntual:{$tolerancia}min Cierre:{$expiracion}min");

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}
