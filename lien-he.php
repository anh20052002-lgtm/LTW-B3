<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/public-layout.php';
$contact = public_contact_details();
$hasDirectContact = $contact['phone'] !== '' || $contact['email'] !== '' || $contact['address'] !== '';
$hasContactInformation = $hasDirectContact || $contact['hours'] !== '';
$contactColumnClass = match (count(array_filter($contact, static fn(string $value): bool => $value !== ''))) {
    1 => 'col-md-8 col-lg-6',
    2 => 'col-md-6',
    3 => 'col-md-6 col-xl-4',
    default => 'col-md-6 col-xl-3',
};
$phoneHref = $contact['phone'] !== '' ? 'tel:' . preg_replace('/[^+0-9]/', '', $contact['phone']) : '';
$emailHref = $contact['email'] !== '' ? 'mailto:' . str_replace('%40', '@', rawurlencode($contact['email'])) : '';
public_header('contact', 'Liên hệ Nexa', 'Thông tin liên hệ Nexa, các kênh hỗ trợ đã được công bố và hướng dẫn gửi, theo dõi yêu cầu tư vấn.');
?>
<div class="contact-page">
<?php public_page_intro('Liên hệ', 'Kết nối với Nexa', 'Bạn cần thêm thông tin về dịch vụ hoặc muốn được hỗ trợ? Hãy chọn cách liên hệ phù hợp hoặc gửi yêu cầu tư vấn để Nexa tiếp nhận.'); ?>

<section class="contact-information" id="thong-tin-lien-he" aria-labelledby="contact-information-title">
    <div class="container">
        <div class="contact-section-heading">
            <div><p class="eyebrow">THÔNG TIN LIÊN HỆ</p><h2 id="contact-information-title">Chọn cách kết nối phù hợp.</h2></div>
            <?php if ($hasDirectContact): ?><p>Trao đổi thông tin chung, tìm hiểu dịch vụ hoặc hỏi cách gửi yêu cầu.</p><?php endif; ?>
        </div>
        <?php if ($hasContactInformation): ?>
        <div class="row g-4 contact-information-grid">
            <?php if ($contact['phone'] !== ''): ?>
            <div class="<?= e($contactColumnClass) ?>"><article class="contact-info-card" data-contact="phone">
                <span class="contact-card-icon"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                <h3>Hotline hỗ trợ</h3><p class="contact-card-value"><?= e($contact['phone']) ?></p>
                <a class="contact-card-action" href="<?= e($phoneHref) ?>">Gọi ngay <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </article></div>
            <?php endif; ?>
            <?php if ($contact['email'] !== ''): ?>
            <div class="<?= e($contactColumnClass) ?>"><article class="contact-info-card" data-contact="email">
                <span class="contact-card-icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                <h3>Email liên hệ</h3><p class="contact-card-value"><?= e($contact['email']) ?></p>
                <a class="contact-card-action" href="<?= e($emailHref) ?>">Gửi email <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </article></div>
            <?php endif; ?>
            <?php if ($contact['address'] !== ''): ?>
            <div class="<?= e($contactColumnClass) ?>"><article class="contact-info-card" data-contact="address">
                <span class="contact-card-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                <h3>Địa chỉ liên hệ</h3><p class="contact-card-value"><?= e($contact['address']) ?></p>
            </article></div>
            <?php endif; ?>
            <?php if ($contact['hours'] !== ''): ?>
            <div class="<?= e($contactColumnClass) ?>"><article class="contact-info-card" data-contact="hours">
                <span class="contact-card-icon"><i class="bi bi-clock" aria-hidden="true"></i></span>
                <h3>Thời gian hỗ trợ</h3><p class="contact-card-value"><?= e($contact['hours']) ?></p>
            </article></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (!$hasDirectContact): ?>
        <div class="contact-availability" role="note">
            <span class="contact-card-icon"><i class="bi bi-chat-left-text" aria-hidden="true"></i></span>
            <div><h3>Các kênh liên hệ trực tiếp chưa được công bố.</h3><p>Bạn vẫn có thể chia sẻ nhu cầu qua trang Tư vấn và nhận mã để theo dõi yêu cầu.</p></div>
            <a class="btn btn-primary" href="<?= e(url('tu-van.php')) ?>">Gửi yêu cầu tư vấn <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="contact-methods" aria-labelledby="contact-methods-title">
    <div class="container">
        <div class="contact-section-heading"><div><p class="eyebrow">LIÊN HỆ HAY TƯ VẤN?</p><h2 id="contact-methods-title">Đúng cách cho điều bạn cần.</h2></div></div>
        <div class="row g-4">
            <div class="col-lg-6"><article class="contact-method-card">
                <span class="contact-method-label">01 / TRAO ĐỔI THÔNG TIN</span><h3>Liên hệ trực tiếp</h3>
                <p>Phù hợp khi bạn muốn hỏi thông tin chung, tìm hiểu dịch vụ hoặc trao đổi qua điện thoại, email.</p>
                <?php if ($contact['phone'] !== '' || $contact['email'] !== ''): ?>
                <div class="contact-method-links">
                    <?php if ($contact['phone'] !== ''): ?><a href="<?= e($phoneHref) ?>"><i class="bi bi-telephone" aria-hidden="true"></i> Gọi Nexa <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($contact['email'] !== ''): ?><a href="<?= e($emailHref) ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Gửi email <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><?php endif; ?>
                </div>
                <?php elseif ($contact['address'] !== ''): ?>
                <a class="text-link" href="#thong-tin-lien-he">Xem địa chỉ liên hệ <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                <?php else: ?>
                <p class="contact-method-note">Hiện chưa có kênh liên hệ trực tiếp được công bố.</p>
                <?php endif; ?>
            </article></div>
            <div class="col-lg-6"><article class="contact-method-card contact-method-consultation">
                <span class="contact-method-label">02 / CÓ NHU CẦU CỤ THỂ</span><h3>Gửi yêu cầu tư vấn</h3>
                <p>Chia sẻ bài toán về website, CRM, marketing hoặc chuyển đổi số. Yêu cầu được tiếp nhận và có mã riêng để bạn theo dõi.</p>
                <a class="text-link" href="<?= e(url('tu-van.php')) ?>">Mở trang tư vấn <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </article></div>
        </div>
    </div>
