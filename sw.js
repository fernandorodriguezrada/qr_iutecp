// Service Worker para Sistema QR IUTECP (PWA)
const CACHE_NAME = 'qr-iutecp-v4';
const STATIC_ASSETS = [
    '/index.php',
    '/style.css',
    '/script.js',
    '/favicon.ico',
    '/logoIUTECP_azul.png',
    '/k-listo-foto.png',
    '/k-listo-happy.png',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/manifest.php'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                STATIC_ASSETS.map((url) => cache.add(url))
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Requisito PWA: manejar eventos fetch devolviendo siempre un objeto Response válido
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Recursos estáticos
    if (url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$/i) || url.pathname === '/manifest.php') {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    fetch(request).then((netRes) => {
                        if (netRes && netRes.status === 200) {
                            caches.open(CACHE_NAME).then((c) => c.put(request, netRes));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }
                return fetch(request).then((netRes) => {
                    if (netRes && netRes.status === 200) {
                        const copy = netRes.clone();
                        caches.open(CACHE_NAME).then((c) => c.put(request, copy));
                    }
                    return netRes;
                });
            })
        );
    } else {
        // Navegación / páginas dinámicas PHP
        event.respondWith(
            fetch(request).catch(async () => {
                const cached = await caches.match(request);
                if (cached) return cached;
                const fallback = await caches.match('/index.php');
                if (fallback) return fallback;
                return new Response('Sin conexión', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({ 'Content-Type': 'text/plain; charset=utf-8' })
                });
            })
        );
    }
});
