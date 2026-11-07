<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username_input = trim($_POST['username'] ?? '');
$cedula_input = trim($_POST['cedula'] ?? '');
$password = $_POST['password'] ?? '';

if ((empty($cedula_input) && empty($username_input)) || empty($password)) {
    header('Location: index.php?error=credenciales');
    exit;
}

$input = !empty($username_input) ? $username_input : $cedula_input;
$cedula_digits = preg_replace('/[^0-9]/', '', $input);
$cedula_con_v = !empty($cedula_digits) ? 'V' . $cedula_digits : '';

$stmt = $pdo->prepare("SELECT id, cedula, username, nombre_completo, email, rol, password_hash, password_temporal, activo 
                       FROM usuarios 
                       WHERE username = ? 
                          OR cedula = ? 
                          OR (? != '' AND cedula = ?)");
$stmt->execute([$input, $input, $cedula_con_v, $cedula_con_v]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: index.php?error=credenciales');
    exit;
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
