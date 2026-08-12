<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/settings.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];

foreach (ADMIN_SETTABLE_KEYS as $k) {
    if (!array_key_exists($k, $input)) continue;
    // Don't overwrite smtp_pass if the client sent the masked placeholder back unchanged.
    if ($k === 'smtp_pass' && $input[$k] === '••••••••') continue;
    setSetting($k, (string) $input[$k]);
}

jsonResponse(['success' => true]);
