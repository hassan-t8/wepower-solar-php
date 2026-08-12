<?php
/**
 * Generic list endpoint — PHP port of the `list()` helper in routes/admin.js.
 * ?type=applications|contacts|bookings|load_calculations|careers
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$allowed = ['applications', 'contacts', 'bookings', 'load_calculations', 'careers'];
$type = $_GET['type'] ?? '';
if (!in_array($type, $allowed, true)) jsonResponse(['error' => 'Invalid type'], 400);

// $type is validated against a fixed whitelist above, so safe to interpolate.
$rows = fetchAll("SELECT * FROM `$type` ORDER BY created_at DESC LIMIT 500");
jsonResponse($rows);
