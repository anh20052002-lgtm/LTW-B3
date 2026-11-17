# Nexa CRM

Website đồ án **Quản lý khách hàng và yêu cầu tư vấn**. Khách vãng lai xem dịch vụ, gửi yêu cầu và tra cứu bằng mã + số điện thoại. Admin đăng nhập để quản lý yêu cầu, khách hàng và thống kê. Các thao tác thêm, sửa, xóa, tìm kiếm, lọc và phân trang dùng Fetch API; dữ liệu được đọc/ghi vào MySQL bằng PHP PDO.

## Công nghệ

Các lượt nâng cấp giao diện phải tuân thủ RULE 001 và RULE 002 trong `AGENTS.md`. Danh mục module/chức năng hiện có và ranh giới Frontend được chốt trong `FRONTEND_SCOPE_REPORT.md`; yêu cầu mở rộng chưa tồn tại từ prompt cũ không được tự triển khai trong phạm vi này.

- PHP thuần/PDO; giữ phiên bản hiện có theo RULE 001, đã kiểm thử bằng PHP 8.0.30.
- MySQL/MariaDB, InnoDB, `utf8mb4`; runtime đã kiểm thử là MariaDB 10.4.32, không tự nâng/hạ phiên bản.
- HTML5, CSS3, JavaScript ES6, Bootstrap 5, Bootstrap Icons, font Inter.
- Apache/XAMPP; không cần Node, npm hay Composer để chạy sản phẩm.
- CSS, JavaScript, font và icon nằm trong `assets/vendor/` để demo không phụ thuộc CDN.

## Chạy bản demo bằng CHAY-WEB.bat trên Windows

Máy nhận cần cài sẵn XAMPP. Bản đã kiểm thử dùng XAMPP 8.0.30. Nếu XAMPP nằm ở vị trí tùy chỉnh, đặt biến môi trường `XAMPP_HOME` trỏ tới thư mục cài đặt.

1. Nén thư mục dự án và bỏ `.git/`, `.tools/`, `config/config.local.php` khỏi ZIP. Giữ `CHAY-WEB.bat`, thư mục `scripts/`, tệp `database.sql` ở gốc và các tệp `.htaccess`.
2. Trên máy nhận, giải nén toàn bộ rồi nhấp đúp `CHAY-WEB.bat`. Tệp sẽ tự tạo cơ sở dữ liệu MariaDB cho bản demo, chạy máy chủ PHP cục bộ qua `scripts/dev-router.php` và mở trình duyệt.
3. Lần chạy đầu, lưu email và mật khẩu Admin hiển thị trong cửa sổ lệnh để đăng nhập.
4. Giữ cửa sổ lệnh mở khi dùng web. Nhấn **Enter** trong cửa sổ đó để dừng. Dữ liệu demo nằm trong `%LOCALAPPDATA%\NexaCRM` và được dùng lại khi chạy từ cùng đường dẫn giải nén.

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
| `index.php` | Trang chủ: giới thiệu ngắn, dịch vụ nổi bật, lợi ích, quy trình và CTA |
| `gioi-thieu.php` | Mô hình dịch vụ, giá trị, định hướng và quy trình phục vụ |
| `dich-vu.php` | Danh sách dịch vụ; `?service=crm` mở nội dung chi tiết tương ứng |
| `tu-van.php` | Gửi yêu cầu tư vấn thật; `?service=crm` chọn sẵn dịch vụ hợp lệ |
| `tra-cuu.php` | Tra cứu bằng mã yêu cầu và số điện thoại, không cần đăng nhập |
| `lien-he.php` | Thông tin liên hệ đã cấu hình và hướng dẫn kết nối |
| `admin/login.php` | Đăng nhập Admin |
| `admin/index.php` | Tổng khách hàng/yêu cầu, ba trạng thái, yêu cầu gần đây |
| `admin/requests.php` | CRUD yêu cầu, chi tiết, trạng thái, tìm kiếm, lọc, sắp xếp, phân trang |
| `admin/customers.php` | CRUD hồ sơ khách hàng, tìm kiếm và phân trang |
| `admin/thong-ke.php` | Thống kê tổng và theo 7/30/90 ngày, biểu đồ và bảng dữ liệu MySQL |
| `admin/tai-khoan.php` | Thông tin Admin, đổi mật khẩu và đăng xuất |

`DEMO.md` có kịch bản trình bày hai vai trò. `IMPLEMENTATION_PLAN.md` mô tả kiến trúc và hợp đồng API. `TESTING.md` ghi kết quả kiểm chứng và giới hạn thực tế.

