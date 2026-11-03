<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$data = customer_fields(json_input());
require_valid(validate_customer($data));
$stmt = db()->prepare('INSERT INTO customers (full_name, phone, email, company, notes) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$data['full_name'], $data['phone'], $data['email'], $data['company'], $data['notes']]);
api_response(true, 'Đã thêm khách hàng.', record_or_404('customers', (int)db()->lastInsertId()), [], 201);
