<?php
/**
 * Closes </main>, renders the footer, the global "Get a Quote" modal,
 * WhatsApp button, promo video popup, then the JS bundle.
 * $pageScripts (set before including header.php) lists any extra
 * page-specific <script> files to load (e.g. load-calculator.js).
 */

$address    = setting('company_address', $brand['address']);
$phone1     = setting('company_phone1', $brand['phones'][0]);
$phone2     = setting('company_phone2', $brand['phones'][1]);
$email      = setting('company_email');
$instagram  = setting('company_instagram', $brand['instagram']);
$fbUrl      = setting('social_facebook');
$igUrl      = setting('social_instagram');
$liUrl      = setting('social_linkedin');
$ytUrl      = setting('social_youtube');
$whatsapp   = setting('company_whatsapp');
$whatsappMsg = setting('whatsapp_message');
$promoUrl   = setting('promo_video_url');
$promoOn    = setting('promo_video_enabled') === 'true';
$year       = date('Y');

// JSON-LD Organization schema — helps Google understand this is a real
// business (name, logo, address, phone, social profiles) for local search
// and rich results. Rendered once per page (every public page includes
// this file), which is enough — it doesn't need to vary per route.
$schemaSameAs = array_values(array_filter([$liUrl, $fbUrl, $igUrl, $ytUrl]));
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $companyName,
    'url' => $siteUrl,
    'logo' => $siteUrl . $logo,
    'image' => $siteUrl . $logo,
    'telephone' => $phone1,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $address,
        'addressCountry' => 'PK',
    ],
];
if ($email) $schema['email'] = $email;
if ($schemaSameAs) $schema['sameAs'] = $schemaSameAs;
?>
</main>

<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<footer class="ftr">
  <div class="container ftr-grid">
    <div class="ftr-brand">
      <img src="<?= h($logoDisplay) ?>" alt="<?= h($companyName) ?>" class="ftr-logo">
      <p><?= h($companyName) ?> — <?= h(t('footer.about')) ?></p>
      <div class="ftr-social">
        <?php if ($liUrl): ?><a href="<?= h($liUrl) ?>" target="_blank" rel="noreferrer" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a><?php endif; ?>
        <?php if ($fbUrl): ?><a href="<?= h($fbUrl) ?>" target="_blank" rel="noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a><?php endif; ?>
        <?php if ($igUrl): ?><a href="<?= h($igUrl) ?>" target="_blank" rel="noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a><?php endif; ?>
        <?php if ($ytUrl): ?><a href="<?= h($ytUrl) ?>" target="_blank" rel="noreferrer" aria-label="YouTube"><i class="bi bi-youtube"></i></a><?php endif; ?>
      </div>
    </div>

    <div class="ftr-col">
      <h5><?= h(t('footer.quickLinks')) ?></h5>
      <ul><?php foreach ($nav as $n): ?><li><a href="<?= h($n['to']) ?>"><?= h($n['label']) ?></a></li><?php endforeach; ?></ul>
    </div>

    <div class="ftr-col">
      <h5><?= h(t('footer.services')) ?></h5>
      <ul><?php foreach (array_slice($services, 0, 5) as $s): ?><li><a href="/services#<?= h($s['id']) ?>"><?= h($s['title']) ?></a></li><?php endforeach; ?></ul>
    </div>

    <div class="ftr-col">
      <h5><?= h(t('footer.getInTouch')) ?></h5>
      <div class="ftr-contact"><i class="bi bi-geo-alt-fill"></i><span><?= h($address) ?></span></div>
      <div class="ftr-contact">
        <i class="bi bi-telephone-fill"></i>
        <span>
          <?php if ($phone1): ?><a href="tel:<?= h(preg_replace('/[-\s]/', '', $phone1)) ?>"><?= h($phone1) ?></a><?php endif; ?>
          <?php if ($phone2): ?> / <a href="tel:<?= h(preg_replace('/[-\s]/', '', $phone2)) ?>"><?= h($phone2) ?></a><?php endif; ?>
        </span>
      </div>
      <?php if ($email): ?><div class="ftr-contact"><i class="bi bi-envelope-fill"></i><span><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></span></div><?php endif; ?>
      <?php if ($instagram): ?><div class="ftr-contact"><i class="bi bi-instagram"></i><span><?= h($instagram) ?></span></div><?php endif; ?>
    </div>
  </div>
  <div class="ftr-bottom">&copy; <?= h($year) ?> <?= h($companyName) ?>. <?= h(t('footer.rights')) ?></div>
</footer>

<?php include __DIR__ . '/modal-apply.php'; ?>

<?php if ($whatsapp):
  $digits = preg_replace('/\D/', '', $whatsapp);
  $waNum = str_starts_with($digits, '0') ? '92' . substr($digits, 1) : $digits;
  $waText = $whatsappMsg ?: "Hi WePower! I'm interested in solar solutions. Can you help me?";
?>
<a href="https://wa.me/<?= h($waNum) ?>?text=<?= h(rawurlencode($waText)) ?>" target="_blank" rel="noreferrer" class="wa-btn" aria-label="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
  <span class="wa-tip">Chat on WhatsApp</span>
</a>
<?php endif; ?>

<?php
// Promo video popup — only render the shell if a video is actually configured/enabled.
if ($promoOn && $promoUrl && preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\s]+)/', $promoUrl, $m)):
  $videoId = $m[1];
  $channelUrl = $ytUrl ?: 'https://www.youtube.com/@wepowersolar';
?>
<div class="vp-overlay" id="vpOverlay" hidden>
  <div class="vp-modal">
    <div class="vp-header">
      <span class="vp-title"><i class="bi bi-play-circle-fill"></i> WePower Solar</span>
      <div class="vp-actions">
        <button type="button" class="vp-icon-btn" id="vpMuteBtn" title="Unmute"><i class="bi bi-volume-mute-fill"></i></button>
        <a href="<?= h($channelUrl) ?>" target="_blank" rel="noreferrer" class="vp-subscribe"><i class="bi bi-youtube"></i><span>Subscribe</span></a>
        <button type="button" class="vp-icon-btn" id="vpCloseBtn" title="Close"><i class="bi bi-x-lg"></i></button>
      </div>
    </div>
    <div class="vp-video-wrap" id="vpVideoWrap" data-video-id="<?= h($videoId) ?>">
      <div class="vp-loader" id="vpLoader"><div class="vp-spinner"></div></div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  window.WP_I18N = {
    saving: <?= json_encode(t('common.saving')) ?>,
    savedMsg: <?= json_encode(t('calc.savedMsg')) ?>,
    applySuccessBody: <?= json_encode(t('apply.successBody')) ?>,
    applyThereFallback: <?= json_encode($LANG_CODE === 'ur' ? 'وہاں' : 'there') ?>,
    careersRequiredErr: <?= json_encode(t('careersModal.requiredErr')) ?>,
<?php foreach (t('validation') as $vKey => $vMsg): ?>
    <?= $vKey ?>: <?= json_encode($vMsg) ?>,
<?php endforeach; ?>
  };
</script>
<script src="<?= h(asset('/assets/js/toast.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/main.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/form-validation.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/apply-modal.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/lang-switcher.js')) ?>"></script>
<script src="<?= h(asset('/assets/js/lang-hint.js')) ?>"></script>
<?php foreach ($pageScripts as $script): ?>
<script src="<?= h(asset('/assets/js/' . $script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
