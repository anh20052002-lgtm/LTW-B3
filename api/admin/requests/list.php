<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('GET');
require_admin_api();
[$page, $perPage, $search, $sort] = list_options();
$where = [];
$params = [];
if (array_key_exists('customer_id', $_GET) && $_GET['customer_id'] !== '') {
    $customerId = is_string($_GET['customer_id'])
        ? filter_var($_GET['customer_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;
    if ($customerId === false) {
        api_response(false, 'Bộ lọc hồ sơ khách hàng không hợp lệ.', null, ['customer_id' => 'ID khách hàng phải là số nguyên dương.'], 422);
    }
    $where[] = 'customer_id = ?';
    $params[] = $customerId;
}
if ($search !== '') {
    $where[] = "(full_name LIKE ? ESCAPE '!' OR phone LIKE ? ESCAPE '!' OR tracking_code LIKE ? ESCAPE '!')";
    $term = like_term($search);
    array_push($params, $term, $term, $term);
}
$status = text_value($_GET, 'status');
if ($status !== '') {
    if (!array_key_exists($status, status_options())) {
        api_response(false, 'Bộ lọc trạng thái không hợp lệ.', null, ['status' => 'Chọn trạng thái hợp lệ.'], 422);
    }
    $where[] = 'status = ?';
    $params[] = $status;
}
$service = text_value($_GET, 'service');
if ($service !== '') {
    if (!array_key_exists($service, service_options())) {
        api_response(false, 'Bộ lọc dịch vụ không hợp lệ.', null, ['service' => 'Chọn dịch vụ hợp lệ.'], 422);
    }
    $where[] = 'service = ?';
    $params[] = $service;
}
$clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$sorts = ['created_desc' => 'created_at DESC, id DESC', 'created_asc' => 'created_at ASC, id ASC', 'name_asc' => 'full_name ASC, id ASC', 'updated_desc' => 'updated_at DESC, id DESC'];
$order = $sorts[$sort] ?? $sorts['created_desc'];
$stmt = db()->prepare('SELECT COUNT(*) FROM customer_requests' . $clause);
$stmt->execute($params);
$paging = pagination((int)$stmt->fetchColumn(), $page, $perPage);
$offset = ($paging['page'] - 1) * $perPage;
$stmt = db()->prepare('SELECT * FROM customer_requests' . $clause . " ORDER BY $order LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
api_response(true, 'Đã tải danh sách yêu cầu.', ['items' => $stmt->fetchAll(), 'pagination' => $paging]);