Đợt nâng cấp theo `prompt2.docx` được mô tả trong `IMPROVEMENT_PLAN.md` và `IMPROVEMENT_REPORT.md`. Giữ nguyên HTML/CSS/JavaScript, Bootstrap 5, Inter, Bootstrap Icons, PHP thuần/PDO, MySQL/MariaDB và Fetch API. Không thêm dependency và không đổi schema; **database hiện có không cần import lại SQL hoặc chạy migration**.

Đợt nhiều trang theo `prompt3.docx` được mô tả trong `MULTIPAGE_PLAN.md` và `MULTIPAGE_REPORT.md`. Sáu mục Navbar dẫn tới sáu trang PHP riêng, dùng chung header/footer; Admin dùng chung sidebar với năm module. Giữ URL `admin/customers.php` và `admin/requests.php` để không làm hỏng liên kết cũ. Bookmark trang chủ với `#tu-van`, `#tra-cuu`, `#dich-vu`, `#lien-he` được chuyển tới trang tương ứng khi bật JavaScript.

Thông tin liên hệ công khai là tùy chọn trong `config/config.local.php`: `contact_email`, `contact_phone`, `contact_address`, `contact_hours`; tương ứng các biến môi trường `NEXA_CONTACT_EMAIL`, `NEXA_CONTACT_PHONE`, `NEXA_CONTACT_ADDRESS`, `NEXA_CONTACT_HOURS`. Chỉ điền thông tin thực tế được phép công khai; để trống nếu chưa có. Trang liên hệ không lấy email Admin hoặc dữ liệu khách hàng làm thông tin doanh nghiệp. Không cần bản đồ/iframe hay dịch vụ bên ngoài.

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
GET  api/admin/reports.php?days=30
GET  api/admin/account.php
POST api/admin/password.php
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

Hai bộ lọc bổ sung dùng quan hệ có sẵn, không suy đoán khách hàng qua số điện thoại/email:

- `GET api/admin/customers/list.php?has_requests=with` hoặc `without`: có/chưa có yêu cầu liên kết; bỏ trống để xem tất cả.
- `GET api/admin/requests/list.php?customer_id=12`: chỉ xem yêu cầu liên kết hồ sơ ID 12. ID không tồn tại trả danh sách rỗng; ID sai định dạng trả 422. Kết hợp được với search/status/service/phân trang.

Các response vẫn giữ `{success,message,data,errors}` và `data.items`, `data.pagination`. Tham số mới không hợp lệ trả 422 và lỗi trường tương ứng. Danh sách quản trị ghi các lựa chọn tìm kiếm/lọc/sắp xếp/phân trang lên URL để khôi phục sau khi tải lại và khi dùng Back/Forward. URL có thể chứa từ khóa tìm kiếm; tránh chia sẻ URL có thông tin riêng.

API báo cáo nhận `days` là `7`, `30` hoặc `90` (mặc định 30), trả `range`, `totals` toàn hệ thống, `period` trong khoảng đang chọn và `daily` đủ từng ngày theo giờ Việt Nam, kể cả ngày có giá trị 0. Trạng thái/tỷ lệ hoàn thành trong kỳ dựa trên **trạng thái hiện tại của yêu cầu được tạo trong kỳ**, không phải lịch sử ngày chuyển trạng thái.

API tài khoản chỉ trả thông tin Admin đang đăng nhập (`id`, `name`, `email`, `created_at`). Đổi mật khẩu nhận `current_password`, `new_password`, `confirm_password`; cần CSRF và xác minh mật khẩu hiện tại. Mật khẩu mới khác mật khẩu cũ, dài 12–72 byte UTF-8, không trim hoặc chứa ký tự null. Endpoint trả token CSRF mới; Fetch wrapper cập nhật token này. Không gửi mật khẩu qua query string hay ghi vào log. Giới hạn xác minh mật khẩu hiện tại là 5 lần/15 phút theo IP và tài khoản; yêu cầu sai cấu trúc bị validation từ trước bước này.

## Bảo mật

Mọi trang/API Admin kiểm tra PHP Session ở backend. Session ID được thay sau đăng nhập; cookie có HttpOnly, SameSite và Secure khi HTTPS. Mutation đều cần CSRF; server validate lại dữ liệu. Giao diện escape dữ liệu, PDO dùng prepared statements. Tra cứu cần cả mã ngẫu nhiên an toàn và số điện thoại, chỉ trả dịch vụ, trạng thái và thời gian. Lỗi SQL/nội bộ chỉ ghi log server.

