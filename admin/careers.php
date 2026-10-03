<?php
$adminPageTitle = 'Career Applications';
$adminActive = 'careers';
require_once __DIR__ . '/includes/admin-header.php';

$listTitle = 'Career Applications';
$listSubtitle = 'Job applications submitted via the Careers page.';
$listSearchPlaceholder = 'Search name, email, position…';
$listHeaderCols = ['Name', 'Phone', 'Position', 'Experience', 'Education', 'Resume'];
require __DIR__ . '/includes/list-page.php';
?>
<script src="<?= h(asset('/assets/js/admin/list-table.js')) ?>"></script>
<script>
initAdminList({
  type: 'careers',
  searchFields: ['full_name', 'email', 'position'],
  statuses: ['new', 'read', 'shortlisted', 'hired', 'rejected'],
  resumeDownload: true,
  columns: [
    { render: (r, esc) => `<span style="font-weight:600;white-space:nowrap">${esc(r.full_name)}</span>` },
    { render: (r, esc) => esc(r.phone) },
    { render: (r, esc) => `<span style="white-space:nowrap">${esc(r.position)}</span>` },
    { render: (r, esc) => r.experience_years ? esc(r.experience_years) + ' yr' : '—' },
    { render: (r, esc) => `<span style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block">${esc(r.education || '—')}</span>` },
    { render: (r) => r.resume_filename
        ? `<a class="adm-action-btn dl" href="/admin/api/resume-download.php?id=${r.id}" target="_blank" title="Download Resume"><i class="bi bi-file-earmark-arrow-down"></i></a>`
        : `<span style="color:var(--gray-300);font-size:.82rem">—</span>` },
  ],
  detailTitle: (r, esc) => esc(r.full_name),
  detailFields: [
    { label: 'Phone', render: (r, esc) => esc(r.phone) },
    { label: 'Email', render: (r, esc) => esc(r.email) },
    { label: 'Position', render: (r, esc) => esc(r.position) },
    { label: 'Experience', render: (r, esc) => r.experience_years ? esc(r.experience_years) + ' years' : '—' },
    { label: 'Education', render: (r, esc) => esc(r.education || '—') },
    { label: 'Status', render: (r, esc) => esc(r.status) },
  ],
  detailExtra: (r, esc) => r.cover_letter ? `<div class="adm-modal-row" style="margin-top:8px"><label>Cover Letter</label><span style="white-space:pre-wrap;line-height:1.65">${esc(r.cover_letter)}</span></div>` : '',
});
</script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
