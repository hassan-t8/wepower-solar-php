<?php
$pageTitle = 'Contact Us — WePower Solar Solutions';
$pageDescription = "Have a question or ready to get a quote? Reach WePower Solar Solutions — Pakistan's trusted solar EPC partner. Our team replies within 24 hours.";
$headerVariant = 'solid';
$pageScripts = ['contact-form.js', 'faq.js'];
require_once __DIR__ . '/includes/header.php';

$contactEmail = setting('company_email', 'info@wepower.pk');
$phones = implode(' / ', $brand['phones']);
?>

<section class="pg-hero">
  <div class="container pg-hero-inner">
    <div class="pg-breadcrumb">
      <a href="/index.php"><?= h(t('common.home')) ?></a><i class="bi bi-chevron-right"></i><span><?= h(t('contact.breadcrumb')) ?></span>
    </div>
    <span class="tag"><?= h(t('contact.heroTag')) ?></span>
    <h1 class="display"><?= h(t('contact.heroTitle')) ?></h1>
    <p><?= h(t('contact.heroLead')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid contact-grid">

      <div class="contact-info-col">
        <h3 style="margin-bottom:24px"><?= h(t('contact.infoTitle')) ?></h3>

        <div style="display:flex;gap:16px;align-items:start;margin-bottom:28px">
          <div style="width:46px;height:46px;border-radius:14px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.15rem;flex-shrink:0;border:1px solid var(--green-200)"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px"><?= h(t('contact.officeAddress')) ?></div>
            <div style="color:var(--gray-800);font-weight:500;line-height:1.5"><?= h($brand['address']) ?></div>
          </div>
        </div>

        <div style="display:flex;gap:16px;align-items:start;margin-bottom:28px">
          <div style="width:46px;height:46px;border-radius:14px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.15rem;flex-shrink:0;border:1px solid var(--green-200)"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px"><?= h(t('contact.phone')) ?></div>
            <div style="color:var(--gray-800);font-weight:500;line-height:1.5"><?= h($phones) ?></div>
          </div>
        </div>

        <div style="display:flex;gap:16px;align-items:start;margin-bottom:28px">
          <div style="width:46px;height:46px;border-radius:14px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.15rem;flex-shrink:0;border:1px solid var(--green-200)"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px"><?= h(t('contact.email')) ?></div>
            <div style="color:var(--gray-800);font-weight:500;line-height:1.5"><a href="mailto:<?= h($contactEmail) ?>" style="color:inherit"><?= h($contactEmail) ?></a></div>
          </div>
        </div>

        <div style="display:flex;gap:16px;align-items:start;margin-bottom:28px">
          <div style="width:46px;height:46px;border-radius:14px;background:var(--green-50);display:flex;align-items:center;justify-content:center;color:var(--green-600);font-size:1.15rem;flex-shrink:0;border:1px solid var(--green-200)"><i class="bi bi-instagram"></i></div>
          <div>
            <div style="font-size:.82rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px"><?= h(t('contact.instagram')) ?></div>
            <div style="color:var(--gray-800);font-weight:500;line-height:1.5"><?= h($brand['instagram']) ?></div>
          </div>
        </div>

        <div class="contact-hours" style="background:var(--green-50);border:1px solid var(--green-200);border-radius:var(--radius-lg);padding:22px 24px;margin-top:8px">
          <h4 style="color:var(--green-700);margin-bottom:12px;font-size:1rem"><i class="bi bi-clock" style="margin-right:8px"></i><?= h(t('contact.hoursTitle')) ?></h4>
          <div style="color:var(--gray-700);font-size:.9rem;line-height:2">
            <div><?= h(t('contact.hoursWeek')) ?> <strong><?= h(t('contact.hoursWeekVal')) ?></strong></div>
            <div><?= h(t('contact.hoursSun')) ?> <strong><?= h(t('contact.hoursSunVal')) ?></strong></div>
          </div>
        </div>
      </div>

      <div class="contact-form-col">
        <div class="contact-card">
          <div id="contactFormWrap">
            <form id="contactForm">
              <h3 style="margin-bottom:6px"><?= h(t('contact.formTitle')) ?></h3>
              <p class="muted" style="margin-bottom:24px;font-size:.9rem"><?= h(t('contact.formSub')) ?></p>
              <div class="form-row">
                <div class="field"><label><?= h(t('contact.fullName')) ?></label><input type="text" name="name" required placeholder="<?= h(t('contact.fullNamePh')) ?>"></div>
                <div class="field"><label><?= h(t('contact.phoneLabel')) ?></label><input type="text" name="phone" placeholder="03xx-xxxxxxx"></div>
              </div>
              <div class="field"><label><?= h(t('contact.emailReq')) ?></label><input type="email" name="email" required placeholder="you@email.com"></div>
              <div class="field"><label><?= h(t('contact.messageReq')) ?></label><textarea name="message" required placeholder="<?= h(t('contact.messagePh')) ?>" style="min-height:90px"></textarea></div>

              <div class="form-error" id="contactError" hidden><span></span></div>

              <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="bi bi-send"></i> <?= h(t('contact.sendMessage')) ?>
              </button>
            </form>
          </div>

          <!-- Popup: shown on tablet/mobile only (CSS hides on desktop) -->
          <div class="csm-overlay" id="contactSuccessPopup" hidden>
            <div class="csm-popup">
              <div class="csm-check"><i class="bi bi-check-lg"></i></div>
              <h3><?= h(t('contact.sentTitle')) ?></h3>
              <p><?= h(t('contact.sentBody1')) ?><br><?= h(t('contact.sentBody2')) ?></p>
              <button type="button" class="btn btn-primary btn-block" id="contactBackBtn"><i class="bi bi-arrow-left"></i> <?= h(t('contact.backToContact')) ?></button>
            </div>
          </div>

          <!-- Inline: shown on desktop only (CSS hides on mobile) -->
          <div class="csm-inline" id="contactSuccessInline" hidden>
            <div style="width:72px;height:72px;border-radius:50%;background:var(--green-500);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 20px"><i class="bi bi-check-lg"></i></div>
            <h3 style="margin-bottom:10px"><?= h(t('contact.sentTitle')) ?></h3>
            <p class="muted" style="margin-bottom:24px"><?= h(t('contact.sentBody1')) ?> <?= h(t('contact.sentBody2')) ?></p>
            <button type="button" class="btn btn-outline btn-block contact-success-btn" id="contactAnotherBtn"><?= h(t('contact.sendAnother')) ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $faqSubtext = t('contact.faqSub'); include __DIR__ . '/includes/faq-section.php'; ?>

<section style="padding-bottom:80px">
  <div class="container">
    <div style="border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);border:1px solid var(--gray-200)">
      <iframe title="WePower Office Location"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3322.4793!2d73.089!3d33.5213!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sBahria+Town+Phase+7!5e0!3m2!1sen!2s!4v1000000000000"
        width="100%" height="380" style="border:0;display:block" allowfullscreen loading="lazy"></iframe>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
