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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="theme-color" content="#262865" />
    <meta name="color-scheme" content="light" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="QR IUTECP" />
    <?php require_once __DIR__ . "/includes/pwa_head.php"; ?>
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
            display: flex;
            align-items: stretch;
            margin-bottom: 15px;
        }
        .cedula-prefix {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            border: 1px solid var(--border-color);
            border-right: none;
            padding: 8px 12px;
            border-radius: 4px 0 0 4px;
            font-weight: bold;
            color: var(--primary);
            font-size: 15px;
            line-height: 1;
            box-sizing: border-box;
            user-select: none;
        }
        .cedula-input-wrapper input {
            margin-bottom: 0 !important;
            border-radius: 0 4px 4px 0 !important;
            flex: 1;
            width: 100%;
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
            <?php elseif ($error === 'formato_cedula'): ?>
                <div class="error-msg">Formato incorrecto: Ingrese únicamente los números de su cédula (ejemplo: <strong>31853086</strong>), sin la letra «V», puntos ni espacios.</div>
            <?php elseif ($error === 'acceso_denegado_admin'): ?>
                <div class="error-msg">Las cuentas de administrador deben ingresar exclusivamente desde el <strong>Acceso para administradores</strong>.</div>
            <?php elseif ($error === 'acceso_denegado_usuario'): ?>
                <div class="error-msg">Este formulario es exclusivo para administradores. Profesores y estudiantes deben ingresar en su sección correspondiente.</div>
            <?php endif; ?>

            <div id="login-section" class="login-section">
                <form method="POST" action="login.php" id="form-usuario" onsubmit="return validarCedulaUsuario(event)">
                    <input type="hidden" name="login_tipo" value="usuario" />
                    <label>Cédula de Identidad</label>
                    <div class="cedula-input-wrapper">
                        <span class="cedula-prefix">V</span>
                        <input type="text" id="input-cedula" name="cedula" inputmode="numeric" pattern="[0-9]*" placeholder="Ej. 31853086" required autocomplete="username" />
                    </div>
                    <div id="cedula-alerta" style="display:none;color:#b91c1c;font-size:12px;margin:-10px 0 12px;line-height:1.3;font-weight:600;"></div>
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password" />
                    <span class="admin-toggle-link" onclick="toggleAdminLogin()">🔑 Acceso para administradores</span>
                    <button type="submit" class="btn btn-primary" style="width:100%;font-size:16px">Ingresar</button>
                </form>
            </div>

            <div id="admin-login-section" class="admin-login-section">
                <form method="POST" action="login.php">
                    <input type="hidden" name="login_tipo" value="admin" />
                    <label>Usuario Administrativo</label>
                    <input type="text" name="username" placeholder="Ej. admin" required autocomplete="username" />
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password" />
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(f) {
            f.addEventListener('submit', function() {
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                window.scrollTo(0, 0);
            });
        });
    });

    
    function validarCedulaUsuario(e) {
        var input = document.getElementById('input-cedula');
        var alerta = document.getElementById('cedula-alerta');
        if (!input) return true;
        var valor = input.value.trim();

        // Si es usuario demo existente de pruebas 'alumno' o 'profesor', permitirlo
        if (valor === 'alumno' || valor === 'profesor') {
            if (alerta) alerta.style.display = 'none';
            return true;
        }

        if (/[a-zA-Z]/.test(valor)) {
            e.preventDefault();
            if (alerta) {
                alerta.textContent = 'Coloque solo los números de su cédula (sin "V" ni letras, la "V" ya está incluida).';
                alerta.style.display = 'block';
            }
            input.focus();
            return false;
        }

        if (/[\.\,\-\s]/.test(valor)) {
            e.preventDefault();
            if (alerta) {
                alerta.textContent = 'Coloque solo los números seguidos, sin puntos, comas ni espacios (ej: 31853086).';
                alerta.style.display = 'block';
            }
            input.focus();
            return false;
        }

        if (!/^[0-9]{4,10}$/.test(valor)) {
            e.preventDefault();
            if (alerta) {
                alerta.textContent = 'Ingrese un número de cédula válido (únicamente números).';
                alerta.style.display = 'block';
            }
            input.focus();
            return false;
        }

        if (alerta) alerta.style.display = 'none';
        return true;
    }

    document.getElementById('input-cedula')?.addEventListener('input', function() {
        var alerta = document.getElementById('cedula-alerta');
        var v = this.value;
        if (/[a-zA-Z]/.test(v)) {
            alerta.textContent = 'Coloque solo los números (sin la letra "V").';
            alerta.style.display = 'block';
        } else if (/[\.\,\-\s]/.test(v)) {
            alerta.textContent = 'Sin puntos ni espacios (ej: 31853086).';
            alerta.style.display = 'block';
        } else {
            alerta.style.display = 'none';
        }
    });

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
            setTimeout(function() {
                navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' })
                    .then(function(reg) { console.log('SW listo:', reg.scope); })
                    .catch(function(e) { console.warn('SW aviso:', e.message); });
            }, 500);
        });
    }
    </script>
</body>
</html>
