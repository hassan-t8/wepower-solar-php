<?php
$pageTitle = 'About Us — WePower Solar Solutions';
$pageDescription = "Pakistan's trusted solar EPC partner. Learn about WePower Solar Solutions' mission, vision, leadership, and track record of landmark solar installations.";
$headerVariant = 'solid';
require_once __DIR__ . '/includes/header.php';
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/index.php">Home</a><i class="bi bi-chevron-right"></i><span>About Us</span>
    </div>
    <span class="tag">Who We Are</span>
    <h1 class="display">Pakistan's Trusted Solar EPC Partner</h1>
    <p><?= h($company['purpose']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid" style="align-items:center;gap:56px">
      <div>
        <span class="tag">Our Story</span>
        <h2 class="section-title" style="text-align:left"><?= h('Engineering Excellence in Solar') ?></h2>
        <p class="muted" style="margin-bottom:20px"><?= h($company['who']) ?></p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <button type="button" class="btn btn-primary" data-open-apply-modal><i class="bi bi-lightning-charge-fill"></i> Get a Free Quote</button>
          <a href="/contact.php" class="btn btn-outline"><i class="bi bi-telephone"></i> Contact Us</a>
        </div>
      </div>
      <div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <?php foreach ($stats as $s): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);border-radius:var(--radius-lg);padding:24px 20px;text-align:center">
              <div style="font-size:2rem;font-weight:700;color:var(--green-700);line-height:1"><?= (int)$s['value'] ?><?= h($s['suffix']) ?></div>
              <div style="color:var(--gray-600);font-size:.85rem;margin-top:6px"><?= h($s['label']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" style="background:var(--gray-50);padding-top:80px;padding-bottom:80px">
  <div class="container">
    <div class="section-head">
      <span class="tag">Purpose &amp; Direction</span>
      <h2>Vision, Mission &amp; Values</h2>
    </div>
    <div class="val-cards">
      <div class="val-card">
        <div class="val-card-ico"><i class="bi bi-eye"></i></div>
        <h4>Our Vision</h4>
        <p><?= h($company['vision']) ?></p>
      </div>
      <div class="val-card">
        <div class="val-card-ico"><i class="bi bi-bullseye"></i></div>
        <h4>Our Mission</h4>
        <p><?= h($company['mission']) ?></p>
      </div>
      <?php foreach ($company['values'] as $v): ?>
        <div class="val-card">
          <div class="val-card-ico"><i class="bi bi-award"></i></div>
          <h4><?= h($v['title']) ?></h4>
          <p><?= h($v['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="tag">Leadership</span>
      <h2>Meet Our Founder</h2>
    </div>
    <div class="ceo-card">
      <div class="ceo-img">
        <img src="<?= h($ceo['image']) ?>" alt="<?= h($ceo['name']) ?>">
      </div>
      <div>
        <h2 class="ceo-name"><?= h($ceo['name']) ?></h2>
        <div class="ceo-role"><?= h($ceo['role']) ?></div>
        <p class="ceo-bio"><?= h($ceo['bio']) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="background:var(--gray-50);padding-top:80px;padding-bottom:80px">
  <div class="container">
    <div class="section-head">
      <span class="tag">Why WePower</span>
      <h2>Why Clients Choose Us</h2>
    </div>
    <div class="val-cards">
      <?php foreach ($whyFeatures as $f): ?>
        <div class="val-card">
          <div class="val-card-ico"><i class="bi <?= h($f['icon']) ?>"></i></div>
          <h4><?= h($f['title']) ?></h4>
          <p><?= h($f['desc']) ?></p>
        </div>
      <?php endforeach; ?>
      <?php foreach ($whyChoose as $w): ?>
        <div class="val-card">
          <div class="val-card-ico"><i class="bi bi-check-circle"></i></div>
          <h4>Proven Track Record</h4>
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
        <span class="tag">Portfolio</span>
        <h2 class="section-title" style="text-align:left;margin-bottom:8px">Notable Projects</h2>
        <p class="muted" style="margin-bottom:24px">A selection of our landmark installations across Pakistan's residential, commercial, and government sectors.</p>
        <ul class="notable-list">
          <?php foreach ($notableProjects as $p): ?>
            <li><i class="bi bi-building-check"></i><?= h($p) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <span class="tag">What's Next</span>
        <h2 class="section-title" style="text-align:left;margin-bottom:8px">Our Roadmap</h2>
        <p class="muted" style="margin-bottom:24px">Where we're taking WePower next — from advanced storage to large-scale commercial projects.</p>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php foreach ($roadmap as $r): ?>
            <div style="display:flex;gap:16px;align-items:start;padding:16px 20px;background:var(--gray-50);border-radius:var(--radius);border:1px solid var(--gray-200)">
              <div style="width:42px;height:42px;border-radius:12px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.1rem;flex-shrink:0">
                <i class="bi <?= h($r['icon']) ?>"></i>
              </div>
              <div>
                <h4 style="font-size:1rem;margin-bottom:4px"><?= h($r['title']) ?></h4>
                <p style="color:var(--gray-500);font-size:.88rem"><?= h($r['desc']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background:linear-gradient(135deg,var(--green-700),var(--green-600));padding:80px 0">
  <div class="container" style="text-align:center">
    <h2 style="color:#fff;margin-bottom:12px">Ready to Go Solar?</h2>
    <p style="color:rgba(255,255,255,.85);margin-bottom:32px;max-width:520px;margin-left:auto;margin-right:auto">Free site survey and a tailored proposal from our experts. No obligation.</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <button type="button" class="btn btn-white btn-lg" data-open-apply-modal><i class="bi bi-send"></i> Apply Now</button>
      <a href="/contact.php" class="btn btn-ghost-light btn-lg"><i class="bi bi-telephone"></i> Talk to Us</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
