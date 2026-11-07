<?php
require_once __DIR__ . "/../includes/academico.php";
require_once __DIR__ . "/includes/header.php";

function findOrCreateCurso($pdo, $codigo, $seccion) {
    if (empty($seccion)) {
        $stmt = $pdo->prepare("SELECT id FROM cursos WHERE codigo = ? AND activo = 1");
        $stmt->execute([$codigo]);
        return $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE codigo = ? AND seccion = ? AND activo = 1");
    $stmt->execute([$codigo, $seccion]);
    $id = $stmt->fetchColumn();
    if ($id) return $id;

    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE codigo = ? AND (seccion IS NULL OR seccion = '') AND activo = 1 LIMIT 1");
    $stmt->execute([$codigo]);
    $existing_id = $stmt->fetchColumn();
    if ($existing_id) {
        $stmt = $pdo->prepare("UPDATE cursos SET seccion = ? WHERE id = ?");
        $stmt->execute([$seccion, $existing_id]);
        return $existing_id;
    }

    $stmt = $pdo->prepare("SELECT nombre, carrera_id, profesor_id, aula, horario, trayecto, creditos, tipo FROM cursos WHERE codigo = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$codigo]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$template) return null;

    $stmt = $pdo->prepare("INSERT INTO cursos (nombre, codigo, carrera_id, profesor_id, aula, seccion, horario, trayecto, creditos, tipo, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([
        $template['nombre'], $codigo, $template['carrera_id'], $template['profesor_id'],
        $template['aula'], $seccion, $template['horario'],
        $template['trayecto'], $template['creditos'], $template['tipo']
    ]);
    return $pdo->lastInsertId();
}

// --- PROCESAR CAMBIO DE PERÍODO ACTIVO ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {
    if ($_POST["accion"] === "cambiar_periodo") {
        $nuevo_periodo = limpiarInput($_POST["periodo_activo"] ?? "");
        if ($nuevo_periodo) {
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'periodo_activo'");
            $stmt->execute([$nuevo_periodo]);
            registrarAuditoria($pdo, $_SESSION["usuario_id"], "Cambiar período activo", "Nuevo: $nuevo_periodo");
            header("Location: cargar_inscripciones.php?msg=periodo_ok");
            exit();
        }
    }
}

$mensaje = $_GET["msg"] ?? "";

