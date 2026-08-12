<?php
/**
 * SMTP connection test — PHP port of POST /admin/api/smtp-test in routes/admin.js.
 * Uses the CURRENT (possibly unsaved) site_settings SMTP config, verifies
 * the connection, and sends a real test email to the logged-in admin.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/mailer.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$cfg = getSmtpConfig();
if (!$cfg['host'] || !$cfg['user'] || !$cfg['pass']) {
    jsonResponse(['error' => 'SMTP settings incomplete. Save your credentials first.'], 400);
}

$admin = currentAdmin();
$result = sendMail($admin['email'], 'WePower Admin — SMTP Test', '<p>SMTP connection successful! Your email settings are working correctly.</p>');

if (!empty($result['error'])) {
    jsonResponse(['error' => $result['error']], 400);
}
if (!empty($result['skipped'])) {
    jsonResponse(['error' => 'SMTP settings incomplete. Save your credentials first.'], 400);
}

jsonResponse(['success' => true, 'message' => 'Test email sent to ' . $admin['email']]);
