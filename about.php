<?php
$pageTitle = 'About Us — WePower Solar Solutions';
$pageDescription = "Pakistan's trusted solar EPC partner. Learn about WePower Solar Solutions' mission, vision, leadership, and track record of landmark solar installations.";
$headerVariant = 'solid';
$pageScripts = array_merge($pageScripts ?? [], ['faq.js']);
require_once __DIR__ . '/includes/header.php';
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/"><?= h(t('common.home')) ?></a><i class="bi bi-chevron-right"></i><span><?= h(t('about.breadcrumb')) ?></span>
    </div>
    <span class="tag"><?= h(t('about.heroTag')) ?></span>
    <h1 class="display"><?= h(t('about.heroTitle')) ?></h1>
    <p><?= h($company['purpose']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid" style="align-items:center;gap:56px">
      <div>
        <span class="tag"><?= h(t('about.storyTag')) ?></span>
        <h2 class="section-title" style="text-align:<?= $DIR === 'rtl' ? 'right' : 'left' ?>"><?= h(t('about.storyTitle')) ?></h2>
        <p class="muted" style="margin-bottom:20px"><?= h($company['who']) ?></p>
      </div>
      <div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px">
          <?php foreach ($stats as $s): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);border-radius:var(--radius-lg);padding:24px 12px;text-align:center;overflow-wrap:anywhere">
              <div style="font-size:2rem;font-weight:700;color:var(--green-700);line-height:1"><?= (int)$s['value'] ?><?= h($s['suffix']) ?></div>
              <div style="color:var(--gray-600);font-size:.85rem;margin-top:6px"><?= h($s['label']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('about.purposeTag')) ?></span>
      <h2><?= h(t('about.purposeTitle')) ?></h2>
    </div>
    <div class="val-cards">
      <div class="val-card">
        <h3><?= h(t('about.visionTitle')) ?></h3>
        <p><?= h($company['vision']) ?></p>
      </div>
      <div class="val-card">
        <h3><?= h(t('about.missionTitle')) ?></h3>
        <p><?= h($company['mission']) ?></p>
      </div>
      <?php foreach ($company['values'] as $v): ?>
        <div class="val-card">
          <h3><?= h($v['title']) ?></h3>
          <p><?= h($v['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('about.leadershipTag')) ?></span>
      <h2><?= h(t('about.leadershipTitle')) ?></h2>
    </div>
    <div class="ceo-card">
      <div class="ceo-img">
        <img src="<?= h($ceo['image']) ?>" alt="<?= h($ceo['name']) ?>" loading="lazy" decoding="async">
      </div>
      <div>
        <h2 class="ceo-name"><?= h($ceo['name']) ?></h2>
        <div class="ceo-role"><?= h($ceo['role']) ?></div>
        <p class="ceo-bio"><?= h($ceo['bio']) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('about.whyTag')) ?></span>
      <h2><?= h(t('about.whyTitle')) ?></h2>
    </div>
    <div class="val-cards">
      <?php foreach ($whyFeatures as $f): ?>
        <div class="val-card">
          <h3><?= h($f['title']) ?></h3>
          <p><?= h($f['desc']) ?></p>
        </div>
      <?php endforeach; ?>
      <?php foreach ($whyChoose as $w): ?>
        <div class="val-card">
          <h3><?= h(t('about.trackRecord')) ?></h3>
          <p><?= h($w) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid" style="align-items:start;gap:56px">
      <div>
        <span class="tag"><?= h(t('about.portfolioTag')) ?></span>
        <h2 class="section-title" style="text-align:<?= $DIR === 'rtl' ? 'right' : 'left' ?>;margin-bottom:8px"><?= h(t('about.portfolioTitle')) ?></h2>
        <p class="muted" style="margin-bottom:24px"><?= h(t('about.portfolioDesc')) ?></p>
        <ul class="notable-list">
          <?php foreach ($notableProjects as $p): ?>
            <li><i class="bi bi-building-check"></i><?= h($p) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <span class="tag"><?= h(t('about.nextTag')) ?></span>
        <h2 class="section-title" style="text-align:<?= $DIR === 'rtl' ? 'right' : 'left' ?>;margin-bottom:8px"><?= h(t('about.nextTitle')) ?></h2>
        <p class="muted" style="margin-bottom:24px"><?= h(t('about.nextDesc')) ?></p>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php foreach ($roadmap as $r): ?>
            <div class="roadmap-item">
              <div>
                <h3 style="font-size:1rem;margin-bottom:4px"><?= h($r['title']) ?></h3>
                <p style="color:var(--gray-500);font-size:.88rem"><?= h($r['desc']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $faqSubtext = t('contact.faqSub'); include __DIR__ . '/includes/faq-section.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
