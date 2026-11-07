<?php require_once __DIR__ . '/includes/header.php'; ?>

<div id="admin-dashboard">
    <h2 style="color:var(--primary);margin-bottom:10px">Panel de Control</h2>
    <p style="margin-bottom:20px;color:#666">Resumen Institucional</p>

    <?php
    global $pdo;

    $total_asistencias = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
    $presentes = $pdo->query("SELECT COUNT(*) FROM asistencias WHERE estado='presente'")->fetchColumn();
    $porcentaje_asistencia = $total_asistencias > 0 ? round(($presentes / $total_asistencias) * 100) : 0;
    $porcentaje_ausencia = 100 - $porcentaje_asistencia;

    $asistentes_semana = $pdo->query("
        SELECT COUNT(DISTINCT i.estudiante_id)
        FROM asistencias a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        WHERE a.estado = 'presente'
        AND a.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ")->fetchColumn();

    $con_asistencia_semana = $pdo->query("
        SELECT COUNT(DISTINCT i.estudiante_id)
        FROM asistencias a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        WHERE a.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ")->fetchColumn();

    $inasistentes_semana = $con_asistencia_semana - $asistentes_semana;

    $ausencias_data = $pdo->query("
        SELECT 
            SUM(CASE WHEN pct >= 25 AND pct < 30 THEN 1 ELSE 0 END) as en_riesgo,
            SUM(CASE WHEN pct >= 30 THEN 1 ELSE 0 END) as criticos
        FROM (
            SELECT 
                ROUND((SUM(CASE WHEN a.estado != 'presente' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100) as pct
            FROM inscripciones i
            JOIN asistencias a ON a.inscripcion_id = i.id
            GROUP BY i.estudiante_id
        ) sub
    ")->fetch();

    $ausencias_riesgo = (int)($ausencias_data['en_riesgo'] ?? 0);
    $ausencias_criticos = (int)($ausencias_data['criticos'] ?? 0);

    $asistencia_semanal = $pdo->query("
        SELECT DAYOFWEEK(a.fecha) as dia, 
               COUNT(*) as total,
               SUM(CASE WHEN a.estado='presente' THEN 1 ELSE 0 END) as presentes
        FROM asistencias a
        WHERE a.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DAYOFWEEK(a.fecha)
        ORDER BY dia
    ")->fetchAll();

    $dias_semana = ['', 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $dias_mostrar = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $porcentajes_dias = [];
    foreach ($asistencia_semanal as $a) {
        $porcentajes_dias[(int)$a['dia']] = $a['total'] > 0 ? round(($a['presentes'] / $a['total']) * 100) : 0;
    }
    ?>

    <div class="dashboard-cards" style="margin-bottom:20px">
        <a href="/admin/ausencias.php" style="text-decoration:none;color:inherit">
            <div class="card" style="text-align:center;padding:15px;cursor:pointer;transition:box-shadow 0.2s;border-left:4px solid #e67e22" onmouseover="this.style.boxShadow='0 2px 10px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow='none'">
                <p style="font-size:12px;color:#666;font-weight:bold">Ausencias Prolongadas</p>
                <p style="font-size:20px;color:#e67e22;font-weight:bold;margin-top:5px">
                    <?= $ausencias_riesgo ?> en riesgo / <?= $ausencias_criticos ?> críticos
                </p>
                <p style="font-size:13px;color:var(--primary);font-weight:bold;margin-top:8px">&gt;&gt;</p>
            </div>
        </a>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Alumnos Asistentes (esta semana)</p>
            <p style="font-size:24px;color:green;font-weight:bold;margin-top:5px">
                <?= number_format($asistentes_semana) ?>
            </p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Alumnos Inasistentes (esta semana)</p>
            <p style="font-size:24px;color:red;font-weight:bold;margin-top:5px">
                <?= number_format($inasistentes_semana) ?>
            </p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Promedio Asistencia</p>
            <p style="font-size:24px;color:green;font-weight:bold;margin-top:5px">
                <?= $porcentaje_asistencia ?>%
            </p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Promedio Ausencias</p>
            <p style="font-size:24px;color:red;font-weight:bold;margin-top:5px">
                <?= $porcentaje_ausencia ?>%
            </p>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <h3 style="color:var(--primary);margin-bottom:20px">Asistencia Semanal</h3>
        <div class="css-chart">
            <div class="chart-y-axis">
                <span>100%</span>
                <span>75%</span>
                <span>50%</span>
                <span>25%</span>
                <span>0%</span>
            </div>
            <div class="chart-content">
                <div class="chart-bars-container">
                    <div class="chart-guide" style="top:0%"></div>
                    <div class="chart-guide" style="top:25%"></div>
                    <div class="chart-guide" style="top:50%"></div>
                    <div class="chart-guide" style="top:75%"></div>

                    <?php
                    function colorAsistencia(int $pct): array {
                        if ($pct >= 90) {
                            return ['text' => 'green', 'bar' => 'linear-gradient(to top, #28a745, #66cc66)'];
                        } elseif ($pct >= 80) {
                            return ['text' => '#d39e00', 'bar' => 'linear-gradient(to top, #ffc107, #ffda6a)'];
                        } elseif ($pct >= 70) {
                            return ['text' => '#e67e22', 'bar' => 'linear-gradient(to top, #e67e22, #f0a04b)'];
                        } else {
                            return ['text' => '#dc3545', 'bar' => 'linear-gradient(to top, #dc3545, #f17b86)'];
                        }
                    }

                    foreach ($dias_mostrar as $i => $dia_nombre):
                        $dia_num = $i + 2;
                        $pct = $porcentajes_dias[$dia_num] ?? 0;
                        $c = colorAsistencia($pct);
                    ?>
                    <div class="chart-bar-wrapper">
                        <div class="chart-bar" style="height:<?= max($pct, 1) ?>%;background:<?= $c['bar'] ?>">
                            <span class="chart-tooltip" style="color:<?= $c['text'] ?>"><?= $pct ?>%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-x-axis">
                    <?php foreach ($dias_mostrar as $dia): ?>
                    <span><?= $dia ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
