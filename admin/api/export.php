<?php
/**
 * CSV export — PHP port of GET /admin/api/export/:type.
 * ?type=contacts|bookings|load_calculations|careers|visitors|applications
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$allowed = ['contacts', 'bookings', 'load_calculations', 'careers', 'visitors', 'applications'];
$type = $_GET['type'] ?? '';
if (!in_array($type, $allowed, true)) jsonResponse(['error' => 'Invalid type'], 400);

$rows = fetchAll("SELECT * FROM `$type` ORDER BY id DESC");
csvExport($type, $rows);
