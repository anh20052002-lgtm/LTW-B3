# Kế hoạch triển khai hệ thống Nexa CRM

## Phạm vi

Triển khai đúng stack PHP thuần 8.0+ (khuyến nghị 8.2+), MySQL 8/MariaDB 10.4+, HTML5, CSS3, JavaScript ES6, Bootstrap 5, Bootstrap Icons và Inter. Khách hàng không đăng nhập; Admin dùng PHP Session. Dữ liệu được lưu bằng PDO vào MySQL. Không dùng backend Node hoặc dữ liệu giả thay database.

Ưu tiên CRUD yêu cầu tư vấn qua Fetch API; bổ sung quản lý khách hàng, dashboard, đăng nhập và tra cứu công khai. Yêu cầu của khách vãng lai giữ thông tin liên hệ tại thời điểm gửi và `customer_id = NULL`, không tự gộp khách hàng.

## Cấu trúc dự kiến

```text
index.php                    Landing page, dịch vụ, gửi yêu cầu và tra cứu
admin/                       Đăng nhập, dashboard, yêu cầu, khách hàng
api/session.php              Token CSRF và trạng thái phiên
api/auth/                    Đăng nhập / đăng xuất
api/public/                  Gửi và tra cứu yêu cầu
api/admin/                   Dashboard và các endpoint CRUD riêng
includes/                    Bootstrap ứng dụng, PDO, auth, validation, layout
config/config.example.php    Cấu hình mẫu không chứa bí mật
assets/css/                  Design tokens, public và admin styles
assets/js/                   Fetch wrapper, tương tác public và admin
assets/vendor/               Bootstrap, Bootstrap Icons, Inter chạy offline
database.sql                 Schema, khóa ngoại, index, dữ liệu demo
scripts/create-admin.php     Tạo Admin qua CLI bằng password_hash
tests/                       Kiểm thử API / kiểm tra cấu trúc phù hợp
README.md                    Cài đặt XAMPP và cấu hình
DEMO.md                      Luồng demo hai vai trò
```

## Thiết kế UI/UX

- Thương hiệu Nexa, giao diện tiếng Việt. Primary `#2563EB`, navy `#0F172A`, nền `#F8FAFC`, surface trắng, slate `#64748B`, success `#16A34A`.
- Inter duy nhất, thang khoảng cách 4/8px, bo góc 12–20px, viền tinh tế, focus rõ ràng, giảm chuyển động khi người dùng yêu cầu.
- Public: navigation → hero hai cột có minh họa quy trình tư vấn → dịch vụ → form tư vấn → tra cứu → liên hệ/footer.
- Admin: sidebar 248px → topbar → tiêu đề và hành động → chỉ số / thanh lọc → bảng phân trang; mobile dùng Bootstrap offcanvas.
- Kiểm tra mục tiêu 375px, 768px và 1440px. Bảng cuộn trong vùng riêng; không làm trang tràn ngang.

## Database

- `admins`: email duy nhất, password hash, tên, thời gian tạo.
- `customers`: hồ sơ khách hàng quản lý thủ công.
- `customer_requests`: mã ngẫu nhiên duy nhất, liên hệ tại thời điểm gửi, dịch vụ, nội dung, trạng thái, customer_id nullable; xóa khách hàng đặt FK về NULL, giữ yêu cầu.
- Bảng rate limit riêng để giới hạn theo IP bền vững qua nhiều phiên, không chỉ dựa vào session.
- Dữ liệu demo rõ ràng; tạo Admin bằng CLI, không hardcode mật khẩu.

## Hợp đồng API

JSON thống nhất: `{ success, message, data, errors }`. HTTP 200/201, 400/422, 401/403, 404, 405, 429, 500/503. Mutation dùng POST JSON và header `X-CSRF-Token`.

