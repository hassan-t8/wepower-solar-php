<?php
$adminPageTitle = 'Settings';
$adminActive = 'settings';
$adminPageScripts = ['settings.js'];
require_once __DIR__ . '/includes/admin-header.php';

$rows = fetchAll('SELECT `key`, value FROM site_settings');
$s = [];
foreach ($rows as $r) $s[$r['key']] = $r['value'];
$smtpPassMasked = !empty($s['smtp_pass']) ? '••••••••' : '';
?>

<h2 style="margin-bottom:6px">Settings</h2>
<p style="color:var(--gray-400);font-size:.88rem;margin-bottom:24px">Manage your website settings, SMTP credentials, and company info.</p>

<div id="settingsAlert" hidden></div>

<div class="adm-settings-tabs">
  <button type="button" class="adm-tab-btn active" data-tab="0">Company</button>
  <button type="button" class="adm-tab-btn" data-tab="1">SMTP / Email</button>
  <button type="button" class="adm-tab-btn" data-tab="2">Site Settings</button>
  <button type="button" class="adm-tab-btn" data-tab="3">Security</button>
</div>

<!-- TAB 0: COMPANY -->
<div class="adm-settings-tab" data-tab-panel="0">
  <div style="background:var(--green-50);border:1px solid var(--green-200);border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.84rem;color:var(--green-800);display:flex;align-items:center;gap:8px">
    <i class="bi bi-info-circle-fill"></i> Company info, logo, and social links update on the website <strong>after visitors refresh the page</strong>.
  </div>

  <div class="adm-settings-card">
    <form class="settings-section-form" data-keys="company_name,company_short,company_tagline,company_address,company_phone1,company_phone2,company_email,company_instagram">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap">
        <h4 style="margin-bottom:0"><i class="bi bi-building" style="margin-right:8px;color:var(--green-600)"></i>Company Information</h4>
        <button type="submit" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-lg"></i> Save Company Info</button>
      </div>
      <div class="adm-settings-grid">
        <div class="field"><label>Company Name</label><input type="text" name="company_name" value="<?= h($s['company_name'] ?? '') ?>"></div>
        <div class="field"><label>Short Name</label><input type="text" name="company_short" value="<?= h($s['company_short'] ?? '') ?>"></div>
        <div class="field" style="grid-column:span 2"><label>Tagline</label><input type="text" name="company_tagline" value="<?= h($s['company_tagline'] ?? '') ?>"></div>
        <div class="field" style="grid-column:span 2"><label>Office Address</label><textarea name="company_address" style="min-height:70px;resize:vertical"><?= h($s['company_address'] ?? '') ?></textarea></div>
        <div class="field"><label>Phone 1</label><input type="text" name="company_phone1" value="<?= h($s['company_phone1'] ?? '') ?>"></div>
        <div class="field"><label>Phone 2</label><input type="text" name="company_phone2" value="<?= h($s['company_phone2'] ?? '') ?>"></div>
        <div class="field"><label>Email Address</label><input type="email" name="company_email" value="<?= h($s['company_email'] ?? '') ?>" placeholder="info@wepower.pk"></div>
        <div class="field"><label>Instagram Handle</label><input type="text" name="company_instagram" value="<?= h($s['company_instagram'] ?? '') ?>"></div>
      </div>
    </form>
  </div>

  <div class="adm-settings-card">
    <form class="settings-section-form" data-keys="social_facebook,social_instagram,social_linkedin,social_youtube">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap">
        <h4 style="margin-bottom:0"><i class="bi bi-share" style="margin-right:8px;color:var(--green-600)"></i>Social Media Links</h4>
        <button type="submit" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-lg"></i> Save Social Links</button>
      </div>
      <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:20px">These links appear in the website footer and contact page.</p>
      <div class="adm-settings-grid">
        <div class="field"><label><i class="bi bi-facebook" style="margin-right:6px;color:#1877f2"></i>Facebook URL</label><input type="text" name="social_facebook" value="<?= h($s['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/..."></div>
        <div class="field"><label><i class="bi bi-instagram" style="margin-right:6px;color:#e1306c"></i>Instagram URL</label><input type="text" name="social_instagram" value="<?= h($s['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/..."></div>
        <div class="field"><label><i class="bi bi-linkedin" style="margin-right:6px;color:#0a66c2"></i>LinkedIn URL</label><input type="text" name="social_linkedin" value="<?= h($s['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/..."></div>
        <div class="field"><label><i class="bi bi-youtube" style="margin-right:6px;color:#ff0000"></i>YouTube URL</label><input type="text" name="social_youtube" value="<?= h($s['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@..."></div>
      </div>
    </form>
  </div>

  <div class="adm-settings-card">
    <form class="settings-section-form" data-keys="company_whatsapp,whatsapp_message">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap">
        <h4 style="margin-bottom:0"><i class="bi bi-whatsapp" style="margin-right:8px;color:var(--green-600)"></i>WhatsApp Chat Button</h4>
        <button type="submit" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-lg"></i> Save WhatsApp</button>
      </div>
      <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:20px">A floating WhatsApp button appears on every public page. Leave number blank to hide it.</p>
      <div class="adm-settings-grid">
        <div class="field">
          <label>WhatsApp Number <span style="color:var(--gray-400);font-weight:400">(international format)</span></label>
          <input type="text" name="company_whatsapp" value="<?= h($s['company_whatsapp'] ?? '') ?>" placeholder="03355777898 or 923355777898">
          <small style="color:var(--gray-400);font-size:.8rem;margin-top:4px;display:block">Pakistani numbers: start with 03… or 923…</small>
        </div>
        <div class="field">
          <label>Pre-filled Message</label>
          <textarea name="whatsapp_message" style="min-height:72px;resize:vertical" placeholder="Hi WePower! I'm interested in solar solutions."><?= h($s['whatsapp_message'] ?? '') ?></textarea>
        </div>
      </div>
    </form>
  </div>

  <div class="adm-settings-card">
    <h4 style="margin-bottom:8px"><i class="bi bi-image" style="margin-right:8px;color:var(--green-600)"></i>Logo</h4>
    <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:16px">PNG or SVG recommended. Max 2 MB. Changes go live on the next public page refresh.</p>
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap">
      <div style="background:var(--gray-50);border:1.5px solid var(--gray-200);border-radius:10px;padding:10px 18px;display:flex;align-items:center;gap:12px">
        <img id="logoPreview" src="<?= h($s['company_logo'] ?? '/assets/images/logo.png') ?>" alt="Logo preview" style="height:48px;max-width:160px;object-fit:contain;border-radius:4px">
      </div>
      <div style="font-size:.82rem;color:var(--gray-400)" id="logoStatusText">Current logo</div>
    </div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <input type="file" id="logoFileInput" accept="image/*" style="flex:1;min-width:0">
      <button type="button" class="btn btn-primary btn-sm" id="logoUploadBtn" disabled style="white-space:nowrap"><i class="bi bi-upload"></i> Upload Logo</button>
    </div>
  </div>
