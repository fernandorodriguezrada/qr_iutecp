<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';

if (estaAutenticado()) {
    redirigirSegunRol();
}

$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="es" class="auth-html">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#262865" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="QR IUTECP" />
    <link rel="manifest" href="/manifest.php" />
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <title>Sistema QR IUTECP - Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>" />
    <style>
        .error-msg {
            background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px;
            margin-bottom: 15px; font-size: 14px; border: 1px solid #f5c6cb;
        }
        .cedula-input-wrapper {
            display: flex; align-items: center; margin-bottom: 15px;
        }
        .cedula-prefix {
            background: #e9ecef; border: 1px solid var(--border-color);
            border-right: none; padding: 8px 12px; border-radius: 4px 0 0 4px;
            font-weight: bold; color: var(--primary); font-size: 15px;
        }
        .cedula-input-wrapper input {
            margin-bottom: 0; border-radius: 0 4px 4px 0;
        }
        .admin-toggle-link {
            display: block; margin-top: 5px; margin-bottom: 15px;
            font-size: 13px; color: var(--primary); cursor: pointer;
            text-align: left; font-weight: bold;
        }
        .admin-toggle-link:hover { text-decoration: underline; }
        .admin-login-section { display: none; }
        .login-section { display: block; }
    </style>
</head>
<body class="auth-body">
    <div class="auth-view">
        <div class="bg-icons">
            <span style="top:10%;left:8%;transform:rotate(-15deg)" title="Informática">💻</span>
            <span style="top:15%;left:85%;transform:rotate(20deg)" title="Administración">💼</span>
            <span style="top:75%;left:10%;transform:rotate(-10deg)" title="Contaduría">🧮</span>
            <span style="top:80%;left:85%;transform:rotate(15deg)" title="Enfermería">🩺</span>
            <span style="top:45%;left:5%;transform:rotate(25deg)" title="Fisioterapia">🦴</span>
            <span style="top:50%;left:90%;transform:rotate(-20deg)" title="Relaciones Industriales">🤝🏾</span>
            <span style="top:12%;left:45%;transform:rotate(10deg)" title="Enfermería">💉</span>
            <span style="top:85%;left:50%;transform:rotate(-15deg)" title="Contaduría">📊</span>
            <span style="top:65%;left:22%;transform:rotate(30deg)" title="Relaciones Industriales">👥</span>
            <span style="top:25%;left:65%;transform:rotate(-25deg)" title="Informática">⌨️</span>
        </div>
        <img src="k-listo-foto.png" alt="K-Listo" class="auth-mascot" />
        <div class="auth-card">
            <img src="logoIUTECP_azul.png" alt="IUTECP" class="auth-logo" />
            <p class="auth-slogan">¡Formando profesionales exitosos!</p>
            <hr class="auth-divider" />
            <h2 style="color: var(--primary); margin-bottom: 20px">Iniciar Sesión</h2>

            <?php if ($error === 'credenciales'): ?>
                <div class="error-msg">Cédula, usuario o contraseña incorrectos.</div>
            <?php elseif ($error === 'inactivo'): ?>
                <div class="error-msg">Usuario inactivo. Contacte al administrador.</div>
            <?php elseif ($error === 'sesion'): ?>
                <div class="error-msg">Debe iniciar sesión primero.</div>
            <?php endif; ?>

            <div id="login-section" class="login-section">
                <form method="POST" action="login.php">
                    <label>Cédula de Identidad o Usuario</label>
                    <div class="cedula-input-wrapper">
                        <span class="cedula-prefix">V</span>
                        <input type="text" name="cedula" placeholder="Ej. 31853086, alumno, profesor" required />
                    </div>
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Ingrese su contraseña" required />
                    <span class="admin-toggle-link" onclick="toggleAdminLogin()">🔑 Acceso para administradores</span>
                    <button type="submit" class="btn btn-primary" style="width:100%;font-size:16px">Ingresar</button>
                </form>
            </div>

            <div id="admin-login-section" class="admin-login-section">
                <form method="POST" action="login.php">
                    <label>Usuario Administrativo</label>
                    <input type="text" name="username" placeholder="Ej. admin" required />
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Ingrese su contraseña" required />
                    <span class="admin-toggle-link" onclick="toggleAdminLogin()">⬅ Volver a inicio de estudiantes/profesores</span>
                    <button type="submit" class="btn btn-primary" style="width:100%;font-size:16px">Ingresar</button>
                </form>
            </div>
        </div>
        <img src="k-listo-happy.png" alt="K-Listo Feliz" class="auth-mascot" />
    </div>

    <?php
    $is_prod = strpos($_SERVER['HTTP_HOST'] ?? '', 'rf.gd') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', 'infinityfree') !== false;
    if (!$is_prod):
    ?>
    <div id="acceso-banner" style="position:fixed;bottom:0;left:0;right:0;background:var(--primary);color:#fff;text-align:center;padding:6px 10px;font-size:13px;z-index:999;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
        <span><strong>Teléfono:</strong></span>
        <span id="url-acceso" style="background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:3px;font-family:monospace;cursor:pointer;" onclick="copiarUrl()">https://192.168.10.9:8443</span>
    </div>
    <?php endif; ?>

    <script>
    function toggleAdminLogin() {
        const loginSection = document.getElementById('login-section');
        const adminSection = document.getElementById('admin-login-section');

        if (loginSection.style.display === 'none') {
            loginSection.style.display = 'block';
            adminSection.style.display = 'none';
        } else {
            loginSection.style.display = 'none';
            adminSection.style.display = 'block';
        }
    }
    function copiarUrl() {
        var url = document.getElementById('url-acceso').textContent;
        var msg = document.getElementById('copiar-msg');
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    msg.textContent = '✅ Copiado';
                    setTimeout(function() { msg.textContent = '📋'; }, 2000);
                }).catch(function() { fallbackCopiar(url, msg); });
            } else {
                fallbackCopiar(url, msg);
            }
        } catch(e) { fallbackCopiar(url, msg); }
    }
    function fallbackCopiar(url, msg) {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            msg.textContent = '✅ Copiado';
        } catch(e) {
            msg.textContent = '❌ Error';
        }
        document.body.removeChild(ta);
        setTimeout(function() { msg.textContent = '📋'; }, 2000);
    }
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').catch(function(e){ console.log(e); });
        });
    }
    </script>
</body>
</html>
