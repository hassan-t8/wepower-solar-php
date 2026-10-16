<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/settings.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Location + call button: normalise and validate before anything is written.
$normalized = [];
if (array_key_exists('company_map_embed', $input)) {
    $v = trim((string) $input['company_map_embed']);
    // Accept the whole "Embed a map" HTML from Google Maps and keep just its src.
    if (preg_match('/<iframe[^>]*\ssrc\s*=\s*["\']([^"\']+)["\']/i', $v, $m)) $v = html_entity_decode($m[1], ENT_QUOTES);
    if ($v !== '' && !preg_match('#^https://www\.google\.com/maps/embed\?#', $v)) {
        jsonResponse(['error' => 'Map embed must be a Google Maps embed. In Google Maps: Share → Embed a map → Copy HTML, then paste it here.'], 422);
    }
    $normalized['company_map_embed'] = $input['company_map_embed'] = $v;
}
if (array_key_exists('company_map_link', $input)) {
    $v = trim((string) $input['company_map_link']);
    if ($v !== '' && !preg_match('#^https://\S+$#', $v)) {
        jsonResponse(['error' => 'Location link must be a full web link starting with https:// (Google Maps → Share → Copy link).'], 422);
    }
    $normalized['company_map_link'] = $input['company_map_link'] = $v;
}
if (array_key_exists('company_call_number', $input)) {
    $v = trim((string) $input['company_call_number']);
    $digits = preg_replace('/\D/', '', $v);
    if ($v !== '' && (!preg_match('/^\+?[\d\s-]+$/', $v) || strlen($digits) < 7 || strlen($digits) > 15)) {
        jsonResponse(['error' => 'Call button number can only contain digits, spaces, dashes and a leading + (7–15 digits).'], 422);
    }
    $normalized['company_call_number'] = $input['company_call_number'] = $v;
}

foreach (ADMIN_SETTABLE_KEYS as $k) {
    if (!array_key_exists($k, $input)) continue;
    // Don't overwrite smtp_pass if the client sent the masked placeholder back unchanged.
    if ($k === 'smtp_pass' && $input[$k] === '••••••••') continue;
    setSetting($k, (string) $input[$k]);
}

jsonResponse(['success' => true, 'values' => $normalized]);