// --- PROCESAR CARGA CSV ---
$resultado_ins = null;
$resultado_notas = null;
$errores_ins = [];
$errores_notas = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo_csv"])) {
    $archivo = $_FILES["archivo_csv"];
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        $resultado_ins = ["ok" => false, "error" => "Error al subir el archivo. Código: " . $archivo["error"]];
    } elseif (strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION)) !== "csv") {
        $resultado_ins = ["ok" => false, "error" => "Solo se permiten archivos .csv"];
    } else {
        $handle = fopen($archivo["tmp_name"], "r");
        if (!$handle) {
            $resultado_ins = ["ok" => false, "error" => "No se pudo abrir el archivo"];
        } else {
            $insertados = 0;
            $actualizados = 0;
            $errores_ins = [];
            $fila = 0;

            $col_cedula = 0;
            $col_codigo = false;
            $col_seccion = false;
            $col_periodo = 2;

            $stmt_check_est = $pdo->prepare("SELECT id, carrera_id FROM usuarios WHERE cedula = ? AND rol = 'estudiante' AND activo = 1");
            $stmt_insert = $pdo->prepare(
                "INSERT INTO inscripciones (estudiante_id, curso_id, periodo, estatus) VALUES (?, ?, ?, 'Activa') ON DUPLICATE KEY UPDATE estatus = 'Activa'"
            );

            while (($data = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
                $fila++;
                if ($fila === 1) {
                    $header_lower = array_map('strtolower', array_map('trim', $data));
                    $header_lower[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header_lower[0]);
                    $col_cedula = array_search('cedula', $header_lower);
                    if ($col_cedula === false) $col_cedula = array_search('cédula', $header_lower);
                    if ($col_cedula === false) $col_cedula = array_search('ci', $header_lower);
                    if ($col_cedula === false) $col_cedula = array_search('documento', $header_lower);
                    $col_codigo = array_search('codigo', $header_lower);
                    if ($col_codigo === false) $col_codigo = array_search('código', $header_lower);
                    if ($col_codigo === false) $col_codigo = array_search('materia', $header_lower);
                    if ($col_codigo === false) $col_codigo = array_search('curso', $header_lower);
                    if ($col_codigo === false) $col_codigo = array_search('codigo_materia', $header_lower);
                    $col_seccion = array_search('seccion', $header_lower);
                    if ($col_seccion === false) $col_seccion = array_search('sección', $header_lower);
                    $col_periodo = array_search('periodo', $header_lower);
                    if ($col_periodo === false) $col_periodo = 2;
                    continue;
                }

                if ($col_cedula === false) {
                    $errores_ins[] = "Fila $fila: no se pudo detectar columna de cédula en el encabezado";
                    break;
                }

                $raw_cedula = $data[$col_cedula] ?? '';
                $raw_seccion = $col_seccion !== false ? trim($data[$col_seccion] ?? '') : '';
                $raw_periodo = $col_periodo !== false ? ($data[$col_periodo] ?? '') : '';

                $cedula = 'V' . preg_replace('/[^0-9]/', '', $raw_cedula);

                if (empty($cedula)) {
                    $errores_ins[] = "Fila $fila: cédula vacía";
                    continue;
                }

                $stmt_check_est->execute([$cedula]);
                $row = $stmt_check_est->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $errores_ins[] = "Fila $fila: estudiante no encontrado (cédula: $cedula)";
                    continue;
                }
                $est_id = $row['id'];

                $periodo = $raw_periodo ? strtoupper(trim($raw_periodo)) : obtenerPeriodoActual();

                if ($col_codigo !== false) {
                    $raw_codigo = $data[$col_codigo] ?? '';
                    $codigo = strtoupper(trim($raw_codigo));

                    if (empty($codigo)) {
                        $errores_ins[] = "Fila $fila: código de materia vacío";
                        continue;
                    }

                    $cur_id = findOrCreateCurso($pdo, $codigo, $raw_seccion);
                    if (!$cur_id) {
                        $errores_ins[] = "Fila $fila: curso no encontrado (código: $codigo" . ($raw_seccion ? ", seccion: $raw_seccion" : "") . ")";
                        continue;
                    }

                    try {
                        $stmt_insert->execute([$est_id, $cur_id, $periodo]);
                        if ($stmt_insert->rowCount() === 1) $insertados++;
                        else $actualizados++;
                    } catch (Exception $e) {
                        $errores_ins[] = "Fila $fila: error BD — " . $e->getMessage();
                    }
                } else {
                    if (empty($raw_seccion)) {
                        $errores_ins[] = "Fila $fila: sección requerida para inscripción automática";
                        continue;
                    }

                    $carrera_id = $row['carrera_id'];
                    if (!$carrera_id) {
                        $errores_ins[] = "Fila $fila: el estudiante $cedula no tiene carrera asignada";
                        continue;
                    }

        $stmt_approved = $pdo->prepare("SELECT DISTINCT i.curso_id FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
        $stmt_approved_codigos = $pdo->prepare("SELECT DISTINCT c.codigo FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
        $stmt_prereq_full = $pdo->prepare("SELECT requiere_codigo, nivel_requerido, creditos_minimos FROM prerrequisitos WHERE curso_id = ?");
        $stmt_niv_total = $pdo->prepare("SELECT COUNT(*) FROM cursos WHERE carrera_id = ? AND trayecto = ? AND activo = 1");
        $stmt_niv_aprob = $pdo->prepare("SELECT COUNT(DISTINCT c.id) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND c.carrera_id = ? AND c.trayecto = ? AND cal.estatus = 'Aprobada'");
        $stmt_cred_total = $pdo->prepare("SELECT COALESCE(SUM(c.creditos), 0) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
                    $stmt_approved->execute([$est_id]);
                    $approved_ids = $stmt_approved->fetchAll(PDO::FETCH_COLUMN);
                    $approved_set = array_flip($approved_ids);

                    $stmt_approved_codigos->execute([$est_id]);
                    $approved_codigos = array_flip($stmt_approved_codigos->fetchAll(PDO::FETCH_COLUMN));

                    $stmt_cred_total->execute([$est_id]);
                    $total_creditos = (int)$stmt_cred_total->fetchColumn();

                    $niveles_ok_est = [];
                    $stmt_niv_todos = $pdo->prepare("SELECT DISTINCT trayecto FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY trayecto");
                    $stmt_niv_todos->execute([$carrera_id]);
                    foreach ($stmt_niv_todos->fetchAll(PDO::FETCH_COLUMN) as $niv) {
                        $stmt_niv_total->execute([$carrera_id, $niv]);
                        $total_n = (int)$stmt_niv_total->fetchColumn();
                        $stmt_niv_aprob->execute([$est_id, $carrera_id, $niv]);
                        $aprob_n = (int)$stmt_niv_aprob->fetchColumn();
                        $niveles_ok_est[$niv] = ($aprob_n >= $total_n);
                    }

                    $stmt_cursos = $pdo->prepare("SELECT id, codigo, trayecto FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY trayecto, id");
                    $stmt_cursos->execute([$carrera_id]);
                    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

                    $cursos_por_t = [];
                    $max_t = 0;
                    $codigos_vistos = [];
                    foreach ($cursos as $c) {
                        $key = $c['trayecto'] . '_' . $c['codigo'];
                        if (isset($codigos_vistos[$key])) continue;
                        $codigos_vistos[$key] = true;
                        $cursos_por_t[$c['trayecto']][] = $c;
                        if ($c['trayecto'] > $max_t) $max_t = $c['trayecto'];
                    }

                    $trayecto_actual = 1;
                    for ($t = 1; $t <= $max_t; $t++) {
                        if (!isset($cursos_por_t[$t])) break;
                        $todos_ok = true;
                        foreach ($cursos_por_t[$t] as $c) {
                            if (!isset($approved_set[$c['id']])) { $todos_ok = false; break; }
                        }
                        if ($todos_ok) $trayecto_actual = $t + 1;
                        else break;
                    }

                    if ($trayecto_actual > $max_t) continue;

                    foreach ($cursos_por_t[$trayecto_actual] as $c) {
                        $stmt_prereq_full->execute([$c['id']]);
                        $reqs = $stmt_prereq_full->fetchAll();
                        $ok = true;
                        foreach ($reqs as $pr) {
                            if ($pr['requiere_codigo'] && !isset($approved_codigos[$pr['requiere_codigo']])) { $ok = false; break; }
                            if ($pr['nivel_requerido'] && !($niveles_ok_est[(int)$pr['nivel_requerido']] ?? false)) { $ok = false; break; }
                            if ($pr['creditos_minimos'] && $total_creditos < (int)$pr['creditos_minimos']) { $ok = false; break; }
                        }
                        if (!$ok) continue;

                        $cur_id = findOrCreateCurso($pdo, $c['codigo'], $raw_seccion);
                        if (!$cur_id) {
                            $errores_ins[] = "Fila $fila: no se pudo crear/encontrar curso {$c['codigo']} para $cedula";
                            continue;
                        }
                        try {
                            $stmt_insert->execute([$est_id, $cur_id, $periodo]);
                            if ($stmt_insert->rowCount() === 1) $insertados++;
                            else $actualizados++;
                        } catch (Exception $e) {
                            $errores_ins[] = "Fila $fila: error BD — " . $e->getMessage();
                        }
                    }
                }
            }
            fclose($handle);

            $total_filas = $fila - 1;
            $resultado_ins = [
                "ok" => true,
                "total" => $total_filas,
                "insertados" => $insertados,
                "actualizados" => $actualizados,
                "errores" => count($errores_ins),
            ];

            if ($insertados > 0 || $actualizados > 0) {
                registrarAuditoria($pdo, $_SESSION["usuario_id"], "Cargar inscripciones CSV", "Insertados: $insertados, Actualizados: $actualizados, Fallos: " . count($errores_ins));
            }
        }
    }
}

// --- PROCESAR CARGA NOTAS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo_notas"])) {
    $archivo = $_FILES["archivo_notas"];
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        $resultado_notas = ["ok" => false, "error" => "Error al subir el archivo. Código: " . $archivo["error"]];
    } elseif (strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION)) !== "csv") {
        $resultado_notas = ["ok" => false, "error" => "Solo se permiten archivos .csv"];
    } else {
        $handle = fopen($archivo["tmp_name"], "r");
        if (!$handle) {
            $resultado_notas = ["ok" => false, "error" => "No se pudo abrir el archivo"];
        } else {
            $procesadas = 0;
            $errores_notas = [];
            $fila = 0;
            $cols = [];

            $stmt_check_est = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ? AND rol = 'estudiante' AND activo = 1");
            $stmt_ins = $pdo->prepare(
                "INSERT INTO inscripciones (estudiante_id, curso_id, periodo, estatus) VALUES (?, ?, ?, 'Activa') ON DUPLICATE KEY UPDATE estatus = 'Activa'"
            );
            $stmt_find_ins = $pdo->prepare("SELECT id FROM inscripciones WHERE estudiante_id = ? AND curso_id = ? AND periodo = ?");
            $stmt_calf = $pdo->prepare(
                "INSERT INTO calificaciones (inscripcion_id, nota, estatus) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nota = VALUES(nota), estatus = VALUES(estatus)"
            );

            while (($data = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
                $fila++;
                if ($fila === 1) {
                    $h = array_map('strtolower', array_map('trim', $data));
                    $h[0] = preg_replace('/^\xEF\xBB\xBF/', '', $h[0]);
                    $cols['cedula'] = array_search('cedula', $h);
                    if ($cols['cedula'] === false) $cols['cedula'] = array_search('cédula', $h);
                    if ($cols['cedula'] === false) $cols['cedula'] = array_search('ci', $h);
                    $cols['codigo'] = array_search('codigo_materia', $h);
                    if ($cols['codigo'] === false) $cols['codigo'] = array_search('codigo', $h);
                    if ($cols['codigo'] === false) $cols['codigo'] = array_search('código', $h);
                    if ($cols['codigo'] === false) $cols['codigo'] = array_search('materia', $h);
                    $cols['periodo'] = array_search('periodo', $h);
                    $cols['nota'] = array_search('nota', $h);
                    $cols['seccion'] = array_search('seccion', $h);
                    if ($cols['seccion'] === false) $cols['seccion'] = array_search('sección', $h);
                    if ($cols['cedula'] === false) { $errores_notas[] = "Encabezado: columna 'cedula' no encontrada"; break; }
                    if ($cols['codigo'] === false) { $errores_notas[] = "Encabezado: columna 'codigo_materia' no encontrada"; break; }
                    if ($cols['nota'] === false) { $errores_notas[] = "Encabezado: columna 'nota' no encontrada"; break; }
                    continue;
                }

                $cedula = 'V' . preg_replace('/[^0-9]/', '', $data[$cols['cedula']] ?? '');
                $codigo = strtoupper(trim($data[$cols['codigo']] ?? ''));
                $raw_seccion = ($cols['seccion'] !== false) ? trim($data[$cols['seccion']] ?? '') : '';
                $raw_periodo = ($cols['periodo'] !== false) ? trim($data[$cols['periodo']] ?? '') : '';
                $raw_nota = trim($data[$cols['nota']] ?? '');

                if (empty($cedula) || empty($codigo) || $raw_nota === '') {
                    $errores_notas[] = "Fila $fila: cédula, código o nota vacío";
                    continue;
                }

                $stmt_check_est->execute([$cedula]);
                $est_id = $stmt_check_est->fetchColumn();
                if (!$est_id) { $errores_notas[] = "Fila $fila: estudiante no encontrado ($cedula)"; continue; }

                if (!empty($raw_seccion)) {
                    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE codigo = ? AND seccion = ? AND activo = 1");
                    $stmt->execute([$codigo, $raw_seccion]);
                    $cur_id = $stmt->fetchColumn();
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE codigo = ? AND activo = 1");
                    $stmt->execute([$codigo]);
                    $cur_id = $stmt->fetchColumn();
                }
                if (!$cur_id) { $errores_notas[] = "Fila $fila: curso no encontrado ($codigo" . ($raw_seccion ? ", seccion: $raw_seccion" : "") . ")"; continue; }

                $periodo = $raw_periodo ? strtoupper($raw_periodo) : obtenerPeriodoActual();

                try {
                    $stmt_ins->execute([$est_id, $cur_id, $periodo]);

                    $insc_id = $pdo->lastInsertId();
                    if ($insc_id == 0) {
                        $stmt_find_ins->execute([$est_id, $cur_id, $periodo]);
                        $insc_id = $stmt_find_ins->fetchColumn();
                    }

                    $nota = floatval(str_replace(',', '.', $raw_nota));
                    if ($nota >= 50) $estatus = 'Aprobada';
                    elseif ($nota >= 37) $estatus = 'Remedial';
                    else $estatus = 'Reprobada';

                    $stmt_calf->execute([$insc_id, $nota, $estatus]);
                    $procesadas++;
                } catch (Exception $e) {
                    $errores_notas[] = "Fila $fila: " . $e->getMessage();
                }
            }
            fclose($handle);

            $resultado_notas = [
                "ok" => true,
                "total" => $fila - 1,
                "procesadas" => $procesadas,
                "errores" => count($errores_notas),
            ];

            if ($procesadas > 0) {
                registrarAuditoria($pdo, $_SESSION["usuario_id"], "Cargar notas CSV", "Procesadas: $procesadas, Fallos: " . count($errores_notas));
            }
        }
    }
}

