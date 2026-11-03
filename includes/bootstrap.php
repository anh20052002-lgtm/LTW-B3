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
    $defaults = ['db_host' => '127.0.0.1', 'db_port' => 3306, 'db_name' => 'nexa_crm', 'db_user' => '', 'db_pass' => '', 'app_url' => ''];
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
        unset($_SESSION['admin']);
        session_regenerate_id(true);
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
    return $_SESSION['admin'] ?? null;
}

function require_admin_page(): void
{
    if (!current_admin()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/validation.php';
