/*
 * Mapleby staff app service worker (scope /app/).
 * - Pages are always fetched live (they contain role-specific, private data and are never cached).
 * - If the network is down, a page request gets the offline screen.
 * - Built JS/CSS and icons are cached so the app opens quickly.
 */
const VERSION = 'mapleby-staff-v2';
const SHELL = ['/offline.html', '/images/icons/icon-192.png', '/images/icons/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(VERSION).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Page loads: network only, offline screen as fallback.
    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }

    // Static build assets are content-hashed, so cache-first is safe.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/icons/')) {
        event.respondWith(
            caches.match(req).then((hit) => hit || fetch(req).then((res) => {
                if (res.ok) {
                    const copy = res.clone();
                    caches.open(VERSION).then((cache) => cache.put(req, copy));
                }
                return res;
            }))
        );
    }
    // Everything else (Inertia JSON, documents, APIs) goes straight to the network.
});
