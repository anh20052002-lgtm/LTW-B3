# Kết quả kiểm chứng

Mục **Website nhiều trang theo prompt3 ngày 04/11/2026** ở cuối tệp ghi kết quả mới nhất. Các mục trước đó là lịch sử kiểm chứng của những phiên bản trước.

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

## Nâng cấp theo prompt2 ngày 03/11/2026

### Môi trường và phạm vi dữ liệu

- PHP 8.0.30, PDO MySQL, MariaDB 10.4.32 và Apache/XAMPP có sẵn. Không cài framework hay dependency.
- Tạo hai database mới: `nexa_crm_improvement_test` cho PHP dev-router tại `127.0.0.1:8090`, và `nexa_crm_improvement_apache_test` cho Apache thử nghiệm tại `127.0.0.1:8091/nexa-crm`. Dùng bản sao SQL có CREATE DATABASE/USE trỏ đúng tên thử nghiệm, không import lại vào `nexa_crm`.
- User MySQL QA chỉ có SELECT/INSERT/UPDATE/DELETE trên database thử nghiệm tương ứng. Cấu hình sản phẩm và datadir/dịch vụ XAMPP của người dùng không bị thay thế.
- Database có sẵn `nexa_crm` được đối chiếu số dòng và hash cả bốn bảng trước/sau: không đổi. Dữ liệu thử nghiệm được dọn theo đúng ID/mã/tên/email, không dùng DELETE hàng loạt trên dữ liệu hiện có.

### Kiểm thử tự động đã chạy

| Môi trường | Integration | Protocol | Kết quả |
| --- | ---: | ---: | --- |
| PHP dev-router 8090 | 114 | 44 | 158 kiểm tra đạt, 0 thất bại; protocol cuối chạy sau browser và khôi phục server |
| Apache 8091 với `/nexa-crm` | 114 | 44 | 158 kiểm tra đạt, 0 thất bại |

Các kiểm tra là HTTP → PHP → MySQL thật. Các lần lặp tiêu quota rate limit không được đếm thành từng kiểm tra. Hai suite được chạy trên hai môi trường; không cộng thành số lượng chức năng khác nhau.

Phạm vi hồi quy gồm login/logout, session ID và CSRF đổi sau login, token cũ bị từ chối, quyền Admin, CSRF mutation, public submit/lookup, privacy whitelist chính xác, CRUD khách hàng/yêu cầu, dữ liệu lưu qua request mới, giữ liên kết khi update bỏ `customer_id`, FK SET NULL khi xóa hồ sơ, Dashboard, search literal `%`/`_`/`!`, SQL injection thử, status/service filter, hai bộ lọc liên kết mới, invalid arrays/ID, count/pagination/sort, JSON/method/header/cookie và private paths. Trên Apache, cookie có `Path=/nexa-crm/` và `.htaccess` chặn cả tài liệu kế hoạch/báo cáo mới.

Hai suite hiện yêu cầu `--allow-write` và `NEXA_TEST_DATABASE` có token `_test`; đã thử thiếu xác nhận, cả hai thoát mã 2 trước HTTP. Guard là xác nhận của người vận hành, không tự đọc database private phía server. Xem README để cấu hình đúng server/URL thử nghiệm.

### Trình duyệt đã chạy

