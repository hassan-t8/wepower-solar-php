<?php
/**
 * site_settings key/value helpers — PHP port of the getSetting/setSetting
 * pattern used throughout database.js / routes/admin.js, plus the public
 * settings whitelist that used to live behind GET /api/settings/public.
 *
 * Unlike the React SPA (which had to fetch this over the network on every
 * page load), header.php/footer.php call getPublicSettings() directly,
 * server-side — branding/contact/social info is baked into the HTML.
 */

require_once __DIR__ . '/db.php';

/** Keys the public site is allowed to read (mirrors PUBLIC_SETTING_KEYS in server.js). */
const PUBLIC_SETTING_KEYS = [
    'company_name', 'company_short', 'company_tagline',
    'company_address', 'company_phone1', 'company_phone2', 'company_email',
    'company_instagram', 'company_logo',
    'social_facebook', 'social_instagram', 'social_linkedin', 'social_youtube',
    'company_whatsapp', 'whatsapp_message',
    'promo_video_url', 'promo_video_enabled',
];

/** Keys the admin Settings page is allowed to write (mirrors the `allowed` list in routes/admin.js). */
const ADMIN_SETTABLE_KEYS = [
    'company_name', 'company_short', 'company_tagline',
    'company_address', 'company_phone1', 'company_phone2', 'company_email', 'company_instagram',
    'social_facebook', 'social_instagram', 'social_linkedin', 'social_youtube',
    'company_whatsapp', 'whatsapp_message',
    'promo_video_url', 'promo_video_enabled',
    'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_name', 'smtp_secure',
    'admin_notification_emails',
];

function getSetting(string $key): ?string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $row = fetchOne('SELECT value FROM site_settings WHERE `key` = ?', [$key]);
    return $cache[$key] = ($row ? $row['value'] : null);
}

function setSetting(string $key, string $value): void
{
    execute(
        'INSERT INTO site_settings (`key`, value, updated_at) VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()',
        [$key, $value]
    );
}

/** All whitelisted public settings in one query — the header/footer's main data source. */
function getPublicSettings(): array
{
    static $settings = null;
    if ($settings !== null) return $settings;

    $placeholders = implode(',', array_fill(0, count(PUBLIC_SETTING_KEYS), '?'));
    $rows = fetchAll("SELECT `key`, value FROM site_settings WHERE `key` IN ($placeholders)", PUBLIC_SETTING_KEYS);

    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

/** Shorthand for reading one public setting with a fallback default. */
function setting(string $key, string $default = ''): string
{
    $all = getPublicSettings();
    return $all[$key] ?? $default;
}

/**
 * Builds the full list of admin notification recipients.
 * Primary = smtp_user (the sending account). Additional = comma-separated
 * admin_notification_emails setting. Deduped, comma-joined — same as
 * mailer.js's getAdminRecipients().
 */
function getAdminRecipients(): string
{
    $user = getSetting('smtp_user') ?: SMTP_USER_FALLBACK;
    $extrasRaw = getSetting('admin_notification_emails') ?: '';
    $extras = array_filter(array_map('trim', explode(',', $extrasRaw)));
    $all = array_filter(array_merge([$user], $extras));
    return implode(', ', array_unique($all));
}
