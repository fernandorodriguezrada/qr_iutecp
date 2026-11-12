<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/academico.php';

global $pdo;

redirigirSiNoAutenticado();

$es_profesor = ($_SESSION['rol'] === 'profesor');
$es_estudiante = ($_SESSION['rol'] === 'estudiante');

if (!$es_profesor && !$es_estudiante) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}

$profesor_id = (int)$_SESSION['usuario_id'];
$accion = $_GET['accion'] ?? '';
$curso_id = (int)($_GET['curso_id'] ?? 0);

function limpiarSesionesExpiradas($pdo, $curso_id, $periodo) {
    $stmt = $pdo->prepare("SELECT id, semana, fecha, bloque_horario FROM sesiones_qr WHERE curso_id = ? AND activo = 1 AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
    $stmt->execute([$curso_id]);
    $expiradas = $stmt->fetchAll();
    $marcados = 0;
    foreach ($expiradas as $sesion) {
        $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ?")->execute([$sesion['id']]);
        $insc = $pdo->prepare("SELECT i.id FROM inscripciones i WHERE i.curso_id = ? AND i.periodo = ?");
        $insc->execute([$curso_id, $periodo]);
        foreach ($insc->fetchAll() as $i) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND bloque_horario = ?");
            $chk->execute([$i['id'], $sesion['semana'], $sesion['fecha'], $sesion['bloque_horario']]);
            if ($chk->fetchColumn() == 0) {
                $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado) VALUES (?, ?, ?, ?, 'ausente')")->execute([$i['id'], $sesion['semana'], $sesion['fecha'], $sesion['bloque_horario']]);
                $marcados++;
            }
        }
    }
    return $marcados;
}

