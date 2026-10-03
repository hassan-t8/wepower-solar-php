<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Logging out stops push on this device: drop its token (sent by admin-common.js) first.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdminLoggedIn()) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $token = (string) ($input['push_token'] ?? '');
    if ($token !== '') {
        try {
            execute('DELETE FROM push_subscriptions WHERE endpoint = ? AND admin_id = ?', [$token, (int) currentAdmin()['id']]);
        } catch (Throwable $e) {
            error_log('[logout] ' . $e->getMessage()); // never block a logout
        }
    }
}

logoutAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    jsonResponse(['success' => true]);
}
header('Location: /admin/login');
