# Kết quả kiểm chứng

Kết quả kiểm chứng thực tế của source được bàn giao ngày 01/11/2026.

## Môi trường đã xác định

- Windows, XAMPP tại `C:\xampp`.
- PHP CLI 8.0.30 có PDO MySQL, mbstring, session và openssl.
- MariaDB 10.4.32.
- Database thử nghiệm cô lập tại `.tools/mysql-data`, chỉ lắng nghe `127.0.0.1:3308`; không dùng datadir hoặc dịch vụ MySQL của người dùng.

## Database và tích hợp HTTP đã chạy

Ngày kiểm thử: 01/11/2026. PHP development server chỉ bind `127.0.0.1:8088`, kết nối MariaDB thử nghiệm cổng 3308. Không cài dịch vụ hệ thống và không thay đổi datadir XAMPP hiện có.

- Import nguyên `database.sql` bằng PDO vào MariaDB thành công: 1 Admin mẫu vô hiệu hóa, 6 khách hàng, 12 yêu cầu và bảng rate limit rỗng.
- Chạy `scripts/create-admin.php` thành công với email riêng và mật khẩu ngẫu nhiên qua biến môi trường; không lưu mật khẩu vào source.
- Chạy bản cuối `tests/integration.py http://127.0.0.1:8088`: **63 kiểm tra tích hợp đạt, 0 thất bại**. Request đi qua HTTP → PHP → MySQL thật, không dùng mock hoặc SQLite. Các lần lặp thăm dò rate limit không được đếm thành kiểm tra riêng.
- Chạy bản cuối `tests/protocol.py`: **26 kiểm tra HTTP/bảo vệ đường dẫn đạt, 0 thất bại**. Tổng hai suite: **89 kiểm tra đạt**. Kiểm tra thứ26 bổ sung đối chiếu đường dẫn cookie với base path của ứng dụng.
- Đã chạy lại cả hai suite sau khi sửa tự nhận base URL trong API Admin và thêm `scripts/dev-router.php`; các kết quả trên dùng router bảo vệ, không chỉ development server mặc định.
- Bản ghi khách hàng/yêu cầu do test tạo đã được dọn sau khi kiểm tra; đối chiếu lại database có 6 khách hàng và 12 yêu cầu.

## Apache và thư mục con XAMPP đã chạy

Đã dùng chính `C:\xampp\apache\bin\httpd.exe` với cấu hình thử nghiệm riêng tại `.tools/httpd-test.conf`, chỉ lắng nghe `127.0.0.1:8089`, PHP8 module XAMPP và alias `/nexa-crm`. Không sửa cấu hình Apache hệ thống hoặc cài service. Chạy nguyên cả hai suite tại `http://127.0.0.1:8089/nexa-crm`: **63 integration +26 protocol đều đạt**. Cookie có `Path=/nexa-crm/`; login, API Admin và CSRF vẫn hoạt động qua thư mục con. `.htaccess` thực tế trả403 cho toàn bộ bảy đường dẫn riêng đã thử. Log Apache chỉ có các lỗi403 dự kiến do chủ động thử truy cập bị cấm, không có lỗi PHP.

Trước/sau lần kiểm thử Apache, database vẫn có 6 khách hàng và 14 yêu cầu (hai yêu cầu do kiểm tra browser được giữ lại). Các bản ghi tự động tạo được dọn sạch. Apache8089 đã dừng sau khi kiểm thử; PHP preview8088 và MariaDB QA3308 được giữ trong lúc kiểm tra browser.

