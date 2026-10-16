<?php
/**
 * "Brands We Install" — photo card per group (panels / inverters) with the
 * brand logos underneath. Data: $brandGroups in config/site.php.
 */
?>
<section class="section brands-sec">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('home.brandsTag')) ?></span>
      <h2><?= h(t('home.brandsTitle')) ?></h2>
      <p><?= h(t('home.brandsSub')) ?></p>
    </div>
    <div class="brands-grid">
      <?php foreach ($brandGroups as $g):
        $rtl = ($DIR ?? 'ltr') === 'rtl';
        $photo = $rtl && !empty($g['photoRtl']) ? $g['photoRtl'] : $g['photo'];
        $photoPos = $g['photoPos'] ?? '';
        if ($rtl && $photoPos) $photoPos = strtr($photoPos, ['right' => 'left', 'left' => 'right']);
      ?>
        <article class="brand-card">
          <div class="brand-media">
            <img src="<?= h(asset($photo)) ?>"<?= $photoPos ? ' style="object-position:' . h($photoPos) . '"' : '' ?> alt="<?= h(t('home.brands_' . $g['key'])) ?>" loading="lazy" decoding="async">
            <div class="brand-media-txt">
              <h3><?= h(t('home.brands_' . $g['key'])) ?></h3>
              <ul class="brand-specs">
                <?php foreach (explode('·', t('home.brands_' . $g['key'] . 'Note')) as $spec): ?>
                  <li><?= h(trim($spec)) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php if (!empty($g['credit'])): ?>
              <a class="brand-credit" href="<?= h($g['creditUrl']) ?>" target="_blank" rel="noopener"><?= h($g['credit']) ?></a>
            <?php endif; ?>
          </div>
          <ul class="brand-logos">
            <?php foreach ($g['items'] as $b): ?>
              <li title="<?= h($b['name']) ?>"<?= !empty($b['tall']) ? ' class="is-tall"' : '' ?>><img src="<?= h(asset($b['logo'])) ?>" alt="<?= h($b['name']) ?>" loading="lazy" decoding="async"></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
