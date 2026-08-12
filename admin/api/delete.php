<?php
/**
 * Delete a row — PHP port of DELETE /admin/api/:type/:id.
 * Body (JSON): { type, id }
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$type = $input['type'] ?? '';
$id = (int) ($input['id'] ?? 0);

$allowed = ['contacts', 'bookings', 'careers', 'load_calculations', 'applications'];
if (!in_array($type, $allowed, true)) jsonResponse(['error' => 'Invalid type'], 400);
if (!$id) jsonResponse(['error' => 'id required'], 400);

execute("DELETE FROM `$type` WHERE id = ?", [$id]);
jsonResponse(['success' => true]);
