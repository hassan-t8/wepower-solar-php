<?php
/**
 * Job postings CRUD — PHP port of GET/POST /admin/api/jobs and
 * PUT/DELETE /admin/api/jobs/:id. Single file dispatch since PHP's
 * built-in/shared-hosting router doesn't do path params like Express.
 * Body (JSON) always includes an explicit `_action`: list|create|update|delete.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    jsonResponse(fetchAll('SELECT * FROM jobs ORDER BY created_at DESC'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['_action'] ?? '';

if ($action === 'create') {
    $title = clean($input['title'] ?? '');
    if (!$title) jsonResponse(['error' => 'Title is required'], 400);
    $id = insertGetId(
        'INSERT INTO jobs (title, type, dept, location, experience, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$title, clean($input['type'] ?? 'full') ?: 'full', clean($input['dept'] ?? ''), clean($input['location'] ?? ''), clean($input['experience'] ?? ''), clean($input['description'] ?? ''), !empty($input['is_active']) ? 1 : 0]
    );
    jsonResponse(['success' => true, 'id' => $id]);
}

if ($action === 'update') {
    $id = (int) ($input['id'] ?? 0);
    $title = clean($input['title'] ?? '');
    if (!$id || !$title) jsonResponse(['error' => 'Title is required'], 400);
    execute(
        'UPDATE jobs SET title=?, type=?, dept=?, location=?, experience=?, description=?, is_active=? WHERE id=?',
        [$title, clean($input['type'] ?? 'full') ?: 'full', clean($input['dept'] ?? ''), clean($input['location'] ?? ''), clean($input['experience'] ?? ''), clean($input['description'] ?? ''), !empty($input['is_active']) ? 1 : 0, $id]
    );
    jsonResponse(['success' => true]);
}

if ($action === 'delete') {
    $id = (int) ($input['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'id required'], 400);
    execute('DELETE FROM jobs WHERE id = ?', [$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Unknown action'], 400);
