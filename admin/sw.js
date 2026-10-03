/**
 * Admin push service worker (scope /admin/), used by Firebase Cloud Messaging.
 * Shows a notification for each push (FCM data message {title, body, url})
 * and opens the related admin page when it's clicked. A push without a
 * usable payload falls back to the newest event from the admin feed.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
  event.waitUntil((async () => {
    let data = null;
    try {
      const msg = event.data ? event.data.json() : null;
      // Firebase Cloud Messaging wraps data messages as {data: {...}, from, fcmMessageId}
      if (msg) {
        const d = msg.data || {};
        const n = msg.notification || {};
        // Our server sends data messages; Firebase console test messages use "notification".
        const pick = d.title ? d : (n.title ? n : (msg.title ? msg : null));
        data = pick ? { ...d, ...pick, url: d.url || pick.url || (msg.fcmOptions && msg.fcmOptions.link) } : null;
      }
    } catch (e) { data = null; }
    if (!data) {
      try {
        const res = await fetch('/admin/api/updates.php?since=0', { credentials: 'include', cache: 'no-store' });
        const feed = await res.json();
        data = (feed.events || []).slice(-1)[0] || null;
      } catch (e) { /* offline or logged out */ }
    }
    data = data || { title: 'WePower Admin', body: 'You have a new notification.', url: '/admin/dashboard.php' };
    let shown = false;
    let error = '';
    try {
      await self.registration.showNotification(data.title, {
        body: data.body || '',
        icon: '/assets/images/logo.png', // PNG: some OS notification centres can't render SVG icons
        badge: '/assets/images/logo.png',
        tag: data.id ? 'wepower-' + data.id : undefined,
        requireInteraction: false,
        data: { url: data.url || '/admin/dashboard.php' },
      });
      shown = true;
    } catch (e) {
      error = String(e && e.message || e);
    }
    // Delivery receipt, so the admin panel can tell "never arrived" from "arrived but OS hid it".
    try {
      await fetch('/api/push-ack.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: data.title, shown, error }),
      });
    } catch (e) { /* offline */ }
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