- **Public không đăng nhập:** menu mobile mở/đóng; chọn card CRM điền đúng service; form trống đánh dấu bốn trường bắt buộc và focus họ tên; nhập nội dung có emoji cho counter `76/5000`, khớp 76 Unicode code point. Gửi bằng Fetch thành công, nhận mã ngẫu nhiên; sao chép có thông báo inline; tra cứu bằng mã và điện thoại trả dịch vụ, trạng thái, thời gian, không hiển thị tên/email/nội dung.
- **Admin và dữ liệu thật:** login thành công, yêu cầu public xuất hiện ở Dashboard. Tạo/xem/sửa khách hàng và yêu cầu thủ công, đổi trạng thái tới Hoàn thành; tải lại vẫn giữ nội dung/trạng thái. Sau tạo, tự mở chi tiết bản ghi mới. Hủy xóa giữ bản ghi; xác nhận xóa loại bỏ đúng bản ghi.
- **Quan hệ:** tạo hồ sơ mới chưa có yêu cầu → filter `without` có một kết quả, `with` loại đúng hồ sơ đó. Liên kết yêu cầu với hồ sơ, điều hướng hai chiều và danh sách `customer_id` trả đúng một yêu cầu. Xóa hồ sơ giữ yêu cầu và snapshot liên hệ, hiển thị chưa liên kết; sau đó xóa yêu cầu QA riêng.
- **Trạng thái danh sách:** kết hợp Đang xử lý + CRM cho hai yêu cầu mẫu; reload giữ lựa chọn, Back trả ba yêu cầu Đang xử lý và Forward trả lại hai yêu cầu CRM. Tìm không có kết quả hiển thị empty state; xóa lọc phục hồi danh sách. Trang 2 hiển thị 11–12 trong 12 yêu cầu và được giữ sau reload.
- **XSS:** lưu chuỗi thử có `<img ...>` và `<script>` qua API/form; chi tiết yêu cầu và ghi chú hồ sơ hiển thị nguyên văn như text. DOM trong chi tiết có 0 thẻ img/script được tạo từ payload.
- **Lỗi mạng có chủ đích:** dừng riêng PHP QA8090, bấm Làm mới danh sách. UI báo không kết nối được, hết busy, nút làm mới bật lại, vẫn giữ `page=2`. Khởi động lại cùng server/database/session rồi Làm mới phục hồi đúng trang 2, không mất dữ liệu. Chưa mô phỏng timeout đủ 15 giây hoặc mọi dạng lỗi mạng.
- **Responsive:** đã xem public và Dashboard tại 375/768/1440px; document không vượt viewport. Public lần lượt 363/753/1425px; Dashboard 360/753/1425px khi có scrollbar. Card dịch vụ một cột mobile, hai cột tablet/desktop. Bảng quản trị cuộn trong vùng bảng; empty và error mobile có region/scroll cùng 322px, không tràn trang. Modal375 rộng340px, input16px. Sidebar375/768 điều hướng đúng sau sửa stacking, Escape đóng menu.
- **Lỗi đã tìm và kiểm tra lại:** backdrop che sidebar mobile/tablet; toast thành công che nút Chỉnh sửa trong modal mobile. Sidebar hiện cao hơn backdrop; quick-status hiện thông báo inline trong modal. Đã thử lại đổi trạng thái → chỉnh sửa thành công. Không ghi nhận lỗi/cảnh báo JavaScript trong console ở các luồng đã thử.

Ảnh thực tế trong `docs/screenshots/improvement/`; một số ảnh chụp trong lúc còn fixture QA nên có số liệu khác ảnh Dashboard cuối. Dashboard cuối có **6 khách hàng, 12 yêu cầu: 5 Mới, 3 Đang xử lý, 4 Hoàn thành**, tỷ lệ hoàn thành 33% lấy từ 4/12. Các fixture browser đã được xóa qua UI; không giữ tài khoản/mật khẩu QA trong source.

### Kiểm tra mã nguồn và công nghệ

PHP lint **30 tệp** và JavaScript syntax **5 tệp ứng dụng** đều đạt trên source cuối. Python compile hai suite đạt. `git diff --check` đạt. `database.sql`, toàn bộ `assets/vendor/` và cấu hình sản phẩm không đổi. Stack trước/sau vẫn là HTML5/CSS3/JavaScript ES6, Bootstrap 5.3.8, Bootstrap Icons 1.13.1, Inter, PHP thuần/PDO, MySQL/MariaDB, PHP Session và Fetch API; Node/Python chỉ được dùng cho kiểm tra lúc phát triển.

### Trạng thái bàn giao

Đã đăng xuất browser, xóa đúng Admin QA, SQL user và tệp credentials tạm của cả hai môi trường. Mỗi database QA còn một Admin seed vô hiệu hóa, 6 khách hàng và 12 yêu cầu mẫu. Database có sẵn `nexa_crm` còn nguyên 1 Admin, 6 khách hàng, 12 yêu cầu và 3 bucket rate limit. Không ghi nhận PHP runtime error trong log PHP/Apache.

PHP8090, Apache8091 và MariaDB3308 đã dừng; MariaDB shutdown hoàn tất bình thường. Kiểm tra cuối **không còn listener trên 8088/8090/8091/3308**. Bản web8088 trước đó được người dùng tắt và không bị mở lại. Khi cần chạy website, dùng cấu hình riêng theo README.

