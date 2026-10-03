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
    if (!res.ok) throw new Error(data.error || 'Request failed');
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

  // ---- Push provider settings ----
  const pushForm = document.getElementById('pushConfigForm');
  pushForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const data = await postJSON('/admin/api/push-config.php', {
        push_enabled: pushForm.push_enabled.checked,
        push_vapid_public_key: pushForm.push_vapid_public_key.value,
        push_vapid_private_key: pushForm.push_vapid_private_key.value,
        push_vapid_subject: pushForm.push_vapid_subject.value,
      });
      showToast(data.configured ? 'Push settings saved.' : 'Saved. Push stays inactive until it is enabled and both keys are entered.', data.configured ? 'success' : 'info');
      setTimeout(() => window.location.reload(), 900);
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // ---- Subscribe this device to push ----
  const subBtn = document.getElementById('pushSubscribeBtn');
  function urlB64ToUint8Array(b64) {
    const pad = '='.repeat((4 - (b64.length % 4)) % 4);
    const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from(raw, (c) => c.charCodeAt(0));
  }
  subBtn.addEventListener('click', async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      showToast('This browser does not support push notifications.', 'error');
      return;
    }
    try {
      if ((await Notification.requestPermission()) !== 'granted') throw new Error('Notifications were not allowed.');
      const reg = await navigator.serviceWorker.register('/admin/sw.js', { scope: '/admin/' });
      await navigator.serviceWorker.ready;
      const sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlB64ToUint8Array(subBtn.dataset.vapid),
      });
      await postJSON('/admin/api/push-subscribe.php', { subscription: sub.toJSON() });
      showToast('Push enabled on this device.', 'success');
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // ---- Relative times in the activity list ----
  document.querySelectorAll('[data-time]').forEach((el) => {
    const d = new Date(el.dataset.time.replace(' ', 'T'));
    el.textContent = isNaN(d) ? '' : d.toLocaleString();
  });
})();
