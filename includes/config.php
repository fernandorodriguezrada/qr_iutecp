<?php
// Configuración de Base de Datos
// Por defecto se conecta a DDEV local ('db'). Para InfinityFree u otro hosting,
// se pueden definir variables de entorno o ajustar los valores de respaldo aquí.
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'db');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'db');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Sistema QR IUTECP');

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
$scheme = $is_https ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('APP_URL', "$scheme://$host");

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
