<?php
/**
 * PDO connection singleton + small query helpers.
 * Direct equivalent of database.js's exported `db` handle, but using
 * PDO prepared statements against MySQL instead of better-sqlite3.
 */

require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            // All stored timestamps are UTC (the live MySQL server runs in UTC). Pin it so
            // every environment agrees; the admin browser converts to local time for display.
            $pdo->exec("SET time_zone = '+00:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            if (APP_ENV !== 'production') {
                die('Database connection failed: ' . $e->getMessage());
            }
            die('Database connection failed. Please try again later.');
        }
    }
    return $pdo;
}

/** UTC 'Y-m-d H:i:s' for the start of a local (PHP timezone) day, e.g. 'today', '-6 days'. */
function utcDayStart(string $relative = 'today'): string
{
    $d = new DateTime($relative); // local timezone (config: Asia/Karachi)
    $d->setTime(0, 0);
    return $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

/** SQL expression: the local calendar date of a UTC DATETIME column. */
function localDateSql(string $column): string
{
    return "DATE(CONVERT_TZ($column, '+00:00', '" . date('P') . "'))";
}

/** Run a query and return the first row (or null). */
function fetchOne(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** Run a query and return all rows. */
function fetchAll(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Run an INSERT/UPDATE/DELETE. Returns the PDOStatement (for lastInsertId etc). */
function execute(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Convenience: run an INSERT and return the new row's id. */
function insertGetId(string $sql, array $params = []): int
{
    execute($sql, $params);
    return (int) db()->lastInsertId();
}

/** Convenience: run a COUNT(*)-style query and return the int. */
function countRows(string $sql, array $params = []): int
{
    $row = fetchOne($sql, $params);
    return $row ? (int) reset($row) : 0;
}
