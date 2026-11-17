# Phân tích tham khảo Frontend — prompt4

Ngày thực hiện: 04/11/2026. Phạm vi: chỉ giao diện theo RULE 001/002.

## Nguồn thực sự kiểm tra

- https://www.haravan.com/ — đọc nội dung và mở trình duyệt, quan sát/chụp viewport desktop 1440×900 và mobile 390×844. Header chia nhóm giải pháp/hỗ trợ; CTA rõ, hero lớn, nền sáng, nhiều khoảng trắng. Một số media không phát trong môi trường kiểm tra. Không sử dụng số liệu, logo, hình minh họa hoặc nội dung độc quyền.
- https://getfly.vn/ — đọc và quan sát website marketing ở desktop 1440×900 và mobile 390×844, có ghi nhận screenshot. Đây là website marketing, không coi là màn hình quản trị CRM.
- https://wiki.getfly.vn/portal/post/2208 — đọc hướng dẫn chính thức, kiểm tra ảnh màn hình CRM được nhúng. Quan sát trực tiếp ảnh danh sách khách hàng `2.png` (1600×752): sidebar hẹp bên trái, tiêu đề và tìm kiếm/bộ lọc phía trên, nút thêm mới, hàng trạng thái, bảng dữ liệu và phân trang. Đây là ảnh tài liệu PC; không đăng nhập CRM thương mại và không tuyên bố đã thử responsive của CRM Getfly.

Ảnh ghi nhận ở `docs/screenshots/redesign/references/`, bao gồm tài liệu Wiki ở mobile. Mobile của tài liệu không chứng minh giao diện CRM chạy ở mobile. Đây là bằng chứng nghiên cứu, không phải tài sản của website Nexa.

## Áp dụng cho Nexa

- Public: menu Dịch vụ có trang danh mục trực tiếp và nút mở mega menu riêng; nhóm Tư vấn dẫn tới gửi/tra cứu/quy trình hiện có. Mỗi liên kết có đích thật, điều hướng bằng bàn phím, mobile dùng Bootstrap collapse/dropdown.
- Hero có phân cấp chữ rõ, hai CTA, minh họa tự xây bằng HTML/CSS/SVG; nhịp nền và bố cục khác nhau giữa các section. Dịch vụ có thông tin phạm vi và đường dẫn chi tiết.
- Admin: giữ năm module, đặt thao tác và bộ lọc gần bảng, tên khách và mã yêu cầu dễ quét, vùng chi tiết chia nhóm, trạng thái chữ+màu. Sidebar/topbar gọn, các KPI dùng dữ liệu API thật.
- Cùng font Inter và bảng màu xanh #2563EB/navy #0F172A; tokens khoảng cách, bo góc, focus, chuyển động 180ms. Giảm chuyển động khi người dùng yêu cầu.

## Không phù hợp / không triển khai

Không sao chép hệ sinh thái bán hàng, đơn hàng, kho, marketing automation, phân quyền, nhóm/nguồn/quan hệ khách hàng, avatar upload, notifications, global search, saved filters, import/export, tỷ lệ chuyển đổi hoặc thành tích thương mại từ nguồn tham khảo. Chúng không thuộc nghiệp vụ đang có. Không thêm lịch sử xử lý/ghi chú yêu cầu vì không có API/schema. Ghi chú khách hàng đã có được giữ.

## Khoảng thiếu của giao diện trước chỉnh sửa

Đã có sáu trang Public và năm module Admin, CRUD/AJAX và states thật. Header Public còn là dãy liên kết phẳng, chưa có mega menu; footer chưa phân nhóm dịch vụ rõ. Minh họa và cấu trúc hero còn khá đồng đều; các trang phụ thiếu nhịp thị giác. Admin có nền tảng tốt nhưng hierarchy tiêu đề/toolbars, form grouping và vùng danh sách cần hoàn thiện nhất quán. Cần kiểm tra lại cả sáu breakpoint, menu bàn phím, modal/offcanvas và luồng dữ liệu sau redesign.
