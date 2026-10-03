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
  // MySQL returns "YYYY-MM-DD HH:MM:SS"; Safari can't parse the space form, so use ISO "T".
  function parseDate(v) {
    const d = new Date(typeof v === 'string' ? v.replace(' ', 'T') : v);
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
  if (searchInput) searchInput.addEventListener('input', (e) => { search = e.target.value.toLowerCase(); render(); });
  if (filterSelect) filterSelect.addEventListener('change', (e) => { filter = e.target.value; render(); });
  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
  if (modalOverlay) modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) closeModal(); });

  function closeModal() { selected = null; modalOverlay.hidden = true; }

  async function updateStatus(id, status) {
    await fetch('/admin/api/status.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: config.statusUpdateType || config.type, id, status }),
    });
    rows = rows.map((r) => (sameId(r.id, id) ? { ...r, status } : r));
    if (selected && sameId(selected.id, id)) selected = { ...selected, status };
    render();
    if (selected) openModal(selected);
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

  function filtered() {
    return rows.filter((r) => {
      const matchQ = !search || (config.searchFields || []).some((f) => (r[f] || '').toString().toLowerCase().includes(search));
      const matchF = filter === 'all' || r.status === filter;
      return matchQ && matchF;
    });
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
      const dateCell = `<td style="color:var(--gray-400);font-size:.82rem;white-space:nowrap">${fmtDate(row.created_at, false)}</td>`;
      return `<tr>
        <td style="color:var(--gray-400);font-weight:600">#${row.id}</td>
        ${cells}${statusCell}${dateCell}
        <td><div style="display:flex;gap:6px">
          <button class="adm-action-btn view" title="View" onclick='__admView_${config.type}(${row.id})'><i class="bi bi-eye"></i></button>
          <button class="adm-action-btn del" title="Delete" onclick="__admDel_${config.type}(${row.id})"><i class="bi bi-trash"></i></button>
        </div></td>
      </tr>`;
    }).join('');
  }

  // Accepts a row object or a row id (the table's View button passes the id).
  function openModal(rowOrId) {
    const row = typeof rowOrId === 'object' && rowOrId !== null
      ? rowOrId
      : rows.find((r) => sameId(r.id, rowOrId));
    if (!row) return;
    selected = row;
    const fields = config.detailFields.map((f) => `
      <div class="adm-modal-row"><label>${esc(f.label)}</label><span>${f.render(row, esc)}</span></div>
    `).join('');
    const statusButtons = config.statuses ? `
      <div class="adm-modal-actions">
        ${config.statuses.map((s) => `<button class="btn btn-sm ${row.status === s ? 'btn-primary' : 'btn-outline'}" style="font-size:.82rem" onclick="__admStatus_${config.type}(${row.id}, '${s}')">${cap(s)}</button>`).join('')}
        ${config.resumeDownload && row.resume_filename ? `<a class="btn btn-sm btn-outline" style="margin-left:auto;font-size:.82rem" href="/admin/api/resume-download.php?id=${row.id}" target="_blank"><i class="bi bi-download"></i> Resume</a>` : `<button class="btn btn-sm" style="background:#fef2f2;color:#dc2626;margin-left:auto" onclick="__admDel_${config.type}(${row.id})"><i class="bi bi-trash"></i> Delete</button>`}
      </div>` : '';

    modalBody.innerHTML = `
      <h3>${config.detailTitle(row, esc)}</h3>
      <div class="adm-modal-date">${fmtDate(row.created_at, true)}</div>
      <div class="adm-modal-grid">${fields}</div>
      ${config.detailExtra ? config.detailExtra(row, esc) : ''}
      ${statusButtons}
    `;
    modalOverlay.hidden = false;
  }
  window['__admView_' + config.type] = openModal;

  rows.__loading = true;
  render();
  fetch('/admin/api/list.php?type=' + encodeURIComponent(config.type), { credentials: 'include' })
    .then((r) => r.json())
    .then((data) => { rows = Array.isArray(data) ? data : []; render(); })
    .catch(() => { rows = []; render(); });
}
