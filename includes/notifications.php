<?php
/**
 * Admin notifications: an event feed (admin_notifications) that drives the
 * admin panel's live updates (admin/api/updates.php is polled every few
 * seconds), per-admin on/off preferences per notification type, and storage
 * for browser push subscriptions.
 *
 * Public form endpoints call notifyAdmins() after saving a record. It never
 * throws — a notification problem must never make a form submission fail.
 *
 * Push goes through Firebase Cloud Messaging (HTTP v1): the admin's browser
 * registers in Admin → Notifications, and sendPushToAdmins() delivers once
 * push is switched on and the Firebase service account has been entered.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/settings.php';

/** Notification types an admin can switch on/off. key => [label, description, icon, admin page]. */
const NOTIFY_TYPES = [
    'applications' => ['Quote / Service Applications', '"Get a Quote" and service requests from the website.', 'bi-lightning-charge', '/admin/applications.php'],
    'careers'      => ['Career Applications', 'Job applications from the Careers page.', 'bi-briefcase', '/admin/careers.php'],
    'contacts'     => ['Contact Messages', 'Messages sent from the Contact page.', 'bi-envelope', '/admin/contacts.php'],
    'bookings'     => ['Site Survey Bookings', 'Free consultation / site survey bookings.', 'bi-calendar-check', '/admin/bookings.php'],
    'calculations' => ['Load Calculator Results', 'Visitors who saved load-calculator results for a quote.', 'bi-calculator', '/admin/calculations.php'],
    'visitors'     => ['Daily Visitor Summary', 'Once a day: how many people visited the website yesterday.', 'bi-graph-up-arrow', '/admin/dashboard.php'],
];

const NOTIFY_SCHEMA_VERSION = '1';

