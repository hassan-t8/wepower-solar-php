<?php
$adminPageTitle = 'Applications';
$adminActive = 'applications';
require_once __DIR__ . '/includes/admin-header.php';

$listTitle = 'Applications';
$listSubtitle = 'Solar quote & service applications from your website.';
$listSearchPlaceholder = 'Search name, email…';
$listHeaderCols = ['Name', 'Phone', 'Email', 'Service', 'City', 'Property'];
require __DIR__ . '/includes/list-page.php';
?>
<script src="/assets/js/admin/list-table.js"></script>
<script>
initAdminList({
  type: 'applications',
  searchFields: ['name', 'email', 'phone'],
  statuses: ['new', 'read', 'done'],
  columns: [
    { render: (r, esc) => `<span style="font-weight:600;white-space:nowrap">${esc(r.name)}</span>` },
    { render: (r, esc) => `<span style="white-space:nowrap">${esc(r.phone)}</span>` },
    { render: (r, esc) => `<span style="color:var(--gray-500);font-size:.85rem">${esc(r.email)}</span>` },
    { render: (r, esc) => esc(r.service_type || '—') },
    { render: (r, esc) => esc(r.city || '—') },
    { render: (r, esc) => esc(r.property_type || '—') },
  ],
  detailTitle: (r) => `Application #${r.id}`,
  detailFields: [
    { label: 'Name', render: (r, esc) => esc(r.name) },
    { label: 'Phone', render: (r, esc) => esc(r.phone) },
    { label: 'Email', render: (r, esc) => esc(r.email) },
    { label: 'City', render: (r, esc) => esc(r.city || '—') },
    { label: 'Service', render: (r, esc) => esc(r.service_type || '—') },
    { label: 'Property', render: (r, esc) => esc(r.property_type || '—') },
  ],
  detailExtra: (r, esc) => r.notes ? `<div class="adm-modal-row" style="margin-top:8px"><label>Notes</label><span style="white-space:pre-wrap">${esc(r.notes)}</span></div>` : '',
});
</script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
