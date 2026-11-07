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
    $stmt = $pdo->prepare("SELECT id, semana, fecha FROM sesiones_qr WHERE curso_id = ? AND activo = 1 AND CONCAT(fecha, ' ', hora_expiracion) <= NOW()");
    $stmt->execute([$curso_id]);
    $expiradas = $stmt->fetchAll();
    $marcados = 0;
    foreach ($expiradas as $sesion) {
        $pdo->prepare("UPDATE sesiones_qr SET activo = 0 WHERE id = ?")->execute([$sesion['id']]);
        $insc = $pdo->prepare("SELECT i.id FROM inscripciones i WHERE i.curso_id = ? AND i.periodo = ?");
        $insc->execute([$curso_id, $periodo]);
        foreach ($insc->fetchAll() as $i) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ?");
            $chk->execute([$i['id'], $sesion['semana'], $sesion['fecha']]);
            if ($chk->fetchColumn() == 0) {
                $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, estado) VALUES (?, ?, ?, 'ausente')")->execute([$i['id'], $sesion['semana'], $sesion['fecha']]);
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
               COUNT(DISTINCT CASE WHEN a.estado = 'presente' THEN i.id END) as completas,
               COUNT(DISTINCT a.fecha) as sesiones
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
        $row = [
            'semana' => (int)$s['semana'],
            'contador' => (int)$s['contador'],
            'completas' => (int)$s['completas'],
            'total_inscritos' => $total_inscritos,
            'sesiones' => (int)$s['sesiones'],
        ];
        if ($es_estudiante) {
            $stmt3 = $pdo->prepare("SELECT COUNT(*) FROM asistencias a JOIN inscripciones i ON i.id = a.inscripcion_id WHERE i.estudiante_id = ? AND i.curso_id = ? AND i.periodo = ? AND a.semana = ? AND a.estado = 'presente'");
            $stmt3->execute([$_SESSION['usuario_id'], $curso_id, $periodo, $s['semana']]);
            $row['mis_asistencias'] = (int)$stmt3->fetchColumn();
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

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND profesor_id = ? AND activo = 1");
    $stmt->execute([$curso_id, $profesor_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Curso no válido']);
        exit;
    }

    $periodo = obtenerPeriodoActual();
    limpiarSesionesExpiradas($pdo, $curso_id, $periodo);

    $stmt = $pdo->prepare("SELECT DISTINCT sq.fecha FROM sesiones_qr sq WHERE sq.curso_id = ? AND sq.semana = ? ORDER BY sq.fecha");
    $stmt->execute([$curso_id, $semana]);
    $fechas_qr = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($fechas_qr)) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'sesiones' => []]);
        exit;
    }

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
    foreach ($fechas_qr as $fecha) {
        $stmt = $pdo->prepare("SELECT inscripcion_id, estado, hora_escaneo, justificacion FROM asistencias WHERE semana = ? AND fecha = ?");
        $stmt->execute([$semana, $fecha]);
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
            'estudiantes' => $lista,
        ];
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'sesiones' => $sesiones_list]);
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
    $justificacion = trim($_POST['justificacion'] ?? '');

    if (!$inscripcion_id || !$semana || !$fecha) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE inscripcion_id = ? AND semana = ? AND fecha = ?");
    $stmt->execute([$inscripcion_id, $semana, $fecha]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO asistencias (inscripcion_id, semana, fecha, estado, justificacion) VALUES (?, ?, ?, 'justificado', ?)");
        $stmt->execute([$inscripcion_id, $semana, $fecha, $justificacion]);
    } else {
        $stmt = $pdo->prepare("UPDATE asistencias SET estado = 'justificado', justificacion = ? WHERE inscripcion_id = ? AND semana = ? AND fecha = ?");
        $stmt->execute([$justificacion, $inscripcion_id, $semana, $fecha]);
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
