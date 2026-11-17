# Phạm vi nâng cấp Frontend Nexa CRM

Ngày kiểm kê: 04/11/2026. Mốc bảo toàn là mã nguồn hiện có tại thời điểm nhận ràng buộc bổ sung về module. Báo cáo này áp dụng cho các lượt chỉnh sửa Frontend từ mốc đó và tuân thủ cả RULE 001 lẫn RULE 002 trong `AGENTS.md`.

## Kết luận phạm vi

Chỉ nâng cấp giao diện, bố cục, typography, màu sắc, điều hướng, responsive và trải nghiệm của chức năng đã tồn tại. Giữ nguyên tên/mục đích module, quy trình, quyền, CRUD/AJAX, API contract, cấu trúc dữ liệu, schema và dữ liệu hiện có. Không thêm, xóa, gộp hoặc đổi tên module để phù hợp với một giao diện tham khảo.

Lượt kiểm kê này chỉ đọc code và cập nhật tài liệu; không chỉnh sửa mã ứng dụng, không gọi API, không chạy test mutation, không truy cập hoặc ghi database, không thao tác dịch vụ đang chạy.

## Phạm vi đã rà soát

- Toàn bộ mã ứng dụng PHP: 12 trang public/Admin, các include dùng chung/helper, 21 tệp trong `api/` (20 endpoint và một helper), cấu hình mẫu và hai script CLI/dev-router.
- Bảy JavaScript ứng dụng, hai CSS ứng dụng; đối chiếu menu, form, event handler, payload và các lệnh Fetch.
- Schema `database.sql`, quan hệ giữa bốn bảng, danh mục dịch vụ và trạng thái hiện có.
- Danh mục thư viện vendor, cấu trúc tests và các tài liệu triển khai/báo cáo đã có. Thư viện vendor không được sửa hoặc thay thế.
- Đã đọc đầy đủ cả hai rule trong `AGENTS.md`; không sửa file quy tắc.

## Module Admin đang tồn tại

Tên được lấy trực tiếp từ sidebar trong `includes/admin-layout.php`.

| Module | Trang hiện có | Chức năng phải giữ nguyên |
| --- | --- | --- |
| Tổng quan | `admin/index.php` | Tổng khách hàng/yêu cầu, ba trạng thái, năm yêu cầu gần đây, phân bổ dịch vụ, tỷ lệ hoàn thành và lối vào module |
| Yêu cầu tư vấn | `admin/requests.php` | CRUD, chi tiết, tìm tên/SĐT/mã, lọc trạng thái/dịch vụ/hồ sơ, sắp xếp, phân trang, cập nhật trạng thái, liên kết hồ sơ |
| Khách hàng | `admin/customers.php` | CRUD, chi tiết, tìm kiếm, lọc có/chưa có yêu cầu liên kết, sắp xếp, phân trang và xem yêu cầu liên quan |
| Thống kê và báo cáo | `admin/thong-ke.php` | Báo cáo 7/30/90 ngày, số liệu tổng và trong kỳ, biểu đồ/bảng theo ngày, trạng thái và tỷ lệ hoàn thành |
| Tài khoản | `admin/tai-khoan.php` | Xem tài khoản đang đăng nhập, đổi mật khẩu có xác minh và đăng xuất |

`admin/login.php` là trang đăng nhập hỗ trợ các module Admin. CLI `scripts/create-admin.php` là công cụ tạo/kích hoạt tài khoản hiện có, không phải module quản lý danh sách Admin.

Thống kê và Tài khoản được triển khai trong lượt trước, trước ràng buộc bổ sung này, và hiện đã có mã nguồn/API. Chúng thuộc mốc hiện tại: giữ nguyên, không tự gỡ bỏ, tạo lại hoặc mở rộng. Các mô tả phát triển backend trong kế hoạch/báo cáo cũ là lịch sử công việc, không phải quyền tiếp tục mở rộng nghiệp vụ trong một lượt chỉ chỉnh Frontend.

## Các trang public đang tồn tại

