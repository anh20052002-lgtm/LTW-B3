<?php
declare(strict_types=1);

// Local PHP development server only:
// php -S 127.0.0.1:8080 -t . scripts/dev-router.php
// Apache/XAMPP uses .htaccess instead. Do not use this router for production.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$path = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));
$path = str_replace('\\', '/', $path);
$segments = explode('/', $path);
$forbidden = preg_match('~(?:^|/)(?:config|includes|scripts|tests)(?:/|$)~i', $path)
    || preg_match('~(?:^|/)\.~', $path)
    || preg_match('~\.(?:sql|md|docx|log|ini|bak|zip|py|ps1|sh)$|\.(?:example|local)\.php$~i', $path)
    || str_contains($path, "\0")
    || in_array('..', $segments, true);

if ($forbidden) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Không được phép truy cập đường dẫn này.';
    return true;
}

$resolved = realpath($root . '/' . ltrim($path, '/'));
$normalizedRoot = str_replace('\\', '/', $root) . '/';
$normalizedResolved = $resolved === false ? '' : str_replace('\\', '/', $resolved);
$inside = $normalizedResolved !== '' && (strcasecmp($normalizedResolved, rtrim($normalizedRoot, '/')) === 0
    || strncasecmp($normalizedResolved, $normalizedRoot, strlen($normalizedRoot)) === 0);
$allowedDirectory = in_array(rtrim($path, '/'), ['', '/admin'], true);
$allowedPhp = preg_match('~^/(?:index\.php|admin/(?:index|login|requests|customers)\.php|api/(?!_)[a-zA-Z0-9_/-]+\.php)$~D', $path);
$allowedAsset = str_starts_with($path, '/assets/') && !preg_match('~\.php$~i', $path);

if ($inside && (($allowedDirectory && is_dir($resolved)) || (is_file($resolved) && ($allowedPhp || $allowedAsset)))) {
    return false;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Không tìm thấy trang.';
return true;
