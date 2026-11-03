<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('GET');
require_admin_api();
$id = positive_id($_GET['id'] ?? null);
$record = record_or_404('customers', $id);
$stmt = db()->prepare('SELECT COUNT(*) FROM customer_requests WHERE customer_id = ?');
$stmt->execute([$id]);
$record['request_count'] = (int)$stmt->fetchColumn();
api_response(true, 'Chi tiết khách hàng.', $record);
