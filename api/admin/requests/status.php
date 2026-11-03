<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$input = json_input();
$id = positive_id($input['id'] ?? null);
$status = text_value($input, 'status');
require_valid(array_key_exists($status, status_options()) ? [] : ['status' => 'Trạng thái không hợp lệ.']);
record_or_404('customer_requests', $id);
$stmt = db()->prepare('UPDATE customer_requests SET status = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$status, $id]);
api_response(true, 'Đã cập nhật trạng thái xử lý.', record_or_404('customer_requests', $id));
