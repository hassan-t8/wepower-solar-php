<?php
/**
 * Central configuration — database connection + fallback constants.
 *
 * On Hostinger: fill in the DB_* values from hPanel → Databases → MySQL
 * Databases, and the SMTP_* fallback values from your Gmail app password
 * (these are only used if the corresponding site_settings row is blank —
 * the admin panel's Settings page is the live source of truth after setup).
 *
 * This file is the PHP analogue of the old Node project's .env file.
 * Keep it OUT of version control in production (see .gitignore) — it's
 * committed here only because this is a fresh project with no real
 * secrets in it yet.
 */

// ---- Database ----------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'wepower_solar');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- SMTP fallback (used only if site_settings smtp_* rows are blank) ----
define('SMTP_HOST_FALLBACK', 'smtp.gmail.com');
define('SMTP_PORT_FALLBACK', '587');
define('SMTP_USER_FALLBACK', '');
define('SMTP_PASS_FALLBACK', '');
define('SMTP_FROM_NAME_FALLBACK', 'WePower Solar');
define('SMTP_SECURE_FALLBACK', 'false');

// ---- Admin seed fallback (only used if you re-run the seed manually) ----
define('ADMIN_EMAIL_FALLBACK', 'admin@wepower.pk');

// ---- Misc ----------------------------------------------------------
define('SITE_URL', 'http://localhost'); // change to your real domain in production, no trailing slash
define('APP_ENV', 'development'); // 'development' | 'production' — controls error display

if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

date_default_timezone_set('Asia/Karachi');
