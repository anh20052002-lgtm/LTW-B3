# Nâng cấp trang Liên hệ Nexa — prompt5

Ngày thực hiện: 04/11/2026. Đã đọc AGENTS.md và tuân thủ RULE 001/002.

## Vấn đề trước khi sửa

Trang Liên hệ tập trung vào CTA gửi tư vấn, nội dung chuẩn bị nhu cầu trùng với trang Tư vấn. Khi không có cấu hình liên hệ, người dùng chưa được thông báo rõ về việc thiếu kênh trực tiếp. Email, điện thoại, địa chỉ và giờ làm việc đã có trong cơ chế cấu hình nhưng được gộp trong một khối, chưa có card và hành động riêng.

Kiểm kê xác nhận vẫn có sáu trang Public và năm module Admin: Tổng quan, Yêu cầu tư vấn, Khách hàng, Thống kê và báo cáo, Tài khoản. Không thêm module hoặc chức năng lưu liên hệ.

## Các file thay đổi

| File | Thay đổi |
|---|---|
| `lien-he.php` | Hero riêng, card theo cấu hình, hướng dẫn liên hệ/tư vấn, quy trình hiện có, CTA tra cứu, Bootstrap Accordion FAQ |
| `assets/css/public.css` | Thêm CSS giới hạn trong `.contact-page`, dùng lại design tokens, font và màu hiện tại |
| `includes/public-layout.php` | Một biểu thức mã hóa địa chỉ email trong `mailto:` của Footer, giữ đúng người nhận khi local part chứa ký tự URI đặc biệt |

Tạo báo cáo này và ảnh trước/sau trong `docs/screenshots/contact/`. Không sửa cấu hình thực tế hoặc cấu hình mẫu; bốn trường cần thiết đã có sẵn. Các thay đổi chưa commit từ những lượt trước được giữ nguyên.

## Giao diện và cách hiển thị

- Hero “Kết nối với Nexa”, dùng Header/Footer và breadcrumb hiện có.
- Điện thoại hợp lệ có card Hotline hỗ trợ và link `tel:` đã bỏ dấu phân cách; email có card Email liên hệ và link `mailto:` được mã hóa đúng.
- Địa chỉ và giờ hỗ trợ chỉ xuất hiện khi có giá trị hợp lệ. Số card và chiều rộng cột tự thay đổi theo dữ liệu.
- Khi không có điện thoại, email và địa chỉ, hiển thị thông báo ngắn cùng đường dẫn tới Tư vấn. Chỉ có giờ hỗ trợ vẫn không được coi là đã có kênh liên hệ trực tiếp.
- Hai khối phân biệt trao đổi thông tin chung với nhu cầu tư vấn có mã theo dõi. Không có form liên hệ mới.
- Quy trình bốn bước giữ đúng hành vi gửi yêu cầu → cấp mã → Admin xem xét/cập nhật → khách tra cứu bằng mã và điện thoại.
- CTA dành riêng cho người đã gửi yêu cầu dẫn tới `tra-cuu.php`. Bốn FAQ dựa trên chức năng thật, không đưa ra cam kết phản hồi hoặc gửi email tự động.

Không thêm nút bản đồ vì dự án chưa có cấu hình URL bản đồ. Không tạo địa chỉ, hotline, email hoặc lịch làm việc giả cho website.

## Cách bổ sung thông tin liên hệ thật

Hiện **không có `config/config.local.php` và cả bốn trường liên hệ đều trống**. Vì vậy bản cấu hình thực tế hiển thị thông báo thiếu kênh liên hệ, không có card gọi/email giả.

Nếu chưa có file local, sao chép `config/config.example.php` thành `config/config.local.php`, rồi bổ sung thông tin đã xác minh và cấu hình kết nối theo môi trường của bạn. Nếu đã có file local, chỉ bổ sung/cập nhật các khóa dưới đây trong mảng cấu hình; giữ nguyên thông tin kết nối và các thiết lập khác:

```php
'contact_email' => '',
'contact_phone' => '',
'contact_address' => '',
'contact_hours' => '',
```

Chỉ điền thông tin doanh nghiệp được phép công bố. Các trường để trống sẽ không tạo card hoặc hành động liên hệ. Email phải hợp lệ, điện thoại phải phù hợp bộ lọc đang có; địa chỉ tối đa 500 ký tự và giờ hỗ trợ tối đa 190 ký tự.

