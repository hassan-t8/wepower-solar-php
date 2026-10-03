<?php
/**
 * Push delivery receipt. The admin service worker (/admin/sw.php → sw.js) calls this
 * whenever a push message reaches a browser, so "was it delivered?" can be
 * told apart from "did Windows/the OS show it?". Only the latest receipt is
 * kept (one setting value), shown in Admin → Notifications.
 * Public on purpose: a service worker may run while the admin is logged out.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
setSetting('push_last_received', json_encode([
    'at' => gmdate('Y-m-d H:i:s'), // UTC, like every other stored time
    'title' => mb_substr(clean($input['title'] ?? ''), 0, 80),
    'shown' => !empty($input['shown']),
    'error' => mb_substr(clean($input['error'] ?? ''), 0, 160),
    'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 120),
]));
jsonResponse(['ok' => true]);
