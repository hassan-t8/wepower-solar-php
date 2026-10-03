<?php
/**
 * Resume download / inline view — PHP port of GET /admin/api/careers/:id/resume.
 * ?id=<careers row id>            → download (attachment)
 * ?id=<careers row id>&mode=view  → served inline so the admin resume viewer can
 *                                   display it (PDF natively, DOCX rendered client-side)
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$id = (int) ($_GET['id'] ?? 0);
$row = fetchOne('SELECT resume_filename, full_name FROM careers WHERE id = ?', [$id]);
if (!$row || !$row['resume_filename']) jsonResponse(['error' => 'No resume on file'], 404);

$path = __DIR__ . '/../../uploads/resumes/' . basename($row['resume_filename']);
if (!file_exists($path)) jsonResponse(['error' => 'File missing'], 404);

$ext = strtolower(pathinfo($row['resume_filename'], PATHINFO_EXTENSION));
$downloadName = preg_replace('/[^a-zA-Z0-9._ -]/', '_', $row['full_name']) . '-resume.' . $ext;
$mimeTypes = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$inline = ($_GET['mode'] ?? '') === 'view' && $ext === 'pdf';

header('Content-Type: ' . ($inline ? 'application/pdf' : ($mimeTypes[$ext] ?? 'application/octet-stream')));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
exit;
