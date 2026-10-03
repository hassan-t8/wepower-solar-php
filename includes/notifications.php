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
 * Push delivery itself is a hook (sendPushToAdmins) that stays inactive
 * until push is configured in Admin → Notifications.
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
        $count = countRows('SELECT COUNT(*) c FROM visitors WHERE DATE(visited_at) = (CURDATE() - INTERVAL 1 DAY)');
        $unique = countRows('SELECT COUNT(DISTINCT ip) c FROM visitors WHERE DATE(visited_at) = (CURDATE() - INTERVAL 1 DAY)');
        notifyAdmins('visitors', "Yesterday: $count page views", "$unique unique visitors on " . date('D, j M', strtotime('-1 day')) . '.');
    } catch (Throwable $e) {
        error_log('[maybeNotifyDailyVisitors] ' . $e->getMessage());
    }
}

/** Is browser push configured (keys entered in Admin → Notifications)? */
function isPushConfigured(): bool
{
    return getSetting('push_enabled') === 'true'
        && (string) getSetting('push_vapid_public_key') !== ''
        && (string) getSetting('push_vapid_private_key') !== '';
}

/**
 * Push delivery hook. Finds the devices of admins who have $type switched on
 * and hands each to deliverPush(). Inactive until push is configured.
 */
function sendPushToAdmins(string $type, array $payload): void
{
    if (!isPushConfigured()) return;
    $subs = fetchAll(
        'SELECT s.* FROM push_subscriptions s
         LEFT JOIN notification_prefs p ON p.admin_id = s.admin_id AND p.type = ?
         WHERE p.enabled IS NULL OR p.enabled = 1',
        [$type]
    );
    foreach ($subs as $sub) {
        try {
            deliverPush($sub, $payload);
        } catch (Throwable $e) {
            error_log('[push] ' . $e->getMessage());
        }
    }
}

/**
 * Send one push message to one subscribed device.
 * TODO(push-config): implement with the push provider credentials
 * (VAPID keys / Firebase) once they're provided. The service worker
 * (/admin/sw.js) already shows any payload shaped like $payload, and falls
 * back to fetching the latest event when a push arrives without a payload.
 */
function deliverPush(array $subscription, array $payload): void
{
    // Intentionally a no-op until push credentials are configured.
}
