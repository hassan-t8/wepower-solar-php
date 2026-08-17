<?php
$pageTitle = 'WePower Solar Solutions — Empower Yourself with Solar Energy';
$pageDescription = "Pakistan's trusted solar EPC partner — residential, commercial & industrial solar installation, net metering, and O&M services. Free site survey & tailored proposals.";
$headerVariant = 'light';
$bodyClass = 't1';
$pageStyles = ['home.css'];
$pageScripts = ['faq.js', 'counter.js'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="t1-hero">
  <div class="t1-mesh"></div>
  <div class="t1-orb"></div>
  <div class="container t1-hero-grid">
    <div class="t1-hero-content">
      <span class="tag"><?= h(t('home.badge')) ?></span>
      <h1 class="display"><?= h(t('home.heroTitlePre')) ?> <span class="t1-grad"><?= h(t('home.heroTitleGrad')) ?></span></h1>
      <p class="t1-lead"><?= h(t('home.heroLead')) ?></p>
      <div class="t1-actions">
        <button type="button" class="btn btn-primary btn-lg" data-open-apply-modal>
          <i class="bi bi-lightning-charge-fill"></i> <?= h(t('common.getFreeQuote')) ?>
        </button>
        <a href="/load-calculator.php" class="btn btn-outline btn-lg">
          <i class="bi bi-calculator"></i> <?= h(t('common.calculateLoad')) ?>
        </a>
      </div>
      <div class="t1-hero-stats">
        <?php foreach ($heroStats as $s): ?>
          <div class="t1-hstat">
            <strong class="js-counter" data-value="<?= (int)$s['value'] ?>" data-suffix="<?= h($s['suffix']) ?>">0<?= h($s['suffix']) ?></strong>
            <span><?= h($s['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="t1-hero-visual">
      <img src="/assets/images/hero.png" alt="Solar installation" class="t1-hero-img">
      <div class="t1-float t1-float-1">
        <div class="t1-float-num"><span class="js-counter" data-value="92" data-suffix="%">0%</span></div>
        <div class="t1-float-label"><?= h(t('home.floatSavings')) ?></div>
      </div>
      <div class="t1-float t1-float-2">
        <i class="bi bi-patch-check-fill"></i>
        <div><strong><?= h(t('home.floatBillsTitle')) ?></strong><span><?= h(t('home.floatBillsSub')) ?></span></div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container t1-why">
    <div>
      <span class="tag"><?= h(t('home.whoTag')) ?></span>
      <h2 class="section-title"><?= h(t('home.whoTitle')) ?></h2>
      <p class="muted"><?= h(t('home.whoDesc')) ?></p>
      <div class="t1-feats">
        <?php foreach ($whyFeatures as $f): ?>
          <div class="t1-feat">
            <div class="t1-feat-ico"><i class="bi <?= h($f['icon']) ?>"></i></div>
            <div><h4><?= h($f['title']) ?></h4><p><?= h($f['desc']) ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
      <a href="/about.php" class="btn btn-primary"><?= h(t('common.learnMore')) ?> <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="t1-why-img">
      <img src="/assets/images/who-we-are-solar.png" alt="Home solar">
      <div class="t1-why-badge"><i class="bi bi-sun-fill"></i> <?= h(t('home.whoImgCaption')) ?></div>
    </div>
  </div>
</section>

<section class="section t1-services-sec">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('home.servicesTag')) ?></span>
      <h2><?= h(t('home.servicesTitle')) ?></h2>
      <p><?= h(t('home.servicesSub')) ?></p>
    </div>
    <div class="t1-cards">
      <?php foreach ($services as $s): ?>
        <div class="t1-card" id="<?= h($s['id']) ?>">
          <div class="t1-card-img" style="background-image:url('<?= h($s['image']) ?>')">
            <div class="t1-card-ico"><i class="bi <?= h($s['icon']) ?>"></i></div>
          </div>
          <div class="t1-card-body">
            <h3><?= h($s['title']) ?></h3>
            <p><?= h($s['desc']) ?></p>
            <button type="button" class="t1-apply" data-open-apply-modal data-service="<?= h($s['title']) ?>">
              <?= h(t('common.applyNow')) ?> <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="t1-stats">
  <div class="container t1-stats-grid">
    <?php foreach ($stats as $s): ?>
      <div class="t1-stat">
        <div class="t1-stat-num"><span class="js-counter" data-value="<?= (int)$s['value'] ?>" data-suffix="<?= h($s['suffix']) ?>">0<?= h($s['suffix']) ?></span></div>
        <div class="t1-stat-label"><?= h($s['label']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('home.processTag')) ?></span>
      <h2><?= h(t('home.processTitle')) ?></h2>
    </div>
    <div class="t1-process">
      <?php foreach ($process as $p): ?>
        <div class="t1-step">
          <div class="t1-step-num"><?= (int)$p['n'] ?></div>
          <h4><?= h($p['title']) ?></h4><p><?= h($p['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section t1-services-sec">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('home.productsTag')) ?></span>
      <h2><?= h(t('home.productsTitle')) ?></h2>
    </div>
    <div class="t1-products">
      <?php foreach ($products as $p): ?>
        <div class="t1-product">
          <div class="t1-product-ico"><i class="bi <?= h($p['icon']) ?>"></i></div>
          <h4><?= h($p['title']) ?></h4><p><?= h($p['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/faq-section.php'; ?>

<section class="t1-cta">
  <div class="container">
    <div class="t1-cta-box">
      <h2><?= h(t('home.ctaTitle')) ?></h2>
      <p><?= h(t('home.ctaSub')) ?></p>
      <div class="t1-actions" style="justify-content:center">
        <button type="button" class="btn btn-white btn-lg" data-open-apply-modal><i class="bi bi-send"></i> <?= h(t('common.applyNow')) ?></button>
        <a href="/contact.php" class="btn btn-ghost-light btn-lg"><i class="bi bi-telephone"></i> <?= h(t('common.talkToExpert')) ?></a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
