/* Cache the static emergency guide; never store authenticated portal pages or live readings. */
const CACHE_NAME = 'daguitan-flood-monitor-v9';
const PRECACHE = [
  './manifest.webmanifest',
  './assets/css/landing.css',
  './assets/js/landing.js',
  './assets/icons/pwa-icon-192.png',
  './assets/icons/pwa-icon-512.png',
  './offline-guide.html'
];
const OFFLINE_GUIDE_URL = new URL('./offline-guide.html', self.registration.scope).href;
const CACHEABLE_PAGES = new Set([OFFLINE_GUIDE_URL]);

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

self.addEventListener('push', (event) => {
  let payload = {};
  if (event.data) {
    try {
      payload = event.data.json();
    } catch (error) {
      payload = { body: event.data.text() };
    }
  }

  const title = typeof payload.title === 'string' && payload.title
    ? payload.title
    : 'Daguitan Flood Monitor';
  const notificationId = Number(payload.notificationId);
  const options = {
    body: typeof payload.body === 'string' ? payload.body : 'A new flood monitoring update is available.',
    icon: './assets/icons/pwa-icon-192.png',
    badge: './assets/icons/pwa-icon-192.png',
    tag: typeof payload.tag === 'string' ? payload.tag : 'daguitan-update',
    data: { url: typeof payload.url === 'string' ? payload.url : './' },
    vibrate: [250, 100, 250, 100, 400],
    renotify: true
  };

  const notifyOpenPages = Number.isSafeInteger(notificationId) && notificationId > 0
    ? self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      clients.forEach((client) => client.postMessage({ type: 'daguitan:push', notificationId }));
    })
    : Promise.resolve();

  event.waitUntil(Promise.all([
    self.registration.showNotification(title, options),
    notifyOpenPages
  ]));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const target = new URL(event.notification.data && event.notification.data.url || './', self.registration.scope);
  if (target.origin !== self.location.origin) return;

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (client.url === target.href && 'focus' in client) return client.focus();
      }
      return self.clients.openWindow(target.href);
    })
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
