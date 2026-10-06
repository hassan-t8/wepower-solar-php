<?php
/**
 * General-purpose helpers: input sanitizing, JSON responses, rate limiting,
 * file upload handling, visitor tracking, and CSV export — small PHP
 * equivalents of what Express middleware (helmet/multer/express-rate-limit)
 * handled automatically in the Node version.
 */

require_once __DIR__ . '/db.php';

/** Trim + strip tags on a scalar; safe default for stray types. */
function clean($value): string
{
    if (!is_scalar($value)) return '';
    return trim(strip_tags((string) $value));
}

/** Strict-enough email check: filter_var plus a real TLD (rejects "a@b"). */
function isValidEmail(string $email): bool
{
    return strlen($email) <= 254
        && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && preg_match('/@[^@]+\.[A-Za-z]{2,}$/', $email) === 1;
}

/**
 * Normalize a phone number to "+<code> <national digits>", or return null if
 * invalid. The forms send "+92 3001234567"; a bare "03001234567" (old
 * clients) is treated as Pakistani. Pakistan must be a 10-digit mobile
 * starting with 3; other countries just need 7–14 national digits (E.164).
 */
function normalizePhone(string $raw): ?string
{
    $raw = trim($raw);
    if (preg_match('/^\+(\d{1,4})[\s-]*([\d\s-]+)$/', $raw, $m)) {
        $code = $m[1];
        $national = ltrim(preg_replace('/\D/', '', $m[2]), '0');
    } else {
        $code = '92';
        $national = ltrim(preg_replace('/\D/', '', $raw), '0');
    }
    if ($code === '92') {
        return preg_match('/^3\d{9}$/', $national) ? "+92 $national" : null;
    }
    $len = strlen($national);
    return ($len >= 7 && $len <= 14 && strlen($code . $national) <= 15) ? "+$code $national" : null;
}

/** Send a JSON response and stop execution — mirrors res.json()/res.status().json(). */
function jsonResponse($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Client IP for rate limits and logs. Uses the real connection address.
 * X-Forwarded-For is only trusted when the request comes from a proxy listed
 * in TRUSTED_PROXIES (config.php, e.g. if a CDN is put in front later);
 * otherwise anyone could send a fake header and bypass the login/form limits.
 */
function clientIp(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $trusted = defined('TRUSTED_PROXIES') ? (array) TRUSTED_PROXIES : [];
    if ($trusted && in_array($remote, $trusted, true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip = end($parts); // the address the trusted proxy itself saw
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return $remote;
}

/**
 * DB-backed rate limiter for public form submissions.
 * Mirrors express-rate-limit's formLimiter: 20 requests / 15 min / IP.
 * Call at the top of every api/*.php endpoint before doing any work.
 */
function checkFormRateLimit(string $endpoint): void
{
    $ip = clientIp();
    $count = countRows(
        'SELECT COUNT(*) c FROM form_submissions WHERE ip = ? AND submitted_at > (NOW() - INTERVAL 15 MINUTE)',
        [$ip]
    );
    if ($count >= 20) {
        jsonResponse(['error' => 'Too many submissions. Please try again later.'], 429);
    }
    execute('INSERT INTO form_submissions (ip, endpoint) VALUES (?, ?)', [$ip, $endpoint]);

    // Opportunistic cleanup (~1% of requests) — avoids needing a cron job.
    if (mt_rand(1, 100) === 1) {
        execute('DELETE FROM form_submissions WHERE submitted_at < (NOW() - INTERVAL 1 DAY)');
    }
}

/**
 * DB-backed rate limiter for admin login attempts.
 * Mirrors loginLimiter: 10 attempts / 10 min / IP.
 * Returns true if the request should be BLOCKED.
 */
function isLoginRateLimited(): bool
{
    $count = countRows(
        'SELECT COUNT(*) c FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL 10 MINUTE)',
        [clientIp()]
    );
    return $count >= 10;
}

function recordLoginAttempt(?string $email): void
{
    execute('INSERT INTO login_attempts (ip, email) VALUES (?, ?)', [clientIp(), $email]);
    if (mt_rand(1, 100) === 1) {
        execute('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
    }
}

/**
 * Visitor-page-view tracking. Called once from header.php on every public
 * page (never from api/*.php or admin/* — since header.php is only ever
 * included by real page files, no path-filtering logic is needed here,
 * unlike the Node middleware which had to check req.path).
 */
function trackVisit(string $page): void
{
    try {
        execute(
            'INSERT INTO visitors (ip, user_agent, page, referrer) VALUES (?, ?, ?, ?)',
            [
                clientIp(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
                $page,
                substr($_SERVER['HTTP_REFERER'] ?? '', 0, 250),
            ]
        );
    } catch (Throwable $e) {
        // silently ignore, matches the Node middleware's try/catch-and-ignore
    }
}

/**
 * Handle a single uploaded file with an extension whitelist + size limit.
 * Returns the saved filename (not full path) on success, or throws.
 * Filename scheme matches multer's: `${Date.now()}-${sanitized}`.
 */
function handleUpload(array $file, array $allowedExt, int $maxBytes, string $destDir): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File is too large.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array('.' . $ext, $allowedExt, true)) {
        throw new RuntimeException('File type not allowed.');
    }
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $filename = round(microtime(true) * 1000) . '-' . $safeName . '.' . $ext;

    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $target = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return $filename;
}

/** Stream a query result as a downloadable CSV — mirrors the admin CSV export route. */
function csvExport(string $type, array $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $type . '-' . time() . '.csv"');

    if (empty($rows)) {
        echo 'No data';
        exit;
    }

    $escape = function ($v) {
        if ($v === null) return '';
        $s = str_replace('"', '""', (string) $v);
        return preg_match('/[",\n]/', $s) ? '"' . $s . '"' : $s;
    };

    $headers = array_keys($rows[0]);
    echo implode(',', $headers) . "\n";
    foreach ($rows as $row) {
        echo implode(',', array_map(fn($h) => $escape($row[$h] ?? ''), $headers)) . "\n";
    }
    exit;
}

/**
 * Versioned URL for a file under /assets — appends ?v=<content hash> so
 * browsers and the host's cache fetch the new copy after every deploy
 * instead of serving stale CSS/JS with fresh HTML.
 */
function asset(string $path): string
{
    static $cache = [];
    if (!isset($cache[$path])) {
        $file = dirname(__DIR__) . $path;
        $cache[$path] = is_file($file) ? $path . '?v=' . substr(md5_file($file), 0, 10) : $path;
    }
    return $cache[$path];
}

/** Escape for safe HTML output — shorthand for htmlspecialchars(). */
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
