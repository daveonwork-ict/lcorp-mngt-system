const CACHE_NAME = 'rc-rms-shell-v1';
const SAFE_ASSETS = ['/', '/login', '/offline.html', '/manifest.json'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(SAFE_ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Never cache non-GET or transaction-like URLs that may contain sensitive data.
  if (request.method !== 'GET' || /\/(pos|cash-flow|expenses|warranty|approvals)/i.test(new URL(request.url).pathname)) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
        return response;
      })
      .catch(() => caches.match(request).then((cached) => cached || caches.match('/offline.html')))
  );
});
