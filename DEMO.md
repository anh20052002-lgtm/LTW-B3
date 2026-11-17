# Kịch bản demo Nexa CRM

## Giao diện prompt4

Navbar vẫn có sáu trang riêng. Bấm chữ **Dịch vụ** để mở danh mục; bấm nút mũi tên bên cạnh để mở mega menu bốn dịch vụ. Nhóm **Tư vấn** có đường dẫn gửi yêu cầu, tra cứu và quy trình trên trang chủ. Dùng Tab, Enter/Space và Escape để trình diễn điều hướng bàn phím; ở mobile mở hamburger trước. Bấm ngoài dropdown để đóng.

Dashboard giữ dữ liệu thật và trình bày năm chỉ số riêng. Hai module danh sách giữ tìm kiếm, bộ lọc, sắp xếp, phân trang và CRUD bằng AJAX. Ở màn hình hẹp, cuộn trong vùng bảng để xem các cột; modal thêm/sửa và bảng không được làm trang tràn ngang.

Kiểm tra responsive tại 375, 390, 768, 1024, 1440 và 1920 px. Báo cáo và bằng chứng kiểm thử của lượt này được ghi riêng trong `FRONTEND_REDESIGN_REPORT.md`; các số liệu ở tài liệu lịch sử không thay thế kết quả kiểm thử hiện tại.

Chuẩn bị XAMPP, cấu hình database và tạo một Admin theo `README.md`. Chỉ import SQL khi cài mới vào database đồ án trống; bản nâng cấp nhiều trang không cần import lại hoặc migration. Mở DevTools → Network, bật Preserve log để quan sát Document và Fetch/XHR. Dùng hai cửa sổ: cửa sổ thường cho Admin, cửa sổ ẩn danh cho khách vãng lai. Dùng dữ liệu demo, không nhập thông tin cá nhân thật.

## 1. Khách hàng không đăng nhập

1. Mở `index.php`, giới thiệu nội dung nổi bật và sáu mục Navbar. Bấm Giới thiệu, Dịch vụ, Tư vấn, Tra cứu và Liên hệ: mỗi mục mở một trang PHP riêng, active state đổi đúng trang. Trang chủ không gộp toàn bộ form; menu tạo request Document theo mô hình nhiều trang.
2. Trang Giới thiệu trình bày mô hình, định hướng, giá trị và quy trình. Trang Liên hệ chỉ hiển thị thông tin thực tế đã cấu hình hoặc hướng dẫn gửi tư vấn/tra cứu, không có địa chỉ hay số điện thoại giả.
3. Ở `dich-vu.php`, mở chi tiết Giải pháp CRM, rồi bấm Nhận tư vấn dịch vụ này. Trang `tu-van.php?service=crm` chọn sẵn CRM.
4. Thử bỏ trống tên hoặc nhập điện thoại sai để thấy validation tại trường. Gửi tên tự chọn, điện thoại di động Việt Nam, dịch vụ và nội dung từ 10 ký tự; email có thể bỏ trống.
5. Quan sát loading và thông báo thành công. Sao chép mã `NX-...`. Network có `POST api/public/create.php`, HTTP 201; gửi form dùng Fetch và không reload toàn trang.
6. Bấm Tra cứu yêu cầu để sang `tra-cuu.php`: mã có thể được điền một lần qua sessionStorage, điện thoại vẫn do khách nhập. Không đưa điện thoại vào URL. Tra cứu hiển thị **Mới**, dịch vụ và thời gian; không trả tên/email/nội dung liên hệ.
7. Thử cùng mã với điện thoại khác để thấy thông báo không tìm thấy. Giữ mã và cửa sổ này để kiểm tra lại sau khi Admin đổi trạng thái.

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

Sau cập nhật trạng thái, sang cửa sổ khách và tra cứu lại mã/điện thoại để thấy trạng thái mới. Sau khi xóa bản ghi thử, mã đó không còn tìm thấy. Menu Admin tải trang module tương ứng; các thao tác trong module yêu cầu dùng Fetch/XHR.

Thử tìm tên không tồn tại để thấy empty state. Thử bộ lọc trạng thái và dịch vụ, sắp xếp, chuyển trang. Các thao tác nạp dữ liệu qua Fetch, không chuyển document. Có thể bật Network throttling để quan sát loading; tắt mạng trước khi lưu một bản ghi thử để thấy thông báo lỗi và nút được bật lại.

