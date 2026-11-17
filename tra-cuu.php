<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/public-layout.php';
public_header('lookup', 'Tra cứu yêu cầu', 'Tra cứu trạng thái yêu cầu tư vấn bằng mã yêu cầu và số điện thoại đã gửi. Kết quả chỉ hiển thị dịch vụ, trạng thái và thời gian.');
public_page_intro('Tra cứu yêu cầu', 'Chủ động theo dõi tiến độ.', 'Mỗi yêu cầu có một mã riêng. Dùng mã cùng số điện thoại đã cung cấp để xem trạng thái tư vấn hiện tại.');
?>
    <section class="section lookup-section lookup-page" id="tra-cuu" aria-labelledby="lookup-heading">
        <div class="container">
            <div class="lookup-panel row g-0 align-items-center">
                <div class="col-lg-5 lookup-intro"><span class="lookup-icon" aria-hidden="true"><i class="bi bi-search"></i></span><p class="eyebrow">ĐÃ GỬI YÊU CẦU?</p><h2 id="lookup-heading">Thông tin tra cứu<br> của bạn.</h2><p>Nhập mã yêu cầu và số điện thoại đã sử dụng. Không cần đăng nhập.</p></div>
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
<section class="section section-soft" aria-labelledby="lookup-status-heading"><div class="container"><div class="section-topline"><div><p class="eyebrow">HIỂU TRẠNG THÁI YÊU CẦU</p><h2 class="section-heading" id="lookup-status-heading">Từng bước đều rõ ràng.</h2></div></div><div class="process-grid status-guide"><article><span class="status-badge status-new"><i class="bi bi-inbox" aria-hidden="true"></i> Mới</span><h3>Đã tiếp nhận yêu cầu</h3><p>Thông tin đã được lưu để đội ngũ xem xét và liên hệ với bạn.</p></article><article><span class="status-badge status-processing"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Đang xử lý</span><h3>Đang trao đổi, tư vấn</h3><p>Đội ngũ đang xử lý nhu cầu và trao đổi giải pháp phù hợp.</p></article><article><span class="status-badge status-completed"><i class="bi bi-check-circle" aria-hidden="true"></i> Hoàn thành</span><h3>Đã hoàn tất tư vấn</h3><p>Yêu cầu đã hoàn thành. Bạn có thể gửi nhu cầu mới khi cần trao đổi thêm.</p></article></div><div class="help-note"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Không tìm thấy yêu cầu? Kiểm tra lại mã và số điện thoại đã gửi. Nếu thao tác quá nhiều lần, hãy chờ theo thông báo trước khi thử lại. Mã được hiển thị ngay sau khi gửi tại <a href="<?= e(url('tu-van.php')) ?>">trang tư vấn</a>.</p></div></div></section>
<?php public_footer(); ?>
