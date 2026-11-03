# Kịch bản demo Nexa CRM

Chuẩn bị XAMPP, import SQL, cấu hình database và tạo một Admin theo `README.md`. Mở DevTools → Network, bật Preserve log để quan sát Fetch/XHR. Dùng hai cửa sổ: cửa sổ thường cho Admin, cửa sổ ẩn danh cho khách vãng lai. Dùng dữ liệu demo, không nhập thông tin cá nhân thật.

## 1. Khách hàng không đăng nhập

1. Mở trang chủ ở cửa sổ ẩn danh, giới thiệu navigation, bốn dịch vụ, form tư vấn và tra cứu.
2. Thử bỏ trống tên hoặc nhập điện thoại sai để thấy validation tại trường.
3. Gửi thông tin hợp lệ: tên tự chọn, điện thoại di động Việt Nam 10 chữ số, dịch vụ và nội dung từ 10 ký tự. Email có thể bỏ trống.
4. Quan sát nút đang gửi và thông báo thành công. Sao chép mã dạng `NX-...`. Network có `POST api/public/create.php`, HTTP 201; trang không reload.
5. Tra cứu bằng mã + điện thoại vừa dùng. Hệ thống hiển thị trạng thái **Mới**, dịch vụ và thời gian; không trả tên/email/nội dung liên hệ.
6. Thử cùng mã với điện thoại khác để thấy thông báo không tìm thấy.

## 2. Admin đăng nhập và xem Dashboard

1. Mở `admin/login.php`, đăng nhập bằng tài khoản đã tạo qua CLI.
2. Dashboard hiển thị số liệu lấy từ MySQL: tổng khách hàng, tổng yêu cầu và ba trạng thái. Yêu cầu vừa gửi xuất hiện trong danh sách gần đây.
3. Trong Network, `api/admin/dashboard.php` trả JSON. Tải lại trang để xác minh số liệu vẫn đúng.

## 3. CRUD yêu cầu tư vấn bằng AJAX

| Thao tác | Trình bày | Cách kiểm chứng |
| --- | --- | --- |
| Create | Admin mở Thêm yêu cầu, điền thông tin và lưu | POST `requests/create.php`, HTTP 201; dòng mới xuất hiện không reload |
| Read | Tìm theo mã vừa tạo, mở chi tiết | GET `requests/list.php?search=...`, GET `detail.php?id=...`; dữ liệu khớp form |
| Update | Sửa dịch vụ/nội dung, lưu; đổi trạng thái sang Đang xử lý rồi Hoàn thành | POST `update.php`/`status.php`; badge và danh sách cập nhật; tải lại vẫn giữ |
| Delete | Bấm xóa, thử Hủy trước; mở lại rồi xác nhận | Bootstrap Modal; chỉ xác nhận mới gọi POST `delete.php`; bản ghi biến mất |

Thử tìm tên không tồn tại để thấy empty state. Thử bộ lọc trạng thái và dịch vụ, sắp xếp, chuyển trang. Các thao tác nạp dữ liệu qua Fetch, không chuyển document. Có thể bật Network throttling để quan sát loading; tắt mạng trước khi lưu một bản ghi thử để thấy thông báo lỗi và nút được bật lại.

Để minh họa đầy đủ phân trang, dữ liệu SQL mẫu hoặc số lượng bản ghi hiện tại phải lớn hơn kích thước trang được chọn.

## 4. Quản lý khách hàng

1. Thêm một khách hàng có tên công ty và ghi chú; tìm lại bằng tên/điện thoại.
2. Sửa thông tin, lưu và tải lại để kiểm chứng lưu vào MySQL.
3. Nếu gắn một yêu cầu với hồ sơ này, xóa hồ sơ sẽ giữ yêu cầu và thông tin liên hệ tại thời điểm gửi; `customer_id` trở về `NULL`.
4. Một khách vãng lai gửi cùng số điện thoại sẽ không bị tự gộp vào hồ sơ chưa xác minh.

## 5. Đối chiếu MySQL với giao diện

Trong phpMyAdmin, chọn `nexa_crm` → SQL và chạy SELECT chỉ đọc:

```sql
SELECT id, tracking_code, customer_id, full_name, service, status, created_at, updated_at
FROM customer_requests ORDER BY id DESC LIMIT 10;

SELECT status, COUNT(*) AS total
FROM customer_requests GROUP BY status;

SELECT COUNT(*) AS total_customers FROM customers;
```

So sánh mã/trạng thái với bảng và Dashboard. Sau Update, chạy lại SELECT. Sau Delete, tìm theo đúng ID/mã để xác minh không còn bản ghi. Các bản ghi là MySQL thật; dữ liệu mẫu nằm trong SQL import, không nằm trong mảng JavaScript thay thế backend.

## 6. Quyền truy cập và CSRF

1. Trong cửa sổ ẩn danh chưa đăng nhập, mở `api/admin/requests/list.php`: nhận HTTP 401 JSON. Mở trang Admin sẽ được chuyển sang đăng nhập.
2. Sau khi Admin đăng xuất, gọi lại API quản trị sẽ tiếp tục nhận 401.
3. Trong DevTools có thể xem header `X-CSRF-Token` ở mutation. Gửi request thử không có token từ công cụ API sẽ nhận 403.
4. Không công khai token, cookie phiên hoặc mật khẩu trong báo cáo chụp màn hình.

## 7. Responsive và accessibility

Kiểm tra bằng DevTools Device Toolbar tại 375px, 768px và 1440px. Navigation public và sidebar Admin phải mở/đóng được; form đọc được, bảng cuộn trong khu vực bảng, trang không tràn ngang. Dùng Tab để chuyển focus qua input/nút, Enter để gửi form, Escape để đóng modal. Thử bật reduced motion của hệ điều hành nếu cần.

## Lưu ý cho buổi demo

Đừng gửi lặp form công khai liên tục: giới hạn 5 lần/10 phút và tra cứu 20 lần/10 phút theo IP. Chuẩn bị mã yêu cầu từ lần gửi đầu. Bộ kiểm thử tự động nên chạy ở database riêng trước buổi demo; xem kết quả thực tế trong `TESTING.md`.
