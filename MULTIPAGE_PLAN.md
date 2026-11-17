# Kế hoạch website nhiều trang Nexa CRM

Ngày lập: 03/11/2026. Thực hiện `prompt3.docx` trên mã nguồn hiện có và tuân thủ RULE 001 trong `AGENTS.md`.

## Khảo sát trước khi sửa

- Public hiện chỉ có `index.php`; năm mục điều hướng chủ yếu dùng hash và cùng mở các section trên trang chủ. Form gửi yêu cầu và tra cứu đều nằm trong trang này.
- Admin đã có các trang riêng `admin/index.php`, `admin/customers.php`, `admin/requests.php`, `admin/login.php`, shared layout và CRUD qua Fetch. Giữ các URL đang hoạt động, không đổi tên chỉ để khớp ví dụ trong prompt.
- Chưa có trang public Giới thiệu/Dịch vụ/Tư vấn/Tra cứu/Liên hệ riêng; chưa có module báo cáo theo thời gian và tài khoản/đổi mật khẩu Admin.
- Tái sử dụng hệ màu, Inter, icon, minh họa hero, card, validation, Fetch wrapper, API public/CRUD, modal/toast, bộ lọc và URL history hiện có.
- Database có `admins`, `customers`, `customer_requests`, `rate_limits`; các bảng đã đủ trường và index cho thống kê và đổi mật khẩu. Không cần thay đổi schema hoặc migration.
- Không có thông tin liên hệ doanh nghiệp đã được xác thực trong cấu hình hiện tại. Trang liên hệ chỉ hiển thị dữ liệu được cấu hình, dùng CTA tư vấn/tra cứu khi chưa có thông tin trực tiếp; không tạo địa chỉ, điện thoại, thành tích hay chứng nhận giả.

## Công nghệ được giữ nguyên

HTML5, CSS3, JavaScript ES6, Bootstrap 5.3.8, Bootstrap Icons 1.13.1, Inter cục bộ; PHP thuần 8.0.30/PDO, MySQL qua MariaDB 10.4.32 và Apache/XAMPP hiện có. AJAX tiếp tục dùng Fetch API. Không cài Chart.js hoặc dependency khác; biểu đồ báo cáo dùng HTML/CSS/SVG từ dữ liệu MySQL. Không nâng/hạ phiên bản, không sửa `AGENTS.md`.

## Sơ đồ trang đích

| Trang | Mục đích và hành động chính |
| --- | --- |
| `index.php` | Landing page giới thiệu ngắn, dịch vụ nổi bật, lợi ích, quy trình và CTA tới trang chức năng; không chứa toàn bộ hai form |
| `gioi-thieu.php` | Mô hình dịch vụ, định hướng, giá trị, quy trình và nguyên tắc chất lượng; CTA thực |
| `dich-vu.php` | Bốn dịch vụ và nội dung chi tiết tĩnh có ích; query `service` whitelist để mở chi tiết, CTA chọn dịch vụ |
| `tu-van.php` | Form tư vấn thật qua API hiện có; prefill dịch vụ hợp lệ từ URL, loading/validation/success/mã tra cứu |
| `tra-cuu.php` | Mã + điện thoại → API MySQL giới hạn tần suất, chỉ thông tin tối thiểu |
| `lien-he.php` | Thông tin liên hệ đã cấu hình hoặc hướng dẫn gửi yêu cầu/tra cứu; không thêm form trùng |
| `admin/index.php` | Dashboard hiện có, số liệu thật và truy cập nhanh |
| `admin/customers.php` | CRUD khách hàng và lọc quan hệ hiện có |
| `admin/requests.php` | Module chính CRUD yêu cầu bằng AJAX, trạng thái, tìm kiếm/lọc/phân trang |
| `admin/thong-ke.php` | Thống kê tổng, theo khoảng 7/30/90 ngày, khách hàng/yêu cầu theo ngày và tỷ lệ hoàn thành |
| `admin/tai-khoan.php` | Thông tin Admin đang đăng nhập, đổi mật khẩu có xác minh, đăng xuất |
| `admin/login.php` | Đăng nhập hiện có |

## Điều hướng và shared layout

- `includes/public-layout.php` là nguồn duy nhất cho public header/footer/assets; active state do PHP xác định theo trang, mọi menu chính dùng URL thật và `url()`.
- `includes/admin-layout.php` bổ sung Thống kê/Tài khoản, giữ Sidebar và breadcrumb thống nhất. Các lối sang public dùng trang tư vấn/trang chủ mới.
- Mọi đường dẫn hoạt động ở web root và thư mục con Apache/XAMPP; không hardcode domain hoặc tên thư mục cài đặt.
- Tương thích bookmark cũ `index.php#dich-vu`, `#tu-van`, `#tra-cuu`, `#lien-he` bằng chuyển tới trang tương ứng theo whitelist. Không dùng hash làm menu chính mới.
- Sau gửi yêu cầu, có thể chuyển mã tra cứu qua sessionStorage có try/catch; không đặt số điện thoại trên URL. Form/lookup JS chỉ khởi tạo khi có phần tử tương ứng.

