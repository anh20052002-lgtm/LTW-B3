<?php
declare(strict_types=1);

/** Editorial service content, independent from customer data and CRUD. */
function public_service_catalog(): array
{
    return [
        'website' => [
            'icon' => 'window-sidebar', 'title' => 'Thiết kế website',
            'short' => 'Một điểm chạm rõ ràng để khách hàng hiểu bạn và dễ dàng kết nối.',
            'intro' => 'Bắt đầu từ mục tiêu của website: giới thiệu doanh nghiệp, trình bày dịch vụ hay tiếp nhận nhu cầu. Làm rõ nội dung và hành trình của khách hàng trước khi lựa chọn cách triển khai.',
            'scope' => ['Giao diện responsive', 'Giới thiệu dịch vụ', 'Biểu mẫu liên hệ'],
            'audience' => 'Doanh nghiệp cần xây dựng website mới hoặc muốn cải thiện cách giới thiệu và tiếp nhận liên hệ trên website hiện tại.',
            'topics' => [['Nội dung & cấu trúc', 'Xác định những trang cần có, thông tin khách hàng tìm kiếm và cách sắp xếp nội dung dễ hiểu.'], ['Trải nghiệm đa thiết bị', 'Trao đổi về cách hiển thị trên điện thoại, thao tác biểu mẫu và các điểm chuyển đổi quan trọng.'], ['Tiếp nhận liên hệ', 'Làm rõ dữ liệu cần thu thập, quy trình chuyển yêu cầu và cách theo dõi sau khi khách hàng liên hệ.']],
            'prepare' => ['Mục tiêu chính của website', 'Địa chỉ website hiện tại, nếu có', 'Nội dung và chức năng bạn muốn ưu tiên'],
        ],
        'crm' => [
            'icon' => 'people', 'title' => 'Giải pháp CRM',
            'short' => 'Tập trung thông tin và theo sát quá trình chăm sóc khách hàng.',
            'intro' => 'Một quy trình chăm sóc rõ ràng bắt đầu từ dữ liệu được tổ chức tốt. Trao đổi cách quản lý hồ sơ, tiếp nhận yêu cầu và theo dõi trạng thái phù hợp với hoạt động hiện tại.',
            'scope' => ['Hồ sơ khách hàng', 'Quản lý yêu cầu', 'Quy trình xử lý'],
            'audience' => 'Đội ngũ cần tập trung thông tin khách hàng, giảm việc theo dõi rời rạc và làm rõ tiến độ của từng yêu cầu.',
            'topics' => [['Hồ sơ khách hàng', 'Xác định thông tin cần lưu, cách tìm kiếm và cách liên kết hồ sơ với nhu cầu phát sinh.'], ['Quản lý yêu cầu', 'Làm rõ luồng tiếp nhận, nội dung cần trao đổi và cách cập nhật trạng thái xử lý.'], ['Quy trình chăm sóc', 'Thống nhất các bước làm việc và những chỉ số cần theo dõi để lựa chọn phạm vi phù hợp.']],
            'prepare' => ['Cách lưu thông tin khách hàng hiện tại', 'Các bước chăm sóc đang sử dụng', 'Những điểm khó theo dõi hoặc thường bị bỏ sót'],
        ],
        'marketing' => [
            'icon' => 'bar-chart-line', 'title' => 'Digital Marketing',
            'short' => 'Làm rõ mục tiêu, thông điệp và cách tiếp cận khách hàng phù hợp.',
            'intro' => 'Tư vấn bắt đầu bằng việc hiểu sản phẩm, khách hàng và mục tiêu truyền thông. Từ đó xác định nội dung, kênh tiếp cận và cách đánh giá phù hợp với nguồn lực của bạn.',
            'scope' => ['Định hướng nội dung', 'Kênh truyền thông', 'Kế hoạch đo lường'],
            'audience' => 'Doanh nghiệp muốn tổ chức hoạt động truyền thông hoặc cần làm rõ hướng tiếp cận khách hàng trên các kênh số.',
            'topics' => [['Mục tiêu & khách hàng', 'Xác định nhóm khách hàng, vấn đề cần giải quyết và kết quả mong muốn của hoạt động truyền thông.'], ['Nội dung & kênh', 'Trao đổi thông điệp, định dạng nội dung và các kênh phù hợp với hành vi khách hàng.'], ['Theo dõi kết quả', 'Lựa chọn cách đo lường và nhịp đánh giá để điều chỉnh hoạt động trên dữ liệu thực tế.']],
            'prepare' => ['Sản phẩm hoặc dịch vụ cần giới thiệu', 'Nhóm khách hàng bạn muốn tiếp cận', 'Mục tiêu và các kênh đang sử dụng'],
        ],
        'consulting' => [
            'icon' => 'compass', 'title' => 'Tư vấn chuyển đổi số',
            'short' => 'Xác định việc cần làm trước, phù hợp với nguồn lực hiện tại.',
            'intro' => 'Không phải mọi quy trình đều cần thay đổi cùng lúc. Cùng nhìn lại cách vận hành, xác định điểm cần ưu tiên và trao đổi lộ trình cải thiện theo từng bước có thể thực hiện.',
            'scope' => ['Đánh giá hiện trạng', 'Xác định ưu tiên', 'Lộ trình triển khai'],
            'audience' => 'Doanh nghiệp đang tìm hướng số hóa quy trình hoặc chưa xác định nên bắt đầu từ website, CRM hay hoạt động truyền thông.',
            'topics' => [['Hiểu hiện trạng', 'Trao đổi quy trình đang vận hành, công cụ đang dùng và những bước mất nhiều thời gian.'], ['Chọn ưu tiên', 'Làm rõ nhu cầu cần xử lý trước dựa trên tác động, nguồn lực và mức độ sẵn sàng.'], ['Lộ trình từng bước', 'Xác định phạm vi ban đầu, các bước tiếp theo và thông tin cần kiểm chứng trước khi triển khai.']],
            'prepare' => ['Quy trình hoặc hoạt động muốn cải thiện', 'Các công cụ đội ngũ đang sử dụng', 'Khó khăn và nguồn lực hiện tại'],
        ],
    ];
}

