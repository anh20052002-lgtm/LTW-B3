<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('GET');
$admin = require_admin_api();
$stmt = db()->prepare('SELECT id, name, email, created_at FROM admins WHERE id = ? AND is_active = 1');
$stmt->execute([$admin['id']]);
$account = $stmt->fetch();
if (!$account) {
    invalidate_admin_session();
    api_response(false, 'Phiên đăng nhập không còn hợp lệ. Vui lòng đăng nhập lại.', null, [], 401);
}
$account['id'] = (int)$account['id'];
api_response(true, 'Thông tin tài khoản quản trị.', $account);
