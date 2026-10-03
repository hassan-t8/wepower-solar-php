<?php
/**
 * Live-update feed for the admin panel, polled every few seconds by
 * admin-common.js (shared hosting can't keep WebSocket connections open,
 * so short polling of this small query gives the same "no refresh" feel).
 *
 * GET ?since=<last seen notification id>
 *   since=0  → latest id + sidebar badges + the 15 most recent events (bell list)
 *   since>0  → events newer than that id (max 50) + badges
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
session_write_close(); // don't hold the session lock while polling

ensureNotificationSchema();
$since = max(0, (int) ($_GET['since'] ?? 0));

$cols = 'id, type, title, body, url, created_at';
$events = $since > 0
    ? fetchAll("SELECT $cols FROM admin_notifications WHERE id > ? ORDER BY id ASC LIMIT 50", [$since])
    : array_reverse(fetchAll("SELECT $cols FROM admin_notifications ORDER BY id DESC LIMIT 15"));
$latest = countRows('SELECT COALESCE(MAX(id), 0) c FROM admin_notifications');

jsonResponse([
    'latest_id' => $latest,
    'initial' => $since === 0,
    'events' => $events,
    'prefs' => getNotificationPrefs((int) currentAdmin()['id']),
    'badges' => [
        'applications' => countRows("SELECT COUNT(*) c FROM applications WHERE status = 'new'"),
        'contacts' => countRows("SELECT COUNT(*) c FROM contacts WHERE status = 'new'"),
        'bookings' => countRows("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'"),
        'careers' => countRows("SELECT COUNT(*) c FROM careers WHERE status = 'new'"),
    ],
]);
