const STATIC_CACHE = 'farm-sjb-static-v1';
const PAGE_CACHE = 'farm-sjb-pages-v1';
const OFFLINE_URL = '/offline';

const STATIC_ASSETS = [
    '/manifest.json',
    '/css/bootstrap.css',
    '/css/custom.css',
    '/js/bootstrap.bundle.min.js',
    '/js/app.js',
    '/js/chart.umd.min.js',
    '/js/pwa.js',
    '/favicon.ico',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const staticCache = await caches.open(STATIC_CACHE);
        const pageCache = await caches.open(PAGE_CACHE);

        await staticCache.addAll(STATIC_ASSETS);
        await pageCache.add(OFFLINE_URL);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const currentCaches = [STATIC_CACHE, PAGE_CACHE];
        const cacheNames = await caches.keys();

        await Promise.all(cacheNames
            .filter((cacheName) => cacheName.startsWith('farm-sjb-') && !currentCaches.includes(cacheName))
            .map((cacheName) => caches.delete(cacheName)));

        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isExcludedPath(url.pathname)) {
        event.respondWith(fetch(request));
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (/\.(css|js|png|jpe?g|gif|svg|webp|ico)$/i.test(url.pathname)) {
        event.respondWith(cacheFirst(request));
    }
});

function isExcludedPath(pathname) {
    return pathname === '/login'
        || pathname.startsWith('/login/')
        || pathname === '/logout'
        || pathname.startsWith('/logout/');
}

async function cacheFirst(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cachedResponse = await cache.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    const response = await fetch(request);

    if (response.ok) {
        await cache.put(request, response.clone());
    }

    return response;
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        const contentType = response.headers.get('Content-Type') || '';
        const responsePath = new URL(response.url).pathname;

        if (
            response.ok
            && !response.redirected
            && responsePath !== '/login'
            && contentType.includes('text/html')
        ) {
            const cache = await caches.open(PAGE_CACHE);
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cache = await caches.open(PAGE_CACHE);
        const cachedResponse = await cache.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        const offlineResponse = await cache.match(OFFLINE_URL);

        if (offlineResponse) {
            return offlineResponse;
        }

        throw error;
    }
}
