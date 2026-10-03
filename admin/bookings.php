<?php
$adminPageTitle = 'Bookings';
$adminActive = 'bookings';
require_once __DIR__ . '/includes/admin-header.php';

$listTitle = 'Bookings';
$listSubtitle = 'Consultation and site survey booking requests.';
$listSearchPlaceholder = 'Search name, phone, email, city…';
$listHeaderCols = ['Name', 'Phone', 'Service', 'City', 'Preferred Date'];
require __DIR__ . '/includes/list-page.php';
?>
<script src="<?= h(asset('/assets/js/admin/list-table.js')) ?>"></script>
<script>
initAdminList({
  type: 'bookings',
  searchFields: ['name', 'email', 'phone'],
  statuses: ['pending', 'confirmed', 'cancelled', 'done'],
  columns: [
    { render: (r, esc) => `<span style="font-weight:600;white-space:nowrap">${esc(r.name)}</span>` },
    { render: (r, esc) => esc(r.phone) },
    { render: (r, esc) => esc(r.service_type || '—') },
    { render: (r, esc) => esc(r.city || '—') },
    { render: (r, esc) => `<span style="white-space:nowrap">${esc(r.preferred_date || '—')}</span>` },
  ],
  detailTitle: (r) => `Booking #${r.id}`,
  detailFields: [
    { label: 'Name', render: (r, esc) => esc(r.name) },
    { label: 'Phone', render: (r, esc) => esc(r.phone) },
    { label: 'Email', render: (r, esc) => esc(r.email || '—') },
    { label: 'City', render: (r, esc) => esc(r.city || '—') },
    { label: 'Service', render: (r, esc) => esc(r.service_type || '—') },
    { label: 'Property', render: (r, esc) => esc(r.property_type || '—') },
    { label: 'Preferred Date', render: (r, esc) => esc(r.preferred_date || '—') },
    { label: 'Status', render: (r, esc) => esc(r.status) },
  ],
  detailExtra: (r, esc) => r.notes ? `<div class="adm-modal-row" style="margin-top:8px"><label>Notes</label><span style="white-space:pre-wrap">${esc(r.notes)}</span></div>` : '',
});
</script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
