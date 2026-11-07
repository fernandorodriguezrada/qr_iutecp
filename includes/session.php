<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaAutenticado(): bool {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['rol']);
}

function redirigirSiNoAutenticado(): void {
    if (!estaAutenticado()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function redirigirSegunRol(): void {
    if (!estaAutenticado()) return;

    $rol = $_SESSION['rol'];
    if ($rol === 'admin') {
        header('Location: ' . APP_URL . '/admin/index.php');
    } elseif ($rol === 'profesor') {
        header('Location: ' . APP_URL . '/profesor/index.php');
    } elseif ($rol === 'estudiante') {
        header('Location: ' . APP_URL . '/estudiante/index.php');
    }
    exit;
}

function registrarAuditoria(PDO $pdo, ?int $usuario_id, string $accion, string $detalle = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ($usuario_id !== null) {
        $check = $pdo->prepare("SELECT 1 FROM usuarios WHERE id = ?");
        $check->execute([$usuario_id]);
        if (!$check->fetchColumn()) {
            $usuario_id = null;
        }
    }
    $stmt = $pdo->prepare("INSERT INTO auditoria (usuario_id, accion, detalle, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $accion, $detalle, $ip]);
}
