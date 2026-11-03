<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$input = json_input();
$id = positive_id($input['id'] ?? null);
$stmt = db()->prepare('DELETE FROM customer_requests WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->rowCount()) {
    api_response(false, 'Yêu cầu không tồn tại hoặc đã được xóa.', null, [], 404);
}
api_response(true, 'Đã xóa yêu cầu tư vấn.', ['id' => $id]);
