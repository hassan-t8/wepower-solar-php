<?php
/**
 * Dashboard stats aggregation — PHP port of GET /admin/api/stats in routes/admin.js.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$stats = [
    'visitors' => [
        'total' => countRows('SELECT COUNT(*) c FROM visitors'),
        'today' => countRows('SELECT COUNT(*) c FROM visitors WHERE visited_at >= CURDATE()'),
        'last7' => countRows('SELECT COUNT(*) c FROM visitors WHERE visited_at >= (NOW() - INTERVAL 7 DAY)'),
        'last30' => countRows('SELECT COUNT(*) c FROM visitors WHERE visited_at >= (NOW() - INTERVAL 30 DAY)'),
    ],
    'contacts' => [
        'total' => countRows('SELECT COUNT(*) c FROM contacts'),
        'new' => countRows("SELECT COUNT(*) c FROM contacts WHERE status = 'new'"),
    ],
    'bookings' => [
        'total' => countRows('SELECT COUNT(*) c FROM bookings'),
        'pending' => countRows("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'"),
    ],
    'applications' => [
        'total' => countRows('SELECT COUNT(*) c FROM applications'),
        'new' => countRows("SELECT COUNT(*) c FROM applications WHERE status = 'new'"),
    ],
    'calculations' => ['total' => countRows('SELECT COUNT(*) c FROM load_calculations')],
    'careers' => [
        'total' => countRows('SELECT COUNT(*) c FROM careers'),
        'new' => countRows("SELECT COUNT(*) c FROM careers WHERE status = 'new'"),
    ],
];

$dailyVisitors = fetchAll(
    "SELECT DATE(visited_at) as day, COUNT(*) as count FROM visitors
     WHERE visited_at >= (NOW() - INTERVAL 14 DAY)
     GROUP BY DATE(visited_at) ORDER BY day ASC"
);

$topPages = fetchAll(
    "SELECT page, COUNT(*) as count FROM visitors
     WHERE visited_at >= (NOW() - INTERVAL 30 DAY)
     GROUP BY page ORDER BY count DESC LIMIT 8"
);

$recentApplications = fetchAll(
    "SELECT id, name, service_type, city, status, created_at FROM applications ORDER BY created_at DESC LIMIT 8"
);

jsonResponse([
    'stats' => $stats,
    'dailyVisitors' => $dailyVisitors,
    'topPages' => $topPages,
    'recentApplications' => $recentApplications,
]);
