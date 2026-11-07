<?php
function generarPasswordTemporal(string $nombre_completo, string $fecha): string {
    $partes = explode(' ', trim($nombre_completo));
    $nombres = [];
    $apellidos = [];

    foreach ($partes as $p) {
        $p = trim($p);
        if (empty($p)) continue;
        if (empty($nombres)) {
            $nombres[] = $p;
        } elseif (count($apellidos) < 2) {
            $apellidos[] = $p;
        } elseif (count($apellidos) === 0) {
            $apellidos[] = $p;
        }
    }

    $primera = strtolower(mb_substr($nombres[0] ?? '', 0, 1));

    $segunda = '';
    if (!empty($apellidos[0])) {
        $segunda = strtolower(mb_substr($apellidos[0], 0, 1));
    }

    $tercera = '';
    if (!empty($apellidos[1])) {
        $tercera = strtolower(mb_substr($apellidos[1], 0, 1));
    }

    $fecha_dt = new DateTime($fecha);
    $fecha_str = $fecha_dt->format('j-n-Y');

    return $primera . $segunda . $tercera . $fecha_str . '!';
}

function limpiarInput(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generarUsername(PDO $pdo, string $nombre_completo): string {
    $partes = explode(' ', trim($nombre_completo));
    $nombres = [];
    $apellidos = [];
    foreach ($partes as $p) {
        $p = trim($p);
        if (empty($p)) continue;
        if (empty($nombres)) {
            $nombres[] = $p;
        } elseif (count($apellidos) < 1) {
            $apellidos[] = $p;
        }
    }
    $primera = strtolower(mb_substr($nombres[0] ?? '', 0, 1));
    $apellido = strtolower($apellidos[0] ?? '');
    $base = 'iut.' . $primera . $apellido;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$base]);
    if ((int)$stmt->fetchColumn() === 0) return $base;

    $stmt = $pdo->prepare("SELECT username FROM usuarios WHERE username REGEXP ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['^' . preg_quote($base, '/') . '\d+$']);
    $last = $stmt->fetchColumn();
    if ($last && preg_match('/\d+$/', $last, $m)) return $base . ((int)$m[0] + 1);

    return $base . '2';
}

function obtenerCarreras(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, nombre, nivel, codigo FROM carreras ORDER BY nombre, nivel");
    return $stmt->fetchAll();
}

function obtenerUsuariosPorRol(PDO $pdo, string $rol): array {
    $stmt = $pdo->prepare("SELECT id, cedula, nombre_completo, email, activo FROM usuarios WHERE rol = ? AND activo = 1 ORDER BY nombre_completo");
    $stmt->execute([$rol]);
    return $stmt->fetchAll();
}
