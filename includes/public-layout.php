<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

/** One shared public shell; all destinations remain relative to the installation. */
function public_header(string $active, string $title, string $description): void
{
    $navigation = [
        'home' => ['index.php', 'Trang chủ'],
        'about' => ['gioi-thieu.php', 'Giới thiệu'],
        'services' => ['dich-vu.php', 'Dịch vụ'],
        'consultation' => ['tu-van.php', 'Tư vấn'],
        'lookup' => ['tra-cuu.php', 'Tra cứu'],
        'contact' => ['lien-he.php', 'Liên hệ'],
    ];
    ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="app-base" content="<?= e(url('')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="#2563eb">
    <title><?= e($title) ?> — Nexa</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/inter/inter.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/tokens.css') . '?v=' . filemtime(NEXA_ROOT . '/assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/public.css') . '?v=' . filemtime(NEXA_ROOT . '/assets/css/public.css')) ?>">
</head>
<body data-public-page="<?= e($active) ?>">
<a class="skip-link" href="#main-content">Chuyển đến nội dung</a>
<header class="site-header">
    <nav class="navbar navbar-expand-xl container" aria-label="Điều hướng chính">
        <a class="brand navbar-brand" href="<?= e(url('index.php')) ?>" aria-label="Nexa — Trang chủ"><span class="brand-mark" aria-hidden="true"><i class="bi bi-intersect"></i></span>nexa<span class="brand-dot">.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu điều hướng"><i class="bi bi-list" aria-hidden="true"></i></button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav mx-auto gap-xl-2">
                <?php foreach ($navigation as $key => [$path, $label]): ?>
                <li class="nav-item<?= in_array($key, ['services', 'consultation'], true) ? ' dropdown nav-dropdown' : '' ?>">
                    <a class="nav-link<?= $active === $key ? ' active' : '' ?>" href="<?= e(url($path)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                    <?php if (in_array($key, ['services', 'consultation'], true)): ?><button class="nav-disclosure dropdown-toggle" id="<?= e($key) ?>-menu-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-controls="<?= e($key) ?>-menu" aria-label="<?= $key === 'services' ? 'Mở menu dịch vụ' : 'Mở menu tư vấn' ?>"><i class="bi bi-chevron-down" aria-hidden="true"></i></button><?php endif; ?>
                    <?php if ($key === 'services'): ?>
                    <div class="dropdown-menu mega-menu" id="services-menu" aria-labelledby="services-menu-toggle"><div class="mega-menu-inner"><div><span class="menu-eyebrow">GIẢI PHÁP CHO TỪNG NHU CẦU</span><div class="mega-services">
                        <?php foreach (['website' => ['window-sidebar', 'Thiết kế website', 'Xây dựng điểm chạm với khách hàng.'], 'crm' => ['people', 'Giải pháp CRM', 'Tập trung thông tin và chăm sóc.'], 'marketing' => ['bar-chart-line', 'Digital Marketing', 'Làm rõ mục tiêu và cách tiếp cận.'], 'consulting' => ['compass', 'Tư vấn chuyển đổi số', 'Chọn ưu tiên và hướng triển khai.']] as $serviceKey => [$serviceIcon, $serviceTitle, $serviceDescription]): ?>
                        <a class="dropdown-item mega-service" data-service="<?= e($serviceKey) ?>" href="<?= e(url('dich-vu.php?service=' . $serviceKey)) ?>"><span class="menu-icon"><i class="bi bi-<?= e($serviceIcon) ?>" aria-hidden="true"></i></span><span><strong><?= e($serviceTitle) ?></strong><small><?= e($serviceDescription) ?></small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        <?php endforeach; ?>
                    </div><a class="dropdown-item menu-all-link d-md-none" href="<?= e(url('dich-vu.php')) ?>">Tất cả dịch vụ <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><aside class="mega-aside"><i class="bi bi-intersect" aria-hidden="true"></i><h2>Bắt đầu từ bài toán của bạn.</h2><p>Khám phá phạm vi tư vấn và tìm điểm bắt đầu phù hợp.</p><a class="dropdown-item menu-all-link" href="<?= e(url('dich-vu.php')) ?>">Tất cả dịch vụ <i class="bi bi-arrow-right" aria-hidden="true"></i></a></aside></div></div>
                    <?php elseif ($key === 'consultation'): ?>
                    <div class="dropdown-menu consultation-menu" id="consultation-menu" aria-labelledby="consultation-menu-toggle"><span class="menu-eyebrow">KẾT NỐI & THEO DÕI</span><a class="dropdown-item consultation-menu-link" href="<?= e(url('tu-van.php')) ?>"><i class="bi bi-chat-square-text" aria-hidden="true"></i><span><strong>Gửi yêu cầu tư vấn</strong><small>Chia sẻ nhu cầu, không cần tài khoản.</small></span></a><a class="dropdown-item consultation-menu-link" href="<?= e(url('tra-cuu.php')) ?>"><i class="bi bi-upc-scan" aria-hidden="true"></i><span><strong>Tra cứu yêu cầu</strong><small>Theo dõi bằng mã và số điện thoại.</small></span></a><a class="dropdown-item consultation-menu-link" href="<?= e(url('index.php#quy-trinh-tu-van')) ?>"><i class="bi bi-signpost-split" aria-hidden="true"></i><span><strong>Quy trình tư vấn</strong><small>Tìm hiểu từng bước cùng Nexa.</small></span></a></div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-primary nav-cta" href="<?= e(url('tu-van.php')) ?>">Nhận tư vấn <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i></a>
        </div>
    </nav>
</header>
<main id="main-content">
<?php
}