Quan sát URL sau khi tìm kiếm/lọc/chuyển trang: các lựa chọn được lưu trên URL. Tải lại hoặc dùng Back/Forward để xác minh danh sách khôi phục đúng trạng thái. Nút xóa bộ lọc đưa danh sách về mặc định; thêm/sửa/xóa vẫn giữ bộ lọc đang dùng. Nếu bản ghi vừa thêm không khớp bộ lọc hiện tại, chuyển về tất cả để tìm lại.

Để minh họa đầy đủ phân trang, dữ liệu SQL mẫu hoặc số lượng bản ghi hiện tại phải lớn hơn kích thước trang được chọn.

## 4. Quản lý khách hàng

1. Thêm một khách hàng có tên công ty và ghi chú; tìm lại bằng tên/điện thoại.
2. Sửa thông tin, lưu và tải lại để kiểm chứng lưu vào MySQL.
3. Nếu gắn một yêu cầu với hồ sơ này, xóa hồ sơ sẽ giữ yêu cầu và thông tin liên hệ tại thời điểm gửi; `customer_id` trở về `NULL`.
4. Một khách vãng lai gửi cùng số điện thoại sẽ không bị tự gộp vào hồ sơ chưa xác minh.
5. Thử bộ lọc **Có yêu cầu liên kết** và **Chưa có yêu cầu liên kết**, kết hợp tìm kiếm và phân trang. Đây là quan hệ `customer_id` thực trong MySQL, không suy luận từ số điện thoại.
6. Từ chi tiết hồ sơ, mở danh sách yêu cầu liên kết; danh sách chỉ lấy yêu cầu của hồ sơ đó. Từ chi tiết một yêu cầu đã liên kết, mở lại hồ sơ khách hàng. Dùng nút xóa lọc để trở lại toàn bộ danh sách.

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

## 8. Thống kê và báo cáo

1. Bấm **Thống kê và báo cáo** trong sidebar để mở `admin/thong-ke.php`. Hai tổng toàn hệ thống và bốn chỉ số trong kỳ có nhãn phạm vi riêng.
2. Chọn 7, 30 hoặc 90 ngày: URL, khoảng ngày, biểu đồ, trạng thái và tỷ lệ thay đổi bằng Fetch. Mở bảng theo ngày để đối chiếu số lượng; ngày không có dữ liệu vẫn có dòng giá trị 0.
3. Tổng các dòng ngày phải bằng chỉ số khách hàng/yêu cầu trong kỳ. Tỷ lệ hoàn thành bằng số yêu cầu hiện Hoàn thành chia tổng yêu cầu được tạo trong kỳ. Đây không phải báo cáo lịch sử ngày chuyển trạng thái.
4. Đổi khoảng, dùng Back/Forward để thấy lựa chọn được khôi phục. Xem Network `GET api/admin/reports.php?days=...` và SELECT chỉ đọc nếu cần đối chiếu MySQL.

## 9. Tài khoản Admin

1. Mở **Tài khoản** trong sidebar hoặc bấm avatar/tên ở topbar. Trang `admin/tai-khoan.php` hiển thị đúng Admin đang đăng nhập và ngày tạo tài khoản.
2. Form đổi mật khẩu có ba trường, xác minh mật khẩu hiện tại, xác nhận mật khẩu mới và giới hạn 12–72 byte UTF-8. Thử form trống để thấy lỗi tại trường.
3. Nếu trình diễn đổi mật khẩu, dùng tài khoản demo riêng và mật khẩu do người trình bày tự chọn; không để lộ nội dung nhập. Sau đổi, phiên hiện tại tiếp tục làm việc, phiên Admin ở cửa sổ độc lập phải đăng nhập lại ở request kế tiếp.
4. Bấm **Đăng xuất tài khoản**; trở về trang đăng nhập và API Admin bị từ chối. Tránh thử sai mật khẩu hiện tại liên tục vì giới hạn 5 lần/15 phút.

## 10. Tương thích thư mục con và bookmark

Chạy website tại `http://localhost/LTW-B3/` hoặc tên thư mục thực tế. Thử lại menu, chi tiết dịch vụ, form, API và sidebar; URL được sinh bằng helper `url()`. Bookmark cũ như `index.php#tu-van` được chuyển sang trang tư vấn khi JavaScript hoạt động. Bản cài không cần rewrite URL đẹp hoặc framework router mới.

## Lưu ý cho buổi demo

Đừng gửi lặp form công khai liên tục: giới hạn 5 lần/10 phút và tra cứu 20 lần/10 phút theo IP. Chuẩn bị mã yêu cầu từ lần gửi đầu. Bộ kiểm thử tự động nên chạy ở database riêng trước buổi demo; xem kết quả thực tế trong `TESTING.md`.
