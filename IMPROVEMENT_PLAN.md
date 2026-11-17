# Kế hoạch nâng cấp Nexa CRM

Ngày lập: 03/11/2026. Phạm vi: thực hiện `prompt2.docx` trên dự án hiện có, nâng cấp giao diện hai vai trò và hoàn thiện chức năng đang có.

## Công nghệ trước chỉnh sửa

HTML5, CSS3, JavaScript ES6, Bootstrap 5, Bootstrap Icons, font Inter; PHP thuần 8.0+ với PDO, MySQL 8/MariaDB 10.4+, PHP Session và AJAX qua Fetch API. Assets thư viện được lưu cục bộ. Môi trường QA sẵn có: PHP 8.0.30 và MariaDB 10.4.32 trong XAMPP. Không có bước cài npm/Composer hoặc thêm framework. Python chỉ là công cụ kiểm thử, không phải runtime website.

## Khảo sát và thành phần giữ nguyên

- Website đã có hai vai trò, public submit/tra cứu bằng mã và số điện thoại, login/logout Admin, Dashboard lấy dữ liệu MySQL, CRUD yêu cầu và khách hàng.
- Module chính giữ là quản lý yêu cầu tư vấn: Fetch JSON, modal tạo/sửa/chi tiết/xóa, đổi trạng thái, tìm kiếm, lọc, phân trang.
- Giữ cấu trúc database `admins`, `customers`, `customer_requests`, `rate_limits`, các quan hệ, dữ liệu và cấu hình riêng. Các chức năng yêu cầu trong prompt mới có thể triển khai trên schema hiện tại nên không cần migration.
- Giữ session, password hash, CSRF, prepared statements, giới hạn gửi/tra cứu/đăng nhập, whitelist dữ liệu tra cứu công khai, assets offline và thương hiệu Nexa.
- Public và Admin đã có typography/spacing/responsive tốt. Cải tiến có mục tiêu, không xây lại toàn bộ dự án.

## Tham khảo đã kiểm tra

- [Haravan](https://www.haravan.com/): đã mở trang và quan sát giao diện trong trình duyệt; tham khảo thứ bậc hero, CTA rõ và cách nhóm giải pháp.
- [Getfly](https://getfly.vn/) và [hướng dẫn quản lý khách hàng](https://wiki.getfly.vn/portal/post/2208): đã đọc nội dung hiện tại; tài liệu chỉ rõ toolbar tìm kiếm, bộ lọc, thêm mới và danh sách. Áp dụng nguyên tắc tổ chức, không sao chép thương hiệu, số liệu hoặc tính năng chưa có.
- Không tìm thấy UI/UX Pro Max hoặc Frontend Design Skill trong danh mục skill của phiên và thư mục skill người dùng. Tái sử dụng hướng dẫn thiết kế Sites cho checkout cục bộ, không chuyển hosting hay công nghệ.

## Hạng mục thực hiện

1. **Public**: nâng hero bằng minh họa quy trình phù hợp nghiệp vụ; tổ chức bốn dịch vụ thành card dễ quét, icon/phạm vi/CTA rõ. Giữ anchors và toàn bộ form/API. Đồng bộ bộ đếm Unicode, phản hồi sao chép mã, focus và trạng thái điều hướng; không thêm số liệu kinh doanh hay đánh giá giả.
2. **Admin**: cải thiện toolbar có nhãn, bộ lọc khách hàng có/chưa có yêu cầu liên kết và thao tác xóa lọc. Lưu search/filter/sort/page/page size trên URL, khôi phục khi reload và Back/Forward. Thao tác dòng có nhãn; detail liên kết giữa khách hàng và yêu cầu; nhóm trường modal dễ hiểu. Sửa race khi tải lựa chọn khách hàng hoặc lưu trạng thái; dùng chỉ số MySQL sẵn có, không biểu đồ giả.
3. **Backend**: thêm query tùy chọn `has_requests=with|without` cho danh sách khách hàng bằng `EXISTS`/`NOT EXISTS`, whitelist, kết hợp tìm kiếm/phân trang; thêm `customer_id` nguyên dương cho danh sách yêu cầu để đi từ hồ sơ tới đúng yêu cầu liên kết. Giữ hợp đồng và hành vi mặc định của API cũ. Rà quyền, CSRF, validation, SQLi, escape và lỗi PHP; chỉ sửa lỗi thực tế phát hiện được.
4. **QA**: mở rộng test hồi quy cho filter, CSRF sau login, liên kết khách hàng khi update, LIKE literal, dữ liệu sai và tra cứu tối thiểu. Mọi ghi/xóa chạy trên database mới dành riêng cho đợt nâng cấp; không dùng database `nexa_crm` đang có. Rà browser hai vai trò, Fetch không reload, reload giữ dữ liệu, modal, search/filter/pagination, loading/empty/error và responsive 375/768/1440.
5. **Tài liệu**: cập nhật README, TESTING, DEMO và tạo IMPROVEMENT_REPORT với kết quả thực tế, danh sách file, giới hạn và xác nhận stack trước/sau.

## File dự kiến thay đổi

- `index.php`, `assets/css/public.css`, `assets/js/public.js`.
- `includes/admin-layout.php`, các trang `admin/*.php`, `assets/css/admin.css`, `assets/js/records.js`, `assets/js/dashboard.js`, `assets/js/login.js` khi cần.
- `api/admin/customers/list.php`, `api/admin/requests/list.php`; `assets/js/common.js` hoặc helper PHP chỉ khi phát hiện lỗi liên quan.
- `tests/integration.py`, `tests/protocol.py`, `README.md`, `TESTING.md`, `DEMO.md`, `IMPROVEMENT_PLAN.md`, `IMPROVEMENT_REPORT.md`, ảnh kiểm chứng trong `docs/screenshots/improvement/`.
- Không sửa thư viện vendor, cấu hình nhạy cảm hoặc `database.sql` nếu schema vẫn đáp ứng.

## Rủi ro và cách kiểm chứng

- Thay markup có thể làm lệch selector JS: giữ ID/contract, kiểm tra luồng thật trước bàn giao.
- URL có input sai hoặc response đến muộn có thể ghi đè danh sách/modal: whitelist, AbortController/sequence guard và thử chuyển bộ lọc nhanh.
- Nhãn thao tác có thể làm bảng rộng: cho cuộn trong vùng bảng, kiểm tra cả ba kích thước và trạng thái rỗng/lỗi.
- Test mutation có nguy cơ tác động dữ liệu đang dùng: database thử nghiệm riêng, credentials riêng, không reset cấu hình/dữ liệu chính. Bản preview 8088 đã được người dùng tắt và giữ tắt; chỉ chạy QA tạm trên cổng riêng rồi dừng khi xong.
- Không nhận kết quả cũ là kết quả mới; báo đúng các suite/browser scenario đã chạy và những phần chưa kiểm chứng.
