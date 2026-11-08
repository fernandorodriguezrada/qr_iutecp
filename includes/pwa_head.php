<?php
// Generador e inyector PWA nativo compatible con InfinityFree (Bypass AES testcookie)
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
            "background_color": "#262865",
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
})();
</script>
