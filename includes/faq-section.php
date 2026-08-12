<?php
/**
 * Shared FAQ accordion section. Include wherever the FAQ block is needed
 * (home, contact). Content comes from config/site.php's $faq array —
 * centralized here instead of being duplicated per-page like the original.
 * Optional $faqSubtext overrides the default subheading text.
 */
$faqSubtext = $faqSubtext ?? 'Quick answers about solar in Pakistan.';
?>
<section class="section" style="background:var(--gray-50);padding-top:80px;padding-bottom:80px">
  <div class="container" style="max-width:820px">
    <div class="section-head" style="margin-bottom:48px">
      <span class="tag">FAQ</span>
      <h2>Frequently Asked Questions</h2>
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
