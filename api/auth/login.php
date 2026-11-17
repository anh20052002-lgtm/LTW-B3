<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('POST');
verify_csrf();
$input = json_input();
$email = strtolower(text_value($input, 'email'));
$password = is_string($input['password'] ?? null) ? $input['password'] : '';
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    $errors['email'] = 'Nhập địa chỉ email hợp lệ.';
}
if ($password === '' || strlen($password) > 1024 || str_contains($password, "\0")) {
    $errors['password'] = 'Vui lòng nhập mật khẩu hợp lệ.';
}
require_valid($errors);
rate_limit('login', 10, 900);
$stmt = db()->prepare('SELECT id, name, email, password_hash, is_active FROM admins WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$admin = $stmt->fetch();
// Always run password verification, including unknown accounts, to reduce timing differences.
$hash = $admin['password_hash'] ?? password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
$valid = password_verify($password, $hash);
if (!$admin || !$valid || !(bool)$admin['is_active']) {
    api_response(false, 'Email hoặc mật khẩu không chính xác.', null, [], 401);
}
if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
    $rehash = password_hash($password, PASSWORD_DEFAULT);
    // Do not overwrite a password changed by another session after verification.
    $stmt = db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ? AND password_hash = ? AND is_active = 1');
    $stmt->execute([$rehash, $admin['id'], $admin['password_hash']]);
    if ($stmt->rowCount() !== 1) {
        api_response(false, 'Email hoặc mật khẩu không chính xác.', null, [], 401);
    }
    $admin['password_hash'] = $rehash;
}
session_regenerate_id(true);
$_SESSION['admin'] = ['id' => (int)$admin['id'], 'name' => $admin['name'], 'email' => $admin['email']];
$_SESSION['admin_auth'] = hash('sha256', $admin['password_hash']);
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$authenticated = current_admin();
if (!$authenticated) {
    api_response(false, 'Email hoặc mật khẩu không chính xác.', null, [], 401);
}
api_response(true, 'Đăng nhập thành công.', ['admin' => $authenticated, 'csrf_token' => csrf_token()]);