| Mục Navbar | Trang | Nội dung/chức năng hiện có |
| --- | --- | --- |
| Trang chủ | `index.php` | Nội dung nổi bật, lợi ích, quy trình và CTA tới các trang hiện có |
| Giới thiệu | `gioi-thieu.php` | Nội dung tĩnh về mô hình, giá trị và quy trình |
| Dịch vụ | `dich-vu.php` | Catalog tĩnh bốn dịch vụ, chi tiết qua query `service`, CTA chọn sẵn dịch vụ; không có CRUD dịch vụ |
| Tư vấn | `tu-van.php` | Gửi yêu cầu thật bằng Fetch, validation/loading/success, nhận mã tra cứu |
| Tra cứu | `tra-cuu.php` | Tra bằng mã + điện thoại, đọc trạng thái/dịch vụ/thời gian tối thiểu |
| Liên hệ | `lien-he.php` | Hiển thị thông tin công khai đã cấu hình và CTA; không có form hoặc CRUD liên hệ riêng |

Các trang thông tin/điều hướng không được coi là lý do để tạo thêm module nghiệp vụ. Có thể tạo trang trình bày riêng cho thao tác đã tồn tại, nhưng phải dùng lại hành vi, API, quyền và dữ liệu hiện có; không tự đổi tên/xóa URL đang hoạt động hoặc làm hỏng link cũ.

## Hợp đồng và nghiệp vụ được bảo toàn

Có đúng hai đối tượng CRUD: `customers` và `customer_requests`. Không đổi các trường, quan hệ hoặc hành vi sau để phục vụ thiết kế giao diện:

- Hai nhóm người dùng: khách không đăng nhập và Admin dùng PHP Session. Không thêm đăng nhập khách hàng, vai trò hoặc quyền mới.
- Trạng thái yêu cầu: `new`, `processing`, `completed`. Dịch vụ: `website`, `crm`, `marketing`, `consulting`.
- Yêu cầu lưu bản sao liên hệ. Cập nhật bỏ qua `customer_id` giữ liên kết; gửi `null` bỏ liên kết rõ ràng. Xóa khách hàng đặt FK về `NULL`, giữ yêu cầu và thông tin liên hệ đã lưu.
- Public submit luôn tạo yêu cầu `new`, không tự gộp/liên kết hồ sơ qua số điện thoại hoặc email chưa xác minh.
- Public lookup cần mã và điện thoại, chỉ trả `tracking_code`, `service`, `status`, `created_at`, `updated_at`. Giữ rate limit và không lộ thông tin quản trị.
- Danh sách giữ search/filter/sort/page/per_page, metadata phân trang và URL history hiện có. Không đổi tiêu chí lọc hoặc ý nghĩa của số liệu báo cáo.
- Báo cáo tính trạng thái hiện tại của nhóm yêu cầu được tạo trong khoảng đang chọn; không biến thành lịch sử ngày chuyển trạng thái.
- Tài khoản chỉ xem thông tin hiện tại và đổi mật khẩu theo cơ chế hiện có; giữ xác minh, giới hạn 12–72 byte, CSRF/session rotation, thu hồi phiên cũ và rate limit.
- Giữ envelope JSON `{success,message,data,errors}`, HTTP status, method, endpoint, field names, validation và quyền truy cập hiện có.

### Đối chiếu UI và API hiện có

| Nhóm UI | Endpoint/hành vi đang có |
| --- | --- |
| Phiên và xác thực | `GET api/session.php`, `POST api/auth/login.php`, `POST api/auth/logout.php` |
| Public | `POST api/public/create.php`, `POST api/public/lookup.php` |
| Tổng quan | `GET api/admin/dashboard.php` → `stats`, `recent`, `services` |
| Yêu cầu tư vấn | `GET api/admin/requests/list.php`, `detail.php`; `POST create.php`, `update.php`, `status.php`, `delete.php` cùng thư mục |
| Khách hàng | `GET api/admin/customers/list.php`, `detail.php`; `POST create.php`, `update.php`, `delete.php` cùng thư mục |
| Thống kê | `GET api/admin/reports.php?days=7` (chấp nhận 7/30/90) → `range`, `totals`, `period`, `daily` |
| Tài khoản | `GET api/admin/account.php`; `POST api/admin/password.php` |