function public_service_cards(bool $compact = false): void
{
    $number = 0;
    foreach (public_service_catalog() as $key => $service): $number++;
    ?>
    <article class="service-card<?= $compact ? ' service-card-compact' : '' ?>" data-service="<?= e($key) ?>">
        <div class="service-card-top"><span class="service-icon" aria-hidden="true"><i class="bi bi-<?= e($service['icon']) ?>"></i></span><span class="service-number">0<?= $number ?></span></div>
        <h3><a href="<?= e(url('dich-vu.php?service=' . $key)) ?>"><?= e($service['title']) ?></a></h3><p><?= e($service['short']) ?></p>
        <ul class="service-scope" aria-label="Nội dung tư vấn"><?php foreach ($compact ? array_slice($service['scope'], 0, 2) : $service['scope'] as $scope): ?><li><i class="bi bi-check2" aria-hidden="true"></i><?= e($scope) ?></li><?php endforeach; ?></ul>
        <div class="service-card-actions"><a href="<?= e(url('dich-vu.php?service=' . $key)) ?>" class="service-link" aria-label="Tìm hiểu <?= e($service['title']) ?>">Tìm hiểu dịch vụ <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><?php if (!$compact): ?><a class="service-consult" href="<?= e(url('tu-van.php?service=' . $key)) ?>" aria-label="Nhận tư vấn <?= e($service['title']) ?>">Nhận tư vấn <i class="bi bi-arrow-right" aria-hidden="true"></i></a><?php endif; ?></div>
    </article>
    <?php
    endforeach;
}