## Backend và bảo mật

- Giữ nguyên API public và CRUD. Thêm `GET api/admin/reports.php?days=7|30|90` với `range`, `totals`, `period`, `daily`; ngày tính theo Asia/Ho_Chi_Minh, đủ số ngày và điền 0. Số liệu theo ngày lấy bằng truy vấn MySQL có cận created_at; tỷ lệ của nhóm yêu cầu tạo trong khoảng đang chọn được ghi nhãn rõ.
- Thêm `GET api/admin/account.php`: chỉ `id,name,email,created_at`, không trả hash hoặc thông tin tài khoản khác.
- Thêm `POST api/admin/password.php`: current_password/new_password/confirm_password; auth, CSRF, validation 12–72 byte phù hợp CLI, xác minh mật khẩu hiện tại, rate limit và cập nhật hash bằng prepared statement/transaction. Không ghi mật khẩu vào log/URL/response.
- Bind phiên Admin vào fingerprint của password hash qua `admin_auth`. Khi đổi mật khẩu, giữ phiên hiện tại bằng fingerprint mới, regenerate session ID/CSRF; các phiên cũ bị từ chối ở request kế tiếp. Kiểm tra active/hash ở backend, cache xác minh trong mỗi request; không cần thêm cột database.
- Mọi trang Admin/API mới kiểm tra quyền; xử lý lỗi hệ thống bằng thông báo chung. Giữ escape output, chống CSRF/SQLi, privacy và rate limit của tra cứu.
- Thêm tùy chọn contact email/phone/address/hours vào cấu hình mẫu và biến môi trường; giá trị trống mặc định, validate trước hiển thị; không ghi đè cấu hình local.

## File bị ảnh hưởng

- Public: `index.php`, năm trang public mới, `includes/public-layout.php`, catalog dịch vụ dùng chung khi cần, `assets/css/public.css`, `assets/js/public.js`.
- Admin: shared layout, CSS Admin, hai trang mới, `assets/js/reports.js`, `assets/js/account.js`; chỉ cập nhật shortcut/link các trang cũ khi cần.
- Backend: ba API mới, `api/auth/login.php`, `includes/bootstrap.php`, cấu hình mẫu; `scripts/dev-router.php` thêm allowlist trang mới, giữ chặn file riêng.
- QA: mở rộng `tests/integration.py`, `tests/protocol.py` và kiểm tra navigation; không sửa vendor/schema.
- Tài liệu: `MULTIPAGE_PLAN.md`, `MULTIPAGE_REPORT.md`, `README.md`, `TESTING.md`, `DEMO.md`, ảnh browser khi phù hợp.

## Trình tự và kiểm chứng

1. Lưu baseline của source hiện tại; lập kế hoạch trước khi triển khai.
2. Tách shared public layout; tạo sáu public page có nội dung riêng và chuyển menu/CTA sang URL thực.
3. Hoàn thiện hai module Admin và API thống kê/tài khoản; bổ sung bảo vệ phiên/mật khẩu.
4. Cập nhật router, cấu hình contact và các liên kết còn lại; rà không có menu giả, trang rỗng hoặc form trùng.
5. Chạy lint PHP/JS và hồi quy HTTP/CRUD/auth/CSRF; test reports/password/navigation mới trên database QA mới, không ghi/xóa trong database có sẵn `nexa_crm`.
6. Browser kiểm tra hai vai trò, submit → Admin sửa/trạng thái → public lookup, reload/Fetch/CRUD, tất cả menu; xem 375/768/1440 px. Kiểm tra cả Apache thư mục con.
7. Dọn đúng fixtures/tài khoản/tiến trình QA; giữ preview8088 và MariaDB3308 đang được người dùng xem. Đối chiếu dữ liệu gốc, RULE001, schema/vendor/version trước/sau; cập nhật báo cáo bằng kết quả đã thực chạy.

## Rủi ro cần theo dõi

- JS cũ giả định hai form cùng trên index: cần guard theo trang và kiểm tra lỗi console trên cả sáu trang.
- Các bookmark/hash và shortcut cũ có thể hỏng: hỗ trợ chuyển đổi whitelist và kiểm tra link ở root/thư mục con.
- Token/password phiên thay đổi có thể ảnh hưởng AJAX: đồng bộ token response vào Fetch wrapper, thử hai session và mật khẩu cũ/mới trên tài khoản QA.
- Biểu đồ dễ gây hiểu nhầm giữa tổng dữ liệu và khoảng lọc: ghi nhãn phạm vi/thời gian và đối chiếu trực tiếp MySQL.
- Preview đang dùng database có sẵn: chỉ test mutation trên database mới; không xóa dữ liệu hoặc thay cấu hình nhạy cảm.
