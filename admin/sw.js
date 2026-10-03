/**
 * Admin push service worker (scope /admin/). Shows a notification for each
 * push message and opens the related admin page when it's clicked.
 * A push with a JSON payload {title, body, url} is shown as-is; a push with
 * no payload fetches the newest event from the admin feed instead.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
  event.waitUntil((async () => {
    let data = null;
    try { data = event.data ? event.data.json() : null; } catch (e) { data = null; }
    if (!data) {
      try {
        const res = await fetch('/admin/api/updates.php?since=0', { credentials: 'include', cache: 'no-store' });
        const feed = await res.json();
        data = (feed.events || []).slice(-1)[0] || null;
      } catch (e) { /* offline or logged out */ }
    }
    data = data || { title: 'WePower Admin', body: 'You have a new notification.', url: '/admin/dashboard.php' };
    await self.registration.showNotification(data.title, {
      body: data.body || '',
      icon: '/assets/images/favicon.svg',
      tag: data.id ? 'wepower-' + data.id : undefined,
      data: { url: data.url || '/admin/dashboard.php' },
    });
  })());
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/admin/dashboard.php';
  event.waitUntil((async () => {
    const wins = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const w of wins) {
      if (new URL(w.url).pathname.startsWith('/admin/')) { await w.focus(); return w.navigate(url); }
    }
    return self.clients.openWindow(url);
  })());
});
