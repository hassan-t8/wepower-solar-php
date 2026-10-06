<?php
/**
 * Admin panel shared chrome — sidebar + topbar. Direct PHP port of
 * AdminLayout.jsx. Every admin/*.php page (except login.php) sets
 * $adminPageTitle before including this, then requireAdminPage() guards it.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/settings.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminPage();

$admin = currentAdmin();
$adminPageTitle = $adminPageTitle ?? 'Admin';
$adminActive = $adminActive ?? '';

// Sidebar "new/pending" badge counts — same queries AdminLayout.jsx pulled from /admin/api/stats.
$badgeApplications = countRows("SELECT COUNT(*) c FROM applications WHERE status = 'new'");
$badgeContacts = countRows("SELECT COUNT(*) c FROM contacts WHERE status = 'new'");
$badgeBookings = countRows("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'");
$badgeCareers = countRows("SELECT COUNT(*) c FROM careers WHERE status = 'new'");

$navMain = [['to' => 'dashboard', 'key' => 'dashboard', 'icon' => 'bi-grid-1x2', 'label' => 'Dashboard']];
$navLeads = [
    ['to' => 'applications', 'key' => 'applications', 'icon' => 'bi-lightning-charge', 'label' => 'Applications', 'badge' => $badgeApplications, 'badgeKey' => 'applications'],
    ['to' => 'contacts', 'key' => 'contacts', 'icon' => 'bi-envelope', 'label' => 'Contacts', 'badge' => $badgeContacts, 'badgeKey' => 'contacts'],
    ['to' => 'bookings', 'key' => 'bookings', 'icon' => 'bi-calendar-check', 'label' => 'Bookings', 'badge' => $badgeBookings, 'badgeRed' => true, 'badgeKey' => 'bookings'],
    ['to' => 'careers', 'key' => 'careers', 'icon' => 'bi-briefcase', 'label' => 'Careers', 'badge' => $badgeCareers, 'badgeKey' => 'careers'],
    ['to' => 'jobs', 'key' => 'jobs', 'icon' => 'bi-person-lines-fill', 'label' => 'Job Postings'],
    ['to' => 'calculations', 'key' => 'calculations', 'icon' => 'bi-calculator', 'label' => 'Calculations'],
];
$navSystem = [
    ['to' => 'notifications', 'key' => 'notifications', 'icon' => 'bi-bell', 'label' => 'Notifications'],
    ['to' => 'settings', 'key' => 'settings', 'icon' => 'bi-gear', 'label' => 'Settings'],
];

function admLink(array $n, string $active): void {
    $isActive = $n['key'] === $active;
    echo '<a href="' . h($n['to']) . '" class="adm-link ' . ($isActive ? 'active' : '') . '">';
    echo '<i class="bi ' . h($n['icon']) . '"></i>' . h($n['label']);
    // Badges with a badgeKey are always rendered (hidden at 0) so the live updater can change them.
    if (!empty($n['badgeKey'])) {
        echo '<span class="badge ' . (!empty($n['badgeRed']) ? 'badge-red' : '') . '" data-badge="' . h($n['badgeKey']) . '"'
            . (empty($n['badge']) ? ' hidden' : '') . '>' . (int)$n['badge'] . '</span>';
    }
    echo '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($adminPageTitle) ?> — WePower Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= h(asset('/assets/css/global.css')) ?>">
<link rel="stylesheet" href="<?= h(asset('/assets/css/admin.css')) ?>">
<link rel="stylesheet" href="<?= h(asset('/assets/css/toast.css')) ?>">
<script>
  // Push setup for this browser (push-register.js); public values only.
  window.ADM_PUSH = <?= json_encode(['configured' => isPushConfigured(), 'canRegister' => canRegisterPush(), 'firebase' => firebaseWebConfig()], JSON_UNESCAPED_SLASHES) ?>;
</script>
</head>
<body>

<div class="adm-wrap">
  <div class="adm-overlay" id="admOverlay"></div>

  <aside class="adm-sidebar" id="admSidebar">
    <div class="adm-logo">
      <img src="<?= h(asset('/assets/images/logo-wordmark.png')) ?>" alt="WePower">
      <div class="adm-logo-text">
        <strong>Admin Panel</strong>
        <span>Management</span>
      </div>
    </div>

    <nav class="adm-nav">
      <div class="adm-nav-section">Main</div>
      <?php foreach ($navMain as $n) admLink($n, $adminActive); ?>

      <div class="adm-nav-section">Leads</div>
      <?php foreach ($navLeads as $n) admLink($n, $adminActive); ?>

      <div class="adm-nav-section">System</div>
      <?php foreach ($navSystem as $n) admLink($n, $adminActive); ?>
    </nav>

    <div class="adm-sidebar-footer">
      <div class="adm-avatar"><?= h(strtoupper(substr($admin['email'] ?? 'A', 0, 1))) ?></div>
      <div class="adm-sidebar-user">
        <strong><?= h($admin['name'] ?: 'Administrator') ?></strong>
        <span><?= h($admin['email']) ?></span>
      </div>
      <button class="adm-logout-btn" title="Logout" id="admLogoutBtn"><i class="bi bi-box-arrow-right"></i></button>
    </div>
  </aside>

  <div class="adm-main">
    <header class="adm-topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="adm-topbar-menu" id="admMenuBtn"><i class="bi bi-list"></i></button>
        <div class="adm-topbar-title">WePower Solar — Admin</div>
      </div>
      <div class="adm-topbar-right">
        <div class="adm-bell-wrap">
          <button type="button" class="adm-bell" id="admBellBtn" aria-label="Notifications" aria-expanded="false">
            <i class="bi bi-bell"></i><span class="adm-bell-count" id="admBellCount" hidden>0</span>
          </button>
          <div class="adm-bell-panel" id="admBellPanel" hidden>
            <div class="adm-bell-head">
              <strong>Notifications</strong>
              <span class="adm-live-dot" id="admLiveDot" title="Live updates"><i></i> Live</span>
            </div>
            <div class="adm-bell-list" id="admBellList"><div class="adm-bell-empty">No notifications yet</div></div>
            <a class="adm-bell-foot" href="notifications"><i class="bi bi-sliders"></i> Notification settings</a>
          </div>
        </div>
        <a href="/" target="_blank" rel="noreferrer" class="btn btn-outline btn-sm" style="font-size:.8rem">
          <i class="bi bi-box-arrow-up-right"></i><span class="adm-hide-sm">View Site</span>
        </a>
      </div>
    </header>

    <div class="adm-content">
