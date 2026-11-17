# Báo cáo nâng cấp Nexa CRM

Ngày hoàn thành kiểm tra chức năng: 03/11/2026. Phạm vi: nâng cấp website quản lý khách hàng và yêu cầu tư vấn theo `prompt2.docx`, tái sử dụng dự án đang hoạt động.

## Kết quả

Đã nâng cấp giao diện khách hàng và Admin, bổ sung bộ lọc quan hệ khách hàng/yêu cầu và giữ trạng thái danh sách qua URL. Hai vai trò, CRUD, AJAX, tra cứu công khai, session và CSRF tiếp tục dùng PHP/MySQL thật. Không tạo dự án mới, không thêm framework, không đổi schema hoặc ghi đè cấu hình/dữ liệu hiện có.

## Công nghệ trước và sau

| Thành phần | Trước | Sau |
| --- | --- | --- |
| Giao diện | HTML5, CSS3, JavaScript ES6 | Giữ nguyên |
| CSS và icon | Bootstrap 5.3.8, Bootstrap Icons 1.13.1 | Giữ nguyên các tệp vendor |
| Font | Inter sans-serif cục bộ | Giữ nguyên; đã kiểm tra computed font trong browser |
| Backend | PHP thuần 8.0+, PDO | Giữ nguyên; QA bằng PHP 8.0.30 |
| Database | MySQL 8/MariaDB 10.4+, InnoDB, utf8mb4 | Giữ nguyên; QA bằng MariaDB 10.4.32 |
| Xác thực | PHP Session, password_hash/password_verify, CSRF | Giữ nguyên và kiểm thử hồi quy |
| AJAX | Fetch API, JSON PHP endpoints | Giữ nguyên |
| Máy chủ | Apache/XAMPP; PHP dev-router cho local QA | Giữ nguyên, không cài service hệ thống |
| Dependency sản phẩm | Assets vendor có sẵn, không npm/Composer | Không thêm dependency hoặc thư viện |

Node chỉ chạy kiểm tra cú pháp JavaScript; Python chỉ chạy suite HTTP. Cả hai không tham gia backend website. `database.sql`, vendor assets và cấu hình sản phẩm đã được đối chiếu không đổi bằng Git.

## Vấn đề ban đầu

- Public đã có typography, CTA và form hoạt động tốt, nhưng hero chủ yếu là bộ chọn dịch vụ; danh sách dịch vụ dạng hàng khó quét hơn yêu cầu card trong prompt mới.
- Danh sách khách hàng có tìm kiếm/sắp xếp nhưng thiếu bộ lọc thực theo quan hệ yêu cầu. Từ hồ sơ chưa có đường đi trực tiếp tới đúng yêu cầu liên kết.
- Tìm kiếm, bộ lọc, phân trang chưa được lưu đầy đủ khi reload; các nút thao tác dòng chỉ có icon.
- Select hồ sơ liên kết chỉ tải50 khách hàng. Các response chậm có thể ghi vào modal mới mở; quick-status chưa chặn thao tác trùng đầy đủ.
- HTML bộ lọc trạng thái có thẻ option sai. Bộ đếm nội dung public dùng UTF16 khác cách đếm Unicode ở validation.
- Browser QA phát hiện sidebar nằm cùng lớp với backdrop, khiến menu bị che, và toast sau quick-status che nút sửa trên mobile.

## File tạo và thay đổi

| File | Nội dung |
| --- | --- |
| `index.php` | Hero minh họa nghiệp vụ, card dịch vụ, phản hồi sao chép mã |
| `assets/css/public.css` | Layout hero/card, responsive, hover/focus |
| `assets/js/public.js` | Counter Unicode, select change, sao chép/focus, nav hiện hành |
| `includes/admin-layout.php` | Thành phần nhận diện Workspace trong sidebar |
| `admin/index.php` | Chỉ số có liên kết, khu vực tiến độ thực |
| `admin/requests.php` | Toolbar có nhãn, xóa lọc, nhóm trường modal, option hợp lệ |
| `admin/customers.php` | Bộ lọc quan hệ, toolbar và nhóm thông tin hồ sơ |
| `assets/css/admin.css` | Sidebar navy, bảng/thao tác/modal, stacking, thông báo trạng thái |
| `assets/js/dashboard.js` | Tính tỷ lệ hoàn thành từ stats MySQL có sẵn |
| `assets/js/records.js` | Filter/URL/history, liên kết detail, tải lựa chọn nhiều trang, chống response muộn/gửi trùng |
| `api/admin/customers/list.php` | Filter `has_requests` kết hợp tìm kiếm và phân trang |
| `api/admin/requests/list.php` | Filter `customer_id` với validation/prepared statement |
| `tests/integration.py` | Hồi quy chức năng, filter mới, validation và guard database QA |
| `tests/protocol.py` | HTTP/CSRF/path bảo vệ và guard test mutation |
| `README.md` | Cách chạy, filter mới, stack và lệnh test hiện tại |
| `TESTING.md` | Kết quả thực tế, môi trường, kịch bản và giới hạn |
| `DEMO.md` | Demo hai vai trò, URL history và quan hệ hồ sơ/yêu cầu |
| `IMPROVEMENT_PLAN.md` | Kế hoạch đã lập trước khi triển khai |
| `IMPROVEMENT_REPORT.md` | Báo cáo này |
| `docs/screenshots/improvement/` | Ảnh browser thực ở desktop/tablet/mobile, dịch vụ, tra cứu, danh sách liên kết và trạng thái rỗng |