$periodo_activo = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = 'periodo_activo'");
$periodo_activo->execute();
$periodo_activo_valor = $periodo_activo->fetchColumn() ?: obtenerPeriodoActual();

$periodos_disponibles = $pdo->query("SELECT DISTINCT periodo FROM inscripciones UNION SELECT '2026-I' UNION SELECT '2026-II' UNION SELECT '2026-III' UNION SELECT '2027-I' UNION SELECT '2027-II' UNION SELECT '2027-III' ORDER BY periodo DESC")->fetchAll(PDO::FETCH_COLUMN);

// --- SINCRONIZAR INSCRIPCIONES CON PÉNSUM ---
$sync_preview = null;
$sync_resultado = null;
$sync_periodo_usado = $periodo_activo_valor;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {
    $sync_periodo_usado = limpiarInput($_POST["periodo_sync"] ?? $periodo_activo_valor);
    $confirmar = ($_POST["accion"] === "sync_confirm");

    if ($_POST["accion"] === "sync_preview" || $confirmar) {
        $estudiantes = $pdo->query("SELECT id, cedula, nombre_completo, carrera_id FROM usuarios WHERE rol = 'estudiante' AND activo = 1 ORDER BY carrera_id, nombre_completo")->fetchAll();

        $detalle_por_carrera = [];
        $total_est_ids = [];
        $total_insc = 0;
        $insertados_sync = 0;
        $errores_sync = [];

        $stmt_check_insc = $pdo->prepare("SELECT id FROM inscripciones WHERE estudiante_id = ? AND curso_id = ? AND periodo = ?");
        $stmt_insert_sync = $pdo->prepare("INSERT INTO inscripciones (estudiante_id, curso_id, periodo, estatus) VALUES (?, ?, ?, 'Activa') ON DUPLICATE KEY UPDATE estatus = 'Activa'");
        $stmt_cursos_carrera = $pdo->prepare("SELECT id, codigo, nombre, trayecto FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY trayecto, id");
        $stmt_approved = $pdo->prepare("SELECT DISTINCT i.curso_id FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
        $stmt_approved_codigos = $pdo->prepare("SELECT DISTINCT c.codigo FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");
        $stmt_carrera_nombre = $pdo->prepare("SELECT nombre FROM carreras WHERE id = ?");
        $stmt_es_nuevo = $pdo->prepare("SELECT es_nuevo_ingreso FROM usuarios WHERE id = ?");
        $stmt_prereq_sync = $pdo->prepare("SELECT requiere_codigo, nivel_requerido, creditos_minimos FROM prerrequisitos WHERE curso_id = ?");
        $stmt_niv_total = $pdo->prepare("SELECT COUNT(*) FROM cursos WHERE carrera_id = ? AND trayecto = ? AND activo = 1");
        $stmt_niv_aprob = $pdo->prepare("SELECT COUNT(DISTINCT c.id) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND c.carrera_id = ? AND c.trayecto = ? AND cal.estatus = 'Aprobada'");
        $stmt_cred_total = $pdo->prepare("SELECT COALESCE(SUM(c.creditos), 0) FROM calificaciones cal JOIN inscripciones i ON i.id = cal.inscripcion_id JOIN cursos c ON c.id = i.curso_id WHERE i.estudiante_id = ? AND cal.estatus = 'Aprobada'");

        foreach ($estudiantes as $est) {
            $stmt_es_nuevo->execute([$est['id']]);
            if (!$stmt_es_nuevo->fetchColumn()) continue;

            $stmt_cursos_carrera->execute([$est['carrera_id']]);
            $cursos = $stmt_cursos_carrera->fetchAll();
            if (empty($cursos)) continue;

            $stmt_approved->execute([$est['id']]);
            $approved_ids = $stmt_approved->fetchAll(PDO::FETCH_COLUMN);
            $approved_set = array_flip($approved_ids);

            $stmt_approved_codigos->execute([$est['id']]);
            $approved_codigos = array_flip($stmt_approved_codigos->fetchAll(PDO::FETCH_COLUMN));

            $stmt_cred_total->execute([$est['id']]);
            $total_creditos = (int)$stmt_cred_total->fetchColumn();

            $niveles_ok_sync = [];
            $carrera_id_for_sync = $est['carrera_id'];
            $stmt_niv_todos = $pdo->prepare("SELECT DISTINCT trayecto FROM cursos WHERE carrera_id = ? AND activo = 1 ORDER BY trayecto");
            $stmt_niv_todos->execute([$carrera_id_for_sync]);
            foreach ($stmt_niv_todos->fetchAll(PDO::FETCH_COLUMN) as $niv) {
                $stmt_niv_total->execute([$carrera_id_for_sync, $niv]);
                $total_n = (int)$stmt_niv_total->fetchColumn();
                $stmt_niv_aprob->execute([$est['id'], $carrera_id_for_sync, $niv]);
                $aprob_n = (int)$stmt_niv_aprob->fetchColumn();
                $niveles_ok_sync[$niv] = ($aprob_n >= $total_n);
            }

            $cursos_por_t = [];
            $max_t = 0;
            foreach ($cursos as $c) {
                $cursos_por_t[$c['trayecto']][] = $c;
                if ($c['trayecto'] > $max_t) $max_t = $c['trayecto'];
            }

            $trayecto_actual = 1;
            for ($t = 1; $t <= $max_t; $t++) {
                $todos_ok = true;
                foreach ($cursos_por_t[$t] as $c) {
                    if (!isset($approved_set[$c['id']])) { $todos_ok = false; break; }
                }
                if ($todos_ok) $trayecto_actual = $t + 1;
                else break;
            }

            if ($trayecto_actual > $max_t) continue;

            $pendientes = [];
            foreach ($cursos_por_t[$trayecto_actual] as $c) {
                $stmt_check_insc->execute([$est['id'], $c['id'], $sync_periodo_usado]);
                if ($stmt_check_insc->fetchColumn()) continue;

                $stmt_prereq_sync->execute([$c['id']]);
                $reqs = $stmt_prereq_sync->fetchAll();
                $ok = true;
                foreach ($reqs as $pr) {
                    if ($pr['requiere_codigo'] && !isset($approved_codigos[$pr['requiere_codigo']])) { $ok = false; break; }
                    if ($pr['nivel_requerido'] && !($niveles_ok_sync[(int)$pr['nivel_requerido']] ?? false)) { $ok = false; break; }
                    if ($pr['creditos_minimos'] && $total_creditos < (int)$pr['creditos_minimos']) { $ok = false; break; }
                }
                if ($ok) {
                    $pendientes[] = $c;
                }
            }
            if (empty($pendientes)) continue;

            $total_est_ids[] = $est['id'];
            $total_insc += count($pendientes);

            $found = false;
            foreach ($detalle_por_carrera as &$dc) {
                if ($dc['carrera_id'] == $est['carrera_id']) {
                    $dc['estudiantes'][] = $est;
                    $dc['total_inscripciones'] += count($pendientes);
                    $dc['trayectos'][$trayecto_actual] = true;
                    $found = true;
                    break;
                }
            }
            unset($dc);
            if (!$found) {
                $stmt_carrera_nombre->execute([$est['carrera_id']]);
                $detalle_por_carrera[] = [
                    'carrera_id' => $est['carrera_id'],
                    'carrera_nombre' => $stmt_carrera_nombre->fetchColumn(),
                    'estudiantes' => [$est],
                    'total_inscripciones' => count($pendientes),
                    'trayectos' => [$trayecto_actual => true],
                ];
            }

            if ($confirmar) {
                foreach ($pendientes as $c) {
                    try {
                        $stmt_insert_sync->execute([$est['id'], $c['id'], $sync_periodo_usado]);
                        $insertados_sync++;
                    } catch (Exception $e) {
                        $errores_sync[] = "{$est['nombre_completo']} / {$c['codigo']}: " . $e->getMessage();
                    }
                }
                $pdo->prepare("UPDATE usuarios SET es_nuevo_ingreso = 0 WHERE id = ?")->execute([$est['id']]);
            }
        }

        if ($confirmar) {
            $sync_resultado = ["ok" => true, "insertados" => $insertados_sync, "errores" => $errores_sync];
            if ($insertados_sync > 0) {
                registrarAuditoria($pdo, $_SESSION["usuario_id"], "Sincronizar inscripciones con pénsum", "Período: $sync_periodo_usado, Inscripciones generadas: $insertados_sync");
            }
        }

        $sync_preview = [
            "periodo" => $sync_periodo_usado,
            "detalle" => $detalle_por_carrera,
            "total_estudiantes" => count(array_unique($total_est_ids)),
            "total_inscripciones" => $total_insc,
        ];
    }
}


?>
<h2 style="color:var(--primary);margin-bottom:20px">📥 Carga de Datos</h2>

<p style="font-size:13px;color:#666;margin-bottom:20px">
    Inscripciones y notas en un solo lugar.
    <a href="seed/ejemplo_inscripciones.csv" download style="color:var(--primary)">Descargar ejemplo inscripciones</a> ·
    <a href="seed/ejemplo_notas.csv" download style="color:var(--primary)">Descargar ejemplo notas</a>
</p>

<?php if ($mensaje === "periodo_ok"): ?>
<div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">✓ Período activo actualizado.</div>
<?php endif; ?>

<?php if ($resultado_ins): ?>
    <?php if ($resultado_ins["ok"]): ?>
    <div style="background:#d4edda;color:#155724;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        <strong>✓ Inscripciones cargadas</strong><br>
        Procesadas: <strong><?= $resultado_ins["total"] ?></strong> · Nuevas: <strong><?= $resultado_ins["insertados"] ?></strong> · Actualizadas: <strong><?= $resultado_ins["actualizados"] ?></strong> · Errores: <strong><?= $resultado_ins["errores"] ?></strong>
    </div>
    <?php else: ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">
        <strong>✗ Error inscripciones:</strong> <?= htmlspecialchars($resultado_ins["error"]) ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($resultado_notas): ?>
    <?php if ($resultado_notas["ok"]): ?>
    <div style="background:#d4edda;color:#155724;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        <strong>✓ Notas cargadas</strong><br>
        Procesadas: <strong><?= $resultado_notas["total"] ?></strong> · Registradas: <strong><?= $resultado_notas["procesadas"] ?></strong> · Errores: <strong><?= $resultado_notas["errores"] ?></strong>
    </div>
    <?php else: ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">
        <strong>✗ Error notas:</strong> <?= htmlspecialchars($resultado_notas["error"]) ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($errores_ins)): ?>
