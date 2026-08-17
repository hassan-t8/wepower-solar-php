<?php
/**
 * Language switching: reads ?lang=en|ur (persists it to a cookie), falls
 * back to the existing cookie, and defaults to English. Must run BEFORE
 * any HTML output (setcookie requires that) and before config/site.php's
 * content is used, since it also applies the Urdu content overrides.
 *
 * Defines:
 *   $LANG_CODE  'en' | 'ur'
 *   $DIR        'ltr' | 'rtl'
 *   t($path)    dot-path UI-string lookup, e.g. t('nav.home')
 */

$requestedLang = $_GET['lang'] ?? null;
if ($requestedLang === 'en' || $requestedLang === 'ur') {
    if (!headers_sent()) {
        setcookie('wp_lang', $requestedLang, time() + 60 * 60 * 24 * 365, '/');
    }
    $_COOKIE['wp_lang'] = $requestedLang;
}

$LANG_CODE = ($_COOKIE['wp_lang'] ?? 'en') === 'ur' ? 'ur' : 'en';
$DIR = $LANG_CODE === 'ur' ? 'rtl' : 'ltr';

$GLOBALS['__LANG_EN'] = require __DIR__ . '/../config/lang/en.php';
$GLOBALS['__LANG'] = $LANG_CODE === 'ur' ? require __DIR__ . '/../config/lang/ur.php' : $GLOBALS['__LANG_EN'];

/** Dot-path UI-string lookup (e.g. t('nav.home'), t('careers.whyItems')). Falls back to English, then the key itself. */
function t(string $path)
{
    $lookup = function (array $dict) use ($path) {
        $cur = $dict;
        foreach (explode('.', $path) as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) return null;
            $cur = $cur[$seg];
        }
        return $cur;
    };
    $val = $lookup($GLOBALS['__LANG']);
    if ($val !== null) return $val;
    $val = $lookup($GLOBALS['__LANG_EN']);
    return $val !== null ? $val : $path;
}

// Apply Urdu content overrides on top of the English site.php content that
// was already loaded (this file is always required AFTER config/site.php).
if ($LANG_CODE === 'ur') {
    require __DIR__ . '/../config/site_ur.php';
}