Chưa kiểm thử hosting production, HTTPS thực, trình đọc màn hình chuyên dụng, tải đồng thời lớn hoặc MySQL 8 riêng biệt; MariaDB 10.4.32 là database đã chạy thực tế. Không tuyên bố đã kiểm thử toàn bộ trình duyệt/thiết bị. Nút chọn hồ sơ đã tải nhiều trang API thay vì giới hạn 50 bản ghi, nhưng chưa benchmark với hàng chục nghìn hồ sơ.

## Website nhiều trang theo prompt3 ngày 04/11/2026

Kế hoạch được lập ngày 03/11, hoàn thiện và kiểm chứng ngày 04/11. Các kết quả dưới đây thuộc source nhiều trang, không dùng kết quả prompt2 thay cho lần chạy mới.

### Môi trường và dữ liệu

Giữ PHP 8.0.30, MariaDB 10.4.32, Bootstrap 5.3.8, Bootstrap Icons 1.13.1, Inter và Apache/XAMPP hiện có. Không cài dependency. QA dùng database mới `nexa_crm_multipage_test` trên PHP8090 và database riêng cho Apache8091 với alias `/nexa-crm`; cấu hình `app_url` của Apache để trống nhằm kiểm tra tự nhận thư mục con thực tế. User SQL QA chỉ có quyền trên database QA.

Database có sẵn `nexa_crm` được chụp số dòng/hash dữ liệu và schema trước/sau; cả bốn bảng không đổi. Các fixture biên ngày chỉ thêm/sửa trong database QA, được xóa/phục hồi sau khi đối chiếu. Không reset database hoặc rate limit đang dùng cho preview8088.

### Suite HTTP đã chạy

| Môi trường | Integration | Protocol | Navigation | Kết quả |
| --- | ---: | ---: | ---: | --- |
| PHP dev-router8090 | 187 | 51 | 93 | 331 đạt, 0 thất bại |
| Apache8091 `/nexa-crm` | 187 | 51 | 93 | 331 đạt, 0 thất bại |

- **Integration:** hồi quy CRUD, search/filter/pagination, public submit/lookup, login/logout, privacy, validation, CSRF và rate limit; bổ sung reports 7/30/90 ngày, số ngày liên tục/zero-fill, tổng và trạng thái thực, account whitelist, đổi mật khẩu, token/cookie rotation, mật khẩu cũ bị từ chối và mật khẩu mới đăng nhập được. Hai client độc lập xác minh phiên khác nhận401 sau đổi mật khẩu; mật khẩu QA được phục hồi trước browser QA. Thử sai hiện tại, nhập lại mật khẩu cũ, mismatch, byte length, kiểu dữ liệu và giới hạn số lần xác minh.
- **Protocol:** method/JSON/body/header/cookie/path protections và endpoint mới. Response lỗi không lộ SQL, password hash hoặc thông tin kết nối.
- **Navigation:** sáu trang public có title/h1 riêng, dùng một header/footer, sáu main-nav link thực và một active state. Tất cả link/fragment/asset được đối chiếu; form chỉ nằm đúng trang. Bốn dịch vụ có detail và CTA prefill; giá trị service sai/array/rỗng ở detail trả404 hữu ích. Năm trang Admin bảo vệ login, active state và control đúng. Các đường dẫn hoạt động cả ở root và thư mục con Apache.

Kiểm tra bổ sung đã chạy độc lập:

- **6 kiểm tra SQL parity:** đối chiếu API với MySQL cho 7/30/90 ngày ở fixture có dữ liệu và kỳ hoàn toàn rỗng; có thời điểm 00:00/23:59, ngoài biên và tương lai. Đã phục hồi timestamp và xóa 18 dòng fixture riêng.
- **5 kiểm tra DB outage:** server8092 có kết nối database sai cổng riêng; không dừng MariaDB đang dùng. Trang login có phiên Admin trả503 với thông báo chung; API account/session trả JSON503 không lộ nội bộ; public guest vẫn mở được; session tiếp tục dùng được khi quay lại server khỏe. Server8092 đã dừng sau thử nghiệm.
- **6 kiểm tra preview chỉ đọc:** sáu trang trên8088 trả thành công sau cập nhật. Không gửi form hay mutation vào database có sẵn.

### Browser đã kiểm chứng

