<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$input = json_input();
$id = positive_id($input['id'] ?? null);
$existing = record_or_404('customer_requests', $id);
// Editing contact snapshots does not silently remove an existing customer link.
// A caller can unlink explicitly by supplying customer_id: null.
if (!array_key_exists('customer_id', $input)) {
    $input['customer_id'] = $existing['customer_id'];
}
$data = request_fields($input);
require_valid(validate_request($data));
$stmt = db()->prepare('UPDATE customer_requests SET customer_id = ?, full_name = ?, phone = ?, email = ?, service = ?, message = ?, status = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([$data['customer_id'], $data['full_name'], $data['phone'], $data['email'], $data['service'], $data['message'], $data['status'], $id]);
api_response(true, 'Đã cập nhật yêu cầu tư vấn.', record_or_404('customer_requests', $id));
