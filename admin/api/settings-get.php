<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

$rows = fetchAll('SELECT `key`, value FROM site_settings');
$settings = [];
foreach ($rows as $r) $settings[$r['key']] = $r['value'];
if (!empty($settings['smtp_pass'])) $settings['smtp_pass'] = '••••••••';

jsonResponse($settings);
