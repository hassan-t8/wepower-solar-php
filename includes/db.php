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
