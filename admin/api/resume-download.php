<?php
/**
 * Resume download — PHP port of GET /admin/api/careers/:id/resume.
 * ?id=<careers row id>
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$id = (int) ($_GET['id'] ?? 0);
$row = fetchOne('SELECT resume_filename, full_name FROM careers WHERE id = ?', [$id]);
if (!$row || !$row['resume_filename']) jsonResponse(['error' => 'No resume on file'], 404);

$path = __DIR__ . '/../../uploads/resumes/' . $row['resume_filename'];
if (!file_exists($path)) jsonResponse(['error' => 'File missing'], 404);

$ext = pathinfo($row['resume_filename'], PATHINFO_EXTENSION);
$downloadName = preg_replace('/[^a-zA-Z0-9._ -]/', '_', $row['full_name']) . '-resume.' . $ext;

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
