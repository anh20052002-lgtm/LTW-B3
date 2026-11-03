<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$data = request_fields(json_input());
require_valid(validate_request($data));
$code = 'NX-' . strtoupper(bin2hex(random_bytes(6)));
$stmt = db()->prepare('INSERT INTO customer_requests (tracking_code, customer_id, full_name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$code, $data['customer_id'], $data['full_name'], $data['phone'], $data['email'], $data['service'], $data['message'], $data['status']]);
$record = record_or_404('customer_requests', (int)db()->lastInsertId());
api_response(true, 'Đã thêm yêu cầu tư vấn.', $record, [], 201);
