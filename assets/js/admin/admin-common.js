/**
 * Shared admin chrome behavior — mobile sidebar toggle + logout.
 * Port of the sidebar-open state and logout() function in AdminLayout.jsx.
 */
(function () {
  const sidebar = document.getElementById('admSidebar');
  const overlay = document.getElementById('admOverlay');
  const menuBtn = document.getElementById('admMenuBtn');
  const logoutBtn = document.getElementById('admLogoutBtn');

  function setOpen(open) {
    sidebar.classList.toggle('open', open);
    overlay.classList.toggle('show', open);
  }

  if (menuBtn) menuBtn.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
  if (overlay) overlay.addEventListener('click', () => setOpen(false));

  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      let pushToken = '';
      try { pushToken = localStorage.getItem('admPushToken') || ''; } catch (e) { /* ignore */ }
      try {
        await fetch('/admin/logout', {
          method: 'POST', credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ push_token: pushToken }), // server unregisters this device
        });
      } catch (e) { /* still leave the panel */ }
      // Forget this device's registration so the next login registers it again.
      try { localStorage.removeItem('admPushToken'); sessionStorage.removeItem('admPushRegistered'); } catch (e) { /* ignore */ }
      window.location.href = '/admin/login';
    });
  }
})();

/**
 * Live updates — polls /admin/api/updates.php (every 10s while the tab is
 * visible, 60s while hidden). New events update the sidebar badges and the
 * bell list, show a toast (or a desktop notification when the tab is in the
 * background) for the types the admin switched on, and fire an `adm:live`
 * event so open pages (lists, dashboard) refresh their data in place.
 */
(function () {
  const bellBtn = document.getElementById('admBellBtn');
  if (!bellBtn) return;

  const panel = document.getElementById('admBellPanel');
  const list = document.getElementById('admBellList');
  const countEl = document.getElementById('admBellCount');
  const liveDot = document.getElementById('admLiveDot');
  const ICONS = {
    applications: 'bi-lightning-charge', careers: 'bi-briefcase', contacts: 'bi-envelope',
    bookings: 'bi-calendar-check', calculations: 'bi-calculator', visitors: 'bi-graph-up-arrow',
  };
  const SEEN_KEY = 'admNotifSeenId';
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* private mode */ } },
  };

  let lastId = null;  // null until the first (init) response
  let events = [];   // newest last, max 30 kept for the bell list
  let prefs = {};
  let timer = null;
  let stopped = false;

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }
  function timeAgo(v) {
    const d = new Date(typeof v === 'string' && /^\d{4}-\d\d-\d\d \d\d:\d\d/.test(v) ? v.replace(' ', 'T') + 'Z' : v); // DB times are UTC
    if (isNaN(d)) return '';
    const s = Math.max(0, (Date.now() - d.getTime()) / 1000);
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s / 60) + 'm ago';
    if (s < 86400) return Math.floor(s / 3600) + 'h ago';
    return d.toLocaleDateString();
  }

  function renderBell() {
    const seen = Number(store.get(SEEN_KEY) || 0);
    const unread = events.filter((e) => Number(e.id) > seen).length;
    countEl.textContent = unread > 9 ? '9+' : String(unread);
    countEl.hidden = unread === 0;
    if (!events.length) {
      list.innerHTML = '<div class="adm-bell-empty">No notifications yet</div>';
      return;
    }
    list.innerHTML = events.slice().reverse().map((e) => `
      <a class="adm-bell-item ${Number(e.id) > seen ? 'unread' : ''}" href="${esc(e.url || '#')}">
        <i class="bi ${ICONS[e.type] || 'bi-bell'}"></i>
        <span><strong>${esc(e.title)}</strong>${e.body ? `<small>${esc(e.body)}</small>` : ''}<em>${timeAgo(e.created_at)}</em></span>
      </a>`).join('');
  }

  function renderBadges(badges) {
    Object.keys(badges || {}).forEach((k) => {
      const el = document.querySelector(`[data-badge="${k}"]`);
      if (!el) return;
      el.textContent = badges[k];
      el.hidden = !Number(badges[k]);
    });
  }

  function alertFor(e) {
    if (prefs[e.type] === false) return;
    const wantDesktop = store.get('admDesktopNotif') !== 'off';
    if (document.hidden && wantDesktop && 'Notification' in window && Notification.permission === 'granted') {
      const n = new Notification(e.title, { body: e.body || '', tag: 'wepower-' + e.id, icon: '/assets/images/logo.png' });
      n.onclick = () => { window.focus(); if (e.url) window.location.href = e.url; n.close(); };
    } else if (window.showToast) {
      showToast(e.title + (e.body ? ' — ' + e.body : ''), 'info');
    }
  }

  async function poll() {
    if (stopped) return;
    try {
      const res = await fetch('/admin/api/updates.php?since=' + (lastId === null ? 'init' : lastId), { credentials: 'include', cache: 'no-store' });
      if (res.status === 401) { stopped = true; liveDot.classList.add('off'); return; }
      const data = await res.json();
      prefs = data.prefs || prefs;
      renderBadges(data.badges);
      const fresh = data.events || [];
      if (fresh.length) {
        events = events.concat(fresh).slice(-30);
        if (!data.initial) {
          fresh.forEach(alertFor);
          window.dispatchEvent(new CustomEvent('adm:live', {
            detail: { events: fresh, types: [...new Set(fresh.map((e) => e.type))] },
          }));
        }
      }
      const latest = Number(data.latest_id) || 0;
      if (data.initial) {
        // first visit, or the notification history was cleared since this browser last looked
        const seen = store.get(SEEN_KEY);
        if (seen === null || Number(seen) > latest) store.set(SEEN_KEY, String(latest));
      }
      if (lastId !== null && latest < lastId) {
        // history was cleared while this page was open: start counting again
        events = [];
        store.set(SEEN_KEY, '0');
      }
      lastId = lastId === null ? latest : (latest < lastId ? latest : Math.max(lastId, latest));
      renderBell();
      liveDot.classList.remove('off');
    } catch (err) {
      liveDot.classList.add('off');
    } finally {
      schedule();
    }
  }
  function schedule() {
    clearTimeout(timer);
    if (!stopped) timer = setTimeout(poll, document.hidden ? 60000 : 10000);
  }
  document.addEventListener('visibilitychange', () => { if (!document.hidden) { clearTimeout(timer); poll(); } });
  // Lets pages refresh badges right after a change (e.g. a record marked read).
  window.admPollNow = () => { clearTimeout(timer); poll(); };

  function setPanel(open) {
    panel.hidden = !open;
    bellBtn.setAttribute('aria-expanded', String(open));
    if (open) {
      renderBell();
      store.set(SEEN_KEY, String(lastId)); // opening the bell marks everything read
      countEl.hidden = true;
    } else {
      renderBell();
    }
  }
  bellBtn.addEventListener('click', (e) => { e.stopPropagation(); setPanel(panel.hidden); });
  document.addEventListener('click', (e) => { if (!panel.hidden && !panel.contains(e.target)) setPanel(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) setPanel(false); });

  poll();
})();

