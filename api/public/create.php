<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('POST');
verify_csrf();
rate_limit('public_create', 5, 600);
$data = request_fields(json_input(), false);
require_valid(validate_request($data));
$code = 'NX-' . strtoupper(bin2hex(random_bytes(6)));
$stmt = db()->prepare('INSERT INTO customer_requests (tracking_code, customer_id, full_name, phone, email, service, message, status) VALUES (?, NULL, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$code, $data['full_name'], $data['phone'], $data['email'], $data['service'], $data['message'], 'new']);
api_response(true, 'Đã tiếp nhận yêu cầu tư vấn của bạn.', ['tracking_code' => $code], [], 201);
