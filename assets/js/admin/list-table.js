/**
 * Generic admin list-table engine — shared logic behind AdminApplications/
 * Contacts/Bookings/Careers/Calculations.jsx, which were all near-identical
 * (search/filter/status-update/delete/export/detail-modal). Each admin
 * list page defines a small config object and calls initAdminList(config).
 *
 * config = {
 *   type: 'applications',            // table name used by list/status/delete/export APIs
 *   deleteType: undefined,           // override table name for delete (calculations uses load_calculations)
 *   searchFields: ['name','email'],  // row keys checked against the search box
 *   statuses: ['new','read','done'], // null/omit to hide status column + filter
 *   statusUpdateType: undefined,     // override table name for status API (defaults to type)
 *   columns: [{ label, render(row) }],
 *   detailTitle(row) => string,
 *   detailFields: [{ label, render(row) }],
 *   detailExtra(row) => html string or '' (notes / cover letter / appliance breakdown),
 *   resumeDownload: false,           // careers only — adds a resume column + download action
 * }
 */
function initAdminList(config) {
  let rows = [];
  let search = '';
  let filter = 'all';
  let selected = null;

  const tbody = document.getElementById('listBody');
  const countEl = document.getElementById('listCount');
  const paginationEl = document.getElementById('listPagination');
  const searchInput = document.getElementById('listSearch');
  const filterSelect = document.getElementById('listFilter');
  const exportBtn = document.getElementById('listExportBtn');
  const modalOverlay = document.getElementById('listModalOverlay');
  const modalBody = document.getElementById('listModalBody');
  const modalCloseBtn = document.getElementById('listModalCloseBtn');

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }
  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
  // Ids may arrive as numbers or strings depending on the PDO driver — compare as strings.
  const sameId = (a, b) => String(a) === String(b);
  // MySQL returns UTC "YYYY-MM-DD HH:MM:SS"; read it as UTC (ISO "T…Z", which Safari also
  // understands) so the browser shows local time.
  function parseDate(v) {
    const d = new Date(typeof v === 'string' && /^\d{4}-\d\d-\d\d \d\d:\d\d/.test(v) ? v.replace(' ', 'T') + 'Z' : v);
    return isNaN(d) ? null : d;
  }
  function fmtDate(v, withTime) {
    const d = parseDate(v);
    if (!d) return '—';
    return withTime ? d.toLocaleString() : d.toLocaleDateString();
  }

  if (config.statuses && filterSelect) {
    filterSelect.innerHTML = '<option value="all">All Status</option>' +
      config.statuses.map((s) => `<option value="${s}">${cap(s)}</option>`).join('');
  } else if (filterSelect) {
    filterSelect.hidden = true;
  }

  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      window.open('/admin/api/export.php?type=' + encodeURIComponent(config.deleteType || config.type), '_blank');
    });
  }
  // Search on every keystroke. Some phone keyboards (Samsung, some Gboard modes) hold the
  // current word "in composition" and only fire a normal input event after a space, so we
  // also listen to the composition/key events and read the live value each time.
  const searchClear = document.getElementById('listSearchClear');
  let searchTimer = null;
  function onSearchChange() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      const next = searchInput.value.trim().toLowerCase();
      if (searchClear) searchClear.hidden = !searchInput.value;
      if (next === search) return;
      search = next;
      render();
    }, 60);
  }
  if (searchInput) {
    ['input', 'keyup', 'compositionupdate', 'compositionend', 'search', 'change', 'paste', 'cut'].forEach((ev) =>
      searchInput.addEventListener(ev, onSearchChange));
  }
  if (searchClear) {
    searchClear.addEventListener('click', () => { searchInput.value = ''; onSearchChange(); searchInput.focus(); });
  }
  if (filterSelect) filterSelect.addEventListener('change', (e) => { filter = e.target.value; render(); });
  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
  if (modalOverlay) modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) closeModal(); });

  function closeModal() { selected = null; modalOverlay.hidden = true; document.body.style.overflow = ''; }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modalOverlay.hidden && !document.querySelector('.rv-overlay:not([hidden])')) closeModal();
  });

  async function updateStatus(id, status, opts) {
    opts = opts || {};
    try {
      const res = await fetch('/admin/api/status.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: config.statusUpdateType || config.type, id, status }),
      });
      if (!res.ok) throw new Error('Could not update status.');
    } catch (err) {
      if (!opts.silent && window.showToast) showToast(err.message, 'error');
      return;
    }
    rows = rows.map((r) => (sameId(r.id, id) ? { ...r, status, __search: null } : r));
    if (selected && sameId(selected.id, id)) selected = { ...selected, status };
    render();
    if (selected && !modalOverlay.hidden) renderModal(selected);
    if (window.admPollNow) window.admPollNow(); // sidebar badges update immediately
    if (!opts.silent && window.showToast) showToast('Status: ' + cap(status), 'success');
  }

  // Opening a record counts as reading it: "new" becomes "read" automatically.
  function markReadOnView(row) {
    if (config.statuses && config.statuses.includes('read') && row.status === 'new') {
      updateStatus(row.id, 'read', { silent: true });
    }
  }

  async function del(id) {
    if (!window.confirm('Delete this record?')) return;
    await fetch('/admin/api/delete.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: config.deleteType || config.type, id }),
    });
    rows = rows.filter((r) => !sameId(r.id, id));
    closeModal();
    render();
  }
  window['__admDel_' + config.type] = del; // exposed for inline onclick handlers

  // Phone digits without country code / trunk 0, so "0300 123", "300123" and "+92 300-123" all match.
  function phoneDigits(v) {
    return String(v).replace(/\D/g, '').replace(/^(?:0092|92|0)/, '');
  }
  function rowText(r) {
    if (!r.__search) {
      const vals = Object.keys(r).filter((k) => !k.startsWith('__')).map((k) => r[k]).filter((v) => v != null && v !== '');
      r.__search = vals.join(' | ').toLowerCase();
      r.__digits = vals.map(phoneDigits).filter((d) => d.length >= 3).join(' ');
    }
    return r;
  }
  function matches(r, q) {
    const t = rowText(r);
    // every typed word must appear somewhere in the record
    const words = q.split(/\s+/).filter(Boolean);
    if (words.every((w) => t.__search.includes(w))) return true;
    const d = phoneDigits(q);
    return d.length >= 3 && /^[\d\s+()-]+$/.test(q) && t.__digits.includes(d);
  }
  function filtered() {
    return rows.filter((r) => (!search || matches(r, search)) && (filter === 'all' || r.status === filter));
  }

  function statusSelectHtml(row) {
    if (!config.statuses) return '';
    return `<select class="adm-select-sm" onchange="__admStatus_${config.type}(${row.id}, this.value)">
      ${config.statuses.map((s) => `<option value="${s}" ${row.status === s ? 'selected' : ''}>${cap(s)}</option>`).join('')}
    </select>`;
  }
  window['__admStatus_' + config.type] = updateStatus;

  function render() {
    const list = filtered();
    if (countEl) countEl.textContent = `(${list.length})`;
    if (paginationEl) paginationEl.textContent = `Showing ${list.length} of ${rows.length}`;

    if (rows.__loading) {
      tbody.innerHTML = `<tr><td colspan="20"><div class="adm-empty">Loading…</div></td></tr>`;
      return;
    }
    if (list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="20"><div class="adm-empty">No records found</div></td></tr>`;
      return;
    }

    tbody.innerHTML = list.map((row) => {
      const cells = config.columns.map((col) => `<td>${col.render(row, esc)}</td>`).join('');
      const statusCell = config.statuses ? `<td>${statusSelectHtml(row)}</td>` : '';
      const d = parseDate(row.created_at);
      const dateCell = `<td class="adm-date-cell">${d
        ? `<span>${d.toLocaleDateString()}</span><small>${d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}</small>`
        : '—'}</td>`;
      return `<tr data-row-id="${esc(row.id)}">
        <td style="color:var(--gray-400);font-weight:600">#${row.id}</td>
        ${dateCell}${cells}${statusCell}
        <td><div style="display:flex;gap:6px">
          <button class="adm-action-btn view" title="View" onclick='__admView_${config.type}(${row.id})'><i class="bi bi-eye"></i></button>
          <button class="adm-action-btn del" title="Delete" onclick="__admDel_${config.type}(${row.id})"><i class="bi bi-trash"></i></button>
        </div></td>
      </tr>`;
    }).join('');
  }

  const FIELD_ICONS = {
    phone: 'bi-telephone', email: 'bi-envelope', city: 'bi-geo-alt', service: 'bi-lightning-charge',
    property: 'bi-house-door', position: 'bi-briefcase', experience: 'bi-award', education: 'bi-mortarboard',
    subject: 'bi-chat-left-text', date: 'bi-calendar-event', people: 'bi-people', load: 'bi-lightning',
    system: 'bi-sun', kva: 'bi-sun', bill: 'bi-receipt', name: 'bi-person',
  };
  function fieldIcon(label) {
    const l = label.toLowerCase();
    const key = Object.keys(FIELD_ICONS).find((k) => l.includes(k));
    return key ? FIELD_ICONS[key] : 'bi-info-circle';
  }
  function initials(name) {
    const parts = String(name || '?').trim().split(/\s+/).slice(0, 2);
    return parts.map((p) => p.charAt(0).toUpperCase()).join('') || '?';
  }
  function timeAgo(v) {
    const d = parseDate(v);
    if (!d) return '';
    const sec = Math.max(0, (Date.now() - d.getTime()) / 1000);
    if (sec < 60) return 'just now';
    if (sec < 3600) return Math.floor(sec / 60) + ' min ago';
    if (sec < 86400) return Math.floor(sec / 3600) + ' h ago';
    const days = Math.floor(sec / 86400);
    return days === 1 ? 'yesterday' : days + ' days ago';
  }
  function shortDate(v) {
    const d = parseDate(v);
    if (!d) return '';
    const opts = { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' };
    if (d.getFullYear() !== new Date().getFullYear()) opts.year = 'numeric';
    return d.toLocaleString(undefined, opts);
  }
  function waLink(phone) {
    let d = String(phone || '').replace(/\D/g, '');
    if (d.startsWith('00')) d = d.slice(2);
    else if (d.startsWith('0')) d = '92' + d.slice(1);
    return d.length >= 10 ? 'https://wa.me/' + d : '';
  }

  function renderModal(row) {
    const name = row.name || row.full_name || '';
    const title = config.detailTitle(row, esc);
    const kicker = name && title !== esc(name) ? title : (cap(config.type.replace(/_/g, ' ')) + ' #' + esc(row.id));
    const phone = row.phone || '';
    const email = row.email || '';
    const wa = waLink(phone);

    const quick = [
      phone ? `<a class="adm-dv-act" href="tel:${esc(phone.replace(/[^\d+]/g, ''))}"><i class="bi bi-telephone-fill"></i><span>Call</span></a>` : '',
      wa ? `<a class="adm-dv-act wa" href="${wa}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i><span>WhatsApp</span></a>` : '',
      email ? `<a class="adm-dv-act" href="mailto:${esc(email)}"><i class="bi bi-envelope-fill"></i><span>Email</span></a>` : '',
      phone || email ? `<button type="button" class="adm-dv-act" data-copy="${esc([phone, email].filter(Boolean).join(' · '))}"><i class="bi bi-clipboard"></i><span>Copy</span></button>` : '',
    ].join('');

    const fields = config.detailFields
      .filter((f) => !['status', 'name'].includes(f.label.toLowerCase()))
      .map((f) => `
        <div class="adm-dv-field">
          <i class="bi ${fieldIcon(f.label)}"></i>
          <div><label>${esc(f.label)}</label><span>${f.render(row, esc)}</span></div>
        </div>`).join('');

    const resume = config.resumeDownload && row.resume_filename ? `
      <div class="adm-dv-resume">
        <i class="bi bi-file-earmark-person"></i>
        <div><strong>Resume attached</strong><span>${esc(String(row.resume_filename).split('.').pop().toUpperCase())} file</span></div>
        <button type="button" class="btn btn-sm btn-primary" onclick="__viewResume(${row.id})"><i class="bi bi-eye"></i> View</button>
        <a class="btn btn-sm btn-outline" href="/admin/api/resume-download.php?id=${row.id}" title="Download"><i class="bi bi-download"></i></a>
      </div>` : '';

    const statusBar = config.statuses ? `
      <div class="adm-dv-status-bar" role="group" aria-label="Status">
        ${config.statuses.map((st) => `<button type="button" class="adm-dv-seg ${row.status === st ? 'on s-' + st : ''}"
          onclick="__admStatus_${config.type}(${row.id}, '${st}')">${cap(st)}</button>`).join('')}
      </div>` : '<span></span>';

    modalBody.innerHTML = `
      <div class="adm-dv">
        <header class="adm-dv-head">
          <div class="adm-dv-avatar">${esc(initials(name || String(row.id)))}</div>
          <div class="adm-dv-headtext">
            <span class="adm-dv-kicker">${kicker}</span>
            <h3>${esc(name) || title}</h3>
            <span class="adm-dv-time"><i class="bi bi-clock"></i><span>${timeAgo(row.created_at)} · ${shortDate(row.created_at)}</span></span>
          </div>
          ${row.status ? `<span class="adm-dv-pill s-${esc(row.status)}">${esc(cap(row.status))}</span>` : ''}
        </header>
        <div class="adm-dv-body">
          ${quick ? `<div class="adm-dv-quick">${quick}</div>` : ''}
          ${resume}
          <div class="adm-dv-grid">${fields}</div>
          ${config.detailExtra ? `<div class="adm-dv-extra">${config.detailExtra(row, esc)}</div>` : ''}
        </div>
        <footer class="adm-dv-foot">
          ${statusBar}
          <button type="button" class="adm-dv-del" onclick="__admDel_${config.type}(${row.id})" title="Delete"><i class="bi bi-trash"></i><span>Delete</span></button>
        </footer>
      </div>`;

    const copyBtn = modalBody.querySelector('[data-copy]');
    if (copyBtn) copyBtn.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(copyBtn.dataset.copy); showToast('Copied: ' + copyBtn.dataset.copy, 'success'); }
      catch (e) { showToast(copyBtn.dataset.copy, 'info'); }
    });
  }

  // Accepts a row object or a row id (the table's View button passes the id).
  function openModal(rowOrId) {
    const row = typeof rowOrId === 'object' && rowOrId !== null
      ? rowOrId
      : rows.find((r) => sameId(r.id, rowOrId));
    if (!row) return;
    selected = row;
    renderModal(row);
    modalOverlay.hidden = false;
    document.body.style.overflow = 'hidden';
    markReadOnView(row);
  }
  window['__admView_' + config.type] = openModal;

  // Resume viewer (careers page loads resume-viewer.js)
  window.__viewResume = (id) => {
    const row = rows.find((r) => sameId(r.id, id));
    if (row && window.openResumeViewer) {
      window.openResumeViewer({ id: row.id, name: row.full_name, filename: row.resume_filename });
      markReadOnView(row);
    }
  };

  let freshIds = new Set();
  function load(silent) {
    if (!silent) { rows.__loading = true; render(); }
    return fetch('/admin/api/list.php?type=' + encodeURIComponent(config.type), { credentials: 'include', cache: 'no-store' })
      .then((r) => r.json())
      .then((data) => {
        const next = Array.isArray(data) ? data : [];
        if (silent) {
          const known = new Set(rows.map((r) => String(r.id)));
          freshIds = new Set(next.filter((r) => !known.has(String(r.id))).map((r) => String(r.id)));
        }
        rows = next;
        render();
        freshIds.forEach((id) => {
          const tr = tbody.querySelector(`[data-row-id="${id}"]`);
          if (tr) tr.classList.add('adm-row-new');
        });
      })
      .catch(() => { if (!silent) { rows = []; render(); } });
  }

  // Live updates (admin-common.js): reload in place when a matching record arrives.
  const LIVE_TYPE = { load_calculations: 'calculations' }[config.type] || config.type;
  window.addEventListener('adm:live', (e) => {
    if (e.detail.types.includes(LIVE_TYPE)) load(true);
  });

  // Deep link: /admin/<page>?open=<id> (dashboard rows, bell items, push notifications)
  const openId = new URLSearchParams(window.location.search).get('open');
  load(false).then(() => {
    if (!openId) return;
    if (rows.some((r) => sameId(r.id, openId))) openModal(openId);
    else if (window.showToast) showToast('That record no longer exists.', 'info');
    history.replaceState(null, '', window.location.pathname); // don't reopen on refresh
  });
}