Cũng có thể dùng các biến môi trường hiện có: `NEXA_CONTACT_EMAIL`, `NEXA_CONTACT_PHONE`, `NEXA_CONTACT_ADDRESS`, `NEXA_CONTACT_HOURS`. Thứ tự ưu tiên là biến môi trường → file local → mặc định trống. Biến môi trường rỗng cũng ghi đè giá trị local. Không có thông tin đăng nhập Admin nào được dùng làm email liên hệ dự phòng.

## Kết quả kiểm thử thực tế

| Kiểm tra | Kết quả |
|---|---|
| HTTP/cấu hình/HTML/liên kết chỉ đọc | **131 kiểm tra đạt** |
| PHP syntax | **42 file đạt** |
| JavaScript syntax | **7 file đạt**; JavaScript ứng dụng không sửa |
| Bố cục trình duyệt | **10 trường hợp đạt**: hai cấu hình × 375/390/768/1024/1440 px |
| Console trình duyệt | Không có warning/error trong phiên kiểm tra |
| Nhóm mã bất biến | **48 file, 0 thay đổi** |
| File database chính đang tắt | **9 file, SHA256 không đổi** |
| Diff whitespace | Đạt |

Các ca cấu hình gồm: không có thông tin, đầy đủ, chỉ điện thoại, chỉ email, chỉ giờ hỗ trợ, dữ liệu sai/độ dài vượt giới hạn, ký tự HTML và email có ký tự `+`, `?`, `#`. Đã xác nhận địa chỉ email vẫn là cùng người nhận trong card và Footer, không bị biến thành query hoặc fragment của URI. Nội dung cấu hình được escape, không tạo HTML/script từ dữ liệu.

Đã GET thực tế sáu trang Public, bốn trang chi tiết dịch vụ, assets và session endpoint; kiểm tra các đích liên kết và anchor. Đã thao tác bằng browser: Navbar → Liên hệ, Footer → Liên hệ, breadcrumb → Trang chủ, CTA → form Tư vấn hiện có, CTA → form Tra cứu hiện có. FAQ mở bằng Enter/Space và đóng mục trước đó; nút gọi/email trên mobile có chiều cao 44 px. URI gọi/email được kiểm tra, không thực hiện cuộc gọi hoặc gửi thư.

Ảnh trước/sau được quan sát trực tiếp. Không có tràn ngang toàn trang; có một h1, Inter và active menu Liên hệ đúng ở cả mười trường hợp. Ảnh `after-qa-configured-*` dùng dữ liệu **chỉ dành cho QA**, thể hiện rõ địa chỉ/lịch QA; các giá trị này chỉ nằm trong môi trường hai tiến trình PHP tạm, không được ghi vào cấu hình thật. Ảnh `after-unconfigured-*` phản ánh cấu hình hiện tại.

## Bảo toàn công nghệ, nghiệp vụ và dữ liệu

Trước/sau giữ nguyên: HTML5, CSS3, JavaScript, Bootstrap **5.3.8**, Bootstrap Icons **1.13.1**, Inter local, PHP thuần **8.0.30**, MySQL-compatible MariaDB **10.4.32**, Fetch/AJAX và môi trường Apache/XAMPP **2.4.58**. Không cài dependency hoặc đổi phiên bản.

Không sửa API, auth, quyền, CRUD, JavaScript AJAX, database schema, dữ liệu, AGENTS.md, vendor hoặc cấu hình chứa thông tin nhạy cảm. Kiểm thử trang thông tin không cần chạy mutation: mã API/CRUD/Auth được đối chiếu hash; không khởi động MySQL và không chạy lại suite mutation của các lượt trước.

Sau kiểm thử đã đóng tab QA và dừng hai tiến trình PHP tạm 8090/8091. Các cổng 8088/8090/8091/3308 đều tắt, không còn tiến trình database; trạng thái đóng dự án được giữ để thuận tiện nén folder. Bằng chứng máy đọc ở `.tools/contact5-verification.json`, `.tools/contact5-cleanup.json`, `.tools/contact5-browser-layout.json` và log `.tools/contact5-http-results.log`.

## Giới hạn còn lại

Cần chủ dự án cung cấp thông tin liên hệ thật qua cấu hình để card tương ứng xuất hiện. Không có bản đồ, kênh chat hay hệ thống liên hệ mới được bổ sung. Browser QA dùng Chromium trong ứng dụng; chưa kiểm thử Safari/Firefox, ứng dụng gọi điện/email trên thiết bị thật hoặc tiến hành kiểm toán accessibility đầy đủ.
