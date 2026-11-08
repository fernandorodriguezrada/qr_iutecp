<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

global $pdo;

redirigirSiNoAutenticado();

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($actual, $user['password_hash'])) {
        $error = 'actual_incorrecta';
    } elseif (strlen($nueva) < 6) {
        $error = 'corta';
    } elseif ($nueva !== $confirmar) {
        $error = 'no_coinciden';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, password_temporal = FALSE WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['usuario_id']]);

        registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Cambio de contraseña', 'Contraseña temporal cambiada');

        $success = 'cambiada';
        redirigirSegunRol();
        exit;
    }

    if ($error) {
        header('Location: cambio_password.php?error=' . $error);
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#e0e0e0" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="QR IUTECP" />
    <?php require_once __DIR__ . "/includes/pwa_head.php"; ?>
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <title>Cambiar Contraseña - Sistema QR IUTECP</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>" />
    <style>
        .msg-info {
            background: #cce5ff; color: #004085; padding: 10px; border-radius: 4px;
            margin-bottom: 15px; font-size: 14px; border: 1px solid #b8daff;
        }
        .msg-error {
            background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px;
            margin-bottom: 15px; font-size: 14px; border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="auth-view" style="display:flex;">
        <div class="bg-icons">
            <span style="top:10%;left:8%;transform:rotate(-15deg)" title="Informática">💻</span>
            <span style="top:15%;left:85%;transform:rotate(20deg)" title="Administración">💼</span>
            <span style="top:75%;left:10%;transform:rotate(-10deg)" title="Contaduría">🧮</span>
            <span style="top:80%;left:85%;transform:rotate(15deg)" title="Enfermería">🩺</span>
            <span style="top:45%;left:5%;transform:rotate(25deg)" title="Fisioterapia">🦴</span>
            <span style="top:50%;left:90%;transform:rotate(-20deg)" title="Relaciones Industriales">🤝🏾</span>
            <span style="top:12%;left:45%;transform:rotate(10deg)" title="Enfermería">💉</span>
            <span style="top:85%;left:50%;transform:rotate(-15deg)" title="Contaduría">📊</span>
            <span style="top:65%;left:22%;transform:rotate(30deg)" title="Relaciones Industriales">👥</span>
            <span style="top:25%;left:65%;transform:rotate(-25deg)" title="Informática">⌨️</span>
        </div>
        <img src="k-listo-foto.png" alt="K-Listo" class="auth-mascot" />
        <div class="auth-card">
            <img src="logoIUTECP_azul.png" alt="IUTECP" class="auth-logo" />
            <p class="auth-slogan">¡Formando profesionales exitosos!</p>
            <hr class="auth-divider" />
            <h2 style="color: var(--primary); margin-bottom: 10px">Cambiar Contraseña</h2>
            <p style="font-size:14px;color:#666;margin-bottom:20px">
                Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>.
                Por ser tu primer inicio de sesión, debes cambiar tu contraseña temporal.
            </p>

            <?php if ($error === 'actual_incorrecta'): ?>
                <div class="msg-error">La contraseña actual no es correcta.</div>
            <?php elseif ($error === 'corta'): ?>
                <div class="msg-error">La nueva contraseña debe tener al menos 6 caracteres.</div>
            <?php elseif ($error === 'no_coinciden'): ?>
                <div class="msg-error">Las contraseñas nuevas no coinciden.</div>
            <?php endif; ?>

            <form method="POST" action="cambio_password.php">
                <label>Contraseña Actual</label>
                <input type="password" name="password_actual" required />
                <label>Nueva Contraseña</label>
                <input type="password" name="password_nueva" required minlength="6" />
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmar" required minlength="6" />
                <button type="submit" class="btn btn-primary" style="width:100%;font-size:16px">
                    Cambiar Contraseña
                </button>
            </form>
        </div>
        <img src="k-listo-happy.png" alt="K-Listo Feliz" class="auth-mascot" />
    </div>
</body>
</html>
