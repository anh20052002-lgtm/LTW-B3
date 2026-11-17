# Báo cáo Frontend Redesign — prompt4

Ngày hoàn tất: 04/11/2026. Phạm vi: giao diện, bố cục, điều hướng và trải nghiệm của chức năng hiện có, theo RULE 001 và RULE 002 trong AGENTS.md.

## Kết quả

Đã nâng cấp giao diện sáu trang Public và năm module Admin cùng trang đăng nhập. Giữ nguyên tên/mục đích module, quyền, quy trình, CRUD/AJAX, API contract, cấu trúc database và dữ liệu chính. Không tạo trang nghiệp vụ, endpoint, migration hoặc dependency mới.

Public có header với liên kết trang và nút mở dropdown riêng; mega menu bốn dịch vụ; nhóm Tư vấn dẫn tới gửi/tra cứu/quy trình thật. Trang chủ có hero hai cột, minh họa quy trình tự xây bằng HTML/CSS, card dịch vụ có phạm vi tư vấn, lợi ích, quy trình bốn bước và CTA. Các trang phụ, form, kết quả tra cứu và footer được chuẩn hóa cùng hệ thống. Không dùng thành tích, số liệu hoặc liên hệ doanh nghiệp giả.

Admin có sidebar sáng, breadcrumb, năm thẻ KPI, toolbar tìm/lọc/sắp xếp, bảng và form chia nhóm, modal chi tiết, trạng thái và phản hồi thao tác. Reports và Account dùng lại API hiện có, được nâng cấp qua stylesheet chung. Bảng trên điện thoại cuộn trong vùng riêng; thao tác ghim chỉ áp dụng ở 768–1199 px để không đè vùng bấm tên khách hàng.

Design tokens dùng chung tại `assets/css/tokens.css`: Inter, màu xanh/navy, surfaces, spacing, radius, focus và motion; có `prefers-reduced-motion`. Bootstrap, icon và font tiếp tục tải từ tài sản local.

## Công nghệ trước và sau

| Thành phần | Trước → Sau |
|---|---|
| Frontend | HTML5, CSS3, JavaScript ES6 → giữ nguyên |
| UI | Bootstrap 5.3.8, Bootstrap Icons 1.13.1 → giữ nguyên |
| Typography | Inter variable local → giữ nguyên |
| AJAX | Fetch API và handlers hiện có → giữ nguyên |
| Backend | PHP thuần 8.0.30 → giữ nguyên |
| Database | MySQL-compatible MariaDB 10.4.32 của XAMPP → giữ nguyên |
| Triển khai | Apache/XAMPP 2.4.58 → giữ nguyên; PHP built-in chỉ phục vụ preview/QA |

Không cài framework, thư viện hoặc dependency. Python/Node có sẵn chỉ dùng kiểm thử, không trở thành công nghệ chạy ứng dụng.

## Phạm vi file

12 file có sẵn được chỉnh sửa so với baseline riêng của prompt4:

- Shell/helper trình bày: `includes/public-layout.php`, `includes/service-content.php`, `includes/admin-layout.php`.
- Trang: `index.php`, `tu-van.php`, `admin/index.php`, `admin/requests.php`, `admin/customers.php`, `admin/login.php`.
- Styles/interaction: `assets/css/public.css`, `assets/css/admin.css`, `assets/js/public.js`.

Thêm một stylesheet `assets/css/tokens.css`. Các trang Public khác, Reports và Account nhận giao diện mới qua shell/CSS chung; không cần sửa logic PHP của chúng. Danh mục nội dung dịch vụ giữ nguyên, helper chỉ đổi cách hiển thị card. JavaScript Admin và helper Fetch chung không thay đổi.

Đã tạo `FRONTEND_REFERENCE_ANALYSIS.md`, `FRONTEND_REDESIGN_PLAN.md`, báo cáo này và cập nhật `DEMO.md`, `README.md`. Không ghi đè hoặc loại bỏ các thay đổi trước task.

## Nghiên cứu và phần không triển khai