<div style="background:#fff3cd;color:#856404;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #ffeeba;">
    <strong>Errores en inscripciones:</strong>
    <ul style="margin:8px 0 0;font-size:12px;max-height:150px;overflow-y:auto;">
        <?php foreach ($errores_ins as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if (!empty($errores_notas)): ?>
<div style="background:#fff3cd;color:#856404;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #ffeeba;">
    <strong>Errores en notas:</strong>
    <ul style="margin:8px 0 0;font-size:12px;max-height:150px;overflow-y:auto;">
        <?php foreach ($errores_notas as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Período Activo -->
<div class="card" style="margin-bottom:20px">
    <h3 style="color:var(--primary);margin-bottom:15px">Período Activo</h3>
    <p style="font-size:13px;color:#666;margin-bottom:10px">
        El período activo determina qué materias ven los estudiantes en su inicio.
        <?php if ($periodo_activo_valor): ?>
        Actual: <strong><?= htmlspecialchars($periodo_activo_valor) ?></strong>
        <?php endif; ?>
    </p>
    <form method="POST" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="accion" value="cambiar_periodo" />
        <select name="periodo_activo" required style="flex:1;min-width:150px">
            <?php foreach ($periodos_disponibles as $p): ?>
            <option value="<?= $p ?>" <?= $p === $periodo_activo_valor ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Establecer Período</button>
    </form>
</div>

<!-- Carga Inscripciones -->
<div class="card" style="margin-bottom:20px">
    <h3 style="color:var(--primary);margin-bottom:10px">📋 Inscripciones</h3>
    <p style="font-size:12px;color:#666;margin-bottom:8px">Columnas: <code>cedula, codigo_materia, periodo</code> (periodo opcional). Se importa <strong>cada período</strong>.</p>
    <form method="POST" action="" enctype="multipart/form-data">
        <div style="margin-bottom:10px">
            <input type="file" name="archivo_csv" accept=".csv" required style="display:block" />
        </div>
        <button type="submit" class="btn btn-primary" style="font-size:13px">Cargar Inscripciones</button>
    </form>
    <hr style="margin:15px 0;border-color:var(--border-color)">
    <p style="font-size:11px;color:#999">
        <strong>Ejemplo:</strong><br>
        <code>cedula,codigo_materia,periodo<br>V3010000001,MAT1143,2026-II</code>
    </p>
</div>

<!-- Carga Notas -->
<div class="card" style="margin-bottom:20px">
    <h3 style="color:var(--primary);margin-bottom:10px">📊 Notas</h3>
    <p style="font-size:12px;color:#666;margin-bottom:8px">Columnas: <code>cedula, codigo_materia, periodo, nota</code>. Se importa <strong>constantemente</strong> (cortes, parciales, finales). Si no existe la inscripción, se crea automáticamente.</p>
    <form method="POST" action="" enctype="multipart/form-data">
        <div style="margin-bottom:10px">
            <input type="file" name="archivo_notas" accept=".csv" required style="display:block" />
        </div>
        <button type="submit" class="btn btn-primary" style="font-size:13px">Cargar Notas</button>
    </form>
    <hr style="margin:15px 0;border-color:var(--border-color)">
    <p style="font-size:11px;color:#999">
        <strong>Ejemplo:</strong><br>
        <code>cedula,codigo_materia,periodo,nota<br>V3010000001,MAT1143,2026-II,85</code><br>
        <span style="margin-top:3px;display:block">Nota 0-100. Estatus automático: ≥50 Aprobada, 37-49 Remedial, &lt;37 Reprobada.</span>
    </p>
</div>

<!-- Sincronizar Inscripciones con Pénsum -->
<div class="card" style="margin-bottom:20px">
    <h3 style="color:var(--primary);margin-bottom:10px">🔄 Avance automático de trayecto</h3>
    <p style="font-size:12px;color:#666;margin-bottom:8px">
        Detecta estudiantes marcados como nuevo ingreso que hayan aprobado todas las materias de su trayecto
        actual y genera las inscripciones del trayecto siguiente en el período seleccionado.
    </p>

    <form method="POST" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:15px">
        <input type="hidden" name="accion" value="sync_preview" />
        <label style="font-size:12px;font-weight:bold">Período:</label>
        <select name="periodo_sync" required style="flex:1;min-width:140px;padding:8px;border:1px solid var(--border-color);border-radius:4px">
            <?php foreach ($periodos_disponibles as $p): ?>
            <option value="<?= $p ?>" <?= $p === $periodo_activo_valor ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="font-size:13px">🔍 Calcular necesidades</button>
    </form>

    <?php if ($sync_resultado): ?>
        <?php if ($sync_resultado["ok"]): ?>
        <div style="background:#d4edda;color:#155724;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
            <strong>✓ Sincronización completada</strong><br>
            Nuevas inscripciones generadas: <strong><?= $sync_resultado["insertados"] ?></strong>
            <?php if (!empty($sync_resultado["errores"])): ?>
                <br>Errores: <strong><?= count($sync_resultado["errores"]) ?></strong>
                <ul style="margin:5px 0 0;font-size:11px;max-height:120px;overflow-y:auto">
                <?php foreach ($sync_resultado["errores"] as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($sync_preview && !$sync_resultado): ?>
        <?php if ($sync_preview["total_inscripciones"] > 0): ?>
        <div style="background:#e8f4fd;color:#0c5460;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #bee5eb;">
            <strong>🔍 Vista previa para período <?= htmlspecialchars($sync_preview["periodo"]) ?></strong><br>
            Se detectaron <strong><?= $sync_preview["total_estudiantes"] ?></strong> estudiantes listos para avanzar.
            Se generarán <strong><?= $sync_preview["total_inscripciones"] ?></strong> nuevas inscripciones.
        </div>

        <table style="width:100%;border-collapse:collapse;margin-bottom:15px;font-size:12px">
            <thead>
                <tr style="background:var(--border-color)">
                    <th style="padding:8px;text-align:left">Carrera</th>
                    <th style="padding:8px;text-align:center">Estudiantes</th>
                    <th style="padding:8px;text-align:center">Trayecto(s)</th>
                    <th style="padding:8px;text-align:center">Cursos a inscribir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sync_preview["detalle"] as $dc): ?>
                <tr style="border-bottom:1px solid var(--border-color)">
                    <td style="padding:8px"><?= htmlspecialchars($dc['carrera_nombre']) ?></td>
                    <td style="padding:8px;text-align:center"><?= count($dc['estudiantes']) ?></td>
                    <td style="padding:8px;text-align:center"><?= implode(', ', array_keys($dc['trayectos'])) ?></td>
                    <td style="padding:8px;text-align:center;font-weight:bold"><?= $dc['total_inscripciones'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST" action="" onsubmit="return confirm('¿Confirmar la generación de <?= $sync_preview["total_inscripciones"] ?> inscripciones para <?= $sync_preview["total_estudiantes"] ?> estudiantes en período <?= htmlspecialchars($sync_preview["periodo"]) ?>?')">
            <input type="hidden" name="accion" value="sync_confirm" />
            <input type="hidden" name="periodo_sync" value="<?= htmlspecialchars($sync_preview["periodo"]) ?>" />
            <button type="submit" class="btn btn-primary" style="font-size:13px">✅ Confirmar sincronización</button>
        </form>
        <?php else: ?>
        <div style="background:#e8f4fd;color:#0c5460;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #bee5eb;">
            No se encontraron estudiantes con necesidades de inscripción para el período seleccionado.
            Todos los estudiantes tienen sus cursos al día o ya están inscritos en su trayecto actual.
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>



<?php
require_once __DIR__ . "/includes/footer.php";
?>