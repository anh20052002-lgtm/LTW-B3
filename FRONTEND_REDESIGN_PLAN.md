# Kế hoạch Frontend Redesign — prompt4

## Ràng buộc và baseline

Đã đọc AGENTS.md (RULE 001/002) và kiểm kê toàn bộ first-party source qua FRONTEND_SCOPE_REPORT.md, gồm 42 PHP, 7 JavaScript, 2 CSS trước task và 3 bộ kiểm thử. Baseline riêng của task được lưu trong `.tools/frontend4-baseline` cùng manifest SHA256. Không ghi đè thay đổi cũ.

Công nghệ trước chỉnh sửa: HTML5/CSS3/JS ES6, Bootstrap 5.3.8, Icons 1.13.1, Inter local, Fetch, PHP 8.0.30, MySQL-compatible MariaDB 10.4.32, Apache/XAMPP 2.4.58. Không cài dependency hay đổi phiên bản. PHP built-in 8088 chỉ dùng preview; môi trường triển khai Apache/XAMPP giữ nguyên.

## Phân loại / file dự kiến

| Nhóm | Quyết định |
|---|---|
| API, auth, PDO, validation, config, database.sql, scripts, vendor, AGENTS | Giữ nguyên, kiểm hash trước/sau |
| 6 trang Public, includes/public-layout.php, includes/service-content.php | Nâng cấp markup/bố cục, giữ nội dung nghiệp vụ và form IDs |
| assets/css/public.css, assets/js/public.js | Mega menu, responsive, interaction; giữ nguyên AJAX payload/API |
| 5 trang Admin + login, includes/admin-layout.php | Nâng cấp shell, toolbars, bảng/form/modal, giữ IDs và quyền |
| assets/css/admin.css, JS Admin | Trình bày cùng dữ liệu thật; không sửa hành vi CRUD |
| assets/css/tokens.css | Thêm stylesheet dùng chung cho design tokens; không phải module mới |
| Trang mới/API mới/schema mới | Không cần |

Module giữ nguyên: Tổng quan; Yêu cầu tư vấn; Khách hàng; Thống kê và báo cáo; Tài khoản. Public giữ Trang chủ, Giới thiệu, Dịch vụ (4 dịch vụ tĩnh và chi tiết), Tư vấn, Tra cứu, Liên hệ.

## Triển khai

1. Nghiên cứu nguồn và kiểm kê — hoàn tất trước khi sửa UI, xem FRONTEND_REFERENCE_ANALYSIS.md.
2. Tokens chung: font Inter, xanh/navy, surface sáng, spacing4/8/12/16/24/32/48/64/96, focus, motion180ms và reduced-motion.
3. Public: split navigation link/disclosure, mega menu4dịch vụ, hỗ trợ gửi/tra cứu/quy trình; hero và minh họa tự tạo; servicecards, footer, pageheros, form/lookup/contact. Không tạo thông tin doanh nghiệp giả.
4. Admin: shellCRM thống nhất, danh sách yêu cầu là trọng tâm, toolbars và bảng, formgroups, chi tiết, KPI/dashboard, reports/account/login. Không tạo action chưa có API.
5. Tích hợp/QA: syntax, assets/routes, các breakpoint375/390/768/1024/1440/1920; keyboard/mobile dropdown, modal, offcanvas, loading/empty/error, console. Regression bằng database QA riêng; primary chỉ đọc.
6. Báo cáo FRONTEND_REDESIGN_REPORT.md và cập nhật DEMO.md theo kết quả thật; giới hạn ghi rõ.

Không bổ sung nhóm/trạng thái khách hàng, workflow mới, import/export, search toàn hệ thống hoặc API thống kê mới. Không migration. Các phần mở rộng trong prompt được loại theo RULE002.
