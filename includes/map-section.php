<?php
/**
 * "Visit Our Office" — address, phones, working hours and the Google map.
 * Shown on the home page. Address/phones come from Admin → Settings → Company.
 */
$mapAddress = setting('company_address', $brand['address']);
$mapPhones = array_values(array_filter([
    setting('company_phone1', $brand['phones'][0] ?? ''),
    setting('company_phone2', $brand['phones'][1] ?? ''),
]));
?>
<section class="section office-sec">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= h(t('home.mapTag')) ?></span>
      <h2><?= h(t('home.mapTitle')) ?></h2>
      <p><?= h(t('home.mapSub')) ?></p>
    </div>
    <div class="office-grid">
      <div class="office-info">
        <div class="office-row">
          <span class="office-label"><?= h(t('contact.officeAddress')) ?></span>
          <p><?= h($mapAddress) ?></p>
        </div>
        <?php if ($mapPhones): ?>
          <div class="office-row">
            <span class="office-label"><?= h(t('contact.phone')) ?></span>
            <p>
              <?php foreach ($mapPhones as $i => $ph): ?>
                <?= $i ? ' / ' : '' ?><a href="tel:<?= h(preg_replace('/[-\s]/', '', $ph)) ?>"><?= h($ph) ?></a>
              <?php endforeach; ?>
            </p>
          </div>
        <?php endif; ?>
        <div class="office-row">
          <span class="office-label"><?= h(t('contact.hoursTitle')) ?></span>
          <p><?= h(t('contact.hoursWeek')) ?> <strong><?= h(t('contact.hoursWeekVal')) ?></strong><br>
             <?= h(t('contact.hoursSun')) ?> <strong><?= h(t('contact.hoursSunVal')) ?></strong></p>
        </div>
        <a class="btn btn-outline" href="<?= h($mapLink) ?>" target="_blank" rel="noopener">
          <?= h(t('home.getDirections')) ?> <i class="bi bi-arrow-up-right"></i>
        </a>
      </div>
      <div class="office-map">
        <iframe title="WePower Office Location" src="<?= h($mapEmbed) ?>"
          width="100%" height="100%" style="border:0;display:block" allowfullscreen loading="lazy"
          referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
    </div>
  </div>
</section>
