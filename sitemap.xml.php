<?php
/**
 * Dynamically generated sitemap.xml — served at /sitemap.xml via the
 * .htaccess rewrite rule below. Uses SITE_URL from config.php so it
 * always matches whatever domain this is actually deployed on.
 */
require_once __DIR__ . '/config/config.php';
header('Content-Type: application/xml; charset=utf-8');

$siteUrl = rtrim(SITE_URL, '/');
$pages = [
    ['loc' => '/', 'priority' => '1.0'],
    ['loc' => '/about', 'priority' => '0.8'],
    ['loc' => '/services', 'priority' => '0.9'],
    ['loc' => '/load-calculator', 'priority' => '0.8'],
    ['loc' => '/careers', 'priority' => '0.6'],
    ['loc' => '/contact', 'priority' => '0.7'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $p): ?>
  <url>
    <loc><?= htmlspecialchars($siteUrl . $p['loc']) ?></loc>
    <changefreq>weekly</changefreq>
    <priority><?= $p['priority'] ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
