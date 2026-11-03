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
if ($password === '' || strlen($password) > 1024) {
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
    db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
}
session_regenerate_id(true);
$_SESSION['admin'] = ['id' => (int)$admin['id'], 'name' => $admin['name'], 'email' => $admin['email']];
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
api_response(true, 'Đăng nhập thành công.', ['admin' => current_admin(), 'csrf_token' => csrf_token()]);
