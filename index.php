<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$services = service_options();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Nexa đồng hành cùng doanh nghiệp với giải pháp website, CRM, marketing và tư vấn chuyển đổi số. Gửi yêu cầu và theo dõi tiến độ tư vấn trực tuyến.">
    <meta name="app-base" content="<?= e(url('')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="#2563eb">
    <title>Nexa — Tư vấn giải pháp doanh nghiệp</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/inter/inter.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/public.css') . '?v=' . filemtime(NEXA_ROOT . '/assets/css/public.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main-content">Chuyển đến nội dung</a>
<header class="site-header">
    <nav class="navbar navbar-expand-lg container" aria-label="Điều hướng chính">
        <a class="brand navbar-brand" href="<?= e(url('index.php')) ?>" aria-label="Nexa — Trang chủ">
            <span class="brand-mark" aria-hidden="true"><i class="bi bi-intersect"></i></span>nexa<span class="brand-dot">.</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu điều hướng"><i class="bi bi-list" aria-hidden="true"></i></button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav mx-auto gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#trang-chu">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="#dich-vu">Dịch vụ</a></li>
                <li class="nav-item"><a class="nav-link" href="#tu-van">Tư vấn</a></li>
                <li class="nav-item"><a class="nav-link" href="#tra-cuu">Tra cứu yêu cầu</a></li>
                <li class="nav-item"><a class="nav-link" href="#lien-he">Liên hệ</a></li>
            </ul>
            <a class="btn btn-primary nav-cta" href="#tu-van">Nhận tư vấn <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i></a>
        </div>
    </nav>
</header>
<main id="main-content">
    <section class="hero-section" id="trang-chu" aria-labelledby="hero-heading">
        <div class="container">
            <div class="row align-items-center gy-5 gx-xl-5">
                <div class="col-lg-7 hero-copy">
                    <p class="eyebrow"><span class="eyebrow-line" aria-hidden="true"></span> TƯ VẤN GIẢI PHÁP DOANH NGHIỆP</p>
                    <h1 id="hero-heading">Cùng bạn chọn<br><span>đúng giải pháp.</span></h1>
                    <p class="hero-description">Website, CRM, marketing hay chuyển đổi số. Chia sẻ điều bạn cần, kết nối với đội ngũ Nexa và theo dõi từng bước tư vấn.</p>
                    <div class="d-flex flex-wrap gap-3 hero-actions">
                        <a class="btn btn-primary btn-lg" href="#tu-van">Gửi yêu cầu tư vấn <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i></a>
                        <a class="hero-secondary-link" href="#dich-vu">Xem các giải pháp <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                    </div>
                    <div class="hero-assurance"><span class="assurance-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span><span>Không cần tài khoản · Nhận mã để tra cứu tiến độ</span></div>
                </div>
                <div class="col-lg-5">
                    <div class="consultation-map">
                        <div class="map-heading"><span><i class="bi bi-intersect" aria-hidden="true"></i> NEXA / KẾT NỐI NHU CẦU</span><span class="map-mark" aria-hidden="true">N.</span></div>
                        <h2>Bạn muốn bắt đầu<br>từ điều gì?</h2>
                        <div class="map-solutions" aria-label="Chọn nhanh dịch vụ cần tư vấn">
                            <a href="#tu-van" data-select-service="website"><i class="bi bi-window-sidebar" aria-hidden="true"></i><span>Website</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="#tu-van" data-select-service="crm"><i class="bi bi-people" aria-hidden="true"></i><span>CRM</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="#tu-van" data-select-service="marketing"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><span>Marketing</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="#tu-van" data-select-service="consulting"><i class="bi bi-compass" aria-hidden="true"></i><span>Chuyển đổi số</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        </div>
                        <div class="map-progress"><p><i class="bi bi-signpost-split" aria-hidden="true"></i> Mỗi yêu cầu đều có lộ trình rõ ràng</p><div class="map-statuses"><span><i class="bi bi-circle-fill" aria-hidden="true"></i>Mới</span><i class="bi bi-arrow-right" aria-hidden="true"></i><span>Đang xử lý</span><i class="bi bi-arrow-right" aria-hidden="true"></i><span>Hoàn thành</span></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="value-strip">
        <div class="container"><div class="row gy-3">
            <div class="col-md-4"><i class="bi bi-chat-square-text" aria-hidden="true"></i><span>Chia sẻ đúng nhu cầu</span></div>
            <div class="col-md-4"><i class="bi bi-upc-scan" aria-hidden="true"></i><span>Theo dõi bằng mã yêu cầu</span></div>
            <div class="col-md-4"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Tra cứu riêng với số điện thoại</span></div>
        </div></div>
    </div>

    <section class="section services-section" id="dich-vu" aria-labelledby="services-heading">
        <div class="container">
            <div class="row gx-lg-5 gy-4">
                <div class="col-lg-4 services-intro"><p class="eyebrow">DỊCH VỤ CỦA NEXA</p><h2 class="section-heading" id="services-heading">Mỗi bài toán,<br>một hướng giải.</h2><p class="section-description">Bắt đầu từ việc bạn muốn cải thiện. Chúng tôi cùng làm rõ phạm vi trước khi đề xuất giải pháp.</p><a class="text-link" href="#tu-van">Chưa biết chọn dịch vụ nào? <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                <div class="col-lg-8 service-list">
                <?php
                $serviceCards = [
                    'website' => ['icon' => 'window-sidebar', 'title' => 'Thiết kế website', 'text' => 'Một điểm chạm rõ ràng để khách hàng hiểu bạn và dễ dàng kết nối.', 'scope' => ['Giao diện responsive', 'Giới thiệu dịch vụ', 'Biểu mẫu liên hệ']],
                    'crm' => ['icon' => 'people', 'title' => 'Giải pháp CRM', 'text' => 'Tập trung thông tin và theo sát quá trình chăm sóc khách hàng.', 'scope' => ['Hồ sơ khách hàng', 'Quản lý yêu cầu', 'Quy trình xử lý']],
                    'marketing' => ['icon' => 'bar-chart-line', 'title' => 'Digital Marketing', 'text' => 'Làm rõ mục tiêu, thông điệp và cách tiếp cận khách hàng phù hợp.', 'scope' => ['Định hướng nội dung', 'Kênh truyền thông', 'Kế hoạch đo lường']],
                    'consulting' => ['icon' => 'compass', 'title' => 'Tư vấn chuyển đổi số', 'text' => 'Xác định việc cần làm trước, phù hợp với nguồn lực hiện tại.', 'scope' => ['Đánh giá hiện trạng', 'Xác định ưu tiên', 'Lộ trình triển khai']],
                ];
                $serviceNumber = 0;
                foreach ($serviceCards as $key => $card): $serviceNumber++;
                ?>
                    <article class="service-row">
                        <span class="service-number">0<?= $serviceNumber ?></span>
                        <div class="service-content"><h3><i class="bi bi-<?= e($card['icon']) ?>" aria-hidden="true"></i><?= e($card['title']) ?></h3><p><?= e($card['text']) ?></p><ul class="service-scope" aria-label="Nội dung tư vấn"><?php foreach ($card['scope'] as $scope): ?><li><?= e($scope) ?></li><?php endforeach; ?></ul></div>
                        <a href="#tu-van" class="service-link" data-select-service="<?= e($key) ?>" aria-label="Nhận tư vấn về <?= e($card['title']) ?>"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    </article>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section consultation-section" id="tu-van" aria-labelledby="consultation-heading">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-5 consultation-copy">
                    <p class="eyebrow">GỬI YÊU CẦU TƯ VẤN</p><h2 class="section-heading" id="consultation-heading">Bước tiếp theo,<br>bắt đầu từ bạn.</h2>
                    <p class="section-description">Chia sẻ mục tiêu hoặc khó khăn hiện tại. Nexa sẽ liên hệ qua thông tin bạn cung cấp để trao đổi cụ thể.</p>
                    <ol class="consultation-steps">
                        <li><span>01</span><div><h3>Gửi nhu cầu</h3><p>Chọn dịch vụ và mô tả điều bạn cần.</p></div></li>
                        <li><span>02</span><div><h3>Lưu mã yêu cầu</h3><p>Mã được cấp ngay sau khi gửi thành công.</p></div></li>
                        <li><span>03</span><div><h3>Chủ động theo dõi</h3><p>Dùng mã và số điện thoại để tra cứu.</p></div></li>
                    </ol>
                    <div class="privacy-note"><i class="bi bi-shield-check" aria-hidden="true"></i><p>Thông tin của bạn được sử dụng để tiếp nhận và xử lý yêu cầu tư vấn.</p></div>
                </div>
                <div class="col-lg-7">
                    <div class="consultation-card">
                        <div class="form-heading"><span class="form-heading-icon" aria-hidden="true"><i class="bi bi-chat-left-text"></i></span><div><h3>Bạn đang cần điều gì?</h3><p>Các trường có dấu <span class="required-mark">*</span> là bắt buộc.</p></div></div>
                        <div id="consultation-success" class="submission-success d-none" role="status" tabindex="-1">
                            <div class="success-symbol" aria-hidden="true"><i class="bi bi-check-lg"></i></div><p class="eyebrow">ĐÃ TIẾP NHẬN</p><h3>Cảm ơn bạn đã kết nối!</h3><p>Yêu cầu đã được lưu. Hãy giữ mã dưới đây và dùng cùng số điện thoại để theo dõi tiến độ.</p>
                            <div class="tracking-code-box"><span>MÃ YÊU CẦU CỦA BẠN</span><strong id="submitted-code"></strong><button class="btn btn-sm copy-button" id="copy-code" type="button" aria-label="Sao chép mã yêu cầu"><i class="bi bi-copy" aria-hidden="true"></i></button></div>
                            <div class="d-flex flex-wrap gap-2 justify-content-center"><a class="btn btn-primary" href="#tra-cuu" id="track-new-request">Tra cứu yêu cầu <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a><button type="button" class="btn btn-light" id="send-another">Gửi yêu cầu khác</button></div>
                        </div>
                        <form id="consultation-form" novalidate>
                            <div class="row g-4">
                                <div class="col-sm-6"><label class="form-label" for="full-name">Họ và tên <span class="required-mark">*</span></label><input class="form-control" id="full-name" name="full_name" autocomplete="name" maxlength="100" minlength="2" placeholder="Nguyễn Minh Anh" required aria-describedby="full-name-error"><div class="invalid-feedback" id="full-name-error">Vui lòng nhập họ tên từ 2 đến 100 ký tự.</div></div>
                                <div class="col-sm-6"><label class="form-label" for="phone">Số điện thoại <span class="required-mark">*</span></label><input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel" maxlength="20" inputmode="tel" pattern="\+?[0-9\s\.\-\(\)]{9,20}" placeholder="0901 234 567" required aria-describedby="phone-error"><div class="invalid-feedback" id="phone-error">Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).</div></div>
                                <div class="col-12"><label class="form-label" for="email">Email <span class="optional-label">(không bắt buộc)</span></label><input type="email" class="form-control" id="email" name="email" autocomplete="email" maxlength="190" placeholder="ban@doanhnghiep.vn" aria-describedby="email-error"><div class="invalid-feedback" id="email-error">Vui lòng nhập địa chỉ email hợp lệ.</div></div>
                                <div class="col-12"><label class="form-label" for="service">Bạn quan tâm dịch vụ nào? <span class="required-mark">*</span></label><select class="form-select" id="service" name="service" required aria-describedby="service-error"><option value="">Chọn dịch vụ cần tư vấn</option><?php foreach ($services as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach; ?></select><div class="invalid-feedback" id="service-error">Vui lòng chọn một dịch vụ.</div></div>
                                <div class="col-12"><div class="d-flex justify-content-between align-items-center"><label class="form-label" for="message">Chia sẻ nhu cầu của bạn <span class="required-mark">*</span></label><span class="character-count" id="message-count" aria-hidden="true">0/5000</span></div><textarea class="form-control" name="message" id="message" rows="4" minlength="10" maxlength="5000" placeholder="Mục tiêu, khó khăn hiện tại hoặc ý tưởng bạn muốn trao đổi…" required aria-describedby="message-error"></textarea><div class="invalid-feedback" id="message-error">Vui lòng mô tả nhu cầu từ 10 đến 5000 ký tự.</div></div>
                                <div class="col-12"><div class="alert alert-danger d-none mb-3 form-error" role="alert"></div><button class="btn btn-primary submit-button w-100" type="submit">Gửi yêu cầu tư vấn <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button><p class="form-footnote"><i class="bi bi-lock" aria-hidden="true"></i> Gửi trực tiếp, không cần đăng ký tài khoản.</p></div>
                            </div>
                        </form>
                        <noscript><p class="alert alert-warning mt-3">Vui lòng bật JavaScript để gửi và tra cứu yêu cầu trực tuyến.</p></noscript>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section lookup-section" id="tra-cuu" aria-labelledby="lookup-heading">
        <div class="container">
            <div class="lookup-panel row g-0 align-items-center">
                <div class="col-lg-5 lookup-intro"><span class="lookup-icon" aria-hidden="true"><i class="bi bi-search"></i></span><p class="eyebrow">ĐÃ GỬI YÊU CẦU?</p><h2 id="lookup-heading">Theo dõi tiến độ<br>tư vấn của bạn.</h2><p>Nhập mã yêu cầu và số điện thoại đã sử dụng. Không cần đăng nhập.</p></div>
                <div class="col-lg-7 lookup-content">
                    <form id="lookup-form" novalidate>
                        <div class="row g-3"><div class="col-md-7"><label class="form-label" for="tracking-code">Mã yêu cầu</label><input class="form-control tracking-input" id="tracking-code" name="tracking_code" maxlength="40" placeholder="Nhập mã yêu cầu của bạn" required aria-describedby="tracking-code-error" autocomplete="off"><div class="invalid-feedback" id="tracking-code-error">Mã yêu cầu gồm NX- và 12 ký tự đã nhận.</div></div><div class="col-md-5"><label class="form-label" for="lookup-phone">Số điện thoại</label><input class="form-control" type="tel" id="lookup-phone" name="phone" maxlength="20" inputmode="tel" pattern="\+?[0-9\s\.\-\(\)]{9,20}" placeholder="Số điện thoại đã gửi" autocomplete="tel" required aria-describedby="lookup-phone-error"><div class="invalid-feedback" id="lookup-phone-error">Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).</div></div><div class="col-12"><div class="alert alert-danger d-none form-error" role="alert"></div><button class="btn btn-dark" type="submit">Tra cứu trạng thái <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button></div></div>
                    </form>
                    <div id="lookup-result" class="lookup-result d-none" role="status" tabindex="-1"></div>
                    <p class="lookup-privacy"><i class="bi bi-info-circle" aria-hidden="true"></i> Kết quả chỉ hiển thị trạng thái, dịch vụ và thời gian xử lý.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section" id="lien-he" aria-labelledby="contact-heading">
        <div class="container"><div class="row align-items-center gy-4"><div class="col-lg-8"><p class="eyebrow">LIÊN HỆ NEXA</p><h2 id="contact-heading">Chưa rõ nên bắt đầu từ đâu?</h2><p>Chọn “Tư vấn chuyển đổi số” và chia sẻ bài toán của bạn. Chúng ta cùng làm rõ hướng đi.</p></div><div class="col-lg-4 text-lg-end"><a href="#tu-van" data-select-service="consulting" class="btn btn-primary btn-lg">Trao đổi với Nexa <i class="bi bi-arrow-up-right ms-3" aria-hidden="true"></i></a></div></div></div>
    </section>
</main>
<footer class="site-footer"><div class="container"><div class="footer-main"><a class="brand" href="#trang-chu"><span class="brand-mark" aria-hidden="true"><i class="bi bi-intersect"></i></span>nexa<span class="brand-dot">.</span></a><p>Kết nối để phát triển.</p><nav aria-label="Điều hướng cuối trang"><a href="#dich-vu">Dịch vụ</a><a href="#tu-van">Tư vấn</a><a href="#tra-cuu">Tra cứu</a></nav></div><div class="footer-bottom"><span>© <?= date('Y') ?> Nexa. Hệ thống quản lý khách hàng và tư vấn.</span><a href="<?= e(url('admin/login.php')) ?>">Khu vực quản trị <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a></div></div></footer>
<div id="toastRegion" class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="false"></div>
<script src="<?= e(url('assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(url('assets/js/common.js')) ?>"></script>
<script src="<?= e(url('assets/js/public.js') . '?v=' . filemtime(NEXA_ROOT . '/assets/js/public.js')) ?>"></script>
</body>
</html>
