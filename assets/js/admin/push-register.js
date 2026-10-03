/**
 * Shared push registration for every admin page (config comes from
 * window.ADM_PUSH, set in admin-header.php).
 *
 *   AdmPush.register()  → registers this browser with Firebase Cloud Messaging
 *                         and saves its device token for the logged-in admin.
 *                         Resolves to the device count; throws on failure.
 *   AdmPush.autoRegister() → same, but quietly, at most once per browser
 *                         session, and only when the Firebase web config is
 *                         present and notifications are already allowed.
 */
(function () {
  const cfg = window.ADM_PUSH || { configured: false, canRegister: false, firebase: {} };
  const FIREBASE_VERSION = '12.19.0';
  // Served by PHP with no-cache headers (the CDN caches static .js for days).
  const SW_URL = '/admin/sw.php';
  let firebaseLoaded = null;

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src; s.onload = resolve; s.onerror = () => reject(new Error('Could not load Firebase.'));
      document.head.appendChild(s);
    });
  }
  function loadFirebase() {
    if (!firebaseLoaded) {
      const base = 'https://cdn.jsdelivr.net/npm/firebase@' + FIREBASE_VERSION + '/';
      firebaseLoaded = loadScript(base + 'firebase-app-compat.js').then(() => loadScript(base + 'firebase-messaging-compat.js'));
    }
    return firebaseLoaded;
  }
  const session = {
    get(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { sessionStorage.setItem(k, v); } catch (e) { /* private mode */ } },
  };

  function supported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
  }

  async function register() {
    if (!supported()) throw new Error('This browser does not support push. On iPhone, add the site to your Home Screen first.');
    if (Notification.permission !== 'granted') throw new Error('Notifications are not allowed in this browser.');
    const [reg] = await Promise.all([
      navigator.serviceWorker.register(SW_URL, { scope: '/admin/' }).then(() => navigator.serviceWorker.ready),
      loadFirebase(),
    ]);
    const fb = cfg.firebase;
    const app = window.firebase.apps.length ? window.firebase.app() : window.firebase.initializeApp({
      apiKey: fb.apiKey, projectId: fb.projectId, messagingSenderId: fb.messagingSenderId, appId: fb.appId,
    });
    const token = await app.messaging().getToken({ vapidKey: fb.vapidKey, serviceWorkerRegistration: reg });
    if (!token) throw new Error('Firebase did not return a device token.');

    const res = await fetch('/admin/api/push-subscribe.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Could not save this device.');
    session.set('admPushRegistered', '1');
    try { localStorage.setItem('admPushToken', token); } catch (e) { /* private mode */ }
    window.dispatchEvent(new CustomEvent('adm:push-token', { detail: { token, devices: data.devices } }));
    return data.devices;
  }

  // Re-registering each session also refreshes tokens that Firebase rotated.
  async function autoRegister() {
    if (!cfg.canRegister || !supported() || Notification.permission !== 'granted') return;
    if (session.get('admPushRegistered')) return;
    try { await register(); } catch (e) { /* silent: the Notifications page shows errors on demand */ }
  }

  // Keep this browser on the current service worker: move old registrations
  // (cached /admin/sw.js) to the uncached URL, otherwise just check for updates.
  // The push subscription belongs to the scope, so the device token is kept.
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistration('/admin/').then((r) => {
      if (!r) return;
      const w = r.active || r.waiting || r.installing;
      if (w && !w.scriptURL.includes(SW_URL)) return navigator.serviceWorker.register(SW_URL, { scope: '/admin/' });
      return r.update();
    }).catch(() => {});
  }

  window.AdmPush = { configured: cfg.configured, canRegister: cfg.canRegister, supported, register, autoRegister };
})();
