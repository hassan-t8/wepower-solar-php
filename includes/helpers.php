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

/** Send a JSON response and stop execution — mirrors res.json()/res.status().json(). */
function jsonResponse($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/** Client IP, respecting a trusted reverse-proxy header if present. */
function clientIp(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
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

/** Escape for safe HTML output — shorthand for htmlspecialchars(). */
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
