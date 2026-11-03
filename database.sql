-- Nexa CRM · MySQL 8.0 / MariaDB 10.4 · UTF-8
-- Import once into a fresh database. Existing tables are intentionally not dropped.
CREATE DATABASE IF NOT EXISTS nexa_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexa_crm;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    company VARCHAR(190) NOT NULL DEFAULT '',
    notes TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_name (full_name),
    INDEX idx_customer_phone (phone),
    INDEX idx_customer_created (created_at, id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customer_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(15) NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    service ENUM('website','crm','marketing','consulting') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new','processing','completed') NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_request_tracking (tracking_code),
    INDEX idx_request_customer (customer_id),
    INDEX idx_request_status_created (status, created_at, id),
    INDEX idx_request_created (created_at, id),
    INDEX idx_request_name (full_name),
    INDEX idx_request_phone (phone),
    INDEX idx_request_service (service),
    CONSTRAINT fk_request_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rate_limits (
    bucket_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    hits INT UNSIGNED NOT NULL DEFAULT 1,
    expires_at DATETIME NOT NULL,
    INDEX idx_rate_expiry (expires_at)
) ENGINE=InnoDB;

-- Demo-only disabled Admin. No reusable password is stored or published.
-- Activate safely by running scripts/create-admin.php with this email and a new password.
INSERT INTO admins (id, name, email, password_hash, is_active) VALUES
(1, 'Quản trị viên Nexa', 'admin@example.test', '!DISABLED_CREATE_WITH_CLI', 0);

-- Demo contacts use example.test reserved email addresses and fictional business names.
INSERT INTO customers (id, full_name, phone, email, company, notes, created_at) VALUES
(1, 'Nguyễn Minh Anh', '0901234501', 'minhanh@example.test', 'An Nhiên Studio', 'Quan tâm xây dựng website giới thiệu doanh nghiệp.', DATE_SUB(NOW(), INTERVAL 18 DAY)),
(2, 'Trần Hoàng Nam', '0912345602', 'hoangnam@example.test', 'Mộc Design', 'Ưu tiên hệ thống quản lý thông tin khách hàng.', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, 'Lê Thu Hà', '0933456703', 'thuha@example.test', 'Lá Xanh Market', 'Cần tư vấn chiến dịch marketing cho sản phẩm mới.', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(4, 'Phạm Quốc Bảo', '0974567804', 'quocbao@example.test', 'Bảo Minh Logistics', 'Đầu mối phụ trách chuyển đổi số.', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'Võ Khánh Linh', '0985678905', 'khanhlinh@example.test', 'Linh Flower', 'Liên hệ tư vấn trong giờ hành chính.', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(6, 'Đặng Đức Huy', '0866789006', 'duchuy@example.test', 'Nova Learning', 'Mong muốn tối ưu trải nghiệm khách hàng.', DATE_SUB(NOW(), INTERVAL 6 DAY));

-- Contact fields are snapshots. They are never automatically overwritten from customers.
INSERT INTO customer_requests (tracking_code, customer_id, full_name, phone, email, service, message, status, created_at, updated_at) VALUES
('NX-A31B920C4D01', 1, 'Nguyễn Minh Anh', '0901234501', 'minhanh@example.test', 'website', 'Tôi cần thiết kế website giới thiệu studio với danh mục dự án và biểu mẫu liên hệ. Mong nhận được tư vấn về thời gian và quy trình triển khai.', 'new', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('NX-A31B920C4D02', NULL, 'Bùi Ngọc Mai', '0387890107', 'ngocmai@example.test', 'marketing', 'Doanh nghiệp muốn triển khai chiến dịch quảng bá sản phẩm mới trên nền tảng số. Cần trao đổi để xác định mục tiêu và ngân sách phù hợp.', 'new', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('NX-A31B920C4D03', 2, 'Trần Hoàng Nam', '0912345602', 'hoangnam@example.test', 'crm', 'Cần một giải pháp quản lý khách hàng tập trung cho đội ngũ kinh doanh, có theo dõi lịch sử yêu cầu và phân loại trạng thái chăm sóc.', 'processing', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('NX-A31B920C4D04', 3, 'Lê Thu Hà', '0933456703', 'thuha@example.test', 'marketing', 'Mong được tư vấn kế hoạch nội dung và quảng cáo cho cửa hàng. Chúng tôi muốn xác định cách đo lường hiệu quả chiến dịch.', 'processing', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 8 HOUR)),
('NX-A31B920C4D05', 4, 'Phạm Quốc Bảo', '0974567804', 'quocbao@example.test', 'consulting', 'Cần khảo sát quy trình vận hành hiện tại và lập lộ trình chuyển đổi số theo từng giai đoạn cho công ty logistics.', 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('NX-A31B920C4D06', NULL, 'Ngô Thanh Tùng', '0358901208', '', 'website', 'Tôi muốn làm website bán hàng nhỏ, cần tư vấn bố cục nội dung và phương án quản lý thông tin liên hệ từ khách hàng.', 'new', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('NX-A31B920C4D07', 5, 'Võ Khánh Linh', '0985678905', 'khanhlinh@example.test', 'website', 'Cần nâng cấp giao diện website cửa hàng hoa, tối ưu sử dụng trên điện thoại và bổ sung form đặt lịch tư vấn.', 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('NX-A31B920C4D08', 6, 'Đặng Đức Huy', '0866789006', 'duchuy@example.test', 'crm', 'Trung tâm cần quản lý yêu cầu tư vấn khóa học và theo dõi tiến độ chăm sóc học viên. Mong trao đổi quy trình triển khai.', 'processing', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('NX-A31B920C4D09', NULL, 'Đỗ Phương Thảo', '0799012309', 'phuongthao@example.test', 'consulting', 'Chúng tôi muốn bắt đầu số hóa dữ liệu khách hàng và cần hướng dẫn lựa chọn công cụ phù hợp với quy mô doanh nghiệp.', 'new', DATE_SUB(NOW(), INTERVAL 9 HOUR), DATE_SUB(NOW(), INTERVAL 9 HOUR)),
('NX-A31B920C4D10', 1, 'Nguyễn Minh Anh', '0901234501', 'minhanh@example.test', 'marketing', 'Nhờ tư vấn chiến lược truyền thông sau khi ra mắt website để giới thiệu dịch vụ của studio đến đúng nhóm khách hàng.', 'completed', DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('NX-A31B920C4D11', 2, 'Trần Hoàng Nam', '0912345602', 'hoangnam@example.test', 'consulting', 'Đề nghị tư vấn tích hợp quy trình tư vấn với các hoạt động chăm sóc sau bán hàng đang vận hành.', 'completed', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
('NX-A31B920C4D12', NULL, 'Hồ Nhật Minh', '0700123410', '', 'website', 'Cần website giới thiệu công ty và danh sách dịch vụ, có chức năng để khách hàng gửi thông tin yêu cầu tư vấn.', 'new', DATE_SUB(NOW(), INTERVAL 15 HOUR), DATE_SUB(NOW(), INTERVAL 15 HOUR));
