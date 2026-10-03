<?php
/**
 * Live-update feed for the admin panel, polled every few seconds by
 * admin-common.js (shared hosting can't keep WebSocket connections open,
 * so short polling of this small query gives the same "no refresh" feel).
 *
 * GET ?since=init → latest id + sidebar badges + the 15 most recent events (bell list)
 * GET ?since=<id>  → events newer than that id (max 50) + badges; works for id 0
 *                   (empty table) so the first event after a data reset counts as new
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
session_write_close(); // don't hold the session lock while polling

ensureNotificationSchema();
$initial = ($_GET['since'] ?? 'init') === 'init';
$since = $initial ? 0 : max(0, (int) $_GET['since']);

$cols = 'id, type, title, body, url, created_at';
$events = !$initial
    ? fetchAll("SELECT $cols FROM admin_notifications WHERE id > ? ORDER BY id ASC LIMIT 50", [$since])
    : array_reverse(fetchAll("SELECT $cols FROM admin_notifications ORDER BY id DESC LIMIT 15"));
$latest = countRows('SELECT COALESCE(MAX(id), 0) c FROM admin_notifications');

jsonResponse([
    'latest_id' => $latest,
    'initial' => $initial,
    'events' => $events,
    'prefs' => getNotificationPrefs((int) currentAdmin()['id']),
    'badges' => [
        'applications' => countRows("SELECT COUNT(*) c FROM applications WHERE status = 'new'"),
        'contacts' => countRows("SELECT COUNT(*) c FROM contacts WHERE status = 'new'"),
        'bookings' => countRows("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'"),
        'careers' => countRows("SELECT COUNT(*) c FROM careers WHERE status = 'new'"),
    ],
]);
