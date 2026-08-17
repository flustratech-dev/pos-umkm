const CACHE_NAME = 'pos-jadisatu-v2';
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

    // Cache static build assets, storage images, and fonts with Cache-First / Stale-While-Revalidate
    const isImageOrAsset = (
        url.pathname.startsWith('/build/assets/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/storage/') ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com' ||
        url.hostname.includes('unsplash.com') ||
        /\.(png|jpe?g|webp|svg|gif|ico)(\?.*)?$/i.test(url.pathname)
    );

    if (isImageOrAsset) {
        e.respondWith(
            caches.match(e.request).then((cached) => {
                const fetchPromise = fetch(e.request).then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(e.request, copy);
                        });
                    }
                    return response;
                }).catch(() => cached);

                // Return cached version in 0ms if available, while fetchPromise updates cache in background
                return cached || fetchPromise;
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
