<?php
/**
 * Script para poblar la base de datos con datos de prueba realistas para:
 * - Fernando Rodríguez Rada (5to Cuatrimestre / Trayecto 5, Informática, Sección 1D1)
 * - Sus 8 compañeros de clase (cohorte reducida de 24 a 9 estudiantes)
 * - Sus profesores reales en las 8 materias de Trayecto 5
 * - Asistencias de las semanas 1 a 10 con el perfil solicitado.
 */

require_once __DIR__ . '/../includes/config.php';

echo "=== INICIANDO POBLADO DE DATOS (5TO CUATRIMESTRE) ===\n\n";

try {
    $pdo->beginTransaction();

    // -------------------------------------------------------------
    // 1. ELIMINAR USUARIOS 'ALUMNO' Y 'PROFESOR' Y CURSOS DUPLICADOS
    // -------------------------------------------------------------
    echo "1. Limpiando usuarios 'alumno' (id 2) y 'profesor' (id 3)...\n";

    // Cursos duplicados creados para profesor dummy
    $cursos_duplicados = [56, 57, 58, 59];
    $in_duplicados = implode(',', $cursos_duplicados);

    // Borrar asistencias de inscripciones en cursos duplicados o de alumno
    $pdo->exec("
        DELETE a FROM asistencias a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        WHERE i.estudiante_id = 2 OR i.curso_id IN ($in_duplicados)
    ");

    // Borrar calificaciones de inscripciones de alumno o cursos duplicados
    $pdo->exec("
        DELETE c FROM calificaciones c
        JOIN inscripciones i ON c.inscripcion_id = i.id
        WHERE i.estudiante_id = 2 OR i.curso_id IN ($in_duplicados)
    ");

    // Borrar inscripciones de alumno o cursos duplicados
    $pdo->exec("
        DELETE FROM inscripciones 
        WHERE estudiante_id = 2 OR curso_id IN ($in_duplicados)
    ");

    // Borrar sesiones QR de profesor id 3 o cursos duplicados
    $pdo->exec("
        DELETE FROM sesiones_qr 
        WHERE profesor_id = 3 OR curso_id IN ($in_duplicados)
    ");

    // Borrar configuracion_profesor de profesor id 3
    $pdo->exec("DELETE FROM configuracion_profesor WHERE profesor_id = 3");

    // Borrar cursos duplicados
    $pdo->exec("DELETE FROM cursos WHERE id IN ($in_duplicados)");

    // Borrar auditoria de usuarios 2 y 3
    $pdo->exec("DELETE FROM auditoria WHERE usuario_id IN (2, 3)");

    // Borrar usuarios 2 y 3
    $pdo->exec("DELETE FROM usuarios WHERE id IN (2, 3)");

    echo "   -> Usuarios 'alumno' y 'profesor' y cursos duplicados eliminados con éxito.\n";

    // -------------------------------------------------------------
    // 2. LIMPIAR ESTUDIANTES RETIRADOS (usuarios 22 a 33)
    // -------------------------------------------------------------
    echo "2. Limpiando estudiantes que no continúan en 5to cuatrimestre (ids 22 a 33)...\n";
    $pdo->exec("
        DELETE a FROM asistencias a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        WHERE i.estudiante_id >= 22
    ");
    $pdo->exec("DELETE FROM calificaciones WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE estudiante_id >= 22)");
    $pdo->exec("DELETE FROM inscripciones WHERE estudiante_id >= 22");
    $pdo->exec("DELETE FROM auditoria WHERE usuario_id >= 22");
    $pdo->exec("DELETE FROM usuarios WHERE id >= 22");
    echo "   -> Estudiantes excedentes eliminados.\n";

    // -------------------------------------------------------------
    // 3. VERIFICAR / CONFIGURAR LOS 9 ESTUDIANTES ACTIVOS
    // -------------------------------------------------------------
    echo "3. Configurando a Fernando Rodríguez y sus 8 compañeros de clase...\n";

    $estudiantes = [
        ['id' => 4,  'cedula' => 'V31853086', 'nombre' => 'Fernando Rodríguez Rada', 'email' => 'fernando.rodriguez@iutecp.edu.ve'],
        ['id' => 14, 'cedula' => 'V30100101', 'nombre' => 'Miguel Vanegas',          'email' => 'miguel.vanegas@iutecp.edu.ve'],
        ['id' => 15, 'cedula' => 'V30100102', 'nombre' => 'Luca Pedrique',           'email' => 'luca.pedrique@iutecp.edu.ve'],
        ['id' => 16, 'cedula' => 'V30100103', 'nombre' => 'Carlos Mendoza',          'email' => 'carlos.mendoza@iutecp.edu.ve'],
        ['id' => 17, 'cedula' => 'V30100104', 'nombre' => 'Andrea Castillo',         'email' => 'andrea.castillo@iutecp.edu.ve'],
        ['id' => 18, 'cedula' => 'V30100105', 'nombre' => 'Gabriel Morales',         'email' => 'gabriel.morales@iutecp.edu.ve'],
        ['id' => 19, 'cedula' => 'V30100106', 'nombre' => 'Valeria Blanco',          'email' => 'valeria.blanco@iutecp.edu.ve'],
        ['id' => 20, 'cedula' => 'V30100107', 'nombre' => 'Daniel Rivas',            'email' => 'daniel.rivas@iutecp.edu.ve'],
        ['id' => 21, 'cedula' => 'V30100108', 'nombre' => 'Sofía Paredes',           'email' => 'sofia.paredes@iutecp.edu.ve'],
    ];

    $hash_std = password_hash('12345678', PASSWORD_DEFAULT);

    foreach ($estudiantes as $est) {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt_check->execute([$est['id']]);
        if ($stmt_check->fetch()) {
            $pdo->prepare("
                UPDATE usuarios 
                SET cedula = ?, nombre_completo = ?, email = ?, carrera_id = 4, rol = 'estudiante', activo = 1 
                WHERE id = ?
            ")->execute([$est['cedula'], $est['nombre'], $est['email'], $est['id']]);
        } else {
            $pdo->prepare("
                INSERT INTO usuarios (id, cedula, username, nombre_completo, email, carrera_id, rol, password_hash, password_temporal, activo)
                VALUES (?, ?, ?, ?, ?, 4, 'estudiante', ?, 0, 1)
            ")->execute([$est['id'], $est['cedula'], preg_replace('/[^0-9]/', '', $est['cedula']), $est['nombre'], $est['email'], $hash_std]);
        }
    }
    echo "   -> 9 estudiantes activos configurados.\n";

    // -------------------------------------------------------------
    // 4. CONFIGURAR MATERIAS DE TRAYECTO 5 (SECCIÓN 1D1)
    // -------------------------------------------------------------
    echo "4. Verificando las 8 materias de Trayecto 5 Sección 1D1...\n";
    $materias_t5 = [
        ['id' => 48, 'codigo' => 'PPR1543', 'nombre' => 'PLANIFICACIÓN DE PROYECTO',           'profesor_id' => 6,  'horario' => 'Lunes 07:45 - 10:15',                          'aula' => 'A3'],
        ['id' => 49, 'codigo' => 'INE1532', 'nombre' => 'INTELIGENCIA DE NEGOCIO',             'profesor_id' => 7,  'horario' => 'Lunes 10:15 - 11:45',                          'aula' => 'A5'],
        ['id' => 50, 'codigo' => 'ETI1522', 'nombre' => 'INFORMÁTICA Y ÉTICA',                 'profesor_id' => 8,  'horario' => 'Martes 07:00 - 08:30',                         'aula' => 'A12'],
        ['id' => 51, 'codigo' => 'PDA1542', 'nombre' => 'PROCESAMIENTO DATOS',                 'profesor_id' => 9,  'horario' => 'Martes 08:45 - 10:15',                         'aula' => 'L1'],
        ['id' => 52, 'codigo' => 'PRO1554', 'nombre' => 'PROGRAMACIÓN IV',                     'profesor_id' => 10, 'horario' => 'Jueves 07:00 - 08:30 / Viernes 08:45 - 10:15', 'aula' => 'L2'],
        ['id' => 53, 'codigo' => 'IOP1543', 'nombre' => 'INVESTIGACIÓN DE OPERACIONES',        'profesor_id' => 11, 'horario' => 'Jueves 08:45 - 10:15',                         'aula' => 'A4'],
        ['id' => 54, 'codigo' => 'RED1532', 'nombre' => 'REDES II',                            'profesor_id' => 12, 'horario' => 'Viernes 07:00 - 08:30',                        'aula' => 'A14'],
        ['id' => 55, 'codigo' => 'ASC1532', 'nombre' => 'AUDITORIA DE SISTEMAS COMPUTARIZADO', 'profesor_id' => 13, 'horario' => 'Viernes 10:15 - 11:45',                        'aula' => 'A11'],
    ];

    foreach ($materias_t5 as $mat) {
        $pdo->prepare("
            UPDATE cursos 
            SET seccion = '1D1', trayecto = 5, profesor_id = ?, horario = ?, aula = ?, activo = 1 
            WHERE id = ?
        ")->execute([$mat['profesor_id'], $mat['horario'], $mat['aula'], $mat['id']]);
    }
    echo "   -> 8 materias y sus profesores verificados.\n";

    // -------------------------------------------------------------
    // 5. INSCRIBIR A LOS 9 ESTUDIANTES EN LAS 8 MATERIAS (2026-III)
    // -------------------------------------------------------------
    echo "5. Inscribiendo a los 9 estudiantes en las 8 materias (2026-III)...\n";
    $inscripciones_map = []; // [estudiante_id][curso_id] => inscripcion_id

    foreach ($estudiantes as $est) {
        foreach ($materias_t5 as $mat) {
            $stmt = $pdo->prepare("SELECT id FROM inscripciones WHERE estudiante_id = ? AND curso_id = ? AND periodo = '2026-III'");
            $stmt->execute([$est['id'], $mat['id']]);
            $ins_id = $stmt->fetchColumn();
            if (!$ins_id) {
                $ins_stmt = $pdo->prepare("INSERT INTO inscripciones (estudiante_id, curso_id, periodo, estatus) VALUES (?, ?, '2026-III', 'Activa')");
                $ins_stmt->execute([$est['id'], $mat['id']]);
                $ins_id = (int)$pdo->lastInsertId();
            }
            $inscripciones_map[$est['id']][$mat['id']] = (int)$ins_id;
        }
    }
    echo "   -> Inscripciones de período 2026-III listas (" . (count($estudiantes) * count($materias_t5)) . " inscripciones).\n";

    // -------------------------------------------------------------
    // 6. GENERAR ASISTENCIAS Y SESIONES QR (SEMANAS 1 A 10)
    // -------------------------------------------------------------
    echo "6. Generando asistencias de Semanas 1 a 10...\n";

    // Limpiar asistencias previas en estas materias para 2026-III
    $curso_ids_list = implode(',', array_column($materias_t5, 'id'));
    $pdo->exec("
        DELETE a FROM asistencias a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        WHERE i.curso_id IN ($curso_ids_list) AND i.periodo = '2026-III'
    ");
    $pdo->exec("
        DELETE FROM sesiones_qr 
        WHERE curso_id IN ($curso_ids_list) AND semana BETWEEN 1 AND 10
    ");

    // Definición de las 9 sesiones semanales
    $sesiones_semanales = [
        [
            'curso_id' => 48,
            'profesor_id' => 6,
            'dia_offset' => 0, // Lunes
            'bloque' => 'Lunes 07:45 - 10:15',
            'hora_ini' => '07:45:00',
            'hora_fin' => '10:15:00',
            'es_temprana' => true
        ],
        [
            'curso_id' => 49,
            'profesor_id' => 7,
            'dia_offset' => 0, // Lunes
            'bloque' => 'Lunes 10:15 - 11:45',
            'hora_ini' => '10:15:00',
            'hora_fin' => '11:45:00',
            'es_temprana' => false
        ],
        [
            'curso_id' => 50,
            'profesor_id' => 8,
            'dia_offset' => 1, // Martes
            'bloque' => 'Martes 07:00 - 08:30',
            'hora_ini' => '07:00:00',
            'hora_fin' => '08:30:00',
            'es_temprana' => true
        ],
        [
            'curso_id' => 51,
            'profesor_id' => 9,
            'dia_offset' => 1, // Martes
            'bloque' => 'Martes 08:45 - 10:15',
            'hora_ini' => '08:45:00',
            'hora_fin' => '10:15:00',
            'es_temprana' => false
        ],
        [
            'curso_id' => 52,
            'profesor_id' => 10,
            'dia_offset' => 3, // Jueves
            'bloque' => 'Jueves 07:00 - 08:30',
            'hora_ini' => '07:00:00',
            'hora_fin' => '08:30:00',
            'es_temprana' => true
        ],
        [
            'curso_id' => 53,
            'profesor_id' => 11,
            'dia_offset' => 3, // Jueves
            'bloque' => 'Jueves 08:45 - 10:15',
            'hora_ini' => '08:45:00',
            'hora_fin' => '10:15:00',
            'es_temprana' => false
        ],
        [
            'curso_id' => 54,
            'profesor_id' => 12,
            'dia_offset' => 4, // Viernes
            'bloque' => 'Viernes 07:00 - 08:30',
            'hora_ini' => '07:00:00',
            'hora_fin' => '08:30:00',
            'es_temprana' => true
        ],
        [
            'curso_id' => 52,
            'profesor_id' => 10,
            'dia_offset' => 4, // Viernes
            'bloque' => 'Viernes 08:45 - 10:15',
            'hora_ini' => '08:45:00',
            'hora_fin' => '10:15:00',
            'es_temprana' => false
        ],
        [
            'curso_id' => 55,
            'profesor_id' => 13,
            'dia_offset' => 4, // Viernes
            'bloque' => 'Viernes 10:15 - 11:45',
            'hora_ini' => '10:15:00',
            'hora_fin' => '11:45:00',
            'es_temprana' => false
        ],
    ];

    $fecha_base_lunes = '2026-09-07';

    $stmt_asist = $pdo->prepare("
        INSERT INTO asistencias (inscripcion_id, semana, fecha, bloque_horario, hora_escaneo, estado, justificacion, qr_code, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt_qr = $pdo->prepare("
        INSERT INTO sesiones_qr (curso_id, profesor_id, codigo_qr, fecha, hora_inicio, hora_tolerancia, tolerancia_minutos, hora_expiracion, semana, bloque_horario, activo, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 10, ?, ?, ?, 0, ?)
    ");

    $total_asistencias_insertadas = 0;

    for ($sem = 1; $sem <= 10; $sem++) {
        $semanas_offset_dias = ($sem - 1) * 7;

        foreach ($sesiones_semanales as $sIdx => $ses) {
            $dias_total = $semanas_offset_dias + $ses['dia_offset'];
            $fecha_sesion = date('Y-m-d', strtotime("$fecha_base_lunes + $dias_total days"));
            $timestamp_sesion = "$fecha_sesion {$ses['hora_ini']}";

            // Crear sesión QR archivada
            $codigo_qr = 'QR_SEM_' . $sem . '_C' . $ses['curso_id'] . '_S' . $sIdx . '_' . substr(md5($fecha_sesion . $ses['bloque']), 0, 8);
            $hora_tol = date('H:i:s', strtotime("{$ses['hora_ini']} + 10 minutes"));

            $stmt_qr->execute([
                $ses['curso_id'],
                $ses['profesor_id'],
                $codigo_qr,
                $fecha_sesion,
                $ses['hora_ini'],
                $hora_tol,
                $ses['hora_fin'],
                $sem,
                $ses['bloque'],
                $timestamp_sesion
            ]);

            // Ahora registrar asistencias para cada uno de los 9 estudiantes
            foreach ($estudiantes as $est) {
                $est_id = $est['id'];
                $ins_id = $inscripciones_map[$est_id][$ses['curso_id']];

                $estado = 'presente';
                $hora_escaneo = null;
                $justificacion = null;

                if ($est_id === 4) {
                    // ==========================================
                    // PERFIL DE FERNANDO RODRÍGUEZ
                    // ==========================================
                    // "con digamos 2 justificaciones y el resto como veas sabiendo que suele ser responsable pero donde vive puede hacerse cola impidiendo su llegada extremadamente puntual"

                    // Justificación 1: Semana 4, Lunes (Planificación de Proyecto)
                    if ($sem === 4 && $ses['curso_id'] === 48) {
                        $estado = 'justificado';
                        $hora_escaneo = null;
                        $justificacion = 'Fuerte retraso en transporte público por colisión vehicular en la autopista (vía Guarenas-Caracas). Se presentó justificativo emitido por línea de transporte.';
                    }
                    // Justificación 2: Semana 7, Jueves (Programación IV)
                    elseif ($sem === 7 && $ses['curso_id'] === 52 && $ses['dia_offset'] === 3) {
                        $estado = 'justificado';
                        $hora_escaneo = null;
                        $justificacion = 'Cita médica odontológica especializada programada. Constancia médica y justificativo entregados a coordinación académica.';
                    }
                    // 1 Ausencia ocasional aislada: Semana 2, Viernes 07:00 (Redes II)
                    elseif ($sem === 2 && $ses['curso_id'] === 54) {
                        $estado = 'ausente';
                        $hora_escaneo = null;
                    }
                    // Resto: Suele llegar puntual, pero en clases tempranas (07:00 o 07:45) por la cola llega tarde ~40% de las veces
                    elseif ($ses['es_temprana']) {
                        // En semanas impares o de alta congestión llega unos minutos tarde
                        $es_tarde_hoy = ($sem % 2 === 1 && ($sIdx % 3 === 0 || $sem === 5 || $sem === 9));
                        if ($es_tarde_hoy) {
                            $estado = 'tarde';
                            // Llegó 11-18 min después de la hora
                            $min_late = rand(11, 19);
                            $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + $min_late minutes"));
                        } else {
                            $estado = 'presente';
                            $min_early = rand(2, 8);
                            $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + $min_early minutes"));
                        }
                    } else {
                        // Clases de segunda hora (08:45, 10:15): ya está en el campus, muy puntual
                        // Solo muy rara vez un ligero retraso
                        if ($sem === 8 && $ses['curso_id'] === 49) {
                            $estado = 'tarde';
                            $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + 12 minutes"));
                        } else {
                            $estado = 'presente';
                            $min_early = rand(1, 6);
                            $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + $min_early minutes"));
                        }
                    }

                } else {
                    // ==========================================
                    // PERFIL DE LOS 8 COMPAÑEROS
                    // ==========================================
                    // "ellos no suelen ser puntuales, pero si que suelen asistir"
                    // Suelen llegar tarde en la mayoría de las clases, pero sí asisten.
                    // Pocas ausencias (1-2 cada uno en 10 semanas), alguna justificación ocasional.

                    $seed = ($est_id * 100) + ($sem * 10) + $sIdx;
                    mt_srand($seed);
                    $roll = mt_rand(1, 100);

                    if ($roll <= 62) {
                        // 62% de las veces: TARDE (suelen llegar tarde)
                        $estado = 'tarde';
                        $min_late = mt_rand(11, 26);
                        $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + $min_late minutes"));
                    } elseif ($roll <= 88) {
                        // 26% de las veces: PRESENTE (cuando logran llegar a tiempo)
                        $estado = 'presente';
                        $min_ok = mt_rand(3, 9);
                        $hora_escaneo = date('H:i:s', strtotime("{$ses['hora_ini']} + $min_ok minutes"));
                    } elseif ($roll <= 95) {
                        // 7% de las veces: AUSENTE
                        $estado = 'ausente';
                        $hora_escaneo = null;
                    } else {
                        // 5% de las veces: JUSTIFICADO
                        $estado = 'justificado';
                        $hora_escaneo = null;
                        $justificaciones_ej = [
                            'Quebranto de salud / malestar general notificado.',
                            'Dificultad de traslado y transporte interurbano.',
                            'Trámites de trabajo / diligencia personal justificada.',
                            'Cita médica personal.'
                        ];
                        $justificacion = $justificaciones_ej[mt_rand(0, count($justificaciones_ej) - 1)];
                    }
                }

                $stmt_asist->execute([
                    $ins_id,
                    $sem,
                    $fecha_sesion,
                    $ses['bloque'],
                    $hora_escaneo,
                    $estado,
                    $justificacion,
                    ($estado === 'presente' || $estado === 'tarde') ? $codigo_qr : null,
                    $timestamp_sesion
                ]);

                $total_asistencias_insertadas++;
            }
        }
    }

    echo "   -> Total asistencias insertadas: $total_asistencias_insertadas\n";

    // -------------------------------------------------------------
    // 7. CALIFICACIONES PREVIAS PARA LOS 8 COMPAÑEROS
    // -------------------------------------------------------------
    // Asegurar que los 8 compañeros tengan aprobados los trayectos 1 al 4,
    // tal como Fernando, para que el sistema los reconozca al 100% como de 5to nivel.
    echo "7. Asignando historial académico previo (Niveles 1-4) a los 8 compañeros...\n";
    $cursos_previos = $pdo->query("SELECT id FROM cursos WHERE carrera_id = 4 AND trayecto BETWEEN 1 AND 4 AND activo = 1")->fetchAll(PDO::FETCH_COLUMN);

    $stmt_ins_prev = $pdo->prepare("SELECT id FROM inscripciones WHERE estudiante_id = ? AND curso_id = ? AND periodo = '2026-I'");
    $stmt_add_ins_prev = $pdo->prepare("INSERT INTO inscripciones (estudiante_id, curso_id, periodo, estatus) VALUES (?, ?, '2026-I', 'Activa')");
    $stmt_add_cal_prev = $pdo->prepare("INSERT INTO calificaciones (inscripcion_id, nota, estatus) VALUES (?, ?, 'Aprobada') ON DUPLICATE KEY UPDATE estatus = 'Aprobada'");

    foreach ($estudiantes as $est) {
        if ($est['id'] === 4) continue; // Fernando ya los tiene
        foreach ($cursos_previos as $cId) {
            $stmt_ins_prev->execute([$est['id'], $cId]);
            $insId = $stmt_ins_prev->fetchColumn();
            if (!$insId) {
                $stmt_add_ins_prev->execute([$est['id'], $cId]);
                $insId = (int)$pdo->lastInsertId();
            }
            $stmt_add_cal_prev->execute([$insId, rand(12, 18)]);
        }
    }
    echo "   -> Historial académico previo sincronizado.\n";

    $pdo->commit();
    echo "\n=== PROCESO COMPLETADO EXITOSAMENTE ===\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}
