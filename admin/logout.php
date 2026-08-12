<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

logoutAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    jsonResponse(['success' => true]);
}
header('Location: /admin/login.php');