if ($accion === 'estado_semanas' && $curso_id > 0) {
    $periodo = obtenerPeriodoActual();
    limpiarSesionesExpiradas($pdo, $curso_id, $periodo);

    if ($es_profesor) {
        $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
        $stmt->execute([$curso_id, $profesor_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? AND periodo = ?");
        $stmt->execute([$curso_id, $_SESSION['usuario_id'], $periodo]);
    }
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT a.semana,
               COUNT(DISTINCT i.id) as contador,
               COUNT(DISTINCT CASE WHEN a.estado IN ('presente', 'tarde') THEN i.id END) as completas,
               COUNT(DISTINCT CONCAT(a.fecha, '_', IFNULL(a.bloque_horario, ''))) as sesiones
        FROM inscripciones i
        LEFT JOIN asistencias a ON a.inscripcion_id = i.id AND a.semana BETWEEN 1 AND 12
        WHERE i.curso_id = ? AND i.periodo = ?
        GROUP BY a.semana
    ");
    $stmt->execute([$curso_id, $periodo]);
    $semanas = $stmt->fetchAll();

    $total_inscritos = 0;
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE curso_id = ? AND periodo = ?");
    $stmt2->execute([$curso_id, $periodo]);
    $total_inscritos = (int)$stmt2->fetchColumn();

    $resultado = [];
    foreach ($semanas as $s) {
        if (empty($s['semana'])) continue;
        $row = [
            'semana' => (int)$s['semana'],
            'contador' => $total_inscritos > 0 ? $total_inscritos : (int)$s['contador'],
            'completas' => (int)$s['completas'],
            'total_inscritos' => $total_inscritos,
            'sesiones' => (int)$s['sesiones'],
        ];
        if ($es_estudiante) {
            $stmt3 = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN a.estado = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN a.estado = 'tarde' THEN 1 ELSE 0 END) as tardes,
                    SUM(CASE WHEN a.estado = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM asistencias a 
                JOIN inscripciones i ON i.id = a.inscripcion_id 
                WHERE i.estudiante_id = ? AND i.curso_id = ? AND i.periodo = ? AND a.semana = ?
            ");
            $stmt3->execute([$_SESSION['usuario_id'], $curso_id, $periodo, $s['semana']]);
            $conteo = $stmt3->fetch(PDO::FETCH_ASSOC);
            $row['mis_asistencias'] = (int)($conteo['presentes'] ?? 0);
            $row['mis_tardes'] = (int)($conteo['tardes'] ?? 0);
            $row['mis_justificados'] = (int)($conteo['justificados'] ?? 0);
        }
        $resultado[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'semanas' => $resultado]);
    exit;
}

if ($accion === 'detalle' && $curso_id > 0) {
    $semana = (int)($_GET['semana'] ?? 1);
    if ($semana < 1 || $semana > 12) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Semana inválida']);
        exit;
    }

    $periodo = obtenerPeriodoActual();
    $inscripcion_estudiante_id = 0;

    if ($es_profesor) {
        $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
        $stmt->execute([$curso_id, $profesor_id]);
        if (!$stmt->fetch()) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
            exit;
        }
        limpiarSesionesExpiradas($pdo, $curso_id, $periodo);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? AND periodo = ?");
        $stmt->execute([$curso_id, $_SESSION['usuario_id'], $periodo]);
        $insc_row = $stmt->fetch();
        if (!$insc_row) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'No estás inscrito en este curso']);
            exit;
        }
        $inscripcion_estudiante_id = (int)$insc_row['id'];
    }

    $stmt = $pdo->prepare("SELECT DISTINCT sq.fecha, sq.bloque_horario FROM sesiones_qr sq WHERE sq.curso_id = ? AND sq.semana = ? ORDER BY sq.fecha, sq.id");
    $stmt->execute([$curso_id, $semana]);
    $sesiones_qr = $stmt->fetchAll();

    if (empty($sesiones_qr)) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'sesiones' => []]);
        exit;
    }

    if ($es_profesor) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre_completo, u.cedula, i.id as inscripcion_id
            FROM inscripciones i
            JOIN usuarios u ON u.id = i.estudiante_id
            WHERE i.curso_id = ? AND i.periodo = ?
            ORDER BY u.nombre_completo
        ");
        $stmt->execute([$curso_id, $periodo]);
        $estudiantes = $stmt->fetchAll();

        $sesiones_list = [];
        foreach ($sesiones_qr as $ses_qr) {
            $fecha = $ses_qr['fecha'];
            $bloque = $ses_qr['bloque_horario'] ?? '';
            $stmt = $pdo->prepare("SELECT inscripcion_id, estado, hora_escaneo, justificacion FROM asistencias WHERE semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
            $stmt->execute([$semana, $fecha, $bloque, $bloque]);
            $asistencias_map = [];
            while ($a = $stmt->fetch()) {
                $asistencias_map[$a['inscripcion_id']] = $a;
            }

            $lista = [];
            foreach ($estudiantes as $e) {
                $asis = $asistencias_map[$e['inscripcion_id']] ?? null;
                $lista[] = [
                    'id' => (int)$e['id'],
                    'inscripcion_id' => (int)$e['inscripcion_id'],
                    'nombre' => htmlspecialchars($e['nombre_completo']),
                    'cedula' => htmlspecialchars($e['cedula']),
                    'hora' => $asis && $asis['hora_escaneo'] ? date('h:i A', strtotime($asis['hora_escaneo'])) : '--:--',
                    'estado' => $asis ? $asis['estado'] : 'pendiente',
                    'justificacion' => $asis ? ($asis['justificacion'] ?? '') : '',
                ];
            }

            $sesiones_list[] = [
                'fecha' => $fecha,
                'bloque_horario' => $bloque,
                'estudiantes' => $lista,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'sesiones' => $sesiones_list]);
        exit;
    } else {
        $sesiones_list = [];
        foreach ($sesiones_qr as $ses_qr) {
            $fecha = $ses_qr['fecha'];
            $bloque = $ses_qr['bloque_horario'] ?? '';
            $stmt = $pdo->prepare("SELECT estado, hora_escaneo, justificacion FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
            $stmt->execute([$inscripcion_estudiante_id, $semana, $fecha, $bloque, $bloque]);
            $asis = $stmt->fetch();

            $sesiones_list[] = [
                'fecha' => $fecha,
                'bloque_horario' => $bloque,
                'estado' => $asis ? $asis['estado'] : 'pendiente',
                'hora' => $asis && $asis['hora_escaneo'] ? date('h:i A', strtotime($asis['hora_escaneo'])) : '--:--',
                'justificacion' => $asis ? ($asis['justificacion'] ?? '') : '',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'es_estudiante' => true, 'sesiones' => $sesiones_list]);
        exit;
    }
}

if ($accion === 'cancelar_sesion' && $curso_id > 0) {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $sesion_id = (int)($_GET['sesion_id'] ?? 0);
    $codigo_qr = $_GET['codigo_qr'] ?? '';

    $stmt = $pdo->prepare("SELECT id, codigo_qr, curso_id, semana, fecha FROM sesiones_qr WHERE (id = ? OR codigo_qr = ?) AND profesor_id = ? AND curso_id = ?");
    $stmt->execute([$sesion_id, $codigo_qr, $profesor_id, $curso_id]);
    $sesion = $stmt->fetch();

    if ($sesion) {
        if (!empty($sesion['codigo_qr'])) {
            $pdo->prepare("DELETE FROM asistencias WHERE qr_code = ?")->execute([$sesion['codigo_qr']]);
        }
        $pdo->prepare("DELETE a FROM asistencias a JOIN inscripciones i ON i.id = a.inscripcion_id WHERE i.curso_id = ? AND a.semana = ? AND a.fecha = ?")->execute([$sesion['curso_id'], $sesion['semana'], $sesion['fecha']]);
        $pdo->prepare("DELETE FROM sesiones_qr WHERE id = ?")->execute([$sesion['id']]);
        registrarAuditoria($pdo, $profesor_id, 'Cancelar QR', "Curso ID: $curso_id, Sesion ID: " . $sesion['id']);
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'finalizar_sesion' && $curso_id > 0) {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $periodo = obtenerPeriodoActual();
    $marcados = limpiarSesionesExpiradas($pdo, $curso_id, $periodo);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'marcados' => $marcados]);
    exit;
}