</div>

<!-- TAB 1: SMTP -->
<div class="adm-settings-tab" data-tab-panel="1" hidden>
  <div class="adm-settings-card">
    <form class="settings-section-form" data-keys="smtp_host,smtp_port,smtp_user,smtp_pass,smtp_from_name,smtp_secure" id="smtpForm" autocomplete="off">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap">
        <h4 style="margin-bottom:0"><i class="bi bi-envelope-at" style="margin-right:8px;color:var(--green-600)"></i>SMTP / Email Configuration</h4>
      </div>
      <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:24px">Configure outgoing email. Used for contact form notifications, application confirmations, etc.</p>
      <div class="adm-settings-grid">
        <div class="field"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= h($s['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com"></div>
        <div class="field"><label>Port</label><input type="number" name="smtp_port" value="<?= h($s['smtp_port'] ?? '587') ?>" placeholder="587"></div>
        <div class="field"><label>Username / Email</label><input type="email" name="smtp_user" value="<?= h($s['smtp_user'] ?? '') ?>" placeholder="yourcompany@gmail.com" autocomplete="off" data-lpignore="true" data-1p-ignore spellcheck="false"></div>
        <div class="field">
          <label>Password / App Password</label>
          <div style="position:relative">
            <input type="password" name="smtp_pass" id="smtpPassInput" value="<?= h($smtpPassMasked) ?>" placeholder="16-letter Gmail App Password" autocomplete="new-password" data-lpignore="true" data-1p-ignore style="padding-right:44px">
            <button type="button" id="smtpPassToggle" style="position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray-400);cursor:pointer;padding:2px;display:flex;align-items:center;font-size:.95rem"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <div class="field"><label>From Name</label><input type="text" name="smtp_from_name" value="<?= h($s['smtp_from_name'] ?? '') ?>" placeholder="WePower Solar"></div>
        <div class="field" style="display:flex;align-items:center;gap:12px">
          <input type="checkbox" id="smtp_secure" name="smtp_secure" style="width:16px;height:16px" <?= ($s['smtp_secure'] ?? '') === 'true' ? 'checked' : '' ?>>
          <label for="smtp_secure" style="margin-bottom:0;cursor:pointer">Use SSL/TLS (port 465)</label>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap">
        <button type="submit" class="btn btn-primary" disabled><i class="bi bi-check-lg"></i> Save SMTP Settings</button>
        <button type="button" class="btn btn-outline" id="smtpTestBtn"><i class="bi bi-send-check"></i> Test Connection</button>
      </div>
    </form>
    <div style="margin-top:20px;background:var(--gray-50);border-radius:var(--radius);padding:16px 20px;font-size:.85rem;color:var(--gray-600)">
      <strong><i class="bi bi-info-circle" style="margin-right:8px"></i>Gmail tip:</strong> Use an App Password (not your regular password). Enable 2FA → Google Account → Security → App Passwords → generate one for this app.
    </div>
  </div>

  <div class="adm-settings-card">
    <form class="settings-section-form" data-keys="admin_notification_emails">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap">
        <h4 style="margin-bottom:0"><i class="bi bi-people" style="margin-right:8px;color:var(--green-600)"></i>Notification Recipients</h4>
        <button type="submit" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-lg"></i> Save Recipients</button>
      </div>
      <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:16px">All form submissions are sent to the <strong>SMTP username</strong> above automatically. Add extra team emails here (comma-separated) for additional copies.</p>
      <div class="field">
        <label>Additional Notification Emails <span style="color:var(--gray-400);font-weight:400">(comma-separated)</span></label>
        <input type="text" name="admin_notification_emails" value="<?= h($s['admin_notification_emails'] ?? '') ?>" placeholder="e.g. owner@gmail.com, sales@gmail.com" autocomplete="off">
      </div>
    </form>
  </div>
</div>

<!-- TAB 2: SITE SETTINGS -->
<div class="adm-settings-tab" data-tab-panel="2" hidden>
  <div class="adm-settings-card">
    <div style="background:var(--green-50);border:1px solid var(--green-200);border-radius:8px;padding:10px 16px;margin-bottom:20px;font-size:.84rem;color:var(--green-800);display:flex;align-items:center;gap:8px">
      <i class="bi bi-info-circle-fill"></i> Site setting changes go live the <strong>next time a visitor loads or refreshes</strong> the public website.
    </div>

    <form class="settings-section-form" data-keys="promo_video_url,promo_video_enabled" id="promoForm">
      <div style="margin-top:0;padding:22px 24px;background:var(--gray-50);border-radius:var(--radius-lg);border:1.5px solid var(--gray-200)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:12px;flex-wrap:wrap">
          <h4 style="margin-bottom:0;font-size:1rem"><i class="bi bi-play-circle" style="margin-right:8px;color:#ff0000"></i>Promo Video Popup</h4>
          <button type="submit" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-lg"></i> Save Video Settings</button>
        </div>
        <p style="color:var(--gray-500);font-size:.88rem;margin-bottom:16px">A video popup appears once per page load when a visitor lands on the website. Paste any YouTube video link below.</p>
        <div class="field" style="margin-bottom:8px">
          <label>YouTube Video URL</label>
          <input type="text" name="promo_video_url" id="promoVideoUrlInput" value="<?= h($s['promo_video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div id="promoVideoTestLink" style="margin-bottom:16px"></div>
        <?php $promoOn = ($s['promo_video_enabled'] ?? '') === 'true'; ?>
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
          <div id="promoEnabledToggle" style="width:48px;height:26px;border-radius:13px;background:<?= $promoOn ? 'var(--green-500)' : 'var(--gray-300)' ?>;position:relative;transition:background .2s;cursor:pointer;flex-shrink:0">
            <div style="position:absolute;top:3px;left:<?= $promoOn ? '25px' : '3px' ?>;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s"></div>
          </div>
          <span style="font-weight:500;color:var(--dark)" id="promoEnabledLabel"><?= $promoOn ? 'Popup is enabled' : 'Popup is disabled' ?></span>
          <input type="hidden" name="promo_video_enabled" id="promoVideoEnabledInput" value="<?= $promoOn ? 'true' : 'false' ?>">
        </div>
        <div style="font-size:.8rem;color:var(--gray-400);line-height:1.5">
          <i class="bi bi-info-circle" style="margin-right:5px"></i> To allow embedding: open your video in <strong>YouTube Studio</strong> → Details → More options → check <strong>"Allow embedding"</strong>.
        </div>
      </div>
    </form>
  </div>
</div>

<!-- TAB 3: SECURITY -->
<div class="adm-settings-tab" data-tab-panel="3" hidden>
  <div class="adm-settings-card">
    <h4 style="margin-bottom:20px"><i class="bi bi-shield-lock" style="margin-right:8px;color:var(--green-600)"></i>Change Password</h4>
    <div id="pwAlert" hidden></div>
    <form id="pwForm" novalidate autocomplete="on">
      <!-- lets password managers save the new password for the right account -->
      <input type="text" name="username" autocomplete="username" value="<?= h($admin['email'] ?? '') ?>" hidden readonly>
      <div style="max-width:420px">
        <div class="field">
          <label for="pwCurrent">Current Password</label>
          <div class="pw-wrap"><input type="password" id="pwCurrent" name="current_password" autocomplete="current-password" required><button type="button" class="pw-eye-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
        </div>
        <div class="field">
          <label for="pwNew">New Password</label>
          <div class="pw-wrap"><input type="password" id="pwNew" name="new_password" autocomplete="new-password" required minlength="8" maxlength="72" placeholder="At least 8 characters"><button type="button" class="pw-eye-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
          <div class="pw-meter" aria-hidden="true"><span id="pwMeterBar"></span></div>
          <div class="pw-hint" id="pwHint">Use 8+ characters. Mixing letters, numbers and symbols makes it stronger.</div>
        </div>
        <div class="field">
          <label for="pwConfirm">Confirm New Password</label>
          <div class="pw-wrap"><input type="password" id="pwConfirm" name="confirm_password" autocomplete="new-password" required maxlength="72"><button type="button" class="pw-eye-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
        </div>
        <button type="submit" class="btn btn-primary" id="pwSubmitBtn"><i class="bi bi-lock"></i> Update Password</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
