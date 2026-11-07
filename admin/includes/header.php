<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

redirigirSiNoAutenticado();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <title>Panel Administrativo - Sistema QR IUTECP</title>
    <link rel="stylesheet" href="/style.css" />
</head>
<body>
<div style="display:flex;flex:1;height:100vh;">
    <aside class="admin-sidebar">
        <img src="/logoIUTECP_azul.png" alt="IUTECP" />
        <ul>
            <li class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <a href="/admin/index.php" style="text-decoration:none;color:inherit;display:block">📊 Panel Principal</a>
            </li>
            <?php $es_gestion = in_array(basename($_SERVER['PHP_SELF']), ['usuarios.php', 'usuarios_lista.php', 'cursos_lista.php']); ?>
            <li class="<?= $es_gestion ? 'active' : '' ?>">
                <a href="/admin/usuarios.php" style="text-decoration:none;color:inherit;display:block">👥 Gestión de Entidades</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'active' : '' ?>">
                <a href="/admin/reportes.php" style="text-decoration:none;color:inherit;display:block">📄 Reportes y Datos</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) === 'cargar_inscripciones.php' ? 'active' : '' ?>">
                <a href="/admin/cargar_inscripciones.php" style="text-decoration:none;color:inherit;display:block">📥 Carga de Datos</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) === 'seguridad.php' ? 'active' : '' ?>">
                <a href="/admin/seguridad.php" style="text-decoration:none;color:inherit;display:block">🔑 Seguridad y Accesos</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) === 'database.php' ? 'active' : '' ?>">
                <a href="/admin/database.php" style="text-decoration:none;color:inherit;display:block">🗄️ Base de Datos</a>
            </li>
            <li style="color:#dc3545;margin-top:20px">
                <a href="/logout.php" style="text-decoration:none;color:#dc3545;display:block">🚪 Cerrar Sesión</a>
            </li>
        </ul>
    </aside>
    <section class="admin-content">
        <div style="position:absolute;top:20px;right:40px;font-size:13px;color:#888;display:flex;gap:20px;">
            <span><strong>Rol:</strong> Administrativo</span>
            <span><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span><strong>Correo:</strong> <?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
        </div>
