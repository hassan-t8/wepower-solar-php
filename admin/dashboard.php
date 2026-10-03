<?php
$adminPageTitle = 'Dashboard';
$adminActive = 'dashboard';
$adminPageScripts = ['dashboard.js'];
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="margin-bottom:24px">
  <h2 style="font-size:1.35rem;margin-bottom:4px">Dashboard</h2>
  <p style="color:var(--gray-400);font-size:.88rem">Welcome back! Here's what's happening with your website.</p>
</div>

<div id="admLoading" style="padding:60px;text-align:center;color:var(--gray-400)">
  <span class="adm-spinner" style="border-color:var(--gray-300);border-top-color:var(--green-600)"></span>
</div>

<div id="admDashboardContent" hidden>
  <div class="adm-visitor-cards" id="visitorCards"></div>

  <div class="adm-stats" id="statCards"></div>

  <div class="adm-charts">
    <div class="adm-chart-card">
      <div class="adm-chart-title">Visitor Trend</div>
      <div class="adm-chart-sub">Daily visitors — last 14 days</div>
      <canvas id="visitorChart" height="220"></canvas>
    </div>

    <div class="adm-chart-card">
      <div class="adm-chart-title">Top Pages</div>
      <div class="adm-chart-sub">Last 30 days</div>
      <div id="topPagesList" style="display:flex;flex-direction:column;gap:10px;margin-top:8px"></div>
    </div>
  </div>

  <div class="adm-table-card">
    <div class="adm-table-header">
      <div class="adm-table-title">Recent Applications</div>
      <a href="applications.php" class="btn btn-outline btn-sm" style="font-size:.8rem">View All</a>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th>#</th><th>Name</th><th>Service</th><th>City</th><th>Status</th><th>Date</th></tr></thead>
        <tbody id="recentApplicationsBody"></tbody>
      </table>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:20px" id="quickStatsRow"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