function public_footer(): void
{
    $contact = public_contact_details();
    ?>
</main>
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand"><a class="brand" href="<?= e(url('index.php')) ?>"><span class="brand-mark" aria-hidden="true"><i class="bi bi-intersect"></i></span>nexa<span class="brand-dot">.</span></a><p>Kết nối nhu cầu.<br> Cùng tìm hướng đi.</p><span class="footer-brand-note">Giải pháp bắt đầu từ sự thấu hiểu.</span></div>
            <nav class="footer-links" aria-label="Khám phá Nexa"><span>Khám phá</span><a href="<?= e(url('index.php')) ?>">Trang chủ</a><a href="<?= e(url('gioi-thieu.php')) ?>">Giới thiệu Nexa</a><a href="<?= e(url('dich-vu.php')) ?>">Tất cả dịch vụ</a></nav>
            <nav class="footer-links" aria-label="Dịch vụ Nexa"><span>Giải pháp</span><?php foreach (service_options() as $serviceKey => $serviceLabel): ?><a href="<?= e(url('dich-vu.php?service=' . $serviceKey)) ?>"><?= e($serviceLabel) ?></a><?php endforeach; ?></nav>
            <nav class="footer-links" aria-label="Hỗ trợ yêu cầu"><span>Hỗ trợ</span><a href="<?= e(url('tu-van.php')) ?>">Gửi yêu cầu tư vấn</a><a href="<?= e(url('tra-cuu.php')) ?>">Tra cứu tiến độ</a><a href="<?= e(url('index.php#quy-trinh-tu-van')) ?>">Quy trình tư vấn</a></nav>
            <nav class="footer-links footer-contact" aria-label="Liên hệ Nexa"><span>Liên hệ</span><a href="<?= e(url('lien-he.php')) ?>">Kết nối cùng Nexa <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><?php if ($contact['email'] !== ''): ?><a href="<?= e('mailto:' . str_replace('%40', '@', rawurlencode($contact['email']))) ?>"><?= e($contact['email']) ?></a><?php endif; ?><?php if ($contact['phone'] !== ''): ?><a href="<?= e('tel:' . preg_replace('/[^+0-9]/', '', $contact['phone'])) ?>"><?= e($contact['phone']) ?></a><?php endif; ?><p>Chia sẻ nhu cầu để bắt đầu cuộc trao đổi.</p></nav>
        </div>
        <div class="footer-bottom"><span>© <?= date('Y') ?> Nexa. Hệ thống quản lý khách hàng và tư vấn.</span><a href="<?= e(url('admin/login.php')) ?>">Khu vực quản trị <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    </div>
</footer>
<div id="toastRegion" class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="false"></div>
<script src="<?= e(url('assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(url('assets/js/common.js') . '?v=' . filemtime(NEXA_ROOT . '/assets/js/common.js')) ?>"></script>
<script src="<?= e(url('assets/js/public.js') . '?v=' . filemtime(NEXA_ROOT . '/assets/js/public.js')) ?>"></script>
</body>
</html>
<?php
}

function public_page_intro(string $label, string $heading, string $description): void
{
    ?>
<section class="page-hero">
    <div class="container">
        <nav class="public-breadcrumb" aria-label="Đường dẫn"><a href="<?= e(url('index.php')) ?>">Trang chủ</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page"><?= e($label) ?></span></nav>
        <div class="page-hero-layout"><div><p class="eyebrow">NEXA / <?= e($label) ?></p><h1><?= e($heading) ?></h1><p class="page-introduction"><?= e($description) ?></p></div><div class="page-hero-art" aria-hidden="true"><span class="page-art-orbit"></span><span class="page-art-orbit orbit-inner"></span><i class="bi bi-intersect"></i><span class="page-art-label">KẾT NỐI ĐỂ PHÁT TRIỂN</span></div></div>
    </div>
</section>
<?php
}

function public_consultation_cta(string $heading = 'Cùng làm rõ bài toán của bạn.'): void
{
    ?>
<section class="contact-section" aria-label="Kết nối tư vấn"><div class="container"><div class="contact-cta-layout"><div><p class="eyebrow">BƯỚC TIẾP THEO CÙNG NEXA</p><h2><?= e($heading) ?></h2><p>Chia sẻ nhu cầu, nhận mã yêu cầu và chủ động theo dõi quá trình tư vấn.</p></div><a href="<?= e(url('tu-van.php')) ?>" class="btn btn-primary btn-lg">Gửi yêu cầu tư vấn <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i></a></div></div></section>
<?php
}
