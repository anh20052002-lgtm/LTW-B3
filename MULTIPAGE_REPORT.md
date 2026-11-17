# Báo cáo website nhiều trang Nexa CRM

Phạm vi Frontend từ ràng buộc bổ sung ngày 04/11/2026 được chốt tại `FRONTEND_SCOPE_REPORT.md` theo RULE 001/002. Nội dung dưới đây ghi lại công việc đã hoàn thành trước ràng buộc đó, không cho phép tiếp tục bổ sung module, nghiệp vụ hoặc backend trong một lượt chỉ chỉnh giao diện.

Hoàn thiện và kiểm chứng ngày 04/11/2026, theo kế hoạch lập ngày 03/11/2026. Dự án được cải tiến tại chỗ theo `prompt3.docx`, tuân thủ RULE 001 trong `AGENTS.md`.

## Kết quả

Website public đã chuyển từ một trang chứa các section sang **sáu trang PHP có mục đích riêng**. Navbar/footer dùng chung, mỗi menu mở đúng trang và có active state. Admin giữ Dashboard và hai module CRUD hiện có, bổ sung **Thống kê và báo cáo** cùng **Tài khoản**, dùng chung Sidebar/Header/Footer. CRUD tiếp tục dùng Fetch/PHP/MySQL thật.

Không tạo dự án mới, không thêm framework/dependency, không nâng/hạ phiên bản, không đổi schema hoặc import lại database có sẵn. Các thay đổi trước đợt nhiều trang được giữ lại.

