<?php
require_once __DIR__ . '/../includes/admin-layout.php';
admin_header('Tổng quan', 'dashboard');
?>
<div class="page-heading dashboard-heading">
    <div class="page-heading-copy"><span class="eyebrow">NEXA WORKSPACE</span><h1>Tổng quan công việc</h1><p>Theo dõi yêu cầu và ưu tiên những khách hàng cần phản hồi.</p></div>
    <div class="page-heading-actions"><a class="btn btn-light" href="<?= e(url('admin/thong-ke.php')) ?>"><i class="bi bi-bar-chart" aria-hidden="true"></i> Xem báo cáo</a><a class="btn btn-primary" href="<?= e(url('admin/requests.php?new=1')) ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Thêm yêu cầu</a></div>
</div>
<div class="dashboard-toolbar">
    <div class="dashboard-date"><i class="bi bi-calendar3" aria-hidden="true"></i><time datetime="<?= e(date('Y-m-d')) ?>"><?= e(date('d/m/Y')) ?></time><span class="dashboard-updated" id="dashboardUpdatedAt" role="status" aria-live="polite">Đang cập nhật dữ liệu…</span></div>
    <button class="btn btn-sm btn-light" id="refreshDashboard" type="button"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Làm mới</button>
</div>
<div id="dashboardError" class="alert alert-danger" role="alert" hidden></div>
<section class="overview-metrics" id="statsGrid" aria-label="Thống kê khách hàng và tiến độ yêu cầu" aria-busy="true">
    <a class="metric-card metric-link metric-customers" href="<?= e(url('admin/customers.php')) ?>" aria-label="Mở danh sách khách hàng">
        <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span class="metric-label">Tổng khách hàng</span>
        <strong class="metric-value skeleton-stat" data-stat="customers">—</strong><small class="metric-note">Hồ sơ đang quản lý <i class="bi bi-arrow-up-right" aria-hidden="true"></i></small>
    </a>
    <a class="metric-card metric-primary metric-link" href="<?= e(url('admin/requests.php')) ?>" aria-label="Mở tất cả yêu cầu tư vấn">
        <span class="metric-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span><span class="metric-label">Tổng yêu cầu</span>
        <strong class="metric-value skeleton-stat" data-stat="total">—</strong><small class="metric-note">Tất cả dịch vụ <i class="bi bi-arrow-up-right" aria-hidden="true"></i></small>
    </a>
    <?php foreach ([['new', 'Yêu cầu mới', 'Chờ tiếp nhận', 'bi-inbox'], ['processing', 'Đang xử lý', 'Đang tư vấn', 'bi-hourglass-split'], ['completed', 'Hoàn thành', 'Đã xử lý', 'bi-check2-circle']] as [$key, $label, $note, $icon]): ?>
    <a class="metric-card metric-link metric-<?= e($key) ?>" href="<?= e(url('admin/requests.php?status=' . $key)) ?>">
        <span class="metric-icon"><i class="bi <?= e($icon) ?>" aria-hidden="true"></i></span><span class="metric-label"><?= e($label) ?></span>
        <strong class="metric-value skeleton-stat" data-stat="<?= e($key) ?>">—</strong><small class="metric-note"><?= e($note) ?> <i class="bi bi-arrow-up-right" aria-hidden="true"></i></small>
    </a>
    <?php endforeach; ?>
</section>
<div class="dashboard-grid">
    <section class="surface-card recent-panel" aria-labelledby="recentTitle">
        <div class="section-heading"><div class="panel-heading-copy"><span class="panel-heading-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span><div><h2 id="recentTitle">Yêu cầu gần đây</h2><p>5 yêu cầu mới nhất trong hệ thống</p></div></div><a class="text-link" href="<?= e(url('admin/requests.php')) ?>">Xem tất cả <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        <div class="recent-table-wrap" id="recentRegion" aria-busy="true">
            <table class="table app-table dashboard-table mb-0"><caption class="visually-hidden">Năm yêu cầu tư vấn mới nhất. Chọn tên khách hàng để xem chi tiết.</caption><thead><tr><th scope="col">Khách hàng</th><th scope="col">Dịch vụ</th><th scope="col">Trạng thái</th><th scope="col">Ngày gửi</th></tr></thead>
                <tbody id="recentRequests"><tr class="recent-state-row"><td colspan="4"><div class="loading-state" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Đang tải yêu cầu…</div></td></tr></tbody>
            </table>
        </div>
    </section>
    <aside class="dashboard-aside" aria-label="Dịch vụ và công việc cần tiếp nhận">
        <section class="surface-card service-breakdown" aria-labelledby="serviceTitle">
            <div class="section-heading"><div><h2 id="serviceTitle">Dịch vụ được quan tâm</h2><p>Phân bổ trên tổng yêu cầu</p></div></div>
            <div id="serviceBreakdown" class="breakdown-body" aria-busy="true"><div class="loading-state" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Đang tải dịch vụ…</div></div>
        </section>
        <section class="surface-card progress-panel" aria-labelledby="progressTitle">
            <div class="progress-panel-label"><h2 id="progressTitle">Tiến độ xử lý</h2><strong id="completionRate">—</strong></div>
            <div class="completion-track" role="progressbar" id="completionProgress" aria-label="Tỷ lệ yêu cầu hoàn thành" aria-valuemin="0" aria-valuemax="100"><span id="completionBar"></span></div>
            <p id="completionSummary">Đang cập nhật tiến độ…</p>
        </section>
        <section class="queue-callout" aria-labelledby="queueTitle">
            <span class="queue-icon"><i class="bi bi-inbox" aria-hidden="true"></i></span>
            <div class="queue-copy"><h2 id="queueTitle">Chờ bạn tiếp nhận</h2><p id="queueSummary" role="status" aria-live="polite">Đang cập nhật hàng chờ…</p></div>
            <a class="queue-link" href="<?= e(url('admin/requests.php?status=new')) ?>">Xem yêu cầu mới <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </aside>
</div>
<?php admin_footer('dashboard.js'); ?>
