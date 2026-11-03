<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('GET');
require_admin_api();
[$page, $perPage, $search, $sort] = list_options();
$params = [];
$where = '';
if ($search !== '') {
    $where = " WHERE (c.full_name LIKE ? ESCAPE '!' OR c.phone LIKE ? ESCAPE '!' OR c.email LIKE ? ESCAPE '!' OR c.company LIKE ? ESCAPE '!')";
    $params = array_fill(0, 4, like_term($search));
}
$sorts = ['created_desc' => 'c.created_at DESC, c.id DESC', 'created_asc' => 'c.created_at ASC, c.id ASC', 'name_asc' => 'c.full_name ASC, c.id ASC', 'updated_desc' => 'c.updated_at DESC, c.id DESC'];
$order = $sorts[$sort] ?? $sorts['created_desc'];
$stmt = db()->prepare('SELECT COUNT(*) FROM customers c' . $where);
$stmt->execute($params);
$paging = pagination((int)$stmt->fetchColumn(), $page, $perPage);
$offset = ($paging['page'] - 1) * $perPage;
$stmt = db()->prepare('SELECT c.*, (SELECT COUNT(*) FROM customer_requests r WHERE r.customer_id = c.id) AS request_count FROM customers c' . $where . " ORDER BY $order LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
api_response(true, 'Đã tải danh sách khách hàng.', ['items' => $stmt->fetchAll(), 'pagination' => $paging]);
