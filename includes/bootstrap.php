<?php
declare(strict_types=1);

define('NEXA_ROOT', dirname(__DIR__));
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

function config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }
    $local = NEXA_ROOT . '/config/config.local.php';
    $config = is_file($local) ? require $local : [];
    if (!is_array($config)) {
        throw new RuntimeException('Invalid local configuration.');
    }
    $defaults = [
        'db_host' => '127.0.0.1', 'db_port' => 3306, 'db_name' => 'nexa_crm',
        'db_user' => '', 'db_pass' => '', 'app_url' => '',
        'contact_email' => '', 'contact_phone' => '', 'contact_address' => '', 'contact_hours' => '',
    ];
    $config = array_merge($defaults, $config);
    foreach ($defaults as $key => $default) {
        $value = getenv('NEXA_' . strtoupper($key));
        if ($value !== false) {
            $config[$key] = $value;
        }
    }
    return $config;
}

function url(string $path = ''): string
{
    $configured = rtrim((string)config()['app_url'], '/');
    if ($configured !== '') {
        return $configured . '/' . ltrim($path, '/');
    }
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $filename = str_replace('\\', '/', (string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $root = str_replace('\\', '/', NEXA_ROOT);
    if ($filename !== '' && strncasecmp($filename, $root . '/', strlen($root) + 1) === 0) {
        $relative = substr($filename, strlen($root));
        if (str_ends_with($script, $relative)) {
            return substr($script, 0, -strlen($relative)) . '/' . ltrim($path, '/');
        }
    }
    if (preg_match('~^(.*?)/(?:api|admin)/~', $script, $matches)) {
        $base = $matches[1];
    } else {
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/.');
    }
    return $base . '/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function service_options(): array
{
    return ['website' => 'Thiết kế website', 'crm' => 'Giải pháp CRM', 'marketing' => 'Digital Marketing', 'consulting' => 'Tư vấn chuyển đổi số'];
}

function status_options(): array
{
    return ['new' => 'Mới', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành'];
}

/** Only explicitly configured public details are shown; Admin email is never a fallback. */
function public_contact_details(): array
{
    $settings = config();
    $details = [];
    foreach (['email', 'phone', 'address', 'hours'] as $field) {
        $value = $settings['contact_' . $field] ?? '';
        $details[$field] = is_string($value) ? trim($value) : '';
    }
    if (strlen($details['email']) > 190 || !filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
        $details['email'] = '';
    }
    if (!preg_match('/^\+?[0-9][0-9\s().\-]{3,28}[0-9]$/D', $details['phone'])) {
        $details['phone'] = '';
    }
    foreach (['address' => 500, 'hours' => 190] as $field => $limit) {
        if (string_length($details[$field]) > $limit) $details[$field] = '';
    }
    return $details;
}

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    header('Cache-Control: no-store, private');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('nexa_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => parse_url(url(), PHP_URL_PATH) ?: '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    if (!empty($_SESSION['admin']) && (int)($_SESSION['last_activity'] ?? 0) < time() - 7200) {
        invalidate_admin_session();
    }
    if (!empty($_SESSION['admin'])) {
        $_SESSION['last_activity'] = time();
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function current_admin(): ?array
{
    $sessionAdmin = $_SESSION['admin'] ?? null;
    if (!is_array($sessionAdmin)) return null;
    $id = filter_var($sessionAdmin['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $fingerprint = $_SESSION['admin_auth'] ?? '';
    if ($id === false || !is_string($fingerprint) || !preg_match('/^[a-f0-9]{64}$/D', $fingerprint)) {
        invalidate_admin_session();
        return null;
    }
    // One database validation per request; changing the fingerprint invalidates this cache.
    static $verifiedKey = '', $verifiedAdmin = null;
    $key = $id . ':' . $fingerprint;
    if ($verifiedKey === $key) return $verifiedAdmin;
    $stmt = db()->prepare('SELECT id, name, email, password_hash, is_active FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    if (!$record || !(bool)$record['is_active'] || !hash_equals($fingerprint, hash('sha256', $record['password_hash']))) {
        invalidate_admin_session();
        return null;
    }
    $verifiedKey = $key;
    $verifiedAdmin = ['id' => (int)$record['id'], 'name' => $record['name'], 'email' => $record['email']];
    $_SESSION['admin'] = $verifiedAdmin;
    return $verifiedAdmin;
}

function invalidate_admin_session(): void
{
    unset($_SESSION['admin'], $_SESSION['admin_auth'], $_SESSION['last_activity']);
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function require_admin_page(): void
{
    try {
        $admin = current_admin();
    } catch (PDOException | RuntimeException $exception) {
        error_log('[Nexa] Admin session validation unavailable (' . get_class($exception) . ').');
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nexa — Kết nối tạm gián đoạn</title><h1>Chưa thể mở khu vực quản trị</h1><p>Hệ thống dữ liệu tạm thời chưa sẵn sàng. Vui lòng thử lại sau.</p></html>';
        exit;
    }
    if (!$admin) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/validation.php';