Tổng cộng 20 endpoint: 8 GET, 12 POST. `api/_init.php` là helper. Không có module API nghiệp vụ ẩn hoặc CRUD khác ngoài các nhóm trên.

Schema hiện có bốn bảng: `admins`, `customers`, `customer_requests`, `rate_limits`. Không thêm cột/bảng/migration hoặc sửa dữ liệu để phục vụ một yêu cầu giao diện.

## Phần mở rộng bị loại khỏi phạm vi Frontend

Nếu được nhắc lại bởi prompt cũ, tài liệu tham khảo hoặc yêu cầu thiết kế, các phần chưa tồn tại sau sẽ được bỏ qua và ghi nhận, không triển khai ngầm:

| Phần chưa có trong codebase | Xử lý theo RULE 002 |
| --- | --- |
| Ghi chú nội bộ theo yêu cầu, bảng `request_activities`, lịch sử trạng thái, API activity/note | Không tạo module, bảng, endpoint hoặc migration |
| CRUD dịch vụ | Giữ catalog tĩnh bốn dịch vụ, không tạo hệ thống quản trị dịch vụ |
| CRUD liên hệ hoặc form liên hệ gửi dữ liệu mới | Giữ trang thông tin/CTA và form tư vấn hiện có |
| Phân cấp quyền, quản lý danh sách nhiều Admin, đăng ký/đăng nhập khách hàng | Giữ hai nhóm quyền và cơ chế tài khoản hiện có |
| Bộ lọc báo cáo mới, export, trường dữ liệu hoặc bước xử lý mới chưa có | Không bổ sung chỉ để làm giao diện trông đầy đủ hơn |

Ghi chú `customers.notes` đã có thuộc hồ sơ khách hàng và phải được giữ; nó không phải ghi chú nội bộ/lịch sử của yêu cầu. Dashboard hiện không có trường `trend`; không thêm trường đó trong lượt chỉ nâng cấp Frontend.

Không còn phần mở rộng backend nào của prompt3 được coi là công việc chờ triển khai theo phạm vi Frontend mới. Nếu một yêu cầu tiếp theo thực sự cần nghiệp vụ/API/schema/quyền khác, dừng phần xung đột, báo rõ ảnh hưởng và chỉ thực hiện khi người dùng cho phép riêng.

## Ranh giới chỉnh sửa cho các lượt Frontend tiếp theo

Được cải thiện markup của các view/layout PHP, CSS, icon có sẵn, typography, khoảng cách, bố cục, responsive, active state, menu, breadcrumb, modal và cách trình bày loading/empty/error/toast. JavaScript UI chỉ được thay đổi khi giữ nguyên payload, endpoint, trạng thái, validation, xử lý phiên/CSRF và hành vi thao tác.

Giữ các tên form/field, ID hoặc binding cần thiết, dữ liệu readonly, nút xác nhận xóa, trường bắt buộc/tùy chọn và kết quả sau CRUD. Không thay cách lưu, liên kết, tính thống kê hoặc xác thực dưới danh nghĩa tối ưu UX.

Trong phạm vi này, backend `api/`, logic auth/database/validation trong `includes/`, script tạo Admin, cấu hình kết nối, schema/migration và vendor là vùng bảo toàn. PHP include dùng để render view có thể được tổ chức lại nếu chỉ là cách trình bày và không thay đổi hành vi nghiệp vụ.

Stack giữ nguyên: HTML5/CSS3/JavaScript ES6, Bootstrap 5.3.8, Bootstrap Icons 1.13.1, Inter, PHP thuần/PDO, MySQL/MariaDB hiện có và Fetch API, tương thích Apache/XAMPP. Không tự thêm dependency hoặc đổi phiên bản.

## Kiểm chứng lượt rà soát

Đối chiếu mã nguồn tĩnh và ánh xạ menu → view → JS → API → schema; không chạy website hoặc suite ghi/xóa để kiểm kê. Lưu hash trước/sau cho 61 tệp source/quy tắc: giữ nguyên. Không có thay đổi ứng dụng, quyền hoặc dữ liệu trong lượt này. Các kết quả kiểm thử ở `TESTING.md` vẫn là lịch sử đã chạy, không được ghi nhận lại thành kiểm thử mới của lượt chỉ rà soát.
