const CACHE_NAME = 'saucepls-v1';
const RUNTIME_CACHE = 'saucepls-runtime-v1';

// Skip the waiting phase so a newly deployed service worker
// takes control of the page immediately.
self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

// Drop caches from previous versions so we never serve stale assets.
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('saucepls-') && key !== RUNTIME_CACHE)
                    .map((key) => caches.delete(key)),
            ),
        ),
    );
});

/**
 * Network-first with a runtime cache fallback for same-origin GET requests.
 *
 * The app is server-rendered and dynamic, so we always prefer the network;
 * the cache is only a fallback when offline or when the network fails.
 * Only caches a small bounded set of same-origin requests, avoiding
 * navigation URLs so user sessions and dynamic pages stay fresh.
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Only handle same-origin requests.
    if (url.origin !== self.location.origin) {
        return;
    }

    // Skip navigation requests (HTML) so pages are always fresh.
    if (request.mode === 'navigate') {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                // Only cache successful, cacheable responses.
                if (response.ok && response.status === 200) {
                    const copy = response.clone();
                    caches.open(RUNTIME_CACHE).then((cache) => {
                        // Best-effort; never block navigation on the write.
                        cache.put(request, copy).catch(() => {});
                    });
                }
                return response;
            })
            .catch(() => caches.match(request)),
    );
});