Phiên Admin được ràng buộc vào fingerprint của password hash và kiểm tra trạng thái tài khoản tại mỗi request quản trị. Đổi mật khẩu đổi session ID/CSRF, giữ phiên hiện tại và từ chối phiên cũ ở request kế tiếp. Tài khoản bị vô hiệu hóa/xóa cũng mất quyền; các phiên đăng nhập trước khi bổ sung cơ chế này cần đăng nhập lại một lần. Không phải thêm cột hoặc sửa dữ liệu hiện có để áp dụng.

Giới hạn mặc định theo IP: gửi yêu cầu 5 lần/10 phút, tra cứu 20 lần/10 phút; đăng nhập cũng được giới hạn. Khi gặp 429, đợi hết khoảng thời gian trước khi thử lại. Không xóa bảng rate limit trên môi trường đang phục vụ người dùng.

Khi đưa lên máy chủ thật, dùng HTTPS, tài khoản MySQL quyền tối thiểu, mật khẩu riêng, sao lưu định kỳ và DocumentRoot/quy tắc Apache phù hợp. Các đường dẫn `config`, `includes`, `scripts`, `tests`, `.tools` không phải trang công khai.

## Kiểm thử lại

Tài liệu lượt nâng cấp giao diện prompt4: [phân tích tham khảo](FRONTEND_REFERENCE_ANALYSIS.md), [kế hoạch](FRONTEND_REDESIGN_PLAN.md), [báo cáo kết quả](FRONTEND_REDESIGN_REPORT.md) và [kịch bản demo](DEMO.md). Phạm vi module được ghi tại [FRONTEND_SCOPE_REPORT.md](FRONTEND_SCOPE_REPORT.md). Không cần import lại SQL khi áp dụng phần giao diện này.

Kiểm tra cú pháp PHP:

```powershell
Get-ChildItem -Recurse -Filter '*.php' -File | Where-Object { $_.FullName -notmatch '\\.tools\\' } | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
```

Chạy `tests/integration.py` bằng Python 3 trên **database thử nghiệm riêng**, không chạy lên dữ liệu thật:

```powershell
$env:NEXA_TEST_DATABASE = 'nexa_crm_multipage_test'
python tests/protocol.py http://localhost/nexa-crm --allow-write
python tests/integration.py http://localhost/nexa-crm --allow-write
python tests/navigation.py http://localhost/nexa-crm --allow-write
```

Trước khi chạy, cấu hình server thử nghiệm kết nối đúng database được ghi trong `NEXA_TEST_DATABASE`; database này phải có schema và Admin riêng. Cờ `--allow-write` và tên chứa token `_test` là xác nhận của người vận hành, không phải cơ chế tự phát hiện database phía server. Không chỉ đổi tên biến rồi trỏ URL vào website chính. Khi tạo database thử nghiệm từ `database.sql`, sửa **bản sao** hai dòng CREATE DATABASE/USE sang tên thử nghiệm trước khi import, vì file gốc chọn `nexa_crm`.

Nhập email/mật khẩu Admin khi được hỏi, hoặc cung cấp `NEXA_TEST_EMAIL` và `NEXA_TEST_PASSWORD` trong môi trường chạy. Bộ kiểm thử tích hợp tạo/xóa bản ghi của chính nó, xác minh quyền, CSRF, public submit/lookup, đăng nhập, CRUD, lưu dữ liệu, hai bộ lọc liên kết, tìm kiếm/lọc/phân trang và rate limit. `protocol.py` kiểm tra method, JSON sai, header/cookie và chặn đường dẫn riêng. Chạy protocol trước integration, hoặc đợi quota hết giữa hai suite: integration sẽ dùng hết quota tra cứu của IP thử nghiệm trong 10 phút.

## Giải thích khi vấn đáp

1. Public form gửi Fetch POST JSON; PHP kiểm tra CSRF, rate limit và validation, sau đó INSERT vào MySQL. Tracking code sinh bằng `random_bytes()`.
2. Admin đăng nhập bằng hash; backend tạo phiên và đổi session ID. Ẩn nút trên giao diện không thay thế kiểm tra quyền ở PHP.
3. Danh sách gọi SELECT có prepared statements và LIMIT/OFFSET; modal gọi POST endpoint khi lưu hoặc xóa. JavaScript nạp lại dữ liệu bảng mà không reload trang.
4. `customer_id` là quan hệ tùy chọn; thông tin liên hệ được giữ tại yêu cầu để lịch sử không mất khi hồ sơ thay đổi.
5. Trạng thái và dịch vụ được whitelist; không nối trực tiếp sort hoặc input người dùng vào câu SQL.
