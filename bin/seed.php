#!/usr/bin/env php
<?php
/**
 * Database Seed Runner (CLI & Web)
 * Populates realistic demo data (Fernando Rodríguez, 8 classmates, Trayecto 5 Section 1D1, attendances).
 */

if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/../includes/session.php';
    if (!estaAutenticado() || $_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        die('Acceso denegado. Se requieren permisos de administrador.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../scripts/poblar_datos_5to_cuatrimestre.php';
