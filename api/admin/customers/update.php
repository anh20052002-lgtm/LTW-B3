<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$input = json_input();
$id = positive_id($input['id'] ?? null);
record_or_404('customers', $id);
$data = customer_fields($input);
require_valid(validate_customer($data));
$stmt = db()->prepare('UPDATE customers SET full_name = ?, phone = ?, email = ?, company = ?, notes = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$data['full_name'], $data['phone'], $data['email'], $data['company'], $data['notes'], $id]);
api_response(true, 'Đã cập nhật thông tin khách hàng.', record_or_404('customers', $id));
