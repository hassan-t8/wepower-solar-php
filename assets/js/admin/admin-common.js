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
      await fetch('/admin/logout.php', { method: 'POST', credentials: 'include' });
      window.location.href = '/admin/login.php';
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

  let lastId = 0;
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
    const d = new Date(String(v).replace(' ', 'T'));
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
      const n = new Notification(e.title, { body: e.body || '', tag: 'wepower-' + e.id, icon: '/assets/images/favicon.svg' });
      n.onclick = () => { window.focus(); if (e.url) window.location.href = e.url; n.close(); };
    } else if (window.showToast) {
      showToast(e.title + (e.body ? ' — ' + e.body : ''), 'info');
    }
  }

  async function poll() {
    if (stopped) return;
    try {
      const res = await fetch('/admin/api/updates.php?since=' + lastId, { credentials: 'include', cache: 'no-store' });
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
      if (data.initial && !store.get(SEEN_KEY)) store.set(SEEN_KEY, String(data.latest_id));
      lastId = Math.max(lastId, Number(data.latest_id) || 0);
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
