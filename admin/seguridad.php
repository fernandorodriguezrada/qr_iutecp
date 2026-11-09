<?php
require_once __DIR__ . "/includes/header.php";
global $pdo;

$mensaje = $_GET["msg"] ?? "";
$error = $_GET["error"] ?? "";

$stmt_auditoria = $pdo->query("
    SELECT a.id, a.accion, a.detalle, a.ip_address, a.created_at, u.nombre_completo
    FROM auditoria a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 50
");
$auditoria = $stmt_auditoria->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {
    if ($_POST["accion"] === "cambiar_mi_password") {
        $actual = $_POST["password_actual"] ?? "";
        $nueva = $_POST["password_nueva"] ?? "";
        $confirmar = $_POST["password_confirmar"] ?? "";

        $stmt = $pdo->prepare(
            "SELECT password_hash FROM usuarios WHERE id = ?",
        );
        $stmt->execute([$_SESSION["usuario_id"]]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($actual, $user["password_hash"])) {
            $error = "actual_incorrecta";
        } elseif (strlen($nueva) < 6) {
            $error = "corta";
        } elseif ($nueva !== $confirmar) {
            $error = "no_coinciden";
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "UPDATE usuarios SET password_hash = ?, password_temporal = FALSE WHERE id = ?",
            );
            $stmt->execute([$hash, $_SESSION["usuario_id"]]);
            registrarAuditoria(
                $pdo,
                $_SESSION["usuario_id"],
                "Cambio de contraseña (admin)",
                "El administrador cambió su propia contraseña",
            );
            header("Location: seguridad.php?msg=pass_cambiada");
            exit();
        }

        if ($error) {
            header("Location: seguridad.php?error=" . $error);
            exit();
        }
    }

    if ($_POST["accion"] === "reset_usuario") {
        $cedula_buscar = limpiarInput($_POST["cedula_buscar"] ?? "");
        $nueva_pass = $_POST["nueva_password"] ?? "";

        if (empty($cedula_buscar) || empty($nueva_pass)) {
            header("Location: seguridad.php?error=campos_vacios");
            exit();
        }

        $cedula_completa = "V" . $cedula_buscar;
        $stmt = $pdo->prepare(
            "SELECT id, nombre_completo, rol FROM usuarios WHERE cedula = ? AND activo = 1",
        );
        $stmt->execute([$cedula_completa]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare(
                "SELECT id, nombre_completo, rol FROM usuarios WHERE cedula = ? AND activo = 1",
            );
            $stmt->execute([$cedula_buscar]);
            $user = $stmt->fetch();
        }

        if (!$user) {
            header("Location: seguridad.php?error=usuario_no_encontrado");
            exit();
        }

        $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET password_hash = ?, password_temporal = TRUE WHERE id = ?",
        );
        $stmt->execute([$hash, $user["id"]]);

        registrarAuditoria(
            $pdo,
            $_SESSION["usuario_id"],
            "Reset de contraseña",
            "Usuario: {$user["nombre_completo"]} ({$user["rol"]}) - Contraseña restablecida",
        );

        header(
            "Location: seguridad.php?msg=reset_ok&nombre=" .
                urlencode($user["nombre_completo"]),
        );
        exit();
    }
}
?>
<div id="admin-security">
    <h2 style="color:var(--primary);margin-bottom:20px">Seguridad y Accesos</h2>

    <?php if ($mensaje === "pass_cambiada"): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
        Contraseña actualizada correctamente.
    </div>
    <?php elseif ($mensaje === "reset_ok"): ?>
    <div style="background:#d4edda;color:#155724;padding:15px;border-radius:4px;margin-bottom:20px;border:1px solid #c3e6cb;">
        <strong>✓ Contraseña restablecida.</strong><br>
        <span style="font-size:14px;">Usuario: <strong><?= htmlspecialchars(
            $_GET["nombre"] ?? "",
        ) ?></strong></span><br>
        <span style="font-size:12px;color:#856404;">⚠️ El usuario deberá cambiar la contraseña en su próximo inicio de sesión.</span>
    </div>
    <?php elseif ($error === "actual_incorrecta"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">La contraseña actual no es correcta.</div>
    <?php elseif ($error === "corta"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">La nueva contraseña debe tener al menos 6 caracteres.</div>
    <?php elseif ($error === "no_coinciden"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Las contraseñas no coinciden.</div>
    <?php elseif ($error === "campos_vacios"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Debe completar todos los campos.</div>
    <?php elseif ($error === "usuario_no_encontrado"): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">Usuario no encontrado o inactivo.</div>
    <?php endif; ?>

    <div class="management-grid">
        <div class="card">
            <h3 style="margin-bottom:15px;color:var(--primary);border-bottom:2px solid var(--accent);padding-bottom:5px;">
                Mi Contraseña (Administrador)
            </h3>
            <form method="POST" action="seguridad.php">
                <input type="hidden" name="accion" value="cambiar_mi_password" />
                <label>Contraseña Actual</label>
                <input type="password" name="password_actual" required />
                <label>Nueva Contraseña</label>
                <input type="password" name="password_nueva" required minlength="6" />
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmar" required minlength="6" />
                <button class="btn btn-primary" style="width:100%">Actualizar Mi Contraseña</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom:15px;color:var(--primary);border-bottom:2px solid var(--accent);padding-bottom:5px;">
                Restablecer Acceso de Usuarios
            </h3>
            <form method="POST" action="seguridad.php">
                <input type="hidden" name="accion" value="reset_usuario" />
                <label>Buscar Usuario (Cédula)</label>
                <div style="display:flex;gap:10px;margin-bottom:15px;">
                    <div style="display:flex;flex:1;">
                        <span style="background:#e9ecef;border:1px solid var(--border-color);border-right:none;padding:8px 12px;border-radius:4px 0 0 4px;font-weight:bold;color:var(--primary);font-size:14px;">V</span>
                        <input type="text" name="cedula_buscar" placeholder="Ej. 31853086" required
                               style="border-radius:0 4px 4px 0;margin-bottom:0;"
                               pattern="[0-9]+" title="Solo números"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                </div>
                <label>Nueva Contraseña Temporal</label>
                <input type="text" name="nueva_password" placeholder="Ingrese la nueva contraseña..." required minlength="6" />
                <button class="btn btn-danger" style="width:100%">Restablecer Contraseña</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <h3 style="color:var(--primary);margin-bottom:15px;border-bottom:2px solid var(--accent);padding-bottom:5px;">
            Bitácora de Auditoría
        </h3>
        <?php if (count($auditoria) === 0): ?>
        <p style="color:#666;font-size:14px;">No hay registros de auditoría aún.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditoria as $log): ?>
                    <tr>
                        <td><?= date(
                            "d/m/Y H:i",
                            strtotime($log["created_at"]),
                        ) ?></td>
                        <td><?= htmlspecialchars(
                            $log["nombre_completo"] ?? "Sistema",
                        ) ?></td>
                        <td><?= htmlspecialchars($log["accion"]) ?></td>
                        <td><?= htmlspecialchars($log["detalle"]) ?></td>
                        <td><?= htmlspecialchars($log["ip_address"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <p style="margin-top:20px;font-size:13px;color:#666;">
            <strong>Advertencia:</strong> El uso de esta herramienta queda registrado en el historial de auditoría del sistema.
        </p>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
