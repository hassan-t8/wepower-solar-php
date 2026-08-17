<?php
/**
 * Shared page chrome — <head> through the opening of <main>.
 * Every public page sets $pageTitle/$pageDescription (and optionally
 * $canonical/$bodyClass/$headerVariant) BEFORE including this file.
 *
 * PHP equivalent of Header.jsx + the <head> markup client/index.html had
 * baked into the SPA shell — except here every page gets its OWN real
 * title/meta tags, which is the core SEO upgrade over the old SPA.
 */

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/lang.php'; // sets $LANG_CODE/$DIR, defines t(), applies Urdu content overrides

$pageTitle       = $pageTitle       ?? 'WePower Solar Solutions — Empower Yourself with Solar Energy';
$pageDescription = $pageDescription ?? 'Pakistan\'s trusted solar EPC partner — residential, commercial & industrial solar installation, net metering, and O&M services.';
$bodyClass       = $bodyClass       ?? '';
$headerVariant   = $headerVariant   ?? 'light'; // light | solid
$pageScripts     = $pageScripts     ?? [];

$siteUrl   = rtrim(SITE_URL, '/');
$canonical = $canonical ?? ($siteUrl . ($_SERVER['REQUEST_URI'] ?? '/'));

$logo       = setting('company_logo', $brand['logo']);
$companyName = setting('company_name', $brand['name']);

// Visitor page-view tracking (public pages only — this file is never
// included from api/*.php or admin/*, so no path filtering is needed).
trackVisit($_SERVER['REQUEST_URI'] ?? '/');
?>
<!DOCTYPE html>
<html lang="<?= h($LANG_CODE) ?>" dir="<?= h($DIR) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($pageDescription) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<meta property="og:image" content="<?= h($siteUrl . $logo) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">

<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="stylesheet" href="/assets/css/global.css">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">
<link rel="stylesheet" href="/assets/css/pages.css">
<link rel="stylesheet" href="/assets/css/apply-modal.css">
<link rel="stylesheet" href="/assets/css/toast.css">
<link rel="stylesheet" href="/assets/css/whatsapp-button.css">
<link rel="stylesheet" href="/assets/css/video-popup.css">
<link rel="stylesheet" href="/assets/css/lang.css">
<?php foreach ($pageStyles ?? [] as $style): ?>
<link rel="stylesheet" href="/assets/css/<?= h($style) ?>">
<?php endforeach; ?>
</head>
<body class="<?= h($bodyClass) ?><?= $LANG_CODE === 'ur' ? ' lang-ur' : '' ?>">

<header class="hdr hdr-<?= h($headerVariant) ?>" id="siteHeader">
  <div class="container hdr-inner">
    <a href="/index.php" class="hdr-logo">
      <img src="<?= h($logo) ?>" alt="<?= h($companyName) ?>">
    </a>

    <nav class="hdr-nav" id="hdrNav">
      <?php foreach ($nav as $n): ?>
        <?php
          $isHome = $n['to'] === '/index.php';
          $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
          $isActive = $isHome ? (basename($currentPath) === 'index.php' || $currentPath === '/') : (basename($currentPath) === basename($n['to']));
        ?>
        <a href="<?= h($n['to']) ?>" class="<?= $isActive ? 'active' : '' ?>"><?= h($n['label']) ?></a>
      <?php endforeach; ?>
      <button type="button" class="btn btn-primary btn-sm hdr-cta" data-open-apply-modal><?= h(t('common.getQuote')) ?></button>

      <div class="lang-switch" id="langSwitch">
        <button type="button" class="lang-switch-btn" id="langSwitchBtn" aria-haspopup="listbox" aria-expanded="false" aria-label="<?= h(t('langSwitcher.label')) ?>">
          <i class="bi bi-translate"></i>
          <span><?= $LANG_CODE === 'ur' ? 'اردو' : 'EN' ?></span>
          <i class="bi bi-chevron-down lang-switch-caret"></i>
        </button>
        <div class="lang-switch-menu" id="langSwitchMenu" role="listbox" hidden>
          <a href="?lang=en" role="option" aria-selected="<?= $LANG_CODE === 'en' ? 'true' : 'false' ?>" class="<?= $LANG_CODE === 'en' ? 'active' : '' ?>">English</a>
          <a href="?lang=ur" role="option" aria-selected="<?= $LANG_CODE === 'ur' ? 'true' : 'false' ?>" class="<?= $LANG_CODE === 'ur' ? 'active' : '' ?>">اردو</a>
        </div>
      </div>
    </nav>

    <button class="hdr-toggle" id="hdrToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <div class="lang-hint" id="langHint" hidden>
    <div class="lang-hint-arrow"></div>
    <button type="button" class="lang-hint-close" id="langHintClose" aria-label="Dismiss"><i class="bi bi-x"></i></button>
    <p class="lang-hint-en"><?= h(t('langHint.en')) ?></p>
    <p class="lang-hint-ur"><?= h(t('langHint.ur')) ?></p>
    <button type="button" class="btn btn-primary btn-sm lang-hint-btn" id="langHintBtn"><?= h(t('langHint.dismiss')) ?></button>
  </div>
</header>

<main class="page-main">
