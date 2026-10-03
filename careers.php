<?php
$pageTitle = 'Careers — WePower Solar Solutions';
$pageDescription = "Build Pakistan's solar future with WePower Solar Solutions. View current job openings and submit your application.";
$headerVariant = 'solid';
$pageStyles = ['careers-modal.css'];
$pageScripts = ['careers-modal.js'];
require_once __DIR__ . '/includes/header.php';

// Server-rendered job listing (better for SEO than the original client-side fetch).
$jobs = fetchAll("SELECT id, title, type, dept, location, experience, description FROM jobs WHERE is_active = 1 ORDER BY created_at DESC");

$whyJoin = t('careers.whyItems');
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/"><?= h(t('common.home')) ?></a><i class="bi bi-chevron-right"></i><span><?= h(t('careers.breadcrumb')) ?></span>
    </div>
    <span class="tag"><?= h(t('careers.heroTag')) ?></span>
    <h1 class="display"><?= h(t('careers.heroTitle')) ?></h1>
    <p><?= h(t('careers.heroLead')) ?></p>
  </div>
</section>

<section class="section" style="padding-bottom:60px">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('careers.whyTag')) ?></span>
      <h2><?= h(t('careers.whyTitle')) ?></h2>
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

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('careers.openTag')) ?></span>
      <h2><?= h(t('careers.openTitle')) ?></h2>
      <p><?= h(count($jobs) > 0 ? t('careers.openSubHasJobs') : t('careers.openSubNoJobs')) ?></p>
    </div>

    <div class="job-cards">
      <?php foreach ($jobs as $job): ?>
        <div class="job-card">
          <span class="job-badge <?= h($job['type']) ?>">
            <i class="bi bi-circle-fill" style="font-size:.55rem"></i>
            <?= $job['type'] === 'full' ? h(t('careers.fullTime')) : ($job['type'] === 'part' ? h(t('careers.partTime')) : h($job['type'])) ?>
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
            <i class="bi bi-send"></i> <?= h(t('careers.applyBtn')) ?>
          </button>
        </div>
      <?php endforeach; ?>

      <div class="job-card job-card-general">
        <span class="job-badge full"><i class="bi bi-circle-fill" style="font-size:.55rem"></i><?= h(t('careers.generalOpen')) ?></span>
        <h3><?= h(t('careers.generalTitle')) ?></h3>
        <div class="job-meta">
          <span><i class="bi bi-building"></i><?= h(t('careers.generalAllDept')) ?></span>
          <span><i class="bi bi-geo-alt"></i><?= h(t('careers.generalLocation')) ?></span>
        </div>
        <p><?= h(t('careers.generalDesc')) ?></p>
        <button type="button" class="btn btn-outline btn-sm" id="generalApplyBtn">
          <i class="bi bi-send"></i> <?= h(t('careers.generalApplyBtn')) ?>
        </button>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/modal-careers.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
