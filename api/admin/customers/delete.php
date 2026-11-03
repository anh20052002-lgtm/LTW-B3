<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('POST');
require_admin_api();
verify_csrf();
$input = json_input();
$id = positive_id($input['id'] ?? null);
$stmt = db()->prepare('DELETE FROM customers WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->rowCount()) {
    api_response(false, 'Khách hàng không tồn tại hoặc đã được xóa.', null, [], 404);
}
api_response(true, 'Đã xóa khách hàng. Các yêu cầu tư vấn vẫn được giữ lại.', ['id' => $id]);
