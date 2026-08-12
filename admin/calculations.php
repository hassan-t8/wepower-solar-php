<?php
$adminPageTitle = 'Load Calculations';
$adminActive = 'calculations';
require_once __DIR__ . '/includes/admin-header.php';

$listTitle = 'Load Calculations';
$listSubtitle = 'Solar load calculations submitted via the calculator page.';
$listSearchPlaceholder = 'Search name, email, city…';
$listHeaderCols = ['Name', 'Phone', 'City', 'Property', 'Total Load (W)', 'Rec. KVA', 'Est. Bill (Rs.)'];
$listNoStatus = true;
require __DIR__ . '/includes/list-page.php';
?>
<script src="/assets/js/admin/list-table.js"></script>
<script>
initAdminList({
  type: 'load_calculations',
  searchFields: ['name', 'email', 'city'],
  columns: [
    { render: (r, esc) => esc(r.name || '—') },
    { render: (r, esc) => esc(r.phone || '—') },
    { render: (r, esc) => esc(r.city || '—') },
    { render: (r, esc) => esc(r.property_type || '—') },
    { render: (r) => `<span style="font-weight:600;color:var(--green-700)">${Number(r.total_load_w || 0).toLocaleString()}</span>` },
    { render: (r) => `<span style="font-weight:700">${r.recommended_kva}</span>` },
    { render: (r) => Number(r.estimated_bill || 0).toLocaleString() },
  ],
  detailTitle: (r) => `Calculation #${r.id}`,
  detailFields: [
    { label: 'Name', render: (r, esc) => esc(r.name || '—') },
    { label: 'Phone', render: (r, esc) => esc(r.phone || '—') },
    { label: 'Email', render: (r, esc) => esc(r.email || '—') },
    { label: 'City', render: (r, esc) => esc(r.city || '—') },
    { label: 'Property', render: (r, esc) => esc(r.property_type || '—') },
    { label: 'People', render: (r, esc) => esc(r.num_people || '—') },
  ],
  detailExtra: (r) => {
    let apps = [];
    try { apps = JSON.parse(r.appliances_json || '[]'); } catch (e) {}
    const metrics = `
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;background:var(--green-50);border-radius:var(--radius);padding:18px;margin-bottom:20px">
        <div style="text-align:center"><div style="font-size:.75rem;color:var(--green-700);font-weight:600;margin-bottom:4px">TOTAL LOAD</div><div style="font-size:1.4rem;font-weight:700">${Number(r.total_load_w || 0).toLocaleString()} W</div></div>
        <div style="text-align:center"><div style="font-size:.75rem;color:var(--green-700);font-weight:600;margin-bottom:4px">REC. SYSTEM</div><div style="font-size:1.4rem;font-weight:700">${r.recommended_kva} KVA</div></div>
        <div style="text-align:center"><div style="font-size:.75rem;color:var(--green-700);font-weight:600;margin-bottom:4px">EST. BILL</div><div style="font-size:1.4rem;font-weight:700">Rs.${Number(r.estimated_bill || 0).toLocaleString()}</div></div>
      </div>`;
    if (!apps.length) return metrics;
    const rows = apps.map((a) => `
      <tr>
        <td style="padding:7px 10px;border-bottom:1px solid var(--gray-100)">${a.name}</td>
        <td style="padding:7px 10px;border-bottom:1px solid var(--gray-100)">${a.quantity}</td>
        <td style="padding:7px 10px;border-bottom:1px solid var(--gray-100)">${a.power}W</td>
        <td style="padding:7px 10px;border-bottom:1px solid var(--gray-100)">${a.hours}h</td>
        <td style="padding:7px 10px;border-bottom:1px solid var(--gray-100);font-weight:600">${Number(a.total || 0).toLocaleString()}W</td>
      </tr>`).join('');
    return metrics + `
      <div>
        <div style="font-weight:700;font-size:.85rem;color:var(--gray-600);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Appliance Breakdown</div>
        <table style="width:100%;border-collapse:collapse;font-size:.83rem">
          <thead><tr style="background:var(--gray-50)">
            <th style="padding:7px 10px;text-align:left;color:var(--gray-500);font-weight:600;border-bottom:1px solid var(--gray-200)">Appliance</th>
            <th style="padding:7px 10px;text-align:left;color:var(--gray-500);font-weight:600;border-bottom:1px solid var(--gray-200)">Qty</th>
            <th style="padding:7px 10px;text-align:left;color:var(--gray-500);font-weight:600;border-bottom:1px solid var(--gray-200)">Watts</th>
            <th style="padding:7px 10px;text-align:left;color:var(--gray-500);font-weight:600;border-bottom:1px solid var(--gray-200)">Hrs/Day</th>
            <th style="padding:7px 10px;text-align:left;color:var(--gray-500);font-weight:600;border-bottom:1px solid var(--gray-200)">Total W</th>
          </tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
  },
});
</script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