if ($accion === 'justificar' && $curso_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $inscripcion_id = (int)($_POST['inscripcion_id'] ?? 0);
    $semana = (int)($_POST['semana'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $bloque = trim($_POST['bloque_horario'] ?? '');
    $justificacion = trim($_POST['justificacion'] ?? '');

    if (!$inscripcion_id || !$semana || !$fecha) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
    $stmt->execute([$inscripcion_id, $semana, $fecha, $bloque, $bloque]);
    $asis = $stmt->fetch();
    if (!$asis) {
        $stmt = $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado, justificacion) VALUES (?, ?, ?, ?, 'justificado', ?)");
        $stmt->execute([$inscripcion_id, $semana, $fecha, $bloque, $justificacion]);
    } else {
        $stmt = $pdo->prepare("UPDATE asistencias SET estado = 'justificado', justificacion = ? WHERE id = ?");
        $stmt->execute([$justificacion, $asis['id']]);
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'eliminar_justificacion' && $curso_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $inscripcion_id = (int)($_POST['inscripcion_id'] ?? 0);
    $semana = (int)($_POST['semana'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $bloque = trim($_POST['bloque_horario'] ?? '');

    if (!$inscripcion_id || !$semana || !$fecha) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE asistencias SET estado = 'ausente', justificacion = NULL WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
    $stmt->execute([$inscripcion_id, $semana, $fecha, $bloque, $bloque]);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'marcar_tarde' && $curso_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $inscripcion_id = (int)($_POST['inscripcion_id'] ?? 0);
    $semana = (int)($_POST['semana'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $bloque = trim($_POST['bloque_horario'] ?? '');

    if (!$inscripcion_id || !$semana || !$fecha) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
    $stmt->execute([$inscripcion_id, $semana, $fecha, $bloque, $bloque]);
    $asis = $stmt->fetch();
    if ($asis) {
        $pdo->prepare("UPDATE asistencias SET estado = 'tarde', hora_escaneo = IFNULL(hora_escaneo, CURTIME()) WHERE id = ?")->execute([$asis['id']]);
    } else {
        $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado, hora_escaneo) VALUES (?, ?, ?, ?, 'tarde', CURTIME())")->execute([$inscripcion_id, $semana, $fecha, $bloque]);
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'cambiar_estado' && $curso_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$es_profesor) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Solo profesores']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $inscripcion_id = (int)($_POST['inscripcion_id'] ?? 0);
    $semana = (int)($_POST['semana'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $bloque = trim($_POST['bloque_horario'] ?? '');
    $nuevo_estado = trim($_POST['nuevo_estado'] ?? '');

    if (!$inscripcion_id || !$semana || !$fecha || !in_array($nuevo_estado, ['presente', 'tarde', 'ausente'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, estado, hora_escaneo FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ? AND (bloque_horario = ? OR (bloque_horario = '' AND ? = ''))");
    $stmt->execute([$inscripcion_id, $semana, $fecha, $bloque, $bloque]);
    $asis = $stmt->fetch();
    $estado_anterior = $asis ? $asis['estado'] : 'ausente';

    if ($nuevo_estado === 'ausente') {
        if ($asis) {
            $pdo->prepare("UPDATE asistencias SET estado = 'ausente', hora_escaneo = NULL, justificacion = NULL WHERE id = ?")->execute([$asis['id']]);
        } else {
            $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado, hora_escaneo, justificacion) VALUES (?, ?, ?, ?, 'ausente', NULL, NULL)")->execute([$inscripcion_id, $semana, $fecha, $bloque]);
        }
    } else {
        if ($asis) {
            $pdo->prepare("UPDATE asistencias SET estado = ?, hora_escaneo = IFNULL(hora_escaneo, CURTIME()), justificacion = NULL WHERE id = ?")->execute([$nuevo_estado, $asis['id']]);
        } else {
            $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, estado, hora_escaneo, justificacion) VALUES (?, ?, ?, ?, ?, CURTIME(), NULL)")->execute([$inscripcion_id, $semana, $fecha, $bloque, $nuevo_estado]);
        }
    }

    $stmt_est = $pdo->prepare("SELECT u.nombre_completo, u.cedula FROM inscripciones i JOIN usuarios u ON u.id = i.estudiante_id WHERE i.id = ?");
    $stmt_est->execute([$inscripcion_id]);
    $est_info = $stmt_est->fetch();
    $nombre_est = $est_info ? $est_info['nombre_completo'] . ' (' . $est_info['cedula'] . ')' : 'Inscripción ' . $inscripcion_id;

    registrarAuditoria(
        $pdo,
        $profesor_id,
        "Cambio manual de asistencia",
        "Estudiante: $nombre_est | Semana $semana | Fecha: $fecha " . ($bloque ? "[$bloque] " : "") . "| Estado: $estado_anterior -> $nuevo_estado"
    );

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
