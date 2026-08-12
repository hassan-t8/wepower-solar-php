<?php
/**
 * Public job listing (JSON) — PHP port of GET /api/jobs in server.js.
 * Kept for parity/AJAX use even though careers.php now server-renders
 * the job list directly (better for SEO than the original client fetch).
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(['error' => 'Method not allowed'], 405);

$jobs = fetchAll(
    "SELECT id, title, type, dept, location, experience, description FROM jobs WHERE is_active = 1 ORDER BY created_at DESC"
);
jsonResponse($jobs);
