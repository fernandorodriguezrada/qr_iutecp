<?php require_once __DIR__ . '/includes/header.php'; ?>

<div id="admin-dashboard">
    <h2 style="color:var(--primary);margin-bottom:10px">Panel de Control</h2>
    <p style="margin-bottom:20px;color:#666">Resumen Institucional</p>

    <?php
    global $pdo;

    $total_asistencias = (int)$pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
    $asistieron = (int)$pdo->query("SELECT COUNT(*) FROM asistencias WHERE estado IN ('presente', 'tarde')")->fetchColumn();
    $presentes = (int)$pdo->query("SELECT COUNT(*) FROM asistencias WHERE estado = 'presente'")->fetchColumn();
    $ausentes = (int)$pdo->query("SELECT COUNT(*) FROM asistencias WHERE estado = 'ausente'")->fetchColumn();

    $porcentaje_asistencia = $total_asistencias > 0 ? round(($asistieron / $total_asistencias) * 100) : 0;
    $porcentaje_puntualidad = $asistieron > 0 ? round(($presentes / $asistieron) * 100) : 0;
    $porcentaje_ausencia = $total_asistencias > 0 ? round(($ausentes / $total_asistencias) * 100) : 0;

    $semana_counts = $pdo->query("
        SELECT 
            SUM(CASE WHEN estado IN ('presente', 'tarde') THEN 1 ELSE 0 END) as asistencias,
            SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as inasistencias
        FROM asistencias
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ")->fetch();

    $asistencias_semana = (int)($semana_counts['asistencias'] ?? 0);
    $inasistencias_semana = (int)($semana_counts['inasistencias'] ?? 0);

    $ausencias_data = $pdo->query("
        SELECT 
            SUM(CASE WHEN pct >= 25 AND pct < 30 THEN 1 ELSE 0 END) as en_riesgo,
            SUM(CASE WHEN pct >= 30 THEN 1 ELSE 0 END) as criticos
        FROM (
            SELECT 
                ROUND((SUM(CASE WHEN a.estado = 'ausente' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100) as pct
            FROM inscripciones i
            JOIN asistencias a ON a.inscripcion_id = i.id
            GROUP BY i.estudiante_id, i.curso_id
            HAVING COUNT(a.id) > 0
        ) sub
    ")->fetch();

    $ausencias_riesgo = (int)($ausencias_data['en_riesgo'] ?? 0);
    $ausencias_criticos = (int)($ausencias_data['criticos'] ?? 0);

    $asistencia_semanal = $pdo->query("
        SELECT DAYOFWEEK(a.fecha) as dia, 
               COUNT(*) as total,
               SUM(CASE WHEN a.estado IN ('presente', 'tarde') THEN 1 ELSE 0 END) as asistieron,
               SUM(CASE WHEN a.estado = 'presente' THEN 1 ELSE 0 END) as puntuales,
               SUM(CASE WHEN a.estado = 'tarde' THEN 1 ELSE 0 END) as tardes,
               SUM(CASE WHEN a.estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
               SUM(CASE WHEN a.estado = 'justificado' THEN 1 ELSE 0 END) as justificados
        FROM asistencias a
        WHERE a.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DAYOFWEEK(a.fecha)
        ORDER BY dia
    ")->fetchAll();

    $dias_mostrar = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $datos_dias = [];
    foreach ($asistencia_semanal as $a) {
        $tot = (int)$a['total'];
        $asist = (int)$a['asistieron'];
        $datos_dias[(int)$a['dia']] = [
            'total' => $tot,
            'asistieron' => $asist,
            'puntuales' => (int)$a['puntuales'],
            'tardes' => (int)$a['tardes'],
            'ausentes' => (int)$a['ausentes'],
            'justificados' => (int)$a['justificados'],
            'pct' => $tot > 0 ? round(($asist / $tot) * 100) : 0,
        ];
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
            <p style="font-size:12px;color:#666;font-weight:bold">Asistencias vs. Inasistencias (esta semana)</p>
            <p style="font-size:24px;font-weight:bold;margin-top:5px">
                <span style="color:green"><?= $asistencias_semana ?></span>
                <span style="color:#bbb;font-size:20px;font-weight:normal;margin:0 4px">/</span>
                <span style="color:<?= $inasistencias_semana > 0 ? '#dc3545' : '#666' ?>"><?= $inasistencias_semana ?></span>
            </p>
            <p style="font-size:11px;color:#888;margin-top:3px"><?= $asistencias_semana ?> asistencias · <?= $inasistencias_semana ?> inasistencias</p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Promedio Asistencia</p>
            <p style="font-size:24px;color:green;font-weight:bold;margin-top:5px">
                <?= $porcentaje_asistencia ?>%
            </p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Índice Puntualidad</p>
            <p style="font-size:24px;color:var(--primary);font-weight:bold;margin-top:5px">
                <?= $porcentaje_puntualidad ?>%
            </p>
        </div>
        <div class="card" style="text-align:center;padding:15px">
            <p style="font-size:12px;color:#666;font-weight:bold">Promedio Ausencias</p>
            <p style="font-size:24px;color:<?= $porcentaje_ausencia >= 25 ? '#dc3545' : ($porcentaje_ausencia >= 15 ? '#e67e22' : '#28a745') ?>;font-weight:bold;margin-top:5px">
                <?= $porcentaje_ausencia ?>%
            </p>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
            <h3 style="color:var(--primary);margin:0;">Asistencia Semanal</h3>
            <div style="font-size:12px;color:#666;display:flex;gap:15px;align-items:center;">
                <span><strong style="color:#28a745;">■</strong> ≥80% Asistencia</span>
                <span><strong style="color:#e67e22;">■</strong> 50-79%</span>
                <span><strong style="color:#dc3545;">■</strong> &lt;50%</span>
            </div>
        </div>
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
                        if ($pct >= 80) {
                            return ['text' => '#28a745', 'bar' => 'linear-gradient(to top, #28a745, #5cd677)'];
                        } elseif ($pct >= 70) {
                            return ['text' => '#d39e00', 'bar' => 'linear-gradient(to top, #ffc107, #ffda6a)'];
                        } elseif ($pct >= 50) {
                            return ['text' => '#e67e22', 'bar' => 'linear-gradient(to top, #e67e22, #f0a04b)'];
                        } else {
                            return ['text' => '#dc3545', 'bar' => 'linear-gradient(to top, #dc3545, #f17b86)'];
                        }
                    }

                    foreach ($dias_mostrar as $i => $dia_nombre):
                        $dia_num = $i + 2;
                        $d = $datos_dias[$dia_num] ?? null;
                    ?>
                    <div class="chart-bar-wrapper">
                        <?php if (!$d || $d['total'] === 0): ?>
                            <div style="margin-bottom:12px;font-size:11px;font-weight:600;color:#999;text-align:center;">
                                Sin clases
                            </div>
                        <?php else:
                            $pct = $d['pct'];
                            $c = colorAsistencia($pct);
                            $tooltip = "Total: {$d['total']} | Puntuales: {$d['puntuales']} | Tardes: {$d['tardes']} | Justificados: {$d['justificados']} | Ausentes: {$d['ausentes']}";
                        ?>
                            <div class="chart-bar" style="height:<?= max($pct, 2) ?>%;background:<?= $c['bar'] ?>" title="<?= htmlspecialchars($tooltip) ?>">
                                <span class="chart-tooltip" style="color:<?= $c['text'] ?>"><?= $pct ?>%</span>
                            </div>
                        <?php endif; ?>
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
