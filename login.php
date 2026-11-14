<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$login_tipo = trim($_POST['login_tipo'] ?? 'usuario');
$username_input = trim($_POST['username'] ?? '');
$cedula_input = trim($_POST['cedula'] ?? '');
$password = $_POST['password'] ?? '';

if ($login_tipo === 'admin') {
    // === FLUJO DE ADMINISTRADOR ===
    if (empty($username_input) || empty($password)) {
        header('Location: index.php?error=credenciales');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, cedula, username, nombre_completo, email, rol, password_hash, password_temporal, activo 
                           FROM usuarios 
                           WHERE (username = ? OR cedula = ?)");
    $stmt->execute([$username_input, $username_input]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header('Location: index.php?error=credenciales');
        exit;
    }

    // No permitir entrar como profesor o estudiante desde la pestaña de admin
    if ($usuario['rol'] !== 'admin') {
        header('Location: index.php?error=acceso_denegado_usuario');
        exit;
    }
} else {
    // === FLUJO DE ESTUDIANTE / PROFESOR ===
    if (empty($cedula_input) || empty($password)) {
        header('Location: index.php?error=credenciales');
        exit;
    }

    // Cuentas demo de prueba
    if ($cedula_input === 'alumno' || $cedula_input === 'profesor') {
        $stmt = $pdo->prepare("SELECT id, cedula, username, nombre_completo, email, rol, password_hash, password_temporal, activo 
                               FROM usuarios 
                               WHERE username = ? OR cedula = ?");
        $stmt->execute([$cedula_input, $cedula_input]);
        $usuario = $stmt->fetch();
    } else {
        // Validar formato estricto: solo números
        if (!preg_match('/^[0-9]+$/', $cedula_input)) {
            header('Location: index.php?error=formato_cedula');
            exit;
        }

        $cedula_con_v = 'V' . $cedula_input;
        $stmt = $pdo->prepare("SELECT id, cedula, username, nombre_completo, email, rol, password_hash, password_temporal, activo 
                               FROM usuarios 
                               WHERE cedula = ? OR cedula = ? OR username = ?");
        $stmt->execute([$cedula_con_v, $cedula_input, $cedula_input]);
        $usuario = $stmt->fetch();
    }

    if (!$usuario) {
        header('Location: index.php?error=credenciales');
        exit;
    }

    // Estricto: Administradores NO pueden ingresar desde el menú de profesores/alumnos
    if ($usuario['rol'] === 'admin') {
        header('Location: index.php?error=acceso_denegado_admin');
        exit;
    }
}

if (!password_verify($password, $usuario['password_hash'])) {
    header('Location: index.php?error=credenciales');
    exit;
}

if (!$usuario['activo']) {
    header('Location: index.php?error=inactivo');
    exit;
}

$_SESSION['usuario_id'] = (int)$usuario['id'];
$_SESSION['cedula'] = $usuario['cedula'];
$_SESSION['nombre'] = $usuario['nombre_completo'];
$_SESSION['email'] = $usuario['email'];
$_SESSION['rol'] = $usuario['rol'];

registrarAuditoria($pdo, (int)$usuario['id'], 'Inicio de sesión', 'Inicio de sesión exitoso');

if ($usuario['password_temporal']) {
    header('Location: cambio_password.php');
    exit;
}

redirigirSegunRol();
