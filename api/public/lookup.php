<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('POST');
verify_csrf();
rate_limit('public_lookup', 20, 600);
$input = json_input();
$code = strtoupper(text_value($input, 'tracking_code'));
$phone = canonical_phone(text_value($input, 'phone'));
$errors = [];
if (!preg_match('/^NX-[A-Z0-9]{12}$/D', $code)) {
    $errors['tracking_code'] = 'Mã tra cứu có dạng NX- và 12 ký tự.';
}
if (!valid_phone($phone)) {
    $errors['phone'] = 'Nhập số điện thoại đã dùng khi gửi yêu cầu.';
}
require_valid($errors);
$stmt = db()->prepare('SELECT tracking_code, service, status, created_at, updated_at FROM customer_requests WHERE tracking_code = ? AND phone = ? LIMIT 1');
$stmt->execute([$code, $phone]);
$row = $stmt->fetch();
if (!$row) {
    api_response(false, 'Không tìm thấy yêu cầu khớp với mã và số điện thoại này.', null, [], 404);
}
api_response(true, 'Đã tìm thấy yêu cầu.', $row);
