<?php
/**
 * /admin and /admin/ entry point: dashboard when logged in, login page otherwise.
 */
require_once __DIR__ . '/../includes/auth.php';
header('Location: ' . (isAdminLoggedIn() ? '/admin/dashboard.php' : '/admin/login.php'), true, 302);
exit;
