CREATE DATABASE IF NOT EXISTS aba_mobile
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE aba_mobile;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100),
    category VARCHAR(100) DEFAULT 'Điện thoại',
    price DECIMAL(15, 0) NOT NULL DEFAULT 0,
    old_price DECIMAL(15, 0),
    image VARCHAR(255),
    description TEXT,
    stock INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products 
(name, brand, category, price, old_price, image, description, stock, status) 
VALUES
('iPhone 15 Pro Max 256GB', 'Apple', 'Điện thoại', 26990000, 29990000, 'iphone-15-pro-max.jpg', 'iPhone 15 Pro Max máy đẹp, hiệu năng mạnh, camera sắc nét.', 10, 1),
('iPhone 14 Pro 128GB', 'Apple', 'Điện thoại', 19990000, 22990000, 'iphone-14-pro.jpg', 'iPhone 14 Pro màn hình ProMotion, Dynamic Island, camera 48MP.', 8, 1),
('Samsung Galaxy S24 Ultra', 'Samsung', 'Điện thoại', 24990000, 27990000, 'samsung-s24-ultra.jpg', 'Galaxy S24 Ultra thiết kế cao cấp, bút S Pen, camera zoom mạnh.', 7, 1),
('Samsung Galaxy Z Fold5', 'Samsung', 'Điện thoại', 27990000, 32990000, 'samsung-z-fold5.jpg', 'Điện thoại gập cao cấp, màn hình lớn, phù hợp làm việc đa nhiệm.', 5, 1),
('Xiaomi 14T Pro', 'Xiaomi', 'Điện thoại', 14990000, 16990000, 'xiaomi-14t-pro.jpg', 'Xiaomi hiệu năng cao, sạc nhanh, camera đẹp trong tầm giá.', 12, 1),
('OPPO Reno11 5G', 'OPPO', 'Điện thoại', 8990000, 10990000, 'oppo-reno11-5g.jpg', 'OPPO Reno11 thiết kế đẹp, camera chân dung nổi bật.', 15, 1),
('Redmi Note 13 Pro', 'Xiaomi', 'Điện thoại', 6990000, 7990000, 'redmi-note-13-pro.jpg', 'Redmi Note 13 Pro pin tốt, màn hình đẹp, giá hợp lý.', 20, 1),
('Samsung Galaxy A55 5G', 'Samsung', 'Điện thoại', 8990000, 9990000, 'samsung-a55-5g.jpg', 'Galaxy A55 5G thiết kế bền, camera ổn định, pin tốt.', 18, 1);

CREATE TABLE IF NOT EXISTS repair_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL,
    icon VARCHAR(20) DEFAULT '🛠️',
    description TEXT NOT NULL,
    price_from INT NOT NULL DEFAULT 0,
    warranty VARCHAR(100),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO repair_services 
(service_name, icon, description, price_from, warranty, is_active) 
VALUES
('Thay màn hình, ép kính', '📱', 'Màn hình zin chính hãng, bảo hành cảm ứng. Ép kính lấy ngay sau 60 phút.', 350000, '6 tháng', 1),
('Thay pin chính hãng', '🔋', 'Pin dung lượng chuẩn, dung lượng cao. Tặng kèm ron chống nước.', 250000, '12 tháng', 1),
('Xử lý rơi nước', '💧', 'Vệ sinh, sấy khô bo mạch chuyên dụng. Hỗ trợ cứu dữ liệu máy sập nguồn do vào nước.', 200000, 'Tùy tình trạng', 1),
('Sửa lỗi phần cứng', '🛠️', 'Sửa mất nguồn, mất sóng, hỏng FaceID, hỏng camera, lỗi IC Audio trên bo mạch.', 300000, '3 - 6 tháng', 1),
('Thay camera', '📷', 'Thay camera trước, camera sau, xử lý lỗi rung camera hoặc camera không lấy nét.', 450000, '6 tháng', 1),
('Sửa loa, mic, chân sạc', '🔌', 'Sửa lỗi mất tiếng, rè loa, mic nhỏ, không nhận sạc hoặc sạc chập chờn.', 250000, '3 tháng', 1);

CREATE TABLE IF NOT EXISTS repair_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    device_name VARCHAR(150) NOT NULL,
    service_id INT NULL,
    issue_description TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'repairing', 'done', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_repair_booking_service
    FOREIGN KEY (service_id) REFERENCES repair_services(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tech_news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255),
    thumbnail VARCHAR(255),
    summary TEXT,
    content LONGTEXT,
    author VARCHAR(100),
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tech_news 
(title, slug, thumbnail, summary, content, author, status) 
VALUES
('5 mẹo kiểm tra điện thoại cũ trước khi mua', '5-meo-kiem-tra-dien-thoai-cu', 'news-phone-check.jpg', 'Những bước cần kiểm tra khi mua điện thoại cũ để tránh lỗi màn hình, pin và phần cứng.', 'Nội dung bài viết đang được cập nhật.', 'ABA Mobile', 1),
('Khi nào nên thay pin điện thoại?', 'khi-nao-nen-thay-pin-dien-thoai', 'news-battery.jpg', 'Các dấu hiệu cho thấy pin điện thoại đã chai và cần được thay mới.', 'Nội dung bài viết đang được cập nhật.', 'ABA Mobile', 1);