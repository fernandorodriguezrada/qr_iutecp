<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

redirigirSiNoAutenticado();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

registrarAuditoria($pdo, $_SESSION['usuario_id'], 'Descargar respaldo BD', 'Descarga de copia de seguridad');

header('Content-Type: text/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="respaldo_qr_iutecp_' . date('Y-m-d_H-i') . '.sql"');

$dump_cmd = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --databases %s --routines --triggers --add-drop-database 2>/dev/null',
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_USER),
    escapeshellarg(DB_PASS),
    escapeshellarg(DB_NAME)
);

passthru($dump_cmd);