## Thay đổi giao diện và trải nghiệm

Public dùng minh họa HTML/CSS/SVG cho luồng nhu cầu → Nexa → giải pháp, ghi rõ đây là minh họa. Bốn dịch vụ có card, icon, phạm vi tư vấn và CTA có nhãn; chọn dịch vụ vẫn điền form thật. Giữ header/anchors, form public và lookup. Counter khớp Unicode; sao chép mã có phản hồi inline và giữ focus.

Admin có sidebar navy và cùng font/màu nhận diện với public. Dashboard hiển thị đủ năm chỉ số thực, liên kết vào danh sách tương ứng, phân bổ dịch vụ và tỷ lệ hoàn thành từ số hoàn thành/tổng yêu cầu. Không thêm số tăng trưởng, đồ thị hoặc chứng thực kinh doanh giả.

Toolbar có label, summary và xóa lọc; URL giữ search/filter/sort/page/page size. Back/Forward khôi phục lựa chọn; mutation giữ bộ lọc. Sau tạo, chi tiết bản ghi đã lưu tự mở để nhìn thấy kết quả ngay cả khi bản ghi không khớp bộ lọc hiện tại. Bảng có nhãn Xem/Sửa/Xóa; modal nhóm thông tin và có trạng thái lỗi rõ.

