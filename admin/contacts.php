<?php
$adminPageTitle = 'Contacts';
$adminActive = 'contacts';
require_once __DIR__ . '/includes/admin-header.php';

$listTitle = 'Contacts';
$listSubtitle = 'Messages submitted via the website contact form.';
$listSearchPlaceholder = 'Search name, email, message…';
$listHeaderCols = ['Name', 'Email', 'Phone', 'Subject'];
require __DIR__ . '/includes/list-page.php';
?>
<script src="<?= h(asset('/assets/js/admin/list-table.js')) ?>"></script>
<script>
initAdminList({
  type: 'contacts',
  searchFields: ['name', 'email', 'subject'],
  statuses: ['new', 'read', 'done'],
  columns: [
    { render: (r, esc) => `<span style="font-weight:600;white-space:nowrap">${esc(r.name)}</span>` },
    { render: (r, esc) => `<span style="color:var(--gray-500);font-size:.85rem">${esc(r.email)}</span>` },
    { render: (r, esc) => esc(r.phone || '—') },
    { render: (r, esc) => esc(r.subject || '—') },
  ],
  detailTitle: (r) => `Contact #${r.id}`,
  detailFields: [
    { label: 'Name', render: (r, esc) => esc(r.name) },
    { label: 'Email', render: (r, esc) => esc(r.email) },
    { label: 'Phone', render: (r, esc) => esc(r.phone || '—') },
    { label: 'Subject', render: (r, esc) => esc(r.subject || '—') },
  ],
  detailExtra: (r, esc) => `<div class="adm-modal-row" style="margin-top:8px"><label>Message</label><span style="white-space:pre-wrap">${esc(r.message)}</span></div>`,
});
</script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
