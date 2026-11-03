<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = config();
    if ((string)$c['db_user'] === '') {
        throw new RuntimeException('Database credentials are not configured.');
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['db_host'], (int)$c['db_port'], $c['db_name']);
    $pdo = new PDO($dsn, (string)$c['db_user'], (string)$c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
    $pdo->exec("SET time_zone = '+07:00'");
    return $pdo;
}

function find_record(string $table, int $id): ?array
{
    if (!in_array($table, ['customers', 'customer_requests'], true)) {
        throw new LogicException('Invalid record table.');
    }
    $stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Shared IP limiter across sessions. Only REMOTE_ADDR is trusted, never client proxy headers. */
function rate_limit(string $scope, int $max, int $seconds): void
{
    $key = hash('sha256', $scope . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $stmt = db()->prepare('INSERT INTO rate_limits (bucket_key, hits, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND)) ON DUPLICATE KEY UPDATE hits = IF(expires_at <= NOW(), 1, hits + 1), expires_at = IF(expires_at <= NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), expires_at)');
    $stmt->execute([$key, $seconds, $seconds]);
    $stmt = db()->prepare('SELECT hits, GREATEST(1, TIMESTAMPDIFF(SECOND, NOW(), expires_at)) AS retry_after FROM rate_limits WHERE bucket_key = ?');
    $stmt->execute([$key]);
    $limit = $stmt->fetch();
    if ((int)$limit['hits'] > $max) {
        header('Retry-After: ' . (int)$limit['retry_after']);
        api_response(false, 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.', null, [], 429);
    }
    if (random_int(1, 100) === 1) {
        db()->exec('DELETE FROM rate_limits WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 1000');
    }
}
