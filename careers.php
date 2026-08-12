<?php
$pageTitle = 'Careers — WePower Solar Solutions';
$pageDescription = "Build Pakistan's solar future with WePower Solar Solutions. View current job openings and submit your application.";
$headerVariant = 'solid';
$pageStyles = ['careers-modal.css'];
$pageScripts = ['careers-modal.js'];
require_once __DIR__ . '/includes/header.php';

// Server-rendered job listing (better for SEO than the original client-side fetch).
$jobs = fetchAll("SELECT id, title, type, dept, location, experience, description FROM jobs WHERE is_active = 1 ORDER BY created_at DESC");

$whyJoin = [
    ['icon' => 'bi-people-fill', 'title' => 'Expert Team', 'desc' => 'Work alongside NUST, FAST, and IST graduates with hands-on field experience in real solar projects.'],
    ['icon' => 'bi-graph-up-arrow', 'title' => 'Fast Growth', 'desc' => 'A company scaling rapidly across Pakistan — grow your skills and career as we grow.'],
    ['icon' => 'bi-lightbulb', 'title' => 'Real Impact', 'desc' => 'Every project you work on powers homes, factories, and businesses with clean energy.'],
    ['icon' => 'bi-shield-check', 'title' => 'Professional Standards', 'desc' => 'We follow international quality and safety standards in everything we do.'],
];
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/index.php">Home</a><i class="bi bi-chevron-right"></i><span>Careers</span>
    </div>
    <span class="tag">Join Our Team</span>
    <h1 class="display">Build Pakistan's Solar Future with Us</h1>
    <p>We're a fast-growing solar EPC company built by engineers, for engineers. Come help power a cleaner Pakistan.</p>
  </div>
</section>

<section class="section" style="padding-bottom:60px">
  <div class="container">
    <div class="section-head">
      <span class="tag">Why WePower</span>
      <h2>Why Work With Us</h2>
    </div>
    <div class="val-cards">
      <?php foreach ($whyJoin as $item): ?>
        <div class="val-card">
          <div class="val-card-ico"><i class="bi <?= h($item['icon']) ?>"></i></div>
          <h4><?= h($item['title']) ?></h4>
          <p><?= h($item['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--gray-50);padding-top:80px;padding-bottom:80px">
  <div class="container">
    <div class="section-head">
      <span class="tag">Open Positions</span>
      <h2>Current Openings</h2>
      <p><?= count($jobs) > 0
          ? "Don't see a match? Submit a general application and we'll keep your CV on file."
          : 'No open positions right now — submit a general application below and we will be in touch.' ?></p>
    </div>

    <div class="job-cards">
      <?php foreach ($jobs as $job): ?>
        <div class="job-card">
          <span class="job-badge <?= h($job['type']) ?>">
            <i class="bi bi-circle-fill" style="font-size:.55rem"></i>
            <?= $job['type'] === 'full' ? 'Full-Time' : ($job['type'] === 'part' ? 'Part-Time' : h($job['type'])) ?>
          </span>
          <h3><?= h($job['title']) ?></h3>
          <div class="job-meta">
            <?php if ($job['dept']): ?><span><i class="bi bi-building"></i><?= h($job['dept']) ?></span><?php endif; ?>
            <?php if ($job['location']): ?><span><i class="bi bi-geo-alt"></i><?= h($job['location']) ?></span><?php endif; ?>
            <?php if ($job['experience']): ?><span><i class="bi bi-briefcase"></i><?= h($job['experience']) ?></span><?php endif; ?>
          </div>
          <?php if ($job['description']): ?><p><?= h($job['description']) ?></p><?php endif; ?>
          <button type="button" class="btn btn-primary btn-sm js-apply-job"
            data-job-title="<?= h($job['title']) ?>"
            data-job-dept="<?= h($job['dept']) ?>"
            data-job-location="<?= h($job['location']) ?>"
            data-job-experience="<?= h($job['experience']) ?>">
            <i class="bi bi-send"></i> Apply Now
          </button>
        </div>
      <?php endforeach; ?>

      <div class="job-card job-card-general">
        <span class="job-badge full"><i class="bi bi-circle-fill" style="font-size:.55rem"></i>Open</span>
        <h3>General Application</h3>
        <div class="job-meta">
          <span><i class="bi bi-building"></i>All Departments</span>
          <span><i class="bi bi-geo-alt"></i>Rawalpindi / Remote</span>
        </div>
        <p>Don't see the right role? Send us your CV anyway — we're always looking for talented people.</p>
        <button type="button" class="btn btn-outline btn-sm" id="generalApplyBtn">
          <i class="bi bi-send"></i> General Apply
        </button>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/modal-careers.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
