<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('POST');
$admin = require_admin_api();
verify_csrf();
$input = json_input();
$current = is_string($input['current_password'] ?? null) ? $input['current_password'] : '';
$password = is_string($input['new_password'] ?? null) ? $input['new_password'] : '';
$confirmation = is_string($input['confirm_password'] ?? null) ? $input['confirm_password'] : '';
$errors = [];
if ($current === '' || strlen($current) > 1024 || str_contains($current, "\0")) {
    $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại hợp lệ.';
}
// Keep the same bcrypt byte limit as the existing Admin CLI, without trimming passwords.
if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
    $errors['new_password'] = 'Mật khẩu mới cần từ 12 đến 72 byte và không chứa ký tự null.';
}
if ($confirmation !== $password || $confirmation === '') {
    $errors['confirm_password'] = 'Mật khẩu xác nhận chưa khớp.';
}
require_valid($errors);
rate_limit('password_change:' . $admin['id'], 5, 900);

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT password_hash, is_active FROM admins WHERE id = ? FOR UPDATE');
    $stmt->execute([$admin['id']]);
    $locked = $stmt->fetch();
    if (!$locked || !(bool)$locked['is_active'] || !hash_equals((string)($_SESSION['admin_auth'] ?? ''), hash('sha256', $locked['password_hash']))) {
        $pdo->rollBack();
        invalidate_admin_session();
        api_response(false, 'Phiên đăng nhập không còn hợp lệ. Vui lòng đăng nhập lại.', null, [], 401);
    }
    if (!password_verify($current, $locked['password_hash'])) {
        $pdo->rollBack();
        require_valid(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
    }
    if (password_verify($password, $locked['password_hash'])) {
        $pdo->rollBack();
        require_valid(['new_password' => 'Mật khẩu mới cần khác mật khẩu hiện tại.']);
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
    $stmt->execute([$hash, $admin['id']]);
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
unset($current, $password, $confirmation, $input, $locked);
$_SESSION['admin_auth'] = hash('sha256', $hash);
$_SESSION['last_activity'] = time();
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
api_response(true, 'Đã đổi mật khẩu. Các phiên đăng nhập khác sẽ cần đăng nhập lại.', ['csrf_token' => csrf_token()]);