| Nhóm | Kết quả thực tế |
| --- | --- |
| Khách vãng lai | Gửi yêu cầu HTTP201, nhận mã ngẫu nhiên, trạng thái mặc định Mới, customer_id NULL |
| Tra cứu | Mã + điện thoại khớp trả200; điện thoại sai trả404; không có tên/điện thoại/email/nội dung trong response |
| Quyền Admin | Chưa đăng nhập bị401 ở Dashboard, list/detail và mọi mutation requests/customers |
| CSRF | Thiếu token bị403 ở public create, login và mutation Admin |
| Đăng nhập | Sai mật khẩu401; đúng200; session ID đổi; quyền tồn tại qua request mới |
| Dashboard | Thống kê trả đủ customers/total/new/processing/completed từ DB |
| Khách hàng | Create/read/update/search/delete đều thành công và dữ liệu lưu qua HTTP request mới |
| Yêu cầu | Create/read/update/status/delete đều thành công; dịch vụ/nội dung/trạng thái cập nhật được lưu |
| Quan hệ dữ liệu | Gắn customer_id rõ ràng; xóa customer giữ yêu cầu và đặt customer_id NULL |
| Tìm kiếm/lọc/phân trang | Tìm theo mã; kết hợp trạng thái + dịch vụ + tên; sorting/pagination envelope hợp lệ |
| Input lỗi | Thiếu trường, điện thoại/email sai, trạng thái không hỗ trợ, FK không tồn tại đều422 |
| SQL injection thử | Chuỗi tìm kiếm `' OR 1=1 --` được coi là dữ liệu, API vẫn200 |
| Đăng xuất | Sau logout, API Admin trả401 |
| Rate limit | Tra cứu vượt quota trả429; tạo session mới vẫn429 |
| HTTP protocol | GET vào mutation trả405 + Allow:POST; JSON hỏng/array/content type sai/body quá lớn trả400; lookup input sai422 |
| Response/cookie | X-Content-Type-Options nosniff, X-Frame-Options DENY, Cache-Control no-store; cookie HttpOnly/SameSite=Lax và đúng application path |
| Đường dẫn riêng | database.sql, README.md, .tools/browser-admin.json, config, includes, scripts và tests trả403 trên PHP dev-router |

Sau test, chỉ bảng `rate_limits` trong **database QA cô lập** được xóa để tiếp tục thử giao diện. Không áp dụng thao tác này lên môi trường phục vụ người dùng.

## Kiểm tra mã nguồn

Đã chạy PHP lint bằng `C:\xampp\php\php.exe` phiên bản8.0.30 trên **30 tệp PHP** ngoài `.tools`: tất cả đạt. Đã chạy Node `--check` trên **5 tệp JavaScript ứng dụng** trong `assets/js`: tất cả đạt. Node chỉ dùng kiểm tra cú pháp lúc phát triển, không làm backend.

## Kiểm tra trình duyệt

Đã chạy trên trình duyệt tích hợp với source cuối cùng và MySQL thật:

- Khách không đăng nhập gửi form, thấy mã ngẫu nhiên và tra cứu đúng yêu cầu vừa gửi. Kết quả không hiển thị thông tin cá nhân.
- Form trống đánh dấu các trường bắt buộc và cập nhật `aria-invalid`; có thông báo inline và không gửi dữ liệu sai lên server.
- Admin đăng nhập, dashboard đọc đúng số liệu, mở yêu cầu từ danh sách, cập nhật trạng thái và sửa dịch vụ/nội dung; bảng thay đổi bằng Fetch, không reload document.
- Admin thêm yêu cầu bằng modal, thêm/xem/sửa hồ sơ khách hàng bằng AJAX.
- Tìm kiếm không có kết quả hiển thị empty state; lọc trạng thái và chuyển trang cập nhật bảng đúng dữ liệu.
- Hộp thoại xóa hiển thị đúng bản ghi; chọn Giữ lại đóng modal và giữ dữ liệu. Thao tác xóa thực trong MySQL đã được xác minh ở suite API, bao gồm quy tắc khóa ngoại.
- Menu public và sidebar Admin trên mobile mở được; liên kết và modal được thử bằng bàn phím.
- Public và Admin được rà ở 375px, 768px và 1440px. Độ rộng document không vượt viewport; bảng có vùng cuộn riêng. Bấm tên khách hàng mở chi tiết ngay trên mobile.
- Không ghi nhận lỗi JavaScript hoặc CSP trong console ở các luồng đã thử. PHP lint và JavaScript syntax đã qua.
- Đã phát hiện và sửa tình huống lưu quá nhanh khi modal còn animation: đóng modal chờ sự kiện `shown.bs.modal`, tránh Bootstrap bỏ qua lệnh hide. Đã chạy lại luồng thêm nhanh thành công.

Ảnh kiểm chứng nằm trong `docs/screenshots/`. Dữ liệu thử browser đã được dọn bằng API sau khi đối chiếu mã/tên; database trở lại 6 khách hàng và 12 yêu cầu mẫu. Tài khoản và cấu hình QA riêng không thuộc source sản phẩm.

