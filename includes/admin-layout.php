<?php
require_once __DIR__ . '/bootstrap.php';

function admin_header(string $title, string $active): void
{
    require_admin_page();
    $admin = current_admin();
    $name = $admin['name'] ?? 'Quản trị viên';
    $initial = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : (preg_match('/^./us', $name, $match) ? $match[0] : 'N');
    $nav = [
        ['dashboard', 'admin/index.php', 'bi-grid-1x2', 'Tổng quan'],
        ['requests', 'admin/requests.php', 'bi-chat-square-text', 'Yêu cầu tư vấn'],
        ['customers', 'admin/customers.php', 'bi-people', 'Khách hàng'],
    ];
    ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="app-base" content="<?= e(url('')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · Nexa CRM</title>
    <link rel="icon" href="<?= e(url('assets/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/inter/inter.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css') . '?v=' . filemtime(NEXA_ROOT . '/assets/css/admin.css')) ?>">
</head>
<body data-admin="true" data-page="<?= e($active) ?>">
<a class="skip-link" href="#mainContent">Chuyển đến nội dung chính</a>
<aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="sidebarTitle">
    <div class="sidebar-top">
        <a class="brand" href="<?= e(url('admin/index.php')) ?>" id="sidebarTitle"><span class="brand-symbol"><i class="bi bi-intersect" aria-hidden="true"></i></span>nexa<span class="brand-suffix">CRM</span></a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Đóng menu"></button>
    </div>
    <div class="workspace-label"><span class="workspace-icon"><i class="bi bi-buildings" aria-hidden="true"></i></span><div>Nexa Workspace<small>Không gian quản trị</small></div><i class="bi bi-shield-check ms-auto" aria-hidden="true"></i></div>
    <div class="nav-caption">QUẢN LÝ CÔNG VIỆC</div>
    <nav class="sidebar-nav" aria-label="Menu quản trị">
        <?php foreach ($nav as [$key, $path, $icon, $label]): ?>
        <a href="<?= e(url($path)) ?>" class="nav-item <?= $active === $key ? 'active' : '' ?>" <?= $active === $key ? 'aria-current="page"' : '' ?>><i class="bi <?= e($icon) ?>" aria-hidden="true"></i><?= e($label) ?><?php if ($active === $key): ?><span class="active-dot" aria-hidden="true"></span><?php endif; ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-shortcut"><span>CỔNG TƯ VẤN</span><a href="<?= e(url('index.php#tu-van')) ?>">Xem trang khách hàng <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <button class="nav-item logout-button" data-logout type="button"><i class="bi bi-box-arrow-left" aria-hidden="true"></i>Đăng xuất</button>
        <div class="sidebar-version">NEXA CRM <span>Phiên bản đồ án 1.0</span></div>
    </div>
</aside>
<div class="admin-shell">
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3"><button class="btn icon-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Mở menu"><i class="bi bi-list" aria-hidden="true"></i></button><span class="topbar-crumb">Không gian làm việc <i class="bi bi-chevron-right" aria-hidden="true"></i> <strong><?= e($title) ?></strong></span></div>
        <div class="topbar-right"><a class="public-link" href="<?= e(url('index.php')) ?>">Trang khách hàng <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><span class="topbar-separator"></span><div class="admin-avatar"><?= e($initial) ?></div><div class="admin-user"><strong><?= e($name) ?></strong><small>Quản trị viên</small></div></div>
    </header>
    <main id="mainContent" class="admin-main">
    <?php
}

function admin_footer(string $script): void
{
    ?>
    </main>
    <footer class="admin-footer"><span>© <?= date('Y') ?> Nexa CRM</span><span>Chăm sóc khách hàng, bắt đầu từ sự thấu hiểu.</span></footer>
</div>
<div id="toastRegion" class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite"></div>
<script src="<?= e(url('assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer></script>
<script src="<?= e(url('assets/js/common.js')) ?>" defer></script>
<script src="<?= e(url('assets/js/' . $script) . '?v=' . filemtime(NEXA_ROOT . '/assets/js/' . $script)) ?>" defer></script>
</body>
</html>
    <?php
}

function field_error(string $field): void
{
    echo '<div id="error-' . e($field) . '" class="invalid-feedback" data-error-for="' . e($field) . '"></div>';
}

function delete_modal(string $noun): void
{
    ?>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteTitle" aria-describedby="deleteDescription" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-body p-4"><div class="delete-symbol"><i class="bi bi-trash3" aria-hidden="true"></i></div><h2 class="h5 mt-3" id="deleteTitle">Xóa <?= e($noun) ?>?</h2><p class="text-secondary small" id="deleteDescription">Thao tác sẽ xóa dữ liệu khỏi hệ thống. Bạn không thể hoàn tác.</p><p id="deleteTarget" class="delete-target"></p><div class="alert alert-danger small" id="deleteError" role="alert" hidden></div><div class="d-flex gap-2 mt-4"><button class="btn btn-light flex-fill" type="button" data-bs-dismiss="modal">Giữ lại</button><button class="btn btn-danger flex-fill" type="button" id="confirmDelete">Xóa dữ liệu</button></div></div></div></div>
</div>
    <?php
}
