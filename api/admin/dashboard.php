<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('GET');
require_admin_api();
$stats = db()->query("SELECT COUNT(*) AS total, COALESCE(SUM(status = 'new'), 0) AS new, COALESCE(SUM(status = 'processing'), 0) AS processing, COALESCE(SUM(status = 'completed'), 0) AS completed FROM customer_requests")->fetch();
$stats['customers'] = (int)db()->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$stats = array_map('intval', $stats);
$recent = db()->query('SELECT * FROM customer_requests ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();
$services = db()->query('SELECT service, COUNT(*) AS total FROM customer_requests GROUP BY service ORDER BY total DESC')->fetchAll();
api_response(true, 'Đã tải thống kê.', ['stats' => $stats, 'recent' => $recent, 'services' => $services]);
