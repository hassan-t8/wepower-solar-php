<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/settings.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (empty($_FILES['logo'])) jsonResponse(['error' => 'No file uploaded'], 400);

try {
    $filename = handleUpload(
        $_FILES['logo'],
        ['.png', '.jpg', '.jpeg', '.svg', '.webp'],
        2 * 1024 * 1024,
        __DIR__ . '/../../uploads/logos'
    );
} catch (Throwable $e) {
    jsonResponse(['error' => $e->getMessage()], 400);
}

// Crop the plain margins so the logo is readable at header size.
try {
    trimImageMargins(__DIR__ . '/../../uploads/logos/' . $filename);
} catch (Throwable $e) {
    error_log('[logo-upload] trim skipped: ' . $e->getMessage()); // keep the original file
}

$url = '/uploads/logos/' . $filename;
setSetting('company_logo', $url);
jsonResponse(['success' => true, 'url' => $url]);