/**
 * Notification permission prompt. Browsers only show their real "Allow"
 * dialog after a click, and never again once the user picks "Block", so:
 *  - not decided yet → popup once per browser session (i.e. after login)
 *    plus a slim banner on every admin page until allowed;
 *  - blocked → banner explaining how to unblock in the browser;
 *  - allowed → desktop alerts on, and this device is registered for push
 *    automatically (push-register.js).
 */
(function () {
  if (!('Notification' in window)) return;
  const content = document.querySelector('.adm-content');
  if (!content) return;
  const session = {
    get(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { sessionStorage.setItem(k, v); } catch (e) { /* private mode */ } },
  };
  const isChromeLike = /Chrome|Edg/.test(navigator.userAgent);
  const unblockHelp = isChromeLike
    ? 'Click the icon left of the address bar → Site settings → Notifications → Allow, then reload.'
    : 'Open this site’s settings in your browser, set Notifications to Allow, then reload.';

  let banner = null;
  let modal = null;

  function removeUi() {
    if (banner) { banner.remove(); banner = null; }
    if (modal) { modal.remove(); modal = null; }
  }

  const isEdge = /Edg\//.test(navigator.userAgent);
  const waitingHelp = isEdge
    ? 'Edge shows a small <i class="bi bi-bell"></i> icon at the right end of the address bar — click it and choose <strong>Allow</strong>.'
    : 'A box should appear near the address bar — choose <strong>Allow</strong>. Only see a small <i class="bi bi-bell"></i> icon in the address bar? Click it and choose <strong>Allow</strong>.';

  // Works with both the promise and the old callback form of requestPermission.
  function requestPermission() {
    return new Promise((resolve) => {
      const r = Notification.requestPermission(resolve);
      if (r && typeof r.then === 'function') r.then(resolve, () => resolve(Notification.permission));
    });
  }

  let finished = false;
  function onGranted() {
    try { localStorage.setItem('admDesktopNotif', 'on'); } catch (e) { /* ignore */ }
    if (window.AdmPush && window.AdmPush.canRegister) {
      showToast('Notifications allowed — setting up push on this device…', 'success');
      window.AdmPush.register()
        .then(() => showToast('Push notifications are on for this device.', 'success'))
        .catch((err) => showToast(err.message, 'error'));
    } else {
      showToast('Notifications allowed. You\u2019ll get alerts for new activity.', 'success');
    }
  }
  function finish(result) {
    if (finished) return;
    if (result === 'granted') { finished = true; removeUi(); onGranted(); return; }
    if (result === 'denied') { finished = true; render(); }
    // 'default' (prompt dismissed): keep the waiting banner so they can try again
  }

  // Edge (and Chrome with "quiet" prompts) show only an address-bar icon instead of a
  // dialog, and the browser's answer may never come back to the page. So close our
  // popup immediately, show where to click, and also watch the permission itself.
  async function allow() {
    removeUi();
    showWaiting();
    finish(await requestPermission());
  }

  if (navigator.permissions && navigator.permissions.query) {
    navigator.permissions.query({ name: 'notifications' }).then((status) => {
      status.onchange = () => finish(Notification.permission);
    }).catch(() => { /* not supported for notifications in this browser */ });
  }

  function showWaiting() {
    banner = document.createElement('div');
    banner.className = 'adm-perm-banner waiting';
    banner.innerHTML = `<i class="bi bi-hourglass-split"></i>
      <div><strong>Waiting for your browser…</strong><span>${waitingHelp}</span></div>
      <button type="button" class="btn btn-outline btn-sm adm-perm-allow"><i class="bi bi-arrow-repeat"></i> Ask again</button>
      <button type="button" class="adm-perm-x" aria-label="Hide"><i class="bi bi-x-lg"></i></button>`;
    content.prepend(banner);
    banner.querySelector('.adm-perm-allow').addEventListener('click', allow);
    banner.querySelector('.adm-perm-x').addEventListener('click', () => { banner.remove(); banner = null; });
    banner.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }

  function showBanner(blocked) {
    banner = document.createElement('div');
    banner.className = 'adm-perm-banner' + (blocked ? ' blocked' : '');
    banner.innerHTML = blocked
      ? `<i class="bi bi-bell-slash"></i><div><strong>Notifications are blocked in this browser.</strong><span>${unblockHelp}</span></div>
         <button type="button" class="adm-perm-x" aria-label="Hide"><i class="bi bi-x-lg"></i></button>`
      : `<i class="bi bi-bell"></i><div><strong>Turn on notifications</strong><span>Get alerted the moment a new application, message or booking arrives.</span></div>
         <button type="button" class="btn btn-primary btn-sm adm-perm-allow"><i class="bi bi-check2-circle"></i> Allow</button>
         <button type="button" class="adm-perm-x" aria-label="Hide"><i class="bi bi-x-lg"></i></button>`;
    content.prepend(banner);
    const allowBtn = banner.querySelector('.adm-perm-allow');
    if (allowBtn) allowBtn.addEventListener('click', allow);
    banner.querySelector('.adm-perm-x').addEventListener('click', () => { banner.remove(); banner = null; });
  }

  function showModal() {
    session.set('admPermModalShown', '1');
    modal = document.createElement('div');
    modal.className = 'adm-perm-overlay';
    modal.innerHTML = `
      <div class="adm-perm-modal" role="dialog" aria-modal="true" aria-labelledby="admPermTitle">
        <div class="adm-perm-icon"><i class="bi bi-bell-fill"></i></div>
        <h3 id="admPermTitle">Allow notifications</h3>
        <p>Get instant alerts for new quote requests, job applications, messages and bookings — even when this tab is in the background.</p>
        <button type="button" class="btn btn-primary btn-block adm-perm-allow"><i class="bi bi-check2-circle"></i> Allow notifications</button>
        <button type="button" class="adm-perm-later">Not now</button>
      </div>`;
    document.body.appendChild(modal);
    modal.querySelector('.adm-perm-allow').addEventListener('click', allow);
    modal.querySelector('.adm-perm-later').addEventListener('click', () => { modal.remove(); modal = null; });
  }

  function render() {
    removeUi();
    const perm = Notification.permission;
    if (perm === 'granted') {
      if (window.AdmPush) window.AdmPush.autoRegister();
      return;
    }
    showBanner(perm === 'denied');
    if (perm === 'default' && !session.get('admPermModalShown')) showModal();
  }

  render();
})();

/**
 * Mobile-friendly tables: copy each column header onto its cells as
 * data-label, so on phones every row can be shown as a card
 * ("Name: …", "Phone: …") instead of a wide table that scrolls sideways
 * (styles in admin.css). Re-runs whenever a table's rows are re-rendered.
 */
(function () {
  function label(table) {
    const heads = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
    table.querySelectorAll('tbody tr').forEach((tr) => {
      Array.from(tr.children).forEach((td, i) => {
        if (td.hasAttribute('colspan')) { tr.classList.add('adm-row-msg'); return; }
        td.setAttribute('data-label', heads[i] || '');
      });
    });
  }
  document.querySelectorAll('table.adm-table').forEach((table) => {
    label(table);
    const body = table.tBodies[0];
    if (body) new MutationObserver(() => label(table)).observe(body, { childList: true });
  });
})();