Đã đọc và quan sát [Haravan](https://www.haravan.com/) desktop/mobile, [website marketing Getfly](https://getfly.vn/) desktop/mobile, và ảnh CRM thật trong [hướng dẫn chính thức Getfly](https://wiki.getfly.vn/portal/post/2208). Không đăng nhập CRM thương mại; ảnh tài liệu PC không chứng minh khả năng responsive của Getfly. Chi tiết và giới hạn ở `FRONTEND_REFERENCE_ANALYSIS.md`.

Chỉ tham khảo phân cấp, bố cục và cách tổ chức tương tác. Không đưa logo, hình ảnh hoặc nội dung độc quyền vào website Nexa. Ảnh nghiên cứu nằm riêng trong thư mục bằng chứng, không được template sử dụng.

Theo RULE 002, không thêm nhóm/nguồn/trạng thái khách hàng mới, phân quyền mới, quản lý nhiều Admin, lịch sử/ghi chú nội bộ yêu cầu, CRUD dịch vụ/liên hệ, global search, notifications, import/export hay workflow marketing. Không bổ sung API thống kê vì module báo cáo đã có API thật. Các phần mở rộng được bỏ qua, không thay đổi nghiệp vụ để phù hợp mẫu tham khảo.

## Kiểm thử thực tế

| Kiểm tra | Kết quả |
|---|---|
| HTTP protocol | 51/51 đạt |
| Integration qua PHP/MySQL thật | 187/187 đạt |
| Điều hướng nhiều trang, liên kết và assets | 93/93 đạt |
| HTML: ID trùng, label, ARIA reference, main/h1 | 12 trang đạt |
| PHP syntax | 42 file đạt |
| JavaScript syntax | 7 file đạt |
| Python QA compile | 4 file đạt |
| `git diff --check` | Đạt |
| So sánh SHA256 nhóm bất biến | 43 file, 0 thay đổi |

Tổng ba suite HTTP: **331 kiểm tra đạt**, chạy trên instance QA riêng cổng 8090 và database `nexa_crm_frontend4_test`, không chạy mutation lên database chính.

Kiểm thử trình duyệt IAB Chromium đã thực hiện trên 12 trang × 6 độ rộng **375, 390, 768, 1024, 1440, 1920 px**: **72 trường hợp bố cục đạt**. Kiểm tra chiều rộng thực của document, font Inter, một h1 và active navigation; không có tràn ngang toàn trang. Bảng quản trị cuộn có chủ đích trong vùng bảng. Đã quan sát ảnh desktop/mobile của các trang và toàn trang chủ; console không có warning/error trong phiên QA cuối.

Đã thao tác thực tế:

- Enter/Space mở menu, Tab tới link, Escape đóng/trả focus, click ngoài đóng; hamburger, nhóm Tư vấn và anchor quy trình hoạt động.
- Dịch vụ CRM → form chọn sẵn → validation → gửi AJAX → nhận/sao chép mã → chuyển tra cứu. Chỉ mã được chuyển một lần; điện thoại không được điền/chuyển sang trang tra cứu.
- Admin đăng nhập, offcanvas mobile, tìm kiếm/xem chi tiết khách hàng, thêm yêu cầu, xem/sửa nội dung, cập nhật Hoàn thành, giữ bộ lọc. Modal có vùng nội dung cuộn và footer thao tác nhìn thấy được.
- Tra cứu thấy trạng thái Mới và Hoàn thành đúng dữ liệu. SQL SELECT trực tiếp xác nhận bản ghi, nội dung và trạng thái sau thao tác.
- Hộp thoại xóa được mở và hủy trong browser. Xóa thật được kiểm thử qua API hiện có; SQL xác nhận không còn từng bản ghi, browser thấy danh sách rỗng và tra cứu không tìm thấy.
- Reports đổi 7/90 ngày, bảng có đúng 7/90 dòng, Back khôi phục 7 ngày; Account có ba lỗi khi gửi form trống và đăng xuất hoạt động. Đổi mật khẩu thật được kiểm thử trong integration suite trên tài khoản QA, không nhập mật khẩu mới qua browser.

Ba bản ghi browser QA (hai từ Public, một từ Admin) đã được xóa bằng API. Database QA trở về 6 khách hàng/12 yêu cầu mẫu. Các ảnh Admin cuối được chụp lại sau khi dọn fixtures; ảnh luồng thao tác riêng có dữ liệu QA tạm thời được ghi nhận tại thời điểm kiểm tra.

## Lỗi phát hiện và đã sửa

1. Mega menu lệch khỏi cạnh trái do specificity của Bootstrap; sửa vị trí và kiểm tra lại bounding box.
2. Gutter `g-5` làm trang tư vấn tràn 4 px trên điện thoại; giảm gutter ngang ở màn hình hẹp.
3. Sau gửi form, thông báo thành công nằm ngoài vùng nhìn; đưa panel vào vùng nhìn, vẫn giữ AJAX và mã tra cứu.
4. Cột thao tác ghim che tâm vùng bấm tên trong bảng điện thoại; chỉ ghim ở tablet/laptop hẹp, điện thoại dùng cuộn bảng.
5. Toast che nút footer modal; đưa lớp thông báo xuống dưới modal. Đã bấm đóng thành công khi toast vẫn đang hiển thị.

## Bảo toàn và giới hạn

Đối chiếu cả hash dữ liệu từng hàng và `SHOW CREATE TABLE` của bốn bảng chính trước/sau: **không thay đổi**. `AGENTS.md`, API, auth, config, schema, scripts, vendor và runtime trong nhóm kiểm tra bất biến giữ nguyên. Không migration, reset hoặc ghi đè database chính.

Bằng chứng ảnh nằm trong `docs/screenshots/redesign/`. Kết quả máy đọc và log chi tiết lưu ở `.tools/frontend4-*` (thư mục ignored; không chứa thông tin đăng nhập trong báo cáo). Bộ kiểm tra layout không thay thế kiểm toán accessibility toàn diện. Chưa kiểm thử trực tiếp Safari/Firefox, thiết bị thật, tải đồng thời hoặc hiệu năng production; reduced-motion được triển khai trong CSS nhưng chưa đổi thiết lập hệ điều hành để kiểm thử.

Preview chính: http://127.0.0.1:8088/index.php. Môi trường này giữ dữ liệu đang có; không tạo tài khoản Admin hoạt động mới trên database chính.

Đã dọn tài khoản Admin, SQL user và file credentials QA, dừng riêng cổng 8090. Giữ preview 8088 (HTTP 200) và MariaDB 3308 hoạt động; database QA chỉ còn dữ liệu mẫu. Bản xác nhận cuối ở `.tools/frontend4-final-verification.json`. Tab preview chính đã được mở và giữ lại trong ứng dụng.