1. Bấm đủ sáu Navbar public trên375/768/1440px: tới đúng trang PHP, active state đúng, không có link menu giả và không tràn ngang. Trang chủ không có form; Liên hệ không nhân bản form hoặc hiện thông tin liên hệ chưa được cấu hình. Xem trang Giới thiệu, dịch vụ và chi tiết CRM; CTA mở tư vấn với CRM chọn sẵn. Bookmark `index.php#tu-van` chuyển đúng sang `tu-van.php`.
2. Trên375px, thử form trống rồi gửi yêu cầu QA thật qua Fetch trên `tu-van.php?service=crm`. Nhận mã ngẫu nhiên; chuyển sang trang tra cứu điền một lần đúng mã, điện thoại vẫn rỗng, URL không chứa điện thoại. Nhập điện thoại và tra cứu nhận trạng thái Mới.
3. Admin đăng nhập trên8090, Dashboard hiện yêu cầu QA mới. Tìm/xem/sửa nội dung và đổi trạng thái Hoàn thành trong module yêu cầu bằng Fetch. Sang trang tra cứu, nhận trạng thái Hoàn thành và chỉ thông tin tối thiểu. Sau xác nhận xóa đúng yêu cầu QA, danh sách tìm kiếm rỗng và tra cứu mã đó báo không tìm thấy. Fixture browser đã được dọn qua UI.
4. Trang Thống kê ở 375/768/1440px: biểu đồ, KPI, ngày, trạng thái và bảng đủ dữ liệu, không tràn ngang. Đổi7→90 ngày tạo đủ 7/90 dòng; bảng chi tiết đang mở vẫn mở sau Fetch; Back khôi phục 7 ngày. Tổng kỳ và số liệu toàn hệ thống được phân biệt bằng nhãn.
5. Trang Tài khoản ở 375/768/1440px hiển thị đúng Admin, input mobile 16px, không tràn ngang. Form trống đánh dấu cả ba trường và focus mật khẩu hiện tại. Chức năng đổi mật khẩu được kiểm thử thực tế qua HTTP API; browser chỉ kiểm tra form/validation, không thay mật khẩu người dùng qua UI. Nút Đăng xuất tài khoản chuyển đúng về login.
6. Cả năm sidebar module đã được bấm ở tablet; mobile chuyển module đúng sau offcanvas mở xong. Console public/Admin không ghi nhận lỗi hoặc cảnh báo JavaScript ở các luồng đã thử. Phát hiện và sửa dính chữ khi CSS ẩn `<br>`; đã thêm khoảng trắng vào markup public và kiểm tra lại.

Ảnh thực trong `docs/screenshots/multipage/`. Một số ảnh có13 yêu cầu hoặc Admin QA vì chụp trước khi dọn fixture; database có sẵn vẫn luôn6 khách hàng/12 yêu cầu. Các ảnh không phải dữ liệu giả trong JavaScript.

### Lint và RULE 001

PHP lint **42 tệp**, JavaScript syntax **7 tệp ứng dụng**, Python compile **3 suite** và `git diff --check` đều đạt. Đối chiếu hash **15 tệp bất biến** gồm AGENTS, schema, vendor và binary runtime: không đổi. `database.sql` không đổi; không có migration vì schema cũ đáp ứng chức năng mới. Config local không bị ghi đè; config example chỉ thêm tùy chọn liên hệ rỗng.

### Trạng thái cuối và giới hạn

Đã xóa tài khoản/SQL user/credentials và fixtures của cả hai môi trường QA, mỗi database QA còn một Admin seed vô hiệu hóa, 6 khách hàng và 12 yêu cầu. Dừng8090/8091/8092; **giữ preview8088 và MariaDB3308 chạy** theo yêu cầu người dùng. HTTP preview200, MySQL ping thành công; không có PHP runtime error trong log QA khỏe. Database có sẵn giữ1 Admin seed, 6 khách hàng, 12 yêu cầu, 3 bucket rate limit cùng hash/schema cũ.

Chưa kiểm thử hosting production, HTTPS thực, toàn bộ loại thiết bị/trình duyệt, screen reader chuyên dụng hoặc tải đồng thời lớn. Chưa chạy runtime MySQL 8 riêng biệt; MariaDB 10.4.32 là engine đã thực chạy. Password change và thu hồi phiên đã kiểm tra bằng các client HTTP độc lập; không tuyên bố đã đổi mật khẩu bằng browser UI. Những giới hạn hiệu năng quy mô lớn của native customer select từ phiên bản trước vẫn còn.
