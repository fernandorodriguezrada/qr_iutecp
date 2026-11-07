// Service Worker para Sistema QR IUTECP (PWA)
const CACHE_NAME = 'qr-iutecp-v1';
const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/style.css',
    '/script.js',
    '/favicon.ico',
    '/logoIUTECP_azul.png',
    '/k-listo-foto.png',
    '/k-listo-happy.png',
    '/assets/js/jsqr.js',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('Algunos recursos estáticos no pudieron ser cacheados:', err);
            });
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

// Estrategia Network-First para páginas dinámicas PHP y peticiones de API
self.addEventListener('fetch', (event) => {
    const request = event.request;
    
    // Solo manejar peticiones GET
    if (request.method !== 'GET') return;

    // Si es un recurso estático (imágenes, CSS, JS, fuentes)
    const url = new URL(request.url);
    const isStatic = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$/i);

    if (isStatic) {
        // Cache-first con actualización en segundo plano
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return networkResponse;
                });
            })
        );
    } else {
        // Network-first para páginas PHP / navegación
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;
                    return caches.match('/index.php');
                });
            })
        );
    }
});
