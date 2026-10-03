<?php
$pageTitle = 'Our Services — WePower Solar Solutions';
$pageDescription = 'Complete solar solutions end-to-end: residential, commercial & industrial, hybrid/off-grid systems, net metering, O&M, and site survey & design.';
$headerVariant = 'solid';
require_once __DIR__ . '/includes/header.php';
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/index.php"><?= h(t('common.home')) ?></a><i class="bi bi-chevron-right"></i><span><?= h(t('services.breadcrumb')) ?></span>
    </div>
    <span class="tag"><?= h(t('services.heroTag')) ?></span>
    <h1 class="display"><?= h(t('services.heroTitle')) ?></h1>
    <p><?= h(t('services.heroLead')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php foreach ($services as $s): ?>
      <div class="svc-detail" id="<?= h($s['id']) ?>">
        <div class="svc-detail-img">
          <img src="<?= h($s['image']) ?>" alt="<?= h($s['title']) ?>">
        </div>
        <div class="svc-detail-txt">
          <span class="tag"><i class="bi <?= h($s['icon']) ?>"></i> <?= h($s['title']) ?></span>
          <h2><?= h($s['title']) ?></h2>
          <p><?= h($s['desc']) ?></p>
          <?php if (!empty($s['points'])): ?>
            <ul class="svc-points">
              <?php foreach ($s['points'] as $pt): ?>
                <li><i class="bi bi-check-circle-fill"></i><?= h($pt) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <button type="button" class="btn btn-primary" data-open-apply-modal data-service="<?= h($s['title']) ?>">
            <i class="bi bi-send"></i> <?= h(t('services.applyFor')) ?> <?= h($s['title']) ?>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('services.processTag')) ?></span>
      <h2><?= h(t('services.processTitle')) ?></h2>
      <p><?= h(t('services.processSub')) ?></p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px">
      <?php foreach ($process as $p): ?>
        <div style="background:#fff;border-radius:var(--radius);padding:24px 20px;border:1px solid var(--gray-200);box-shadow:var(--shadow-md)">
          <div style="width:40px;height:40px;border-radius:50%;background:var(--green-600);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;margin-bottom:14px">
            <?= (int)$p['n'] ?>
          </div>
          <h4 style="margin-bottom:6px"><?= h($p['title']) ?></h4>
          <p style="color:var(--gray-500);font-size:.88rem"><?= h($p['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('services.productsTag')) ?></span>
      <h2><?= h(t('services.productsTitle')) ?></h2>
    </div>
    <div class="val-cards">
      <?php foreach ($products as $p): ?>
        <div class="val-card">
          <div class="val-card-ico"><i class="bi <?= h($p['icon']) ?>"></i></div>
          <h4><?= h($p['title']) ?></h4>
          <p><?= h($p['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="background:linear-gradient(135deg,var(--dark),var(--dark-2));padding:80px 0">
  <div class="container" style="text-align:center">
    <h2 style="color:#fff;margin-bottom:12px"><?= h(t('services.ctaTitle')) ?></h2>
    <p style="color:rgba(255,255,255,.75);max-width:500px;margin:0 auto 32px"><?= h(t('services.ctaSub')) ?></p>
    <button type="button" class="btn btn-primary btn-lg" data-open-apply-modal>
      <i class="bi bi-lightning-charge-fill"></i> <?= h(t('common.getFreeQuote')) ?>
    </button>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
