/* Cache the static emergency guide; never store authenticated portal pages or live readings. */
const CACHE_NAME = 'daguitan-flood-monitor-v3';
const PRECACHE = [
  './',
  './index.php',
  './manifest.webmanifest',
  './assets/css/landing.css',
  './assets/js/landing.js',
  './assets/icons/pwa-icon-192.png',
  './assets/icons/pwa-icon-512.png',
  './offline-guide.html'
];
const OFFLINE_GUIDE_URL = new URL('./offline-guide.html', self.registration.scope).href;
const CACHEABLE_PAGES = new Set([
  new URL('./', self.registration.scope).href,
  new URL('./index.php', self.registration.scope).href,
  OFFLINE_GUIDE_URL
]);

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key.indexOf('daguitan-flood-monitor-') === 0 && key !== CACHE_NAME).map((key) => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const isPage = event.request.mode === 'navigate'
    || (event.request.headers.get('accept') || '').includes('text/html');

  if (isPage) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (CACHEABLE_PAGES.has(event.request.url) && response && response.status === 200 && response.type === 'basic') {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return response;
        })
        .catch(async () => {
          const cachedPage = CACHEABLE_PAGES.has(event.request.url)
            ? await caches.match(event.request)
            : null;
          const guide = cachedPage || await caches.match(OFFLINE_GUIDE_URL);
          return guide || new Response('The offline emergency guide is not available on this device yet. Open it once while connected.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
          });
        })
    );
    return;
  }

  const assetUrl = new URL(event.request.url);
  if (assetUrl.origin !== self.location.origin || !/\.(?:css|js|png|svg|webmanifest|woff2?)$/i.test(assetUrl.pathname)) return;

  event.respondWith(
    caches.open(CACHE_NAME).then((cache) => cache.match(event.request)).then((cached) => {
      const network = fetch(event.request)
        .then((response) => {
          if (response && response.status === 200 && response.type === 'basic') {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return response;
        })
        .catch(() => cached);

      return cached || network;
    })
  );
});