Đã tham khảo trực tiếp [Haravan](https://www.haravan.com/) về hero/CTA/nhóm giải pháp và [tài liệu Getfly](https://wiki.getfly.vn/portal/post/2208) về toolbar tìm kiếm/lọc/thêm/danh sách. Giữ thương hiệu và nội dung Nexa, không sao chép nhận diện hoặc số liệu của các bên đó.

## Chức năng bổ sung và API

- `GET api/admin/customers/list.php?has_requests=with`: hồ sơ có ít nhất một yêu cầu với `customer_id` trỏ tới hồ sơ. `without` trả hồ sơ chưa có yêu cầu; rỗng/bỏ tham số giữ hành vi cũ. Query dùng EXISTS/NOT EXISTS trên FK sẵn có.
- `GET api/admin/requests/list.php?customer_id=12`: lọc yêu cầu của hồ sơ12, kết hợp với status/service/search/pagination. ID sai định dạng hoặc array trả422; ID nguyên dương chưa có dữ liệu trả danh sách rỗng.
- Hai endpoint vẫn yêu cầu Admin và giữ response `{success,message,data,errors}`, `data.items` và `data.pagination`. Không đổi các mutation hoặc public API.
- Detail khách hàng dẫn tới danh sách yêu cầu đã lọc; detail yêu cầu dẫn tới hồ sơ liên kết. Không tự gộp theo email/số điện thoại chưa xác minh.

Không cần migration: các bảng, FK và index hiện tại đã hỗ trợ những truy vấn trên. **Không import lại `database.sql` cho bản nâng cấp trên database có sẵn.**

## Lỗi được sửa

- Option trạng thái Mới được tạo đúng HTML.
- Counter public dùng Unicode code point, nhất quán với validation backend.
- Select hồ sơ tải các trang100 bản ghi thay vì dừng ở50; có AbortController/generation guard để response cũ không ghi vào modal khác.
- Các yêu cầu list/detail được hủy hoặc bỏ qua khi hết hiệu lực. Giới hạn thao tác submit trùng, chặn đóng/sửa modal trong lúc mutation chưa trả kết quả, không tự retry mutation.
- Trạng thái không đổi không cho bấm cập nhật trên UI. Sau lưu trạng thái, thông báo thành công nằm trong modal, tránh toast che nút Chỉnh sửa.
- Sidebar dùng lớp1045 cao hơn backdrop1040; modal vẫn nằm phía trên sidebar.
- Lịch sử URL tạo pushState cho thay đổi của người dùng, replaceState cho làm mới/chuẩn hóa, tránh tạo lịch sử trùng và hủy debounce cũ khi Back/Forward.

## Kiểm thử thực tế

- PHP lint **30 tệp**, JavaScript syntax **5 tệp ứng dụng**, Python compile hai suite và `git diff --check`: đạt.
- PHP dev-router 8090: **114 integration +44 protocol đạt**. Apache 8091 với alias `/nexa-crm`: **114 integration +44 protocol đạt**. Mỗi môi trường có 158 kiểm tra đạt, 0 thất bại. Protocol cuối trên PHP chạy sau browser và thử khôi phục kết nối; bộ kiểm tra có bảo vệ đường dẫn hai tài liệu mới.
- Browser public: gửi/tra cứu/copy mã, validation, chọn dịch vụ, Unicode, menu; browser Admin: tạo/xem/sửa/xóa cả hai module, cập nhật trạng thái, reload, filter quan hệ, link hai chiều, tìm kiếm rỗng, trang2, Back/Forward và logout.
- Browser XSS: payload HTML/script được hiển thị dưới dạng text, DOM không tạo thẻ img/script từ dữ liệu lưu. API suite đã kiểm tra SQLi thử, CSRF, quyền Admin, privacy, validation và đường dẫn riêng.
- Mô phỏng ngắt kết nối bằng dừng riêng PHP QA: UI hiện lỗi, bật lại nút thử lại, giữ trang2; khởi động cùng môi trường rồi làm mới phục hồi đúng dữ liệu.
- Đã quan sát public và Dashboard ở375/768/1440px. Không tràn ngang; modal mobile340px, input16px; bảng cuộn riêng; empty/error mobile hiển thị trọn vùng bảng. Không ghi nhận lỗi JavaScript trong console của luồng đã thử.

Mọi mutation chỉ chạy trên database thử nghiệm mới. Các fixture browser đã được xóa qua UI, mỗi database QA trở về 6 khách hàng/12 yêu cầu mẫu và một Admin seed vô hiệu hóa. Database có sẵn `nexa_crm` được đối chiếu hash/số dòng của cả bốn bảng không đổi. Đã xóa Admin, SQL user và credentials QA tạm; không ghi nhận lỗi PHP runtime trong log. PHP8090, Apache8091 và MariaDB3308 đã dừng; kiểm tra cuối không còn listener trên 8088/8090/8091/3308.

## Giới hạn còn lại

Chưa kiểm thử production hosting, HTTPS thực, mọi trình duyệt/thiết bị, screen reader chuyên dụng, tải đồng thời lớn hoặc runtime MySQL8 riêng biệt. Đã thử ngắt kết nối, chưa mô phỏng đủ mọi timeout15 giây. Lựa chọn hồ sơ dùng native select tải nhiều trang API, phù hợp phạm vi đồ án nhưng chưa benchmark hàng chục nghìn hồ sơ. Các số trong ảnh chụp khi còn fixture QA có thể khác Dashboard cuối6khách/12yêu cầu.

Không có chức năng được vẽ thêm như một hệ thống CRM hoàn chỉnh nhưng chưa có backend. Schema và luồng nghiệp vụ hiện tại đáp ứng phạm vi đồ án; yêu cầu bổ sung khác cần đặc tả riêng.

## Chạy bằng XAMPP và trình bày

1. Khởi động Apache và MySQL trong XAMPP; đặt dự án tại `C:\xampp\htdocs\nexa-crm` hoặc alias/DocumentRoot tương ứng.
2. Cài mới: import `database.sql` vào database đồ án trống. Nâng cấp bản hiện có: giữ database, không import lại hoặc reset.
3. Sao chép `config/config.example.php` thành `config/config.local.php` nếu chưa có, nhập đúng thông tin MySQL riêng; không ghi đè cấu hình đã dùng.
4. Tạo Admin riêng bằng `C:\xampp\php\php.exe scripts/create-admin.php`. Tài khoản seed vẫn vô hiệu hóa, không có mật khẩu chung.
5. Mở `http://localhost/nexa-crm/` cho khách và `/admin/login.php` cho Admin. Dùng `DEMO.md` để trình bày hai vai trò và Network Fetch/XHR.
6. Kiểm thử tự động dùng database `_test` riêng, cờ `--allow-write`, cấu hình/credentials theo README; không chạy mutation suite trên website chứa dữ liệu cần giữ.

Ảnh giao diện cuối và chi tiết kết quả có trong thư mục screenshots và TESTING. Các server QA chỉ dùng tạm trên loopback, không thay cho môi trường XAMPP của người nhận.
