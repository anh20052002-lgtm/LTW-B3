\# AGENTS.md — QUY TẮC BẮT BUỘC CHO CODEX



\## RULE 001 — TECH STACK IMMUTABILITY



\*\*MỨC ĐỘ: CRITICAL — NON-NEGOTIABLE\*\*



TUYỆT ĐỐI KHÔNG ĐƯỢC TỰ Ý THAY ĐỔI, THAY THẾ HOẶC CHUYỂN ĐỔI CÔNG NGHỆ CỦA DỰ ÁN.



Quy tắc này áp dụng cho mọi nhiệm vụ: tạo trang mới, sửa lỗi, tối ưu, cải thiện UI/UX, bổ sung chức năng, tái cấu trúc code và bảo mật.



\### 1. Công nghệ được phép sử dụng



\- Frontend: HTML5, CSS3, JavaScript.

\- UI Framework: Bootstrap hiện có.

\- Backend: PHP thuần.

\- Database: MySQL.

\- AJAX: Fetch API hoặc phương pháp AJAX đã có trong dự án.

\- Font: Inter (Sans-serif).

\- Môi trường: Apache/XAMPP.



Phải kiểm tra phiên bản thực tế trước khi sửa. Không tự ý nâng cấp hoặc hạ cấp phiên bản công nghệ, thư viện hiện có.



\### 2. Các hành động bị nghiêm cấm



\- KHÔNG chuyển PHP sang Laravel, Symfony, Node.js, Python hoặc framework backend khác.

\- KHÔNG chuyển HTML/JavaScript sang React, Vue, Angular, Next.js hoặc SPA framework.

\- KHÔNG thay Bootstrap bằng Tailwind CSS hoặc framework CSS khác.

\- KHÔNG thay MySQL bằng PostgreSQL, MongoDB, Firebase hoặc Supabase.

\- KHÔNG thay AJAX hiện có bằng một kiến trúc giao tiếp khác.

\- KHÔNG tự ý cài framework hoặc dependency mới.

\- KHÔNG thay đổi công nghệ chỉ vì giải pháp mới hiện đại, phổ biến hoặc dễ triển khai hơn.

\- KHÔNG viết lại toàn bộ dự án nếu có thể cải tiến mã nguồn hiện tại.

\- KHÔNG tự ý nâng cấp phiên bản PHP, Bootstrap hoặc MySQL.

\- KHÔNG tự ý chỉnh sửa hoặc xóa quy tắc này.



\### 3. Nguyên tắc thực hiện



Khi nhận nhiệm vụ:



1\. Kiểm tra công nghệ và cấu trúc hiện có.

2\. Tìm phương án sử dụng chính công nghệ đó.

3\. Ưu tiên tái sử dụng code đang hoạt động.

4\. Chỉ thay đổi những file cần thiết.

5\. Giữ tương thích với XAMPP và MySQL.

6\. Không làm hỏng CRUD, AJAX hoặc các chức năng hiện có.

7\. Sau khi sửa, rà soát các file thay đổi để xác nhận công nghệ không bị thay thế.



\### 4. Khi gặp yêu cầu xung đột



Nếu một nhiệm vụ không thể hoàn thành bằng công nghệ hiện tại:



\- DỪNG phần công việc gây xung đột.

\- Giải thích rõ vấn đề.

\- Đề xuất giải pháp sử dụng công nghệ hiện có.

\- Không tự ý thực hiện chuyển đổi.

\- Chỉ thay đổi công nghệ khi người dùng cho phép rõ ràng.



\### 5. Quy tắc ưu tiên



Các prompt yêu cầu thiết kế giao diện theo Haravan, Getfly CRM, UI/UX Pro Max hoặc bất kỳ website nào khác chỉ được phép tham khảo thiết kế và trải nghiệm người dùng.



Không được coi chúng là lý do để thay đổi tech stack.



\*\*NGUYÊN TẮC CUỐI CÙNG:\*\*



"Improve the existing system without changing its technology stack."



Cải thiện dự án bằng công nghệ hiện có, không thay thế công nghệ để thực hiện cải tiến.

# RULE 002 — MODULE & FUNCTIONAL SCOPE IMMUTABILITY

**PRIORITY: CRITICAL — MANDATORY**

TUYỆT ĐỐI KHÔNG ĐƯỢC TỰ Ý THAY ĐỔI, XÓA, THAY THẾ HOẶC MỞ RỘNG CÁC MODULE CHỨC NĂNG HIỆN CÓ.

## Quy tắc bắt buộc

1. Trước khi chỉnh sửa, phải phân tích toàn bộ module hiện tại của dự án.
2. Giữ nguyên tên, mục đích và nghiệp vụ của từng module.
3. Không tự ý tạo module mới.
4. Không xóa hoặc gộp các module hiện có.
5. Không tự ý thay đổi quy trình nghiệp vụ.
6. Không thay đổi quyền hạn của Khách hàng và Admin.
7. Không thay đổi chức năng CRUD và AJAX hiện tại, ngoại trừ sửa lỗi hoặc cải thiện cách hiển thị mà vẫn giữ nguyên hành vi.
8. Không thay đổi database schema, API contract hoặc cấu trúc dữ liệu khi chỉ được yêu cầu nâng cấp giao diện.
9. Được phép thiết kế lại giao diện, bố cục, màu sắc, typography, điều hướng và responsive.
10. Được phép tạo trang giao diện riêng cho chức năng đã tồn tại, nhưng không được tự ý bổ sung chức năng nghiệp vụ mới.
11. Nếu prompt yêu cầu chức năng hoặc module chưa tồn tại, phải báo cáo rõ và không tự ý triển khai.
12. Không được tự ý chỉnh sửa hoặc loại bỏ RULE 001 và RULE 002 trong AGENTS.md.

## Khi xảy ra xung đột

Nếu yêu cầu thiết kế Frontend xung đột với cấu trúc module hiện tại:

- Ưu tiên bảo toàn module và chức năng hiện có.
- Chỉ thay đổi cách trình bày giao diện.
- Không tự ý thay đổi nghiệp vụ để phù hợp với giao diện tham khảo.
- Báo cáo những thay đổi cần được người dùng phê duyệt.

**FINAL RULE: Redesign the interface, preserve the existing modules, business logic, database and technology stack.**


