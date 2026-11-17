<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/_init.php';
require_method('GET');
require_admin_api();

$rawDays = $_GET['days'] ?? '30';
require_valid(is_string($rawDays) && in_array($rawDays, ['7', '30', '90'], true) ? [] : ['days' => 'Chọn khoảng thời gian 7, 30 hoặc 90 ngày.']);
$days = (int)$rawDays;
$pdo = db();
// Pin the calendar range to MySQL's application timezone, including today.
$today = new DateTimeImmutable((string)$pdo->query('SELECT CURRENT_DATE()')->fetchColumn(), new DateTimeZone('Asia/Ho_Chi_Minh'));
$start = $today->modify('-' . ($days - 1) . ' days');
$bounds = [$start->format('Y-m-d') . ' 00:00:00', $today->modify('+1 day')->format('Y-m-d') . ' 00:00:00'];
$requestCounts = "SELECT COUNT(*) AS total, COALESCE(SUM(status = 'new'), 0) AS new, COALESCE(SUM(status = 'processing'), 0) AS processing, COALESCE(SUM(status = 'completed'), 0) AS completed FROM customer_requests";

$pdo->beginTransaction();
try {
    $totals = array_map('intval', $pdo->query($requestCounts)->fetch());
    $totals['customers'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    $stmt = $pdo->prepare($requestCounts . ' WHERE created_at >= ? AND created_at < ?');
    $stmt->execute($bounds);
    $period = array_map('intval', $stmt->fetch());
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM customers WHERE created_at >= ? AND created_at < ?');
    $stmt->execute($bounds);
    $period['customers'] = (int)$stmt->fetchColumn();
    $period['completion_rate'] = $period['total'] ? round($period['completed'] / $period['total'] * 100, 1) : 0;

    // The WHERE ranges can use the existing created_at indexes; DATE is only grouped.
    $stmt = $pdo->prepare('SELECT DATE(created_at) AS date, COUNT(*) AS total FROM customers WHERE created_at >= ? AND created_at < ? GROUP BY DATE(created_at)');
    $stmt->execute($bounds);
    $customersByDay = array_column($stmt->fetchAll(), 'total', 'date');
    $stmt = $pdo->prepare('SELECT DATE(created_at) AS date, COUNT(*) AS total FROM customer_requests WHERE created_at >= ? AND created_at < ? GROUP BY DATE(created_at)');
    $stmt->execute($bounds);
    $requestsByDay = array_column($stmt->fetchAll(), 'total', 'date');
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}

$daily = [];
for ($offset = 0; $offset < $days; $offset++) {
    $date = $start->modify('+' . $offset . ' days')->format('Y-m-d');
    $daily[] = ['date' => $date, 'customers' => (int)($customersByDay[$date] ?? 0), 'requests' => (int)($requestsByDay[$date] ?? 0)];
}
api_response(true, 'Đã tải thống kê và báo cáo.', [
    'range' => ['days' => $days, 'start_date' => $start->format('Y-m-d'), 'end_date' => $today->format('Y-m-d'), 'timezone' => 'Asia/Ho_Chi_Minh'],
    'totals' => $totals,
    'period' => $period,
    'daily' => $daily,
]);
