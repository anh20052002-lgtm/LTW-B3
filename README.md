# Nexa CRM

Website đồ án **Quản lý khách hàng và yêu cầu tư vấn**. Khách vãng lai xem dịch vụ, gửi yêu cầu và tra cứu bằng mã + số điện thoại. Admin đăng nhập để quản lý yêu cầu, khách hàng và thống kê. Các thao tác thêm, sửa, xóa, tìm kiếm, lọc và phân trang dùng Fetch API; dữ liệu được đọc/ghi vào MySQL bằng PHP PDO.

## Công nghệ

- PHP thuần 8.0 trở lên; khuyến nghị PHP 8.2+ còn được hỗ trợ.
- MySQL 8.0 hoặc MariaDB 10.4+, InnoDB, `utf8mb4`.
- HTML5, CSS3, JavaScript ES6, Bootstrap 5, Bootstrap Icons, font Inter.
- Apache/XAMPP; không cần Node, npm hay Composer để chạy sản phẩm.
- CSS, JavaScript, font và icon nằm trong `assets/vendor/` để demo không phụ thuộc CDN.

## Chạy bằng XAMPP

1. Mở XAMPP Control Panel, khởi động **Apache** và **MySQL**.
2. Chép thư mục dự án vào `C:\xampp\htdocs\nexa-crm` (hoặc DocumentRoot tương ứng). Giữ nguyên cấu trúc thư mục.
3. Mở `http://localhost/phpmyadmin`, chọn **Import**, nhập `database.sql`. Script tạo database `nexa_crm`, các bảng và dữ liệu demo. Chỉ import vào database đồ án; không dùng script để ghi đè môi trường đang có dữ liệu thật.
4. Sao chép `config/config.example.php` thành `config/config.local.php`. Điền cấu hình kết nối tại máy của bạn:

   ```php
   <?php
   return [
       'db_host' => '127.0.0.1',
       'db_port' => 3306,
       'db_name' => 'nexa_crm',
       'db_user' => 'ten_user_mysql_cua_ban',
       'db_pass' => 'mat_khau_mysql_cua_ban',
       'app_url' => '',
   ];
   ```

   `app_url` để trống sẽ tự nhận thư mục con trên XAMPP. Nếu dùng virtual host hoặc reverse proxy, có thể đặt URL gốc, chẳng hạn `http://localhost/nexa-crm`. `config.local.php` là cấu hình riêng và đã được bỏ qua trong `.gitignore`; không đưa mật khẩu lên kho mã nguồn. Tài khoản MySQL cần quyền đọc/ghi bảng của database `nexa_crm`; thao tác import schema cần quyền tạo database/bảng.

5. Tạo Admin bằng PHP CLI theo mục bên dưới.
6. Mở **`http://localhost/nexa-crm/`**. Khu vực Admin: **`http://localhost/nexa-crm/admin/login.php`**.

Nếu Apache dùng cổng khác 80, thêm cổng vào URL. Nếu MySQL dùng cổng khác 3306, sửa `db_port`. PHP cần bật `pdo_mysql`, `session`, `openssl`; `mbstring` được khuyến nghị. XAMPP có sẵn các extension này.

## Tạo tài khoản Admin an toàn

Không có mật khẩu Admin dùng chung được hardcode trong sản phẩm. Tài khoản được tạo bằng `scripts/create-admin.php` qua CLI; mật khẩu được băm bằng `password_hash()`, đăng nhập kiểm tra bằng `password_verify()`. Script không chạy qua trình duyệt.

Mở PowerShell trong thư mục dự án:

```powershell
& 'C:\xampp\php\php.exe' scripts/create-admin.php
```

Nhập họ tên, email và mật khẩu theo hướng dẫn. Mật khẩu cần từ 12 đến 72 byte và được nhập ẩn. Chọn mật khẩu riêng cho buổi demo và giữ ở nơi riêng. Dữ liệu Admin mẫu `admin@example.test` trong SQL bị vô hiệu hóa, không có mật khẩu đăng nhập; dùng email đó trong CLI để kích hoạt hoặc tạo email mới. Script từ chối ghi đè mật khẩu của tài khoản đang hoạt động.

