/**
 * Admin → Notifications page: per-type switches, this-device desktop
 * alerts, push provider settings, and push subscription for this device.
 */
(function () {
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* private mode */ } },
  };
  async function postJSON(url, body) {
    const res = await fetch(url, {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || (data.errors && data.errors.join(' ')) || 'Request failed');
    return data;
  }

  // ---- Per-type switches (saved instantly) ----
  document.querySelectorAll('[data-pref]').forEach((input) => {
    input.addEventListener('change', async () => {
      try {
        await postJSON('/admin/api/notification-prefs.php', { type: input.dataset.pref, enabled: input.checked });
        showToast((input.checked ? 'On: ' : 'Off: ') + input.getAttribute('aria-label'), 'success');
      } catch (err) {
        input.checked = !input.checked;
        showToast(err.message, 'error');
      }
    });
  });

  // ---- Desktop alerts (Notification API, works while the panel is open) ----
  const statusEl = document.getElementById('desktopStatus');
  const toggle = document.getElementById('desktopToggle');
  const allowBtn = document.getElementById('desktopAllowBtn');
  const testBtn = document.getElementById('desktopTestBtn');
  const supported = 'Notification' in window;

  function renderDesktop() {
    const perm = supported ? Notification.permission : 'unsupported';
    const on = store.get('admDesktopNotif') !== 'off';
    toggle.checked = on && perm === 'granted';
    toggle.disabled = perm !== 'granted';
    allowBtn.hidden = perm === 'granted' || perm === 'unsupported';
    const map = {
      granted: on ? ['ok', 'On'] : ['off', 'Off on this device'],
      denied: ['warn', 'Blocked in browser settings'],
      default: ['warn', 'Not allowed yet'],
      unsupported: ['off', 'Not supported by this browser'],
    };
    const [cls, text] = map[perm];
    statusEl.className = 'ntf-status ' + cls;
    statusEl.textContent = text;
  }
  allowBtn.addEventListener('click', async () => {
    if (!supported) return;
    await Notification.requestPermission();
    store.set('admDesktopNotif', 'on');
    renderDesktop();
  });
  toggle.addEventListener('change', () => { store.set('admDesktopNotif', toggle.checked ? 'on' : 'off'); renderDesktop(); });
  testBtn.addEventListener('click', () => {
    if (supported && Notification.permission === 'granted' && store.get('admDesktopNotif') !== 'off') {
      new Notification('WePower test alert', { body: 'Desktop alerts are working on this device.', icon: '/assets/images/favicon.svg' });
    } else {
      showToast('Test: alerts appear like this while desktop alerts are off or not allowed.', 'info');
    }
  });
  renderDesktop();

  // ---- Push (Firebase Cloud Messaging) settings ----
  const pushForm = document.getElementById('pushConfigForm');
  const firebaseCfg = JSON.parse(pushForm.dataset.firebase || '{}');
  const saFile = document.getElementById('saFile');
  saFile.addEventListener('change', async () => {
    const file = saFile.files[0];
    if (file) pushForm.fcm_service_account.value = await file.text();
  });
  pushForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = pushForm;
    try {
      const data = await postJSON('/admin/api/push-config.php', {
        push_enabled: f.push_enabled.checked,
        fcm_service_account: f.fcm_service_account.value,
        fcm_project_id: f.fcm_project_id.value,
        fcm_sender_id: f.fcm_sender_id.value,
        fcm_api_key: f.fcm_api_key.value,
        fcm_app_id: f.fcm_app_id.value,
        fcm_vapid_key: f.fcm_vapid_key.value,
      });
      showToast(data.configured
        ? 'Push is active. Now click "Enable push on this device".'
        : 'Saved. Push stays off until it is switched on and the service account is added.', data.configured ? 'success' : 'info');
      setTimeout(() => window.location.reload(), 1200);
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // ---- Register this device for push ----
  const FIREBASE_VERSION = '12.19.0';
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

  const subBtn = document.getElementById('pushSubscribeBtn');
  subBtn.addEventListener('click', async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
      showToast('This browser does not support push. On iPhone, first add the site to your Home Screen.', 'error');
      return;
    }
    subBtn.disabled = true;
    const origHtml = subBtn.innerHTML;
    subBtn.innerHTML = '<span class="btn-spinner" style="border-color:rgba(22,163,74,.3);border-top-color:var(--green-600)"></span> Registering… (can take up to 30s)';
    try {
      if ((await Notification.requestPermission()) !== 'granted') throw new Error('Notifications were not allowed in this browser.');
      const [reg] = await Promise.all([
        navigator.serviceWorker.register('/admin/sw.js', { scope: '/admin/' }).then(() => navigator.serviceWorker.ready),
        loadFirebase(),
      ]);
      const app = window.firebase.apps.length ? window.firebase.app() : window.firebase.initializeApp({
        apiKey: firebaseCfg.apiKey,
        projectId: firebaseCfg.projectId,
        messagingSenderId: firebaseCfg.messagingSenderId,
        appId: firebaseCfg.appId,
      });
      const token = await app.messaging().getToken({ vapidKey: firebaseCfg.vapidKey, serviceWorkerRegistration: reg });
      if (!token) throw new Error('Firebase did not return a device token.');
      const data = await postJSON('/admin/api/push-subscribe.php', { token });
      document.getElementById('pushDeviceCount').textContent = data.devices;
      store.set('admPushToken', token);
      showToast('Push enabled on this device. Try "Send test push".', 'success');
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      subBtn.disabled = false;
      subBtn.innerHTML = origHtml;
    }
  });

  const testBtn2 = document.getElementById('pushTestBtn');
  testBtn2.addEventListener('click', async () => {
    testBtn2.disabled = true;
    try {
      const data = await postJSON('/admin/api/push-test.php', {});
      showToast(`Test push sent to ${data.sent} of ${data.total} device(s).`, 'success');
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      testBtn2.disabled = false;
    }
  });

  // ---- Relative times in the activity list ----
  document.querySelectorAll('[data-time]').forEach((el) => {
    const d = new Date(el.dataset.time.replace(' ', 'T'));
    el.textContent = isNaN(d) ? '' : d.toLocaleString();
  });
})();
