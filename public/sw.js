const CACHE_VERSION = 'v2';
const CACHE_NAME = `rc-rms-static-${CACHE_VERSION}`;
const SAFE_ASSETS = [
  '/offline',
  '/manifest.json',
  '/icons/icon-96x96.svg',
  '/icons/icon-144x144.svg',
  '/icons/icon-192x192.svg',
  '/icons/icon-512x512.svg',
  '/favicon.ico',
];

const SENSITIVE_PATH = /\/(pos|sales|customers|warranty|cash-flow|expenses|reports|profile|admin|finance|approvals|inventory|purchasing)/i;
const STATIC_EXT = /\.(?:css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|webp|ico)$/i;

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
  const url = new URL(request.url);

  if (request.method !== 'GET') {
    return;
  }

  if (url.origin !== self.location.origin) {
    return;
  }

  if (SENSITIVE_PATH.test(url.pathname) || /\/(login|register|logout|password)/i.test(url.pathname)) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match('/offline'))
    );
    return;
  }

  if (!STATIC_EXT.test(url.pathname) && !SAFE_ASSETS.includes(url.pathname)) {
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) {
        return cached;
      }

      return fetch(request).then((response) => {
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }

        const clone = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
        return response;
      }).catch(() => caches.match('/offline'));
    })
  );
});
