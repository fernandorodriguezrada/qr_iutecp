<?php

function obtenerInicioCuatrimestre(string $periodo): ?DateTime {
    $partes = explode('-', $periodo);
    if (count($partes) !== 2) return null;
    $anio = (int)$partes[0];
    $cuatri = strtoupper($partes[1]);

    switch ($cuatri) {
        case 'I':   $mes = 1;  break;
        case 'II':  $mes = 5;  break;
        case 'III': $mes = 9;  break;
        default:    return null;
    }

    $fecha = new DateTime("$anio-$mes-01");
    $diaSemana = (int)$fecha->format('N');
    if ($diaSemana !== 1) {
        $diasParaSumar = (8 - $diaSemana) % 7;
        if ($diasParaSumar === 0) $diasParaSumar = 7;
        $fecha->modify("+$diasParaSumar days");
    }
    return $fecha;
}

function obtenerInicioPeriodo(string $periodo): ?DateTime {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT fecha_inicio FROM periodos WHERE codigo = ?");
        $stmt->execute([$periodo]);
        $fecha = $stmt->fetchColumn();
        if ($fecha) return new DateTime($fecha);
    } catch (Exception $e) {}
    return obtenerInicioCuatrimestre($periodo);
}

function obtenerInicioPeriodoNuevos(string $periodo): ?DateTime {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT fecha_inicio_nuevos FROM periodos WHERE codigo = ?");
        $stmt->execute([$periodo]);
        $fecha = $stmt->fetchColumn();
        if ($fecha) return new DateTime($fecha);
    } catch (Exception $e) {}
    return null;
}

function obtenerPeriodoActual(): string {
    global $pdo;
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
            $stmt->execute(['periodo_activo']);
            $periodo = $stmt->fetchColumn();
            if ($periodo) return $periodo;
        } catch (Exception $e) {}
    }
    $mes = (int)date('n');
    $anio = date('Y');
    if ($mes >= 1 && $mes <= 4)      $cuatri = 'I';
    elseif ($mes >= 5 && $mes <= 8)  $cuatri = 'II';
    else                             $cuatri = 'III';
    return "$anio-$cuatri";
}

function obtenerSemanaActual(?int $usuario_id = null): int {
    $periodo = obtenerPeriodoActual();
    $inicio = obtenerInicioPeriodo($periodo);

    if ($usuario_id) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT es_nuevo_ingreso FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $es_nuevo = $stmt->fetchColumn();
            if ($es_nuevo) {
                $inicio_nuevos = obtenerInicioPeriodoNuevos($periodo);
                if ($inicio_nuevos) $inicio = $inicio_nuevos;
            }
        } catch (Exception $e) {}
    }

    if (!$inicio) return 1;
    $hoy = new DateTime('today');
    if ($hoy < $inicio) return 1;
    $diff = (int)$inicio->diff($hoy)->days;
    $semana = (int)floor($diff / 7) + 1;
    return min($semana, 12);
}

function fechaEnPeriodo(string $periodo, string $fecha): bool {
    $inicio = obtenerInicioPeriodo($periodo);
    if (!$inicio) return false;
    $fin = clone $inicio;
    $fin->modify('+12 weeks');
    $fechaDt = new DateTime($fecha);
    return $fechaDt >= $inicio && $fechaDt < $fin;
}

function semanaDeFecha(string $periodo, string $fecha): int {
    $inicio = obtenerInicioPeriodo($periodo);
    if (!$inicio) return 1;
    $fechaDt = new DateTime($fecha);
    if ($fechaDt < $inicio) return 1;
    $diff = (int)$inicio->diff($fechaDt)->days;
    $semana = (int)floor($diff / 7) + 1;
    return min($semana, 12);
}

function numeroRomano(int $n): string {
    $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
    return $map[$n] ?? '';
}
