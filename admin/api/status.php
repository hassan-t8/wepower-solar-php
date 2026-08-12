<?php
/**
 * Update a row's status — PHP port of POST /admin/api/:type/:id/status.
 * Body (JSON): { type, id, status }
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$type = $input['type'] ?? '';
$id = (int) ($input['id'] ?? 0);
$status = clean($input['status'] ?? '');

$allowed = ['contacts', 'bookings', 'careers', 'applications'];
if (!in_array($type, $allowed, true)) jsonResponse(['error' => 'Invalid type'], 400);
if (!$id || !$status) jsonResponse(['error' => 'id and status required'], 400);

execute("UPDATE `$type` SET status = ? WHERE id = ?", [$status, $id]);
jsonResponse(['success' => true]);