- `GET api/session.php`: `{csrf_token, admin}`.
- `POST api/auth/login.php`, `logout.php`.
- `POST api/public/create.php`: họ tên, điện thoại, email, service, message → mã tra cứu.
- `POST api/public/lookup.php`: tracking_code + phone → dịch vụ, trạng thái và mốc thời gian tối thiểu.
- `GET api/admin/dashboard.php`: `{stats, recent, services}`.
- `api/admin/requests/{list,detail,create,update,status,delete}.php`.
- `api/admin/customers/{list,detail,create,update,delete}.php`.
- List nhận `search`, `status` (requests), `service` (requests), `sort`, `page`, `per_page`; trả `{items, pagination:{page,per_page,total,total_pages}}`.
- Request: `id, tracking_code, customer_id, full_name, phone, email, service, message, status, created_at, updated_at`.
- Customer: `id, full_name, phone, email, company, notes, created_at, updated_at`.
- Service: `website`, `crm`, `marketing`, `consulting`; trạng thái: `new`, `processing`, `completed`.
- Stats: `customers`, `total`, `new`, `processing`, `completed`.

## Các bước và kiểm chứng

1. Đọc prompt, lập kế hoạch (tệp này), xác định runtime sẵn có.
2. Viết schema/config/helpers, auth, validation, CSRF, rate limit và endpoint. Kiểm tra syntax và quyền truy cập.
3. Xây public và Admin bằng Bootstrap với CSS tùy biến, tích hợp AJAX, modal, toast, skeleton, empty/error states.
4. Tạo Admin CLI, tài liệu cài đặt, kịch bản demo.
5. Chạy PHP lint, kiểm thử tích hợp MySQL/API nếu runtime cho phép; thử public submit/lookup, đăng nhập, CRUD, tìm kiếm/lọc/phân trang, quyền, CSRF, input lỗi và dữ liệu sau reload.
6. Kiểm tra giao diện ba kích thước nếu có browser/runtime. Ghi rõ mọi giới hạn kiểm chứng; không tuyên bố chạy thành công khi chưa chạy.

## Bảo mật

PDO prepared statements, escape output, xác thực ở mọi trang/API Admin, regenerate session ID sau login, cookie HttpOnly/SameSite, CSRF, random_bytes cho tracking code, password_hash/password_verify, validation server, rate limit public/login. Log lỗi nội bộ ở server; response không chứa lỗi SQL hoặc thông tin cấu hình.

## Trạng thái

- Đã đọc toàn bộ prompt Word; dự án ban đầu chưa có source.
- Đã chốt kiến trúc và hợp đồng API trước khi viết code.
- Các mục kiểm chứng sẽ được cập nhật bằng kết quả thực tế trong `TESTING.md`.

## Kết quả bàn giao

- Đã triển khai public, đăng nhập, dashboard, CRUD yêu cầu tư vấn và khách hàng bằng PHP/MySQL thật.
- Fetch API tích hợp các thao tác quản trị và public; Bootstrap Modal, toast, loading, empty state, validation và responsive đã có.
- Đã chạy 89 kiểm tra tích hợp/HTTP trên cả PHP development server và Apache/XAMPP tại thư mục con; PHP lint 30 tệp và JS syntax 5 tệp đều đạt.
- Đã thử browser hai vai trò, tìm kiếm/lọc/phân trang, trạng thái, modal, bàn phím và các chiều rộng 375/768/1440px; ảnh và chi tiết ở `TESTING.md`.
- `README.md` hướng dẫn chạy, import SQL, cấu hình và tạo Admin; `DEMO.md` là kịch bản trình bày đồ án.

## Nâng cấp giao diện ngày 02 tháng 11 năm 2026

- Viết lại CSS public và Admin theo cùng hệ thống màu, chữ và khoảng cách; tăng tương phản chữ phụ, kích thước input và các điều khiển.
- Public có hero chọn nhanh dịch vụ, danh sách dịch vụ đánh số với phạm vi tư vấn cụ thể và biểu mẫu được nhấn rõ; giữ nguyên luồng gửi/tra cứu và PHP/MySQL.
- Dashboard chuyển sang hai chỉ số tổng và một nhóm ba trạng thái gọn. Bảng gần đây có chiều cao tự nhiên, hiển thị dạng danh sách đầy đủ trên mobile; khối dịch vụ và hàng chờ không bị kéo giãn.
- Trạng thái loading/rỗng/lỗi của bảng quản trị vừa chiều rộng mobile. Các tệp CSS/JS ứng dụng đổi phiên bản theo thời gian sửa để trình duyệt nhận bản mới.
- Đã rà trực tiếp 375/768/1440px, form gửi và tra cứu thực, modal, liên kết, tìm kiếm/lọc và console. Kết quả đợt này ghi riêng trong `TESTING.md`; không tính lại 89 kiểm tra API của lần đầu.
