const CACHE = 'kp-sds-static-v1';

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    const isStatic = url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname.startsWith('/images/');

    if (! isStatic) {
        return;
    }

    event.respondWith(cacheFirst(request));
});

async function cacheFirst(request) {
    const cache = await caches.open(CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response.ok) {
        await cache.put(request, response.clone());
    }

    return response;
}
