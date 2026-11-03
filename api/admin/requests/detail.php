<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/_init.php';
require_method('GET');
require_admin_api();
$record = record_or_404('customer_requests', positive_id($_GET['id'] ?? null));
api_response(true, 'Chi tiết yêu cầu.', $record);