Sau khi chụp ảnh và kiểm tra đăng xuất, hai Admin QA và tệp credentials đã được xóa. Database QA còn đúng Admin mẫu vô hiệu hóa, 6 khách hàng và 12 yêu cầu. PHP8088, Apache8089 và MariaDB3308 đã dừng; không còn listener của các tiến trình thử nghiệm. Người nhận tạo tài khoản Admin riêng theo README.

## Giới hạn kiểm chứng

Chưa kiểm thử production hosting, HTTPS thực, trình đọc màn hình chuyên dụng, mất mạng vật lý hoặc tải đồng thời quy mô lớn. Fetch wrapper có timeout 15 giây và xử lý lỗi mạng/JSON/server; chưa mô phỏng tất cả tình huống mạng trong trình duyệt. Các server và dữ liệu QA được cô lập, không cấu hình sẵn database hay mật khẩu cho môi trường của người nhận; xem README để chạy bằng XAMPP.

## Kiểm chứng giao diện cải thiện ngày 02/11/2026

Đợt này kiểm tra thay đổi frontend; không chạy lại 89 kiểm tra tích hợp/protocol của ngày 01/11. Đã kiểm tra cú pháp 30 tệp PHP và ba JavaScript thay đổi (`public.js`, `dashboard.js`, `records.js`), tất cả đạt. Sau khi thêm phiên bản assets theo `filemtime`, đã kiểm tra lại cú pháp `index.php`, layout Admin và trang đăng nhập. Rà soát HTTP chỉ đọc trên năm trang xác nhận ID, label, tên trường, liên kết, anchor, assets và response Dashboard/list vẫn khớp API; không thay đổi hồ sơ hoặc yêu cầu trong bước rà soát này.

Đã kiểm tra trực tiếp bằng trình duyệt tích hợp:

- Public và Dashboard tại 375px, 768px và 1440px đều không tràn ngang. Public có độ rộng document lần lượt 363/753/1425px; Admin 360/753/1425px. Bảng Dashboard hiển thị đủ bốn cột ở tablet và không có vùng cuộn dọc lồng trong bảng.
- Dashboard mobile có phần chỉ số cao 194px, bảng gần đây bắt đầu tại 446px nên nhìn thấy các dòng đầu sớm. Liên kết yêu cầu mở đúng chi tiết. Modal chỉnh sửa ở 375px rộng 340px, input 16px; chọn Hủy giữ nguyên dữ liệu.
- Danh sách mobile ở 375px hiển thị đầy đủ loading/empty/error trong viewport. Tìm từ khóa không tồn tại cho empty state có chiều rộng bảng và scroll đều 307px, document 360px; xóa từ khóa trả lại đủ 12 yêu cầu. Lọc Đang xử lý trả đúng 3 bản ghi. Vùng bảng không có thanh cuộn dọc do sai số làm tròn; Dashboard vẫn cho nội dung mở rộng theo chiều dọc.
- Public có body 15px, input mobile 16px và desktop 14px. Chọn nhanh Giải pháp CRM điền đúng dịch vụ; biểu mẫu trống đánh dấu lỗi. Menu mobile/tablet mở và đóng được.
- Gửi yêu cầu tư vấn bằng AJAX thành công, nhận mã ngẫu nhiên; tra cứu trả đúng dịch vụ CRM, trạng thái Mới và thông tin tối thiểu. Không ghi nhận cảnh báo hoặc lỗi JavaScript trong các luồng đã thử.

Ảnh kiểm chứng mới nằm trong `docs/screenshots/frontend-v2/`. Yêu cầu public do browser QA tạo đã được xóa riêng qua Admin API sau khi đối chiếu chính xác mã, tên và email; database trở lại **6 khách hàng, 12 yêu cầu mẫu**. PHP8088 và MariaDB3308 được giữ chạy để người dùng tiếp tục xem website; cấu hình và tài khoản QA không thuộc source sản phẩm.

Sau khi đăng xuất browser QA, đã xóa đúng Admin frontend tạm bằng điều kiện email và tên, đồng thời xóa tệp credentials riêng. Database còn một Admin mẫu, 6 khách hàng và 12 yêu cầu; không thay đổi hồ sơ/yêu cầu khi dọn tài khoản. PHP8088 và MariaDB3308 tiếp tục chạy cho bản xem trước của người dùng.
