const CACHE_NAME = 'pos-jadisatu-v1';
const STATIC_ASSETS = [
    '/images/favicon.png',
    '/images/favicon-32x32.png',
    '/images/favicon-16x16.png',
    '/images/apple-touch-icon.png',
    '/images/logo_pos_umkm.png',
    '/images/logo_jadisatu.png'
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((k) => {
                    if (k !== CACHE_NAME) {
                        return caches.delete(k);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    const url = new URL(e.request.url);

    // Only cache GET requests
    if (e.request.method !== 'GET') return;

    // Cache static build assets and fonts with Cache-First strategy
    if (
        url.pathname.startsWith('/build/assets/') ||
        url.pathname.startsWith('/images/') ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com'
    ) {
        e.respondWith(
            caches.match(e.request).then((cached) => {
                if (cached) return cached;
                return fetch(e.request).then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(e.request, copy);
                        });
                    }
                    return response;
                }).catch(() => cached);
            })
        );
        return;
    }

    // Dynamic HTML / API requests: Network First
    e.respondWith(
        fetch(e.request).catch(() => {
            return caches.match(e.request);
        })
    );
});
