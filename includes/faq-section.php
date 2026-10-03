<?php
/**
 * Shared FAQ accordion section. Include wherever the FAQ block is needed
 * (home, contact). Content comes from config/site.php's $faq array —
 * centralized here instead of being duplicated per-page like the original.
 * Optional $faqSubtext overrides the default subheading text.
 */
$faqSubtext = $faqSubtext ?? t('home.faqSub');
?>
<section class="section section-alt">
  <div class="container" style="max-width:820px">
    <div class="section-head" style="margin-bottom:48px">
      <span class="tag">FAQ</span>
      <h2><?= h(t('faq.title')) ?></h2>
      <p><?= h($faqSubtext) ?></p>
    </div>
    <div class="faq-list">
      <?php foreach ($faq as $item): ?>
        <div class="faq-item">
          <button type="button" class="faq-q">
            <span><?= h($item['q']) ?></span>
            <i class="bi bi-plus-lg"></i>
          </button>
          <div class="faq-a" hidden><?= h($item['a']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