Tham khảo cách chia nhóm trang/CTA của [Haravan](https://www.haravan.com/) và cách tổ chức module, tìm kiếm, bộ lọc trong [tài liệu Getfly](https://wiki.getfly.vn/portal/post/2208). Đã đọc lại các trang tham khảo trong đợt này; giữ nhận diện Nexa và không sao chép thành tích, số liệu hoặc thương hiệu của bên khác.

## Công nghệ trước và sau

| Thành phần | Trước | Sau |
| --- | --- | --- |
| Frontend | HTML5, CSS3, JavaScript ES6 | Giữ nguyên |
| UI | Bootstrap 5.3.8, Bootstrap Icons 1.13.1 | Giữ nguyên vendor |
| Font | Inter sans-serif cục bộ | Giữ nguyên |
| Backend | PHP thuần 8.0.30, PDO, PHP Session | Giữ nguyên |
| Database | MariaDB 10.4.32 qua PDO MySQL, InnoDB/utf8mb4 | Giữ nguyên engine/schema/dữ liệu |
| AJAX | Fetch API và JSON PHP endpoints | Giữ nguyên |
| Môi trường | Apache/XAMPP, PHP dev-router cho local preview/QA | Giữ nguyên |
| Biểu đồ | Không có thư viện chart | SVG/HTML/CSS thuần, không thêm dependency |

Đã đối chiếu hash AGENTS, SQL, vendor và binary runtime trước/sau; 15 tệp bất biến không đổi. Node và Python chỉ dùng kiểm tra lúc phát triển, không làm backend website. Không sửa hoặc xóa RULE 001.

## Sơ đồ trang và menu

| Trang | Thay đổi | Nội dung và chức năng |
| --- | --- | --- |
| `index.php` | Sửa | Hero, dịch vụ nổi bật, lợi ích, quy trình, CTA; không chứa toàn bộ form tư vấn/tra cứu |
| `gioi-thieu.php` | Mới | Mô hình dịch vụ, định hướng, giá trị, quy trình, nguyên tắc chất lượng và CTA |
| `dich-vu.php` | Mới | Danh sách 4 dịch vụ; `?service=crm` mở chi tiết whitelist, nội dung chuẩn bị và CTA chọn sẵn dịch vụ |
| `tu-van.php` | Mới | Form tư vấn thật, prefill hợp lệ, validation/loading/Fetch/success/mã tra cứu |
| `tra-cuu.php` | Mới | Mã + điện thoại, API MySQL/rate limit, trạng thái/thời gian tối thiểu |
| `lien-he.php` | Mới | Thông tin liên hệ thực được cấu hình hoặc hướng dẫn gửi tư vấn/tra cứu; không form trùng, không liên hệ giả |
| `admin/index.php` | Giữ chức năng | Dashboard đọc MySQL, lối vào các module |
| `admin/customers.php` | Giữ URL/chức năng | CRUD khách hàng, lọc quan hệ và phân trang |
| `admin/requests.php` | Giữ URL/chức năng | Module chính CRUD/AJAX, chi tiết, trạng thái, tìm kiếm/lọc/phân trang |
| `admin/thong-ke.php` | Mới | Số liệu tổng và trong 7/30/90 ngày, biểu đồ, trạng thái và bảng ngày |
| `admin/tai-khoan.php` | Mới | Thông tin Admin đang đăng nhập, đổi mật khẩu có xác minh và đăng xuất |
| `admin/login.php` | Sửa | Tương thích kiểm tra phiên mới, lỗi kết nối hiển thị thông báo503 chung |

Giữ hai URL Admin tiếng Anh đang hoạt động thay vì đổi sang tên ví dụ trong prompt; các liên kết cũ không bị phá. Public logo dẫn về trang chủ, Admin logo về Dashboard. Sáu Navbar và năm sidebar module đều là URL thật, không `href="#"` và không mở tab mới. Các link/menu/CTA làm việc ở root và `/nexa-crm` trên Apache; không hardcode tên thư mục cài đặt.

Bookmark cũ `index.php#dich-vu`, `#tu-van`, `#tra-cuu`, `#lien-he` được chuyển tới trang tương ứng bằng whitelist khi JavaScript hoạt động. Các menu mới không dựa vào hash.

## Shared layout và file liên quan

Public dùng một `includes/public-layout.php` cho Header/Navbar/Footer/assets/active state và helper page intro/CTA. `includes/service-content.php` dùng chung catalog nội dung tĩnh hợp lý; không phát sinh CRUD dịch vụ hoặc bảng mới. `assets/css/public.css` tái sử dụng design system, thêm page hero/content/service detail/contact; `assets/js/public.js` chỉ khởi tạo form hiện hữu trên từng trang.

Admin dùng lại `includes/admin-layout.php` và `assets/css/admin.css`; bổ sung menu, link profile, module layout và CSS cục bộ. JavaScript mới là `assets/js/reports.js`, `assets/js/account.js`. Module CRUD và Fetch wrapper hiện có được giữ.

Các file backend/cấu hình sửa hoặc tạo:

- `api/admin/reports.php`, `api/admin/account.php`, `api/admin/password.php`: ba endpoint mới.
- `includes/bootstrap.php`: contact helper, kiểm tra phiên Admin theo fingerprint, vô hiệu hóa phiên, thông báo lỗi trang quản trị chung.
- `api/auth/login.php`: fingerprint, kiểm tra NUL, rehash bằng cập nhật có điều kiện để tránh ghi đè thay đổi mật khẩu đồng thời.
- `api/session.php`: xác minh Admin trước khi trả token, tránh trả CSRF cũ khi phiên vừa bị thu hồi.
- `admin/login.php`: giữ phiên khi database gián đoạn và hiển thị lỗi chung thay vì trang trống.
- `config/config.example.php`: thêm contact email/phone/address/hours rỗng; không thay config local.
- `scripts/dev-router.php`: allowlist các trang mới, giữ chặn source/config/tooling.
- `tests/integration.py`, `tests/protocol.py`: mở rộng hồi quy; `tests/navigation.py`: suite mới.
- `README.md`, `TESTING.md`, `DEMO.md`, `MULTIPAGE_PLAN.md`, báo cáo này và ảnh trong `docs/screenshots/multipage/`.

## Chức năng và hợp đồng API mới

Tất cả endpoint Admin yêu cầu phiên hợp lệ. POST dùng JSON và `X-CSRF-Token`; giữ response `{success,message,data,errors}` và mã HTTP chuẩn của dự án.

### Thống kê

`GET api/admin/reports.php?days=7` nhận 7/30/90 ngày, mặc định 30, dữ liệu sai trả422. `data` gồm:

```json
{
  "range": {"days":7,"start_date":"2026-10-29","end_date":"2026-11-04","timezone":"Asia/Ho_Chi_Minh"},
  "totals": {"total":12,"new":5,"processing":3,"completed":4,"customers":6},
  "period": {"total":0,"new":0,"processing":0,"completed":0,"customers":0,"completion_rate":0},
  "daily": [{"date":"2026-10-29","customers":0,"requests":0}]
}
```

Ví dụ trên chỉ minh họa schema, rút gọn daily; endpoint thực luôn trả đủ 7/30/90 dòng liên tục theo khoảng được truy vấn. `totals` là toàn hệ thống, `period` là dữ liệu được tạo trong kỳ. Số liệu dùng truy vấn MySQL với cận ngày có thể sử dụng index; ngày trống điền 0. `completion_rate` là phần trăm Hoàn thành hiện tại trên nhóm yêu cầu tạo trong kỳ, làm tròn 1 chữ số; không phải lịch sử ngày chuyển trạng thái. UI có nhãn phạm vi, chart SVG có mô tả và bảng ngày để đối chiếu.

### Tài khoản và mật khẩu

`GET api/admin/account.php` chỉ trả `data:{id,name,email,created_at}` của Admin hiện tại; không nhận ID để xem người khác và không trả hash.

`POST api/admin/password.php` nhận ba chuỗi `current_password`, `new_password`, `confirm_password`. Không cung cấp mật khẩu mẫu dùng chung. Mật khẩu mới cần khác hiện tại, dài 12–72 byte UTF-8, không chứa NUL và không bị trim. Sai hiện tại/mismatch/input không hợp lệ trả422, thiếu auth401, sai CSRF403, vượt giới hạn429. Xác minh giới hạn 5 lần/15 phút theo IP và tài khoản, sau validation cấu trúc.

Endpoint khóa dòng Admin trong transaction, xác minh mật khẩu rồi cập nhật hash bằng prepared statement. Thành công trả `data:{csrf_token}`; session ID và CSRF được đổi, Fetch wrapper nhận token mới. Phiên hiện tại tiếp tục dùng được, phiên khác mất quyền ở request kế tiếp do fingerprint hash không còn khớp. Không thêm cột database hoặc quét/xóa session người khác trên filesystem. Phiên có từ trước cơ chế fingerprint cần đăng nhập lại một lần.

### Public và liên hệ

Giữ nguyên `api/public/create.php` và `lookup.php`, prepared statements, validation và rate limit. Sau gửi, chỉ mã tra cứu có thể đi qua sessionStorage một lần; không lưu điện thoại/nội dung yêu cầu ở đó và không đặt điện thoại trên URL. Storage bị chặn vẫn sử dụng được mã đang hiển thị.

Contact chỉ lấy các giá trị công khai đã cấu hình: `contact_email`, `contact_phone`, `contact_address`, `contact_hours`, hoặc biến `NEXA_CONTACT_*`. Mặc định trống, email/phone được validate, output escape. Không lấy email Admin làm email doanh nghiệp, không tự tạo địa chỉ/số điện thoại/chứng nhận/thành tích. Khi chưa cấu hình, trang vẫn có nội dung hữu ích và CTA thật; không chèn bản đồ bên ngoài.

## Kiểm thử và lỗi đã sửa

| Môi trường | Integration | Protocol | Navigation | Tổng mỗi môi trường |
| --- | ---: | ---: | ---: | ---: |
| PHP dev-router8090 | 187 | 51 | 93 | 331 đạt |
| Apache8091 `/nexa-crm`, app_url trống | 187 | 51 | 93 | 331 đạt |

Không có thất bại ở các suite cuối. Thêm 6 kiểm tra SQL độc lập về biên ngày/kỳ rỗng, 5 kiểm tra database gián đoạn/phục hồi và 6 kiểm tra chỉ đọc preview8088 đều đạt. PHP lint 42 tệp, JS syntax 7 tệp, Python compile 3 suite và diff check đạt. Chi tiết trong `TESTING.md`.

Browser đã bấm sáu public menu ở 375/768/1440px, xem nội dung/active/CTA, thử chi tiết CRM → tư vấn prefill → gửi thật → tra cứu. Admin thấy yêu cầu mới, sửa nội dung, đổi Hoàn thành; public tra cứu thấy trạng thái mới. Xóa đúng fixture qua UI rồi tra cứu không tìm thấy. Cả năm sidebar module được mở; reports/account được xem ở ba kích thước, không tràn ngang. Báo cáo đổi 7/90 ngày qua Fetch, giữ bảng mở và Back khôi phục đúng; tài khoản kiểm tra form trống và đăng xuất. Đổi mật khẩu thực được kiểm tra qua HTTP API với hai client độc lập; không tuyên bố đã đổi mật khẩu qua browser UI.

Đã sửa các vấn đề phát hiện trong đợt này: JS cũ giả định hai form cùng trang; header/menu hash chưa có đích trang riêng; thiếu module báo cáo/tài khoản; phiên cũ còn quyền sau đổi mật khẩu; token trả trước lúc vô hiệu hóa phiên; race rehash; trang login có thể trống khi DB gián đoạn; dính chữ quanh `<br>` bị CSS ẩn. Không ghi nhận lỗi/cảnh báo console trong các luồng browser đã chạy.

Đối chiếu cuối: database có sẵn vẫn 1 Admin seed, 6 khách hàng, 12 yêu cầu, 3 bucket rate limit, cùng hash dữ liệu/schema trước đó. Đã dọn fixtures/tài khoản/SQL user/credentials của hai QA DB, giữ 6 khách hàng/12 yêu cầu mẫu ở mỗi DB. Server QA8090/8091/8092 đã dừng; **preview8088 và MariaDB3308 giữ chạy** cho người dùng xem bản mới. Một số ảnh chụp trong lúc có fixture QA nên hiển thị 13 yêu cầu hoặc thông tin Admin QA.

## Giới hạn và cách chạy

Chưa kiểm thử production hosting/HTTPS thực, mọi thiết bị/trình duyệt, screen reader chuyên dụng hoặc tải đồng thời lớn; engine thực chạy là MariaDB 10.4.32, chưa chạy MySQL 8 riêng biệt. Native select khách hàng chưa benchmark với hàng chục nghìn hồ sơ. Không bổ sung hệ thống phân cấp Admin, CRUD dịch vụ hoặc lịch sử thay đổi trạng thái vì không thuộc yêu cầu này.

Cài mới trên XAMPP: đặt thư mục dưới htdocs/alias, import `database.sql` vào database đồ án trống, điền cấu hình riêng và tạo Admin bằng `scripts/create-admin.php`. Với database hiện có, chỉ cập nhật source và giữ cấu hình, **không import lại SQL hoặc chạy migration**. Trang public không cần đăng nhập; Admin dùng tài khoản riêng, seed vẫn vô hiệu hóa và không có mật khẩu công khai.

Xem `README.md` cho cấu hình/test guards và `DEMO.md` cho kịch bản trình bày hai vai trò. Khi chạy test, dùng database `_test` riêng, không trỏ suite mutation vào preview chứa dữ liệu cần giữ.
