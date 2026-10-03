/**
 * Admin dashboard — fetches /admin/api/stats.php and renders stat cards,
 * a Chart.js visitor-trend area chart (replaces Recharts), a top-pages
 * bar list, and the recent applications table. Port of AdminDashboard.jsx.
 */
(function () {
  const STATUS_COLORS = { new: '#22c55e', read: '#3b82f6', done: '#16a34a', pending: '#f59e0b', confirmed: '#3b82f6', cancelled: '#ef4444' };

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  let chart = null;

  function load(silent) {
    return fetch('/admin/api/stats.php', { credentials: 'include', cache: 'no-store' })
      .then((r) => r.json())
      .then(renderDashboard)
      .catch(() => {
        if (!silent) document.getElementById('admLoading').innerHTML = '<div style="color:#dc2626">Failed to load dashboard data.</div>';
      });
  }
  load(false);

  // Live updates (admin-common.js): refresh the numbers in place when anything new arrives.
  let liveTimer = null;
  window.addEventListener('adm:live', () => {
    clearTimeout(liveTimer);
    liveTimer = setTimeout(() => load(true), 400); // coalesce bursts
  });
  // Visitor counts and the graph change without any form event, so also refresh
  // every 30 s while the tab is visible, and right away when you come back to it.
  setInterval(() => { if (!document.hidden) load(true); }, 30000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) load(true); });

  function renderDashboard(data) {
    document.getElementById('admLoading').hidden = true;
    document.getElementById('admDashboardContent').hidden = false;

    const { stats, dailyVisitors = [], topPages = [], recentApplications = [] } = data;

    // ---- Visitor summary cards ----
    const visitorCards = [
      { label: 'Today', value: stats.visitors.today },
      { label: 'Last 7 Days', value: stats.visitors.last7 },
      { label: 'Last 30 Days', value: stats.visitors.last30 },
      { label: 'All Time', value: stats.visitors.total },
    ];
    document.getElementById('visitorCards').innerHTML = visitorCards.map((c) => `
      <div style="background:#fff;border-radius:var(--radius);padding:16px 20px;border:1px solid var(--gray-200)">
        <div style="font-size:.77rem;font-weight:600;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">${esc(c.label)}</div>
        <div style="font-size:1.7rem;font-weight:700;color:var(--dark)">${c.value.toLocaleString()}</div>
        <div style="font-size:.78rem;color:var(--gray-400);margin-top:2px">visitors</div>
      </div>
    `).join('');

    // ---- Lead stat cards ----
    const statCards = [
      { label: 'Applications', value: stats.applications.total, badge: `${stats.applications.new} new`, ico: 'bi-lightning-charge', color: 'green', to: 'applications' },
      { label: 'Contacts', value: stats.contacts.total, badge: `${stats.contacts.new} new`, ico: 'bi-envelope', color: 'blue', to: 'contacts' },
      { label: 'Bookings', value: stats.bookings.total, badge: `${stats.bookings.pending} pending`, ico: 'bi-calendar-check', color: 'amber', to: 'bookings' },
      { label: 'Careers', value: stats.careers.total, badge: `${stats.careers.new} new`, ico: 'bi-briefcase', color: 'rose', to: 'careers' },
    ];
    document.getElementById('statCards').innerHTML = statCards.map((c) => `
      <a href="${c.to}" style="text-decoration:none">
        <div class="adm-stat">
          <div class="adm-stat-top">
            <div class="adm-stat-ico ${c.color}"><i class="bi ${c.ico}"></i></div>
            <span class="adm-stat-badge ${c.color === 'amber' ? 'pend' : 'new'}">${esc(c.badge)}</span>
          </div>
          <div class="adm-stat-num">${c.value}</div>
          <div class="adm-stat-label">${esc(c.label)}</div>
        </div>
      </a>
    `).join('');

    // ---- Visitor trend chart (Chart.js) ----
    const map = {};
    dailyVisitors.forEach((d) => { map[d.day] = d.count; });
    const labels = [];
    const values = [];
    for (let i = 13; i >= 0; i--) {
      const d = new Date(Date.now() - i * 86400000);
      // local calendar date (the server groups visits by local day too)
      const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      labels.push(d.toLocaleDateString('en-PK', { month: 'short', day: 'numeric' }));
      values.push(map[key] || 0);
    }
    const total = values.reduce((a, v) => a + v, 0);
    const avg = Math.round(total / values.length);
    const peak = Math.max(...values);
    document.getElementById('visitorKpis').innerHTML = `
      <div><strong>${total.toLocaleString()}</strong><span>total</span></div>
      <div><strong>${avg.toLocaleString()}</strong><span>avg / day</span></div>
      <div><strong>${peak.toLocaleString()}</strong><span>peak</span></div>`;

    const canvas = document.getElementById('visitorChart');
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.parentElement.clientHeight || 260);
    gradient.addColorStop(0, 'rgba(34,197,94,.28)');
    gradient.addColorStop(1, 'rgba(34,197,94,0)');
    const narrow = window.innerWidth < 600;
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data: values, borderColor: '#16a34a', borderWidth: 2.5, backgroundColor: gradient,
          fill: true, tension: 0.35, cubicInterpolationMode: 'monotone',
          pointRadius: narrow ? 0 : 3, pointBackgroundColor: '#fff', pointBorderColor: '#16a34a', pointBorderWidth: 2,
          pointHoverRadius: 6, pointHoverBackgroundColor: '#16a34a', pointHoverBorderColor: '#fff',
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        animation: chart ? false : { duration: 600 }, // no re-animation on live refreshes
        interaction: { mode: 'index', intersect: false },
        layout: { padding: { top: 6, right: 4 } },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a', padding: 10, cornerRadius: 10, displayColors: false,
            titleFont: { size: 12, weight: '600' }, bodyFont: { size: 13, weight: '700' },
            callbacks: { label: (c) => c.parsed.y + (c.parsed.y === 1 ? ' visitor' : ' visitors') },
          },
        },
        scales: {
          x: {
            grid: { display: false }, border: { display: false },
            ticks: { font: { size: 11 }, color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: narrow ? 4 : 7 },
          },
          y: {
            beginAtZero: true, border: { display: false },
            ticks: { precision: 0, font: { size: 11 }, color: '#94a3b8', maxTicksLimit: 5, padding: 8 },
            grid: { color: '#f1f5f9' },
          },
        },
      },
    });

    // ---- Top pages ----
    const topPagesEl = document.getElementById('topPagesList');
    if (topPages.length === 0) {
      topPagesEl.innerHTML = '<div class="adm-empty" style="padding:40px 0">No page data yet</div>';
    } else {
      const max = topPages[0].count;
      topPagesEl.innerHTML = topPages.slice(0, 6).map((p) => {
        const pct = Math.round((p.count / max) * 100);
        return `
          <div>
            <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px">
              <span style="color:var(--gray-700);font-weight:500">${esc(p.page || '/')}</span>
              <span style="color:var(--gray-400);font-weight:600">${p.count}</span>
            </div>
            <div style="background:var(--gray-100);border-radius:4px;height:6px">
              <div style="width:${pct}%;height:100%;background:var(--green-500);border-radius:4px"></div>
            </div>
          </div>`;
      }).join('');
    }

    // ---- Recent applications table ----
    const tbody = document.getElementById('recentApplicationsBody');
    if (recentApplications.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="adm-empty">No applications yet</td></tr>';
    } else {
      // Each row opens that application's detail view on the Applications page.
      tbody.innerHTML = recentApplications.map((a) => {
        const d = new Date(String(a.created_at).replace(' ', 'T') + 'Z'); // DB times are UTC
        const href = 'applications?open=' + encodeURIComponent(a.id);
        return `
        <tr class="adm-row-link" data-href="${href}" tabindex="0" title="Open application #${esc(a.id)}">
          <td style="color:var(--gray-400);font-weight:600">#${esc(a.id)}</td>
          <td style="font-weight:600">${esc(a.name)}</td>
          <td>${esc(a.service_type || '—')}</td>
          <td>${esc(a.city || '—')}</td>
          <td><span class="status-badge status-${esc(a.status)}">${esc(a.status)}</span></td>
          <td class="adm-date-cell">${isNaN(d) ? '—' : `<span>${d.toLocaleDateString()}</span><small>${d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}</small>`}</td>
        </tr>`;
      }).join('');
      tbody.querySelectorAll('.adm-row-link').forEach((tr) => {
        const go = () => { window.location.href = tr.dataset.href; };
        tr.addEventListener('click', go);
        tr.addEventListener('keydown', (e) => { if (e.key === 'Enter') go(); });
      });
    }

    // ---- Quick stats row ----
    const quick = [
      { label: 'Load Calculations', value: stats.calculations.total, icon: 'bi-calculator', sub: 'Total submitted' },
      { label: 'Total Visitors', value: stats.visitors.total, icon: 'bi-eye', sub: 'All time' },
      { label: "Today's Visitors", value: stats.visitors.today, icon: 'bi-person-lines-fill', sub: 'Unique page views' },
    ];
    document.getElementById('quickStatsRow').innerHTML = quick.map((c) => `
      <div style="background:#fff;border-radius:var(--radius-lg);padding:22px 24px;border:1px solid var(--gray-200);display:flex;gap:16px;align-items:center">
        <div style="width:46px;height:46px;border-radius:12px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.2rem;flex-shrink:0">
          <i class="bi ${c.icon}"></i>
        </div>
        <div>
          <div style="font-size:1.6rem;font-weight:700;color:var(--dark);line-height:1">${c.value.toLocaleString()}</div>
          <div style="font-size:.85rem;font-weight:600;color:var(--dark);margin-top:2px">${esc(c.label)}</div>
          <div style="font-size:.77rem;color:var(--gray-400)">${esc(c.sub)}</div>
        </div>
      </div>
    `).join('');
  }
})();
