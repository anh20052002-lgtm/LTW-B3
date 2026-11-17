<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/public-layout.php';
$services = service_options();
$selectedService = is_string($_GET['service'] ?? null) && isset($services[$_GET['service']]) ? $_GET['service'] : '';
public_header('consultation', 'Gửi yêu cầu tư vấn', 'Chia sẻ nhu cầu với Nexa về website, CRM, marketing hoặc chuyển đổi số. Không cần tài khoản, nhận mã yêu cầu để theo dõi tiến độ.');
?>
    <section class="section consultation-section consultation-page" id="tu-van" aria-labelledby="consultation-heading">
        <div class="container"><nav class="public-breadcrumb" aria-label="Đường dẫn"><a href="<?= e(url('index.php')) ?>">Trang chủ</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">Yêu cầu tư vấn</span></nav>
            <div class="row gx-4 gy-5 gx-lg-5 align-items-start">
                <div class="col-lg-5 consultation-copy">
                    <p class="eyebrow">GỬI YÊU CẦU TƯ VẤN</p><h1 class="section-heading" id="consultation-heading">Bước tiếp theo,<br> bắt đầu từ bạn.</h1>
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
                        <div class="form-heading"><span class="form-heading-icon" aria-hidden="true"><i class="bi bi-chat-left-text"></i></span><div><h2>Bạn đang cần điều gì?</h2><p>Các trường có dấu <span class="required-mark">*</span> là bắt buộc.</p></div></div>
                        <div id="consultation-success" class="submission-success d-none" role="status" tabindex="-1">
                            <div class="success-symbol" aria-hidden="true"><i class="bi bi-check-lg"></i></div><p class="eyebrow">ĐÃ TIẾP NHẬN</p><h3>Cảm ơn bạn đã kết nối!</h3><p>Yêu cầu đã được lưu. Hãy giữ mã dưới đây và dùng cùng số điện thoại để theo dõi tiến độ.</p>
                            <div class="tracking-code-box"><span>MÃ YÊU CẦU CỦA BẠN</span><strong id="submitted-code"></strong><button class="btn btn-sm copy-button" id="copy-code" type="button" aria-label="Sao chép mã yêu cầu"><i class="bi bi-copy" aria-hidden="true"></i></button></div><p id="copy-status" class="copy-status" role="status"></p>
                            <div class="d-flex flex-wrap gap-2 justify-content-center"><a class="btn btn-primary" href="<?= e(url('tra-cuu.php')) ?>" id="track-new-request">Tra cứu yêu cầu <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a><button type="button" class="btn btn-light" id="send-another">Gửi yêu cầu khác</button></div>
                        </div>
                        <form id="consultation-form" novalidate>
                            <div class="row g-4">
                                <div class="col-12"><p class="form-section-title">01 / Thông tin liên hệ</p></div>
                                <div class="col-sm-6"><label class="form-label" for="full-name">Họ và tên <span class="required-mark">*</span></label><input class="form-control" id="full-name" name="full_name" autocomplete="name" maxlength="100" minlength="2" placeholder="Nguyễn Minh Anh" required aria-describedby="full-name-error"><div class="invalid-feedback" id="full-name-error">Vui lòng nhập họ tên từ 2 đến 100 ký tự.</div></div>
                                <div class="col-sm-6"><label class="form-label" for="phone">Số điện thoại <span class="required-mark">*</span></label><input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel" maxlength="20" inputmode="tel" pattern="\+?[0-9\s\.\-\(\)]{9,20}" placeholder="0901 234 567" required aria-describedby="phone-error"><div class="invalid-feedback" id="phone-error">Nhập số di động Việt Nam hợp lệ (10 số hoặc +84).</div></div>
                                <div class="col-12"><label class="form-label" for="email">Email <span class="optional-label">(không bắt buộc)</span></label><input type="email" class="form-control" id="email" name="email" autocomplete="email" maxlength="190" placeholder="ban@doanhnghiep.vn" aria-describedby="email-error"><div class="invalid-feedback" id="email-error">Vui lòng nhập địa chỉ email hợp lệ.</div></div>
                                <div class="col-12"><p class="form-section-title">02 / Nhu cầu của bạn</p></div>
                                <div class="col-12"><label class="form-label" for="service">Bạn quan tâm dịch vụ nào? <span class="required-mark">*</span></label><select class="form-select" id="service" name="service" required aria-describedby="service-error"><option value="">Chọn dịch vụ cần tư vấn</option><?php foreach ($services as $key => $label): ?><option value="<?= e($key) ?>"<?= $selectedService === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><div class="invalid-feedback" id="service-error">Vui lòng chọn một dịch vụ.</div></div>
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
<section class="section" aria-labelledby="consultation-faq-heading"><div class="container"><div class="row gy-4 gx-lg-5"><div class="col-lg-5"><p class="eyebrow">THÔNG TIN CẦN BIẾT</p><h2 class="section-heading" id="consultation-faq-heading">Để cuộc trao đổi<br> thuận tiện hơn.</h2><p class="section-description">Thông tin rõ ràng giúp Nexa hiểu nhu cầu và liên hệ đúng với bạn.</p></div><div class="col-lg-7"><div class="public-faq"><details><summary>Tôi nên mô tả nhu cầu như thế nào?</summary><p>Hãy chia sẻ mục tiêu, khó khăn hiện tại và nội dung muốn trao đổi. Không gửi mật khẩu, thông tin thanh toán hoặc dữ liệu nhạy cảm trong biểu mẫu.</p></details><details><summary>Sau khi gửi, tôi cần làm gì?</summary><p>Lưu mã yêu cầu được hiển thị khi gửi thành công. Nexa sử dụng thông tin bạn cung cấp để trao đổi; bạn có thể xem trạng thái tại trang <a href="<?= e(url('tra-cuu.php')) ?>">Tra cứu yêu cầu</a>.</p></details><details><summary>Tôi chưa biết nên chọn dịch vụ nào?</summary><p>Chọn “Tư vấn chuyển đổi số” và mô tả bài toán của bạn. Bạn cũng có thể <a href="<?= e(url('dich-vu.php')) ?>">tìm hiểu các dịch vụ</a> trước khi gửi.</p></details></div></div></div></div></section>
<?php public_footer(); ?>