Khi chạy tự động, CLI nhận `NEXA_ADMIN_NAME`, `NEXA_ADMIN_EMAIL`, `NEXA_ADMIN_PASSWORD` từ biến môi trường. Không truyền mật khẩu qua tham số dòng lệnh và không lưu biến đó trong mã nguồn.

## Cấu hình bằng biến môi trường

Ứng dụng cũng nhận `NEXA_DB_HOST`, `NEXA_DB_PORT`, `NEXA_DB_NAME`, `NEXA_DB_USER`, `NEXA_DB_PASS`, `NEXA_APP_URL`; biến môi trường ưu tiên hơn cấu hình local. Có thể chạy PHP development server cho kiểm thử cục bộ sau khi cấu hình database:

```powershell
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8080 -t . scripts/dev-router.php
```

Development server chỉ dùng cục bộ; luôn dùng router đi kèm để chặn source, cấu hình và thư mục riêng vì PHP development server không đọc `.htaccess`. Bản demo chính dùng Apache/XAMPP và các quy tắc `.htaccess`.

## Các trang và chức năng

| Trang | Chức năng |
| --- | --- |
| `index.php` | Giới thiệu, dịch vụ, gửi yêu cầu không cần tài khoản, mã tra cứu và tra cứu trạng thái |
| `admin/login.php` | Đăng nhập Admin |
| `admin/index.php` | Tổng khách hàng/yêu cầu, ba trạng thái, yêu cầu gần đây |
| `admin/requests.php` | CRUD yêu cầu, chi tiết, trạng thái, tìm kiếm, lọc, sắp xếp, phân trang |
| `admin/customers.php` | CRUD hồ sơ khách hàng, tìm kiếm và phân trang |

`DEMO.md` có kịch bản trình bày hai vai trò. `IMPLEMENTATION_PLAN.md` mô tả kiến trúc và hợp đồng API. `TESTING.md` ghi kết quả kiểm chứng và giới hạn thực tế.

## Database và quan hệ

- `admins`: tài khoản quản trị; email duy nhất, chỉ lưu password hash.
- `customers`: hồ sơ khách hàng do Admin chủ động quản lý. Số điện thoại/email chưa xác minh không được tự dùng để gộp hồ sơ.
- `customer_requests`: thông tin liên hệ tại thời điểm gửi, dịch vụ, nội dung, trạng thái và mã tra cứu duy nhất. `customer_id` được phép `NULL` cho khách vãng lai. Xóa hồ sơ khách hàng đặt `customer_id` về `NULL`, giữ nguyên lịch sử yêu cầu và bản sao thông tin liên hệ.
- `rate_limits`: bộ đếm theo IP và hành động để giới hạn công khai qua nhiều phiên truy cập.

Trạng thái lưu trong DB: `new` (Mới), `processing` (Đang xử lý), `completed` (Hoàn thành). Dịch vụ: `website`, `crm`, `marketing`, `consulting`.

## CRUD và AJAX

Từng thao tác gọi endpoint PHP riêng. Mutation dùng POST JSON và header `X-CSRF-Token`; list/detail dùng GET. Response nhất quán:

```json
{"success":true,"message":"Thông báo tiếng Việt","data":{},"errors":{}}
```

HTTP 201 khi tạo; 200 khi đọc/sửa/xóa; 400 cho request sai; 422 cho validation; 401 khi chưa đăng nhập; 403 khi CSRF sai; 404 khi không tìm thấy; 429 khi vượt giới hạn; 500/503 cho lỗi hệ thống. Lỗi trường được hiển thị tại form; lỗi thao tác hiển thị toast. Nút bị khóa trong lúc gửi; danh sách cập nhật mà không reload cả trang. Xóa phải xác nhận qua Bootstrap Modal.

Endpoint chính:

```text
GET  api/session.php
POST api/auth/login.php
POST api/auth/logout.php
POST api/public/create.php
POST api/public/lookup.php
GET  api/admin/dashboard.php
GET  api/admin/requests/list.php
GET  api/admin/requests/detail.php?id=...
POST api/admin/requests/create.php
POST api/admin/requests/update.php
POST api/admin/requests/status.php
POST api/admin/requests/delete.php
GET  api/admin/customers/list.php
GET  api/admin/customers/detail.php?id=...
POST api/admin/customers/create.php
POST api/admin/customers/update.php
POST api/admin/customers/delete.php
```

List hỗ trợ `search`, `page`, `per_page`, `sort`; yêu cầu hỗ trợ thêm `status`, `service`. Giá trị sort được whitelist ở backend; SQL dùng prepared statements.

## Bảo mật

Mọi trang/API Admin kiểm tra PHP Session ở backend. Session ID được thay sau đăng nhập; cookie có HttpOnly, SameSite và Secure khi HTTPS. Mutation đều cần CSRF; server validate lại dữ liệu. Giao diện escape dữ liệu, PDO dùng prepared statements. Tra cứu cần cả mã ngẫu nhiên an toàn và số điện thoại, chỉ trả dịch vụ, trạng thái và thời gian. Lỗi SQL/nội bộ chỉ ghi log server.

Giới hạn mặc định theo IP: gửi yêu cầu 5 lần/10 phút, tra cứu 20 lần/10 phút; đăng nhập cũng được giới hạn. Khi gặp 429, đợi hết khoảng thời gian trước khi thử lại. Không xóa bảng rate limit trên môi trường đang phục vụ người dùng.

Khi đưa lên máy chủ thật, dùng HTTPS, tài khoản MySQL quyền tối thiểu, mật khẩu riêng, sao lưu định kỳ và DocumentRoot/quy tắc Apache phù hợp. Các đường dẫn `config`, `includes`, `scripts`, `tests`, `.tools` không phải trang công khai.

## Kiểm thử lại

Kiểm tra cú pháp PHP:

```powershell
Get-ChildItem -Recurse -Filter '*.php' -File | Where-Object { $_.FullName -notmatch '\\.tools\\' } | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
```

Chạy `tests/integration.py` bằng Python 3 trên **database thử nghiệm riêng**, không chạy lên dữ liệu thật:

```powershell
python tests/protocol.py http://localhost/nexa-crm
python tests/integration.py http://localhost/nexa-crm
```

Nhập email/mật khẩu Admin khi được hỏi, hoặc cung cấp `NEXA_TEST_EMAIL` và `NEXA_TEST_PASSWORD` trong môi trường chạy. Bộ kiểm thử tích hợp tạo/xóa bản ghi của chính nó, xác minh quyền, CSRF, public submit/lookup, đăng nhập, CRUD, lưu dữ liệu, tìm kiếm/lọc/phân trang và rate limit. `protocol.py` kiểm tra method, JSON sai, header/cookie và chặn đường dẫn riêng. Chạy protocol trước integration, hoặc đợi quota hết giữa hai suite: integration sẽ dùng hết quota tra cứu của IP thử nghiệm trong 10 phút.

## Giải thích khi vấn đáp

1. Public form gửi Fetch POST JSON; PHP kiểm tra CSRF, rate limit và validation, sau đó INSERT vào MySQL. Tracking code sinh bằng `random_bytes()`.
2. Admin đăng nhập bằng hash; backend tạo phiên và đổi session ID. Ẩn nút trên giao diện không thay thế kiểm tra quyền ở PHP.
3. Danh sách gọi SELECT có prepared statements và LIMIT/OFFSET; modal gọi POST endpoint khi lưu hoặc xóa. JavaScript nạp lại dữ liệu bảng mà không reload trang.
4. `customer_id` là quan hệ tùy chọn; thông tin liên hệ được giữ tại yêu cầu để lịch sử không mất khi hồ sơ thay đổi.
5. Trạng thái và dịch vụ được whitelist; không nối trực tiếp sort hoặc input người dùng vào câu SQL.
