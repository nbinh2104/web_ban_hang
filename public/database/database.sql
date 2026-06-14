-- ===========================
-- DATABASE: phone_shop
-- ===========================
CREATE DATABASE IF NOT EXISTS phone_shop CHARACTER SET utf8mb4;
USE phone_shop;

-- Bảng SAN_PHAM
CREATE TABLE san_pham (
    id_sp INT AUTO_INCREMENT PRIMARY KEY,
    ten_sp VARCHAR(150) NOT NULL,
    hang_sx VARCHAR(50) NOT NULL,
    danh_muc VARCHAR(50) NOT NULL,      -- VD: Apple, Samsung, Xiaomi...
    gia DECIMAL(12,2) NOT NULL,
    mau_sac VARCHAR(50),
    dung_luong VARCHAR(20),
    so_luong_kho INT DEFAULT 0,
    hinh_anh VARCHAR(255),
    mo_ta TEXT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng DON_HANG
CREATE TABLE don_hang (
    id_dh INT AUTO_INCREMENT PRIMARY KEY,
    ten_khach VARCHAR(100) NOT NULL,
    ngay_dat DATETIME DEFAULT CURRENT_TIMESTAMP,
    tong_tien DECIMAL(12,2) NOT NULL,
    trang_thai ENUM('Chờ xác nhận','Đang giao','Hoàn thành','Đã hủy') DEFAULT 'Chờ xác nhận',
    dia_chi_nhan VARCHAR(255) NOT NULL,
    sdt_nhan VARCHAR(15) NOT NULL,
    phuong_thuc_tt VARCHAR(50)
);

-- Bảng CHI_TIET_DON_HANG
CREATE TABLE chi_tiet_don_hang (
    id_ctdh INT AUTO_INCREMENT PRIMARY KEY,
    id_dh INT NOT NULL,
    id_sp INT NOT NULL,
    so_luong INT NOT NULL,
    gia_ban DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_dh) REFERENCES don_hang(id_dh),
    FOREIGN KEY (id_sp) REFERENCES san_pham(id_sp)
);
-- ===========================
-- DỮ LIỆU MẪU
-- ===========================
INSERT INTO san_pham (ten_sp, hang_sx, danh_muc, gia, mau_sac, dung_luong, so_luong_kho, hinh_anh, mo_ta) VALUES
('iPhone 15 Pro Max', 'Apple', 'Apple', 32990000, 'Titan Đen', '256GB', 10, 'https://via.placeholder.com/300x300?text=iPhone+15+Pro+Max', 'Flagship mới nhất của Apple với chip A17 Pro.'),
('iPhone 14', 'Apple', 'Apple', 18990000, 'Xanh', '128GB', 15, 'https://via.placeholder.com/300x300?text=iPhone+14', 'iPhone 14 hiệu năng mạnh, camera đẹp.'),
('Samsung Galaxy S24 Ultra', 'Samsung', 'Samsung', 29990000, 'Đen', '256GB', 8, 'https://via.placeholder.com/300x300?text=Galaxy+S24+Ultra', 'Camera 200MP, bút S-Pen tích hợp.'),
('Samsung Galaxy A55', 'Samsung', 'Samsung', 8990000, 'Trắng', '128GB', 20, 'https://via.placeholder.com/300x300?text=Galaxy+A55', 'Tầm trung mạnh mẽ, pin trâu.'),
('Xiaomi 14T', 'Xiaomi', 'Xiaomi', 11990000, 'Xám', '256GB', 12, 'https://via.placeholder.com/300x300?text=Xiaomi+14T', 'Camera Leica, sạc nhanh 67W.'),
('Xiaomi Redmi Note 13', 'Xiaomi', 'Xiaomi', 4990000, 'Xanh', '128GB', 30, 'https://via.placeholder.com/300x300?text=Redmi+Note+13', 'Giá rẻ, màn hình AMOLED 120Hz.');