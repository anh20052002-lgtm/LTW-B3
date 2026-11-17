<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/public-layout.php';
require_once __DIR__ . '/includes/service-content.php';
public_header('home', 'Tư vấn giải pháp doanh nghiệp', 'Khám phá giải pháp website, CRM, marketing và chuyển đổi số cùng Nexa. Gửi yêu cầu và theo dõi tiến độ tư vấn trực tuyến.');
?>
    <section class="hero-section" id="trang-chu" aria-labelledby="hero-heading">
        <div class="container">
            <div class="row align-items-center gy-5 gx-xl-5">
                <div class="col-lg-6 hero-copy">
                    <p class="eyebrow"><span class="eyebrow-line" aria-hidden="true"></span> TƯ VẤN GIẢI PHÁP DOANH NGHIỆP</p>
                    <h1 id="hero-heading">Cùng bạn chọn<br> <span>đúng giải pháp.</span></h1>
                    <p class="hero-description">Website, CRM, marketing hay chuyển đổi số. Chia sẻ điều bạn cần, kết nối với đội ngũ Nexa và theo dõi từng bước tư vấn.</p>
                    <div class="d-flex flex-wrap gap-3 hero-actions">
                        <a class="btn btn-primary btn-lg" href="<?= e(url('tu-van.php')) ?>">Gửi yêu cầu tư vấn <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i></a>
                        <a class="btn btn-light hero-secondary-link" href="<?= e(url('dich-vu.php')) ?>">Xem các giải pháp <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="hero-assurance"><span class="assurance-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span><span>Không cần tài khoản · Nhận mã để tra cứu tiến độ</span></div>
                </div>
                <div class="col-lg-6 hero-visual-column">
                    <figure class="journey-visual">
                        <div class="journey-top"><span><i class="bi bi-intersect" aria-hidden="true"></i> NEXA CONNECT</span><span>Minh họa quy trình</span></div>
                        <figcaption><span>TỪ NHU CẦU ĐẾN HƯỚNG ĐI</span><strong>Một kết nối.<br>Nhiều khả năng.</strong></figcaption>
                        <div class="journey-request"><span class="journey-request-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span><div><strong>Bắt đầu với điều bạn cần</strong><span>Chia sẻ bài toán của doanh nghiệp</span></div><i class="bi bi-arrow-down-right" aria-hidden="true"></i></div>
                        <div class="journey-service-grid">
                            <a href="<?= e(url('tu-van.php?service=website')) ?>" aria-label="Nhận tư vấn thiết kế website"><i class="bi bi-window-sidebar" aria-hidden="true"></i><span>Website</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="<?= e(url('tu-van.php?service=crm')) ?>" aria-label="Nhận tư vấn giải pháp CRM"><i class="bi bi-people" aria-hidden="true"></i><span>CRM</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="<?= e(url('tu-van.php?service=marketing')) ?>" aria-label="Nhận tư vấn Digital Marketing"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><span>Marketing</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            <a href="<?= e(url('tu-van.php?service=consulting')) ?>" aria-label="Nhận tư vấn chuyển đổi số"><i class="bi bi-compass" aria-hidden="true"></i><span>Chuyển đổi số</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        </div>
                        <div class="journey-outcome"><span><i class="bi bi-check2-circle" aria-hidden="true"></i> Tiếp nhận</span><i class="bi bi-arrow-right" aria-hidden="true"></i><span>Trao đổi</span><i class="bi bi-arrow-right" aria-hidden="true"></i><span>Theo dõi</span></div>
                    </figure>
                </div>
            </div>
        </div>
    </section>
    <div class="value-strip"><div class="container"><div class="row gy-3"><div class="col-md-4"><i class="bi bi-chat-square-text" aria-hidden="true"></i><span>Chia sẻ đúng nhu cầu</span></div><div class="col-md-4"><i class="bi bi-upc-scan" aria-hidden="true"></i><span>Theo dõi bằng mã yêu cầu</span></div><div class="col-md-4"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Tra cứu riêng với số điện thoại</span></div></div></div></div>
    <section class="section" aria-labelledby="home-services-heading"><div class="container">
        <div class="section-topline"><div><p class="eyebrow">GIẢI PHÁP DÀNH CHO BẠN</p><h2 class="section-heading" id="home-services-heading">Bắt đầu từ điều cần cải thiện.</h2></div><a class="text-link" href="<?= e(url('dich-vu.php')) ?>">Khám phá dịch vụ <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="service-grid home-service-grid"><?php public_service_cards(true); ?></div>
    </div></section>
    <section class="section section-soft home-benefits" aria-labelledby="home-benefits-heading"><div class="container"><div class="row gx-lg-5 gy-4 align-items-start">
        <div class="col-lg-5"><p class="eyebrow">RÕ RÀNG TỪ BƯỚC ĐẦU</p><h2 class="section-heading" id="home-benefits-heading">Một nơi kết nối.<br> Một luồng theo dõi.</h2><p class="section-description">Nexa giúp bạn gửi nhu cầu đến đúng nơi và nắm được tiến độ, từ lúc tiếp nhận đến khi hoàn thành tư vấn.</p><a class="text-link mt-4" href="<?= e(url('gioi-thieu.php')) ?>">Tìm hiểu cách Nexa đồng hành <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        <div class="col-lg-7"><div class="benefit-list"><article><span class="benefit-icon"><i class="bi bi-bullseye" aria-hidden="true"></i></span><div><h3>Bắt đầu từ nhu cầu thực tế</h3><p>Chọn dịch vụ và chia sẻ điều bạn muốn giải quyết để cuộc trao đổi có trọng tâm.</p></div></article><article><span class="benefit-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><h3>Kết nối không cần tài khoản</h3><p>Gửi thông tin trực tiếp qua biểu mẫu. Mỗi yêu cầu được cấp một mã tra cứu riêng.</p></div></article><article><span class="benefit-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span><div><h3>Chủ động theo dõi tiến độ</h3><p>Dùng mã và số điện thoại đã gửi để xem trạng thái hiện tại của yêu cầu.</p></div></article></div></div>
    </div></div></section>
    <section class="section home-process" id="quy-trinh-tu-van" aria-labelledby="home-process-heading"><div class="container"><div class="section-topline"><div><p class="eyebrow">QUY TRÌNH TƯ VẤN</p><h2 class="section-heading" id="home-process-heading">Dễ bắt đầu. Dễ theo dõi.</h2></div><a class="text-link" href="<?= e(url('tra-cuu.php')) ?>">Đã có mã yêu cầu? <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div><div class="process-grid process-four"><article><span class="process-number">01</span><h3>Chia sẻ nhu cầu</h3><p>Chọn dịch vụ, mô tả mục tiêu và để lại thông tin liên hệ.</p></article><article><span class="process-number">02</span><h3>Nhận mã yêu cầu</h3><p>Hệ thống tiếp nhận và cấp mã riêng. Hãy giữ mã để dùng khi tra cứu.</p></article><article><span class="process-number">03</span><h3>Trao đổi giải pháp</h3><p>Đội ngũ xem xét và liên hệ qua thông tin bạn đã cung cấp.</p></article><article><span class="process-number">04</span><h3>Chủ động theo dõi</h3><p>Dùng mã và số điện thoại để xem trạng thái xử lý hiện tại.</p></article></div></div></section>
<?php public_consultation_cta(); public_footer(); ?>
