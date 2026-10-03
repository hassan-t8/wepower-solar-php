<?php
/**
 * Serves the admin push service worker (admin/sw.js) with no-cache headers.
 * The hosting CDN caches static .js files for days, which kept browsers on a
 * stale service worker; PHP responses are never cached, so registering this
 * URL guarantees every browser gets the current worker.
 */
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Service-Worker-Allowed: /admin/');
readfile(__DIR__ . '/sw.js');