</section>

<section class="contact-process" aria-labelledby="contact-process-title">
    <div class="container">
        <div class="contact-section-heading"><div><p class="eyebrow">SAU KHI GỬI YÊU CẦU</p><h2 id="contact-process-title">Từ tiếp nhận đến theo dõi.</h2></div></div>
        <ol class="contact-support-steps">
            <li><span>01</span><div><h3>Gửi nhu cầu</h3><p>Chọn dịch vụ, mô tả nội dung và để lại thông tin liên hệ tại trang Tư vấn.</p></div></li>
            <li><span>02</span><div><h3>Nhận mã tra cứu</h3><p>Hệ thống tiếp nhận và hiển thị mã yêu cầu ngay sau khi gửi thành công.</p></div></li>
            <li><span>03</span><div><h3>Xem xét & xử lý</h3><p>Quản trị viên xem xét yêu cầu, trao đổi và cập nhật trạng thái xử lý.</p></div></li>
            <li><span>04</span><div><h3>Chủ động theo dõi</h3><p>Dùng mã yêu cầu và số điện thoại đã gửi để kiểm tra tiến độ.</p></div></li>
        </ol>
    </div>
</section>

<section class="contact-help" aria-label="Tra cứu và giải đáp">
    <div class="container"><div class="contact-help-grid">
        <aside class="contact-lookup-card" aria-labelledby="contact-lookup-title">
            <span class="contact-lookup-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
            <p class="eyebrow">ĐÃ KẾT NỐI CÙNG NEXA?</p><h2 id="contact-lookup-title">Bạn đã gửi yêu cầu trước đó?</h2>
            <p>Sử dụng mã yêu cầu và số điện thoại để kiểm tra trạng thái xử lý.</p>
            <a class="btn btn-light" href="<?= e(url('tra-cuu.php')) ?>">Tra cứu yêu cầu <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </aside>
        <div class="contact-faq"><p class="eyebrow">GIẢI ĐÁP NHANH</p><h2>Câu hỏi thường gặp</h2>
            <div class="accordion accordion-flush" id="contactFaq">
                <?php
                $faqs = [
                    'login' => ['Tôi có cần đăng nhập để gửi yêu cầu tư vấn không?', 'Không. Bạn có thể gửi yêu cầu tại trang Tư vấn mà không cần tài khoản. Hãy cung cấp đúng thông tin liên hệ để Nexa trao đổi với bạn.'],
                    'lookup' => ['Làm thế nào để kiểm tra trạng thái yêu cầu?', 'Mở trang Tra cứu, nhập mã yêu cầu và số điện thoại đã cung cấp khi gửi. Kết quả hiển thị dịch vụ, trạng thái và thời gian cập nhật.'],
                    'code' => ['Tôi tìm mã tra cứu ở đâu?', 'Mã được hiển thị ngay sau khi gửi yêu cầu thành công tại trang Tư vấn. Bạn có thể sao chép và lưu lại mã để dùng cho những lần tra cứu sau.'],
                    'status' => ['Những trạng thái yêu cầu có ý nghĩa gì?', 'Mới: yêu cầu đã được tiếp nhận. Đang xử lý: đội ngũ đang xem xét, trao đổi và tư vấn. Hoàn thành: yêu cầu tư vấn đã được xử lý xong.'],
                ];
                foreach ($faqs as $key => [$question, $answer]):
                ?>
                <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" id="contact-faq-<?= e($key) ?>-button" data-bs-toggle="collapse" data-bs-target="#contact-faq-<?= e($key) ?>" aria-expanded="false" aria-controls="contact-faq-<?= e($key) ?>"><?= e($question) ?></button></h3><div class="accordion-collapse collapse" id="contact-faq-<?= e($key) ?>" aria-labelledby="contact-faq-<?= e($key) ?>-button" data-bs-parent="#contactFaq"><div class="accordion-body"><?= e($answer) ?></div></div></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div></div>
</section>
</div>
<?php public_footer(); ?>
