#!/usr/bin/env php
<?php
/**
 * Database Migration Runner (CLI & Web)
 * Executes pending SQL migrations in numerical order.
 */

if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/../includes/session.php';
    if (!estaAutenticado() || $_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        die('Acceso denegado. Se requieren permisos de administrador.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../includes/config.php';
global $pdo;

echo "=========================================\n";
echo "  IUTECP - Database Migration Runner     \n";
echo "=========================================\n\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration_name` VARCHAR(255) NOT NULL UNIQUE,
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
");

$stmt = $pdo->query("SELECT `migration_name` FROM `migrations`");
$appliedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

$migrationsDir = realpath(__DIR__ . '/../database/migrations');
if (!$migrationsDir || !is_dir($migrationsDir)) {
    die("[ERROR] Directorio de migraciones no encontrado: database/migrations\n");
}

$files = glob($migrationsDir . '/*.sql');
sort($files);

$pendingCount = 0;

foreach ($files as $filePath) {
    $fileName = basename($filePath);

    if (in_array($fileName, $appliedMigrations)) {
        continue;
    }

    $pendingCount++;
    echo "[MIGRATING] Applying {$fileName} ... ";

    $sql = file_get_contents($filePath);
    if ($sql === false || trim($sql) === '') {
        echo "SALTADO (archivo vacío)\n";
        continue;
    }

    try {
        $pdo->exec($sql);
        $insertStmt = $pdo->prepare("INSERT INTO `migrations` (`migration_name`) VALUES (?)");
        $insertStmt->execute([$fileName]);
        echo "OK\n";
    } catch (Throwable $e) {
        echo "FAILED\n";
        echo "\n[ERROR] En migración {$fileName}:\n" . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($pendingCount === 0) {
    echo "Base de datos al día. No hay migraciones pendientes.\n";
} else {
    echo "\n[SUCCESS] Se aplicaron {$pendingCount} migraciones exitosamente.\n";
}
