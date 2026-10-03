<?php
$adminPageTitle = 'Notifications';
$adminActive = 'notifications';
$adminPageScripts = ['notifications.js'];
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/notifications.php';

$prefs = getNotificationPrefs((int) $admin['id']);
$pushConfigured = isPushConfigured();
$pushEnabled = getSetting('push_enabled') === 'true';
$fb = firebaseWebConfig();
$serviceAccount = fcmServiceAccount();
$myDevices = countRows('SELECT COUNT(*) c FROM push_subscriptions WHERE admin_id = ?', [(int) $admin['id']]);
$pushState = $pushConfigured ? ['ok', 'Active'] : ($serviceAccount ? ['warn', 'Switched off'] : ['warn', 'Needs service account']);
$recent = fetchAll('SELECT type, title, body, url, created_at FROM admin_notifications ORDER BY id DESC LIMIT 30');
?>

<h2 style="margin-bottom:6px">Notifications</h2>
<p style="color:var(--gray-400);font-size:.88rem;margin-bottom:24px">Choose what you get notified about. The admin panel updates live — new records appear without refreshing.</p>

<div class="ntf-card">
  <h4><i class="bi bi-toggles"></i> What to notify me about</h4>
  <p>Switch each type on or off. Changes save instantly and apply to your account on every device.</p>
  <div class="ntf-grid">
    <?php foreach (NOTIFY_TYPES as $type => [$label, $desc, $icon]): ?>
      <div class="ntf-row">
        <i class="bi <?= h($icon) ?>"></i>
        <div class="ntf-row-text"><strong><?= h($label) ?></strong><span><?= h($desc) ?></span></div>
        <label class="ntf-switch" title="<?= h($label) ?>">
          <input type="checkbox" data-pref="<?= h($type) ?>" <?= $prefs[$type] ? 'checked' : '' ?> aria-label="<?= h($label) ?>">
          <span></span>
        </label>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="ntf-card">
  <h4><i class="bi bi-display"></i> Desktop alerts on this device <span class="ntf-status off" id="desktopStatus">Checking…</span></h4>
  <p>While the admin panel is open (even in a background tab), new items pop up as system notifications on this computer or phone. Works now — no setup needed.</p>
  <div class="ntf-row">
    <i class="bi bi-bell"></i>
    <div class="ntf-row-text"><strong>Show desktop alerts here</strong><span>Turn off to only see in-page toasts on this device.</span></div>
    <label class="ntf-switch"><input type="checkbox" id="desktopToggle" aria-label="Desktop alerts"><span></span></label>
  </div>
  <div class="ntf-actions">
    <button type="button" class="btn btn-primary btn-sm" id="desktopAllowBtn"><i class="bi bi-check2-circle"></i> Allow notifications in this browser</button>
    <button type="button" class="btn btn-outline btn-sm" id="desktopTestBtn"><i class="bi bi-send"></i> Send a test alert</button>
  </div>
</div>

<div class="ntf-card">
  <h4><i class="bi bi-phone-vibrate"></i> Push notifications (admin panel closed)
    <span class="ntf-status <?= h($pushState[0]) ?>" id="pushStatus"><?= h($pushState[1]) ?></span>
  </h4>
  <p>Delivers alerts to your phone or computer even when the admin panel isn't open, via Firebase Cloud Messaging.
    Your devices registered: <strong id="pushDeviceCount"><?= (int) $myDevices ?></strong></p>

  <form id="pushConfigForm" autocomplete="off"
        data-firebase='<?= h(json_encode($fb, JSON_UNESCAPED_SLASHES)) ?>'>
    <div class="ntf-row">
      <i class="bi bi-power"></i>
      <div class="ntf-row-text"><strong>Enable push notifications</strong><span>Needs the service account below.</span></div>
      <label class="ntf-switch"><input type="checkbox" name="push_enabled" <?= $pushEnabled ? 'checked' : '' ?> aria-label="Enable push"><span></span></label>
    </div>

    <div class="field" style="margin-top:8px">
      <label>Firebase service account (JSON) <span class="field-hint">— Firebase → Project settings → Service accounts → Generate new private key</span></label>
      <?php if ($serviceAccount): ?>
        <div class="ntf-status ok" style="margin-bottom:8px"><i class="bi bi-shield-check"></i> Saved: <?= h($serviceAccount['client_email']) ?></div>
      <?php endif; ?>
      <textarea name="fcm_service_account" rows="4" spellcheck="false" style="font-family:monospace;font-size:.8rem"
        placeholder="<?= $serviceAccount ? 'Saved securely — paste a new JSON only to replace it' : 'Paste the whole JSON file here, or choose the file below' ?>"></textarea>
      <input type="file" id="saFile" accept="application/json,.json" style="margin-top:8px">
    </div>

    <details style="margin:6px 0 4px">
      <summary style="cursor:pointer;font-weight:600;font-size:.88rem;color:var(--gray-600)">Firebase web app settings (pre-filled)</summary>
      <div class="form-row" style="margin-top:12px">
        <div class="field"><label>Project ID</label><input type="text" name="fcm_project_id" value="<?= h($fb['projectId']) ?>"></div>
        <div class="field"><label>Sender ID</label><input type="text" name="fcm_sender_id" value="<?= h($fb['messagingSenderId']) ?>"></div>
      </div>
      <div class="form-row">
        <div class="field"><label>API key (web)</label><input type="text" name="fcm_api_key" value="<?= h($fb['apiKey']) ?>"></div>
        <div class="field"><label>App ID</label><input type="text" name="fcm_app_id" value="<?= h($fb['appId']) ?>"></div>
      </div>
      <div class="field"><label>Web Push certificate key (VAPID)</label><input type="text" name="fcm_vapid_key" value="<?= h($fb['vapidKey']) ?>"></div>
    </details>

    <div class="ntf-actions">
      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Save push settings</button>
      <button type="button" class="btn btn-outline btn-sm" id="pushSubscribeBtn" <?= $pushConfigured ? '' : 'disabled' ?>>
        <i class="bi bi-phone"></i> Enable push on this device
      </button>
      <button type="button" class="btn btn-outline btn-sm" id="pushTestBtn" <?= $pushConfigured ? '' : 'disabled' ?>>
        <i class="bi bi-send"></i> Send test push
      </button>
    </div>
  </form>
</div>

<div class="ntf-card">
  <h4><i class="bi bi-clock-history"></i> Recent activity</h4>
  <p>The latest events across the website.</p>
  <?php if (!$recent): ?>
    <div class="adm-empty" style="padding:24px 0">No notifications yet — they'll appear here as soon as someone submits a form.</div>
  <?php else: ?>
    <?php foreach ($recent as $n): ?>
      <a class="ntf-row" href="<?= h($n['url'] ?: '#') ?>" style="color:inherit">
        <i class="bi <?= h(NOTIFY_TYPES[$n['type']][2] ?? 'bi-bell') ?>"></i>
        <div class="ntf-row-text"><strong><?= h($n['title']) ?></strong><span><?= h($n['body']) ?></span></div>
        <span style="color:var(--gray-400);font-size:.78rem;white-space:nowrap" data-time="<?= h($n['created_at']) ?>"></span>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
