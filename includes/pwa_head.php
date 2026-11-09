<?php
?>
<script>
(function() {
    try {
        var base = window.location.origin;
        var manifestData = {
            "id": base + "/",
            "name": "Sistema QR IUTECP",
            "short_name": "QR IUTECP",
            "description": "Sistema de Control de Asistencias y Gestión Académica IUTECP",
            "start_url": base + "/index.php",
            "scope": base + "/",
            "display": "standalone",
            "orientation": "portrait-primary",
            "background_color": "#e0e0e0",
            "theme_color": "#262865",
            "lang": "es",
            "categories": ["education", "productivity"],
            "icons": [
                {
                    "src": base + "/assets/icons/icon-192.png",
                    "sizes": "192x192",
                    "type": "image/png",
                    "purpose": "any"
                },
                {
                    "src": base + "/assets/icons/icon-maskable-192.png",
                    "sizes": "192x192",
                    "type": "image/png",
                    "purpose": "maskable"
                },
                {
                    "src": base + "/assets/icons/icon-512.png",
                    "sizes": "512x512",
                    "type": "image/png",
                    "purpose": "any"
                },
                {
                    "src": base + "/assets/icons/icon-maskable-512.png",
                    "sizes": "512x512",
                    "type": "image/png",
                    "purpose": "maskable"
                }
            ]
        };

        var blob = new Blob([JSON.stringify(manifestData)], { type: 'application/manifest+json' });
        var manifestURL = URL.createObjectURL(blob);
        var link = document.createElement('link');
        link.rel = 'manifest';
        link.href = manifestURL;
        document.head.appendChild(link);
    } catch (e) {
        console.warn('Error al inyectar PWA manifest blob:', e);
    }

    try {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        function syncAppViewport() {
            var vh = window.innerHeight;
            if (window.visualViewport) {
                vh = Math.round(window.visualViewport.height);
            }
            document.documentElement.style.setProperty('--app-height', vh + 'px');
            var mc = document.querySelector('.mobile-container');
            if (mc) {
                mc.style.height = vh + 'px';
                mc.style.maxHeight = vh + 'px';
            }
            if (window.scrollY !== 0) {
                window.scrollTo(0, 0);
            }
        }

        syncAppViewport();
        window.addEventListener('DOMContentLoaded', syncAppViewport);
        window.addEventListener('load', syncAppViewport);
        window.addEventListener('resize', syncAppViewport);
        window.addEventListener('pageshow', syncAppViewport);
        window.addEventListener('orientationchange', function() {
            setTimeout(syncAppViewport, 100);
            setTimeout(syncAppViewport, 300);
        });

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', syncAppViewport);
        }
    } catch (err) {
        console.warn('Error al sincronizar viewport:', err);
    }
})();
</script>