/** Create the notification tables on first use (the live DB has no migration step). */
function ensureNotificationSchema(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    if (getSetting('notify_schema_version') === NOTIFY_SCHEMA_VERSION) return;

    execute("CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(30) NOT NULL,
        title VARCHAR(200) NOT NULL,
        body VARCHAR(500),
        url VARCHAR(255),
        ref_id INT UNSIGNED,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_notifications_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execute("CREATE TABLE IF NOT EXISTS notification_prefs (
        admin_id INT UNSIGNED NOT NULL,
        type VARCHAR(30) NOT NULL,
        enabled TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (admin_id, type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execute("CREATE TABLE IF NOT EXISTS push_subscriptions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        admin_id INT UNSIGNED NOT NULL,
        endpoint VARCHAR(500) NOT NULL,
        keys_json TEXT,
        user_agent VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_push_endpoint (endpoint(191)),
        INDEX idx_push_admin (admin_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    setSetting('notify_schema_version', NOTIFY_SCHEMA_VERSION);
}

/** type => bool for one admin. Types with no saved row default to ON. */
function getNotificationPrefs(int $adminId): array
{
    ensureNotificationSchema();
    $prefs = array_fill_keys(array_keys(NOTIFY_TYPES), true);
    foreach (fetchAll('SELECT type, enabled FROM notification_prefs WHERE admin_id = ?', [$adminId]) as $r) {
        if (isset($prefs[$r['type']])) $prefs[$r['type']] = (bool) $r['enabled'];
    }
    return $prefs;
}

function setNotificationPref(int $adminId, string $type, bool $enabled): void
{
    if (!isset(NOTIFY_TYPES[$type])) return;
    ensureNotificationSchema();
    execute(
        'INSERT INTO notification_prefs (admin_id, type, enabled) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE enabled = VALUES(enabled)',
        [$adminId, $type, $enabled ? 1 : 0]
    );
}

/**
 * Record a new event for the admin panel and (once configured) push it to
 * subscribed admin devices. Safe to call from any public endpoint.
 */
function notifyAdmins(string $type, string $title, string $body = '', ?int $refId = null): void
{
    try {
        ensureNotificationSchema();
        $url = NOTIFY_TYPES[$type][3] ?? '/admin/dashboard.php';
        if ($refId && $type !== 'visitors') $url .= '?open=' . $refId; // opens that record's detail view
        $id = insertGetId(
            'INSERT INTO admin_notifications (type, title, body, url, ref_id) VALUES (?, ?, ?, ?, ?)',
            [$type, mb_substr($title, 0, 200), mb_substr($body, 0, 500), $url, $refId]
        );
        sendPushToAdmins($type, ['id' => $id, 'type' => $type, 'title' => $title, 'body' => $body, 'url' => $url]);
    } catch (Throwable $e) {
        error_log('[notifyAdmins] ' . $e->getMessage());
    }
}

/**
 * Lazily emits yesterday's visitor count as a 'visitors' notification —
 * runs on the first page view of each day, so no cron job is needed.
 */
function maybeNotifyDailyVisitors(): void
{
    try {
        $today = date('Y-m-d');
        if (getSetting('visitor_summary_date') === $today) return;
        setSetting('visitor_summary_date', $today);
        $range = [utcDayStart('yesterday'), utcDayStart('today')];
        $count = countRows('SELECT COUNT(*) c FROM visitors WHERE visited_at >= ? AND visited_at < ?', $range);
        $unique = countRows('SELECT COUNT(DISTINCT ip) c FROM visitors WHERE visited_at >= ? AND visited_at < ?', $range);
        notifyAdmins('visitors', "Yesterday: $count page views", "$unique unique visitors on " . date('D, j M', strtotime('-1 day')) . '.');
    } catch (Throwable $e) {
        error_log('[maybeNotifyDailyVisitors] ' . $e->getMessage());
    }
}

/**
 * Firebase web-app config the admin browser uses to register for push.
 * These values are public by design (they ship to every browser); the
 * defaults are the "wepower" Firebase project and can be changed in
 * Admin → Notifications. The service account (the secret half) is only
 * ever entered in the admin panel and stored server-side.
 */
const FIREBASE_DEFAULTS = [
    'fcm_api_key' => 'AIzaSyAwYOAlbPblO_BnagyjjWXBuaVA9WC8DVM',
    'fcm_project_id' => 'wepower-f0857',
    'fcm_sender_id' => '1092050392107',
    'fcm_app_id' => '1:1092050392107:web:404fa43f7ed7507931066e',
    'fcm_vapid_key' => 'BHx2_DHkNusEW02ZXznsvqzoNddCmml-Yu7_548UJfB0mqWgPXnoA8UIt5oCJLQRXQBPwLJMNZHilWlhmO_VVPY',
];

function fcmSetting(string $key): string
{
    $v = (string) getSetting($key);
    return $v !== '' ? $v : (FIREBASE_DEFAULTS[$key] ?? '');
}

/** Public config for the browser (never includes the service account). */
function firebaseWebConfig(): array
{
    return [
        'apiKey' => fcmSetting('fcm_api_key'),
        'projectId' => fcmSetting('fcm_project_id'),
        'messagingSenderId' => fcmSetting('fcm_sender_id'),
        'appId' => fcmSetting('fcm_app_id'),
        'vapidKey' => fcmSetting('fcm_vapid_key'),
    ];
}

/** Can browsers register for push? Needs only the public web config (not the service account). */
function canRegisterPush(): bool
{
    $c = firebaseWebConfig();
    return $c['apiKey'] !== '' && $c['projectId'] !== '' && $c['appId'] !== '' && $c['vapidKey'] !== '';
}

/** Parsed service-account JSON, or null if missing/invalid. */
function fcmServiceAccount(): ?array
{
    $sa = json_decode((string) getSetting('fcm_service_account'), true);
    return (is_array($sa) && !empty($sa['client_email']) && !empty($sa['private_key'])) ? $sa : null;
}

/** Is push fully configured and switched on? */
function isPushConfigured(): bool
{
    return getSetting('push_enabled') === 'true'
        && fcmSetting('fcm_project_id') !== ''
        && fcmSetting('fcm_vapid_key') !== ''
        && fcmServiceAccount() !== null;
}

/**
 * Queue a push for the devices of admins who have $type switched on.
 * Delivery runs after the HTTP response has been sent, so a visitor
 * submitting a form never waits on Google's servers.
 */
function sendPushToAdmins(string $type, array $payload): void
{
    if (!isPushConfigured()) return;
    register_shutdown_function(function () use ($type, $payload) {
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        elseif (function_exists('litespeed_finish_request')) litespeed_finish_request();
        pushToAdminDevices($type, $payload);
    });
}

/** Send now to every subscribed device whose admin has $type on. Returns per-device results. */
function pushToAdminDevices(string $type, array $payload, ?int $onlyAdminId = null): array
{
    $sql = 'SELECT s.* FROM push_subscriptions s
            LEFT JOIN notification_prefs p ON p.admin_id = s.admin_id AND p.type = ?
            WHERE (p.enabled IS NULL OR p.enabled = 1)';
    $params = [$type];
    if ($onlyAdminId !== null) {
        $sql .= ' AND s.admin_id = ?';
        $params[] = $onlyAdminId;
    }

    $results = [];
    foreach (fetchAll($sql, $params) as $sub) {
        try {
            $results[] = deliverPush($sub, $payload);
        } catch (Throwable $e) {
            error_log('[push] ' . $e->getMessage());
            $results[] = ['ok' => false, 'error' => $e->getMessage()];
        }
    }
    return $results;
}

/**
 * Send one message to one device through the FCM HTTP v1 API.
 * Data-only message: /admin/sw.js turns it into the visible notification.
 * Device tokens that FCM reports as gone are removed.
 */
function deliverPush(array $subscription, array $payload): array
{
    $project = fcmSetting('fcm_project_id');
    $data = [];
    foreach (['id', 'type', 'title', 'body', 'url'] as $k) {
        $data[$k] = (string) ($payload[$k] ?? ''); // FCM data values must be strings
    }
    $absolute = rtrim(defined('SITE_URL') ? SITE_URL : '', '/') . ($data['url'] ?: '/admin/dashboard.php');

    $webpush = ['headers' => ['Urgency' => 'high', 'TTL' => '86400']];
    if (preg_match('#^https://#', $absolute)) $webpush['fcm_options'] = ['link' => $absolute];

    [$status, $resp] = httpJson(
        'https://fcm.googleapis.com/v1/projects/' . rawurlencode($project) . '/messages:send',
        ['message' => ['token' => $subscription['endpoint'], 'data' => $data, 'webpush' => $webpush]],
        ['Authorization: Bearer ' . fcmAccessToken()]
    );
    if ($status === 200) return ['ok' => true];

    $err = $resp['error']['status'] ?? '';
    $detail = json_encode($resp['error']['details'] ?? []);
    if ($status === 404 || $err === 'NOT_FOUND' || str_contains($detail, 'UNREGISTERED')) {
        execute('DELETE FROM push_subscriptions WHERE id = ?', [$subscription['id']]);
        return ['ok' => false, 'error' => 'Device is no longer registered (removed).'];
    }
    return ['ok' => false, 'error' => 'FCM ' . $status . ': ' . ($resp['error']['message'] ?? 'unknown error')];
}

/** OAuth2 access token for FCM, from the service account (cached ~55 min). */
function fcmAccessToken(): string
{
    $cached = json_decode((string) getSetting('fcm_access_token_cache'), true);
    if (is_array($cached) && ($cached['exp'] ?? 0) > time() + 60) return $cached['token'];

    $sa = fcmServiceAccount();
    if (!$sa) throw new RuntimeException('Firebase service account is missing.');
    $now = time();
    $tokenUri = $sa['token_uri'] ?? 'https://oauth2.googleapis.com/token';
    $jwt = jwtRs256(
        ['alg' => 'RS256', 'typ' => 'JWT'],
        [
            'iss' => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ],
        $sa['private_key']
    );
    [$status, $resp] = httpForm($tokenUri, [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]);
    if ($status !== 200 || empty($resp['access_token'])) {
        throw new RuntimeException('Google sign-in failed: ' . ($resp['error_description'] ?? $resp['error'] ?? ('HTTP ' . $status)));
    }
    setSetting('fcm_access_token_cache', json_encode([
        'token' => $resp['access_token'],
        'exp' => $now + (int) ($resp['expires_in'] ?? 3600) - 300,
    ]));
    return $resp['access_token'];
}

function base64url(string $s): string
{
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function jwtRs256(array $header, array $claims, string $privateKeyPem): string
{
    $input = base64url(json_encode($header)) . '.' . base64url(json_encode($claims));
    $key = openssl_pkey_get_private($privateKeyPem);
    if (!$key || !openssl_sign($input, $sig, $key, OPENSSL_ALGO_SHA256)) {
        throw new RuntimeException('Invalid service-account private key.');
    }
    return $input . '.' . base64url($sig);
}

/** POST JSON; returns [status, decoded body]. */
function httpJson(string $url, array $body, array $headers = []): array
{
    return httpPost($url, json_encode($body), array_merge(['Content-Type: application/json'], $headers));
}

/** POST form-encoded; returns [status, decoded body]. */
function httpForm(string $url, array $fields): array
{
    return httpPost($url, http_build_query($fields), ['Content-Type: application/x-www-form-urlencoded']);
}

function httpPost(string $url, string $body, array $headers): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Network error: ' . $err);
    }
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$status, json_decode($raw, true) ?? []];
}
