CREATE DATABASE IF NOT EXISTS phone_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phone_shop;

-- ===========================
-- BẢNG SẢN PHẨM (duy nhất)
-- ===========================
CREATE TABLE san_pham (
    id_sp       INT AUTO_INCREMENT PRIMARY KEY,
    ten_sp      VARCHAR(255)    NOT NULL,
    gia         DECIMAL(12,2)   NOT NULL,
    gia_cu      DECIMAL(12,2)   DEFAULT NULL,       -- giá gạch (giá cũ)
    phan_tram_giam TINYINT      DEFAULT NULL,        -- ví dụ: 15 (tương ứng -15% trên badge)
    -- Thông số kỹ thuật (khớp với bảng spec trên detail.html)
    ram         VARCHAR(20)     DEFAULT NULL,        -- '8 GB'
    dung_luong  VARCHAR(20)     DEFAULT NULL,        -- '256 GB'
    man_hinh    VARCHAR(100)    DEFAULT NULL,        -- 'OLED, 6.7", Super Retina XDR'
    camera      VARCHAR(255)    DEFAULT NULL,        -- 'Chính 48 MP & Phụ 12 MP, 12 MP'
    camera_truoc VARCHAR(50)    DEFAULT NULL,        -- '12 MP'
    chip        VARCHAR(100)    DEFAULT NULL,        -- 'Apple A17 Pro 6 nhân'
    pin         VARCHAR(50)     DEFAULT NULL,        -- '4422 mAh, 20 W'
    cong_ket_noi VARCHAR(50)    DEFAULT NULL,        -- 'Type-C'
    he_dieu_hanh VARCHAR(50)    DEFAULT NULL,        -- 'iOS 17'
    -- Màu sắc & dung lượng có thể chọn (lưu dạng JSON hoặc bảng phụ)
    mau_sac_list VARCHAR(255)   DEFAULT NULL,        -- 'Titan Tự Nhiên,Titan Xanh,Titan Đen'
    dung_luong_list VARCHAR(100) DEFAULT NULL,       -- '256GB,512GB,1TB'
    -- Hình ảnh & mô tả
    hinh_anh    VARCHAR(255)    DEFAULT NULL,        -- đường dẫn ảnh chính
    mo_ta       TEXT            DEFAULT NULL,
    -- Kho
    so_luong_kho INT            DEFAULT 0,
    ngay_tao    DATETIME        DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- BẢNG ĐƠN HÀNG
-- ===========================
CREATE TABLE don_hang (
    id_dh           INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(100)    NOT NULL,
    so_dien_thoai   VARCHAR(15)     NOT NULL,
    dia_chi         TEXT            NOT NULL,
    tong_tien       DECIMAL(12,2)   NOT NULL,
    phuong_thuc_tt  VARCHAR(50)     DEFAULT 'COD',   -- 'COD', 'VNPAY', 'Chuyển khoản'
    trang_thai      ENUM(
                        'Chờ xác nhận',
                        'Đang giao',
                        'Hoàn thành',
                        'Đã hủy'
                    )               DEFAULT 'Chờ xác nhận',
    ngay_dat        DATETIME        DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- BẢNG CHI TIẾT ĐƠN HÀNG
-- ===========================
CREATE TABLE chi_tiet_don_hang (
    id_ctdh     INT AUTO_INCREMENT PRIMARY KEY,
    id_dh       INT             NOT NULL,
    id_sp       INT             NOT NULL,
    so_luong    INT             NOT NULL,
    gia_ban     DECIMAL(12,2)   NOT NULL,    -- giá tại thời điểm mua (không đổi dù sp thay giá)
    mau_sac     VARCHAR(50)     DEFAULT NULL, -- màu người dùng đã chọn
    dung_luong  VARCHAR(20)     DEFAULT NULL, -- dung lượng người dùng đã chọn
    FOREIGN KEY (id_dh) REFERENCES don_hang(id_dh) ON DELETE CASCADE,
    FOREIGN KEY (id_sp) REFERENCES san_pham(id_sp) ON DELETE RESTRICT
);

-- ===========================
-- DỮ LIỆU MẪU (khớp với detail.html)
-- ===========================
INSERT INTO san_pham (
    ten_sp, gia, gia_cu, phan_tram_giam,
    ram, dung_luong, man_hinh, camera, camera_truoc, chip, pin, cong_ket_noi, he_dieu_hanh,
    mau_sac_list, dung_luong_list,
    hinh_anh, mo_ta, so_luong_kho
) VALUES
(
    'iPhone 15 Pro Max 256GB Chính hãng VN/A',
    29990000, 34990000, 15,
    '8 GB', '256 GB', 'OLED, 6.7", Super Retina XDR',
    'Chính 48 MP & Phụ 12 MP, 12 MP', '12 MP',
    'Apple A17 Pro 6 nhân', '4422 mAh, 20 W', 'Type-C', 'iOS 17',
    'Titan Tự Nhiên,Titan Xanh,Titan Đen',
    '256GB,512GB,1TB',
    'images/iphone15promax.jpg',
    'iPhone 15 Pro Max là mẫu flagship cao cấp nhất của Apple. Khung Titan chuẩn hàng không vũ trụ, chip A17 Pro và camera tele 5x hoàn toàn mới.',
    50
),
(
    'Samsung Galaxy S24 Ultra 256GB',
    31490000, 34490000, 9,
    '12 GB', '256 GB', 'Dynamic AMOLED, 6.8"',
    'Chính 200 MP & Phụ 12 MP, 10 MP, 50 MP', '12 MP',
    'Snapdragon 8 Gen 3', '5000 mAh, 45 W', 'Type-C', 'Android 14',
    'Titanium Black,Titanium Gray,Titanium Violet',
    '256GB,512GB',
    'images/s24ultra.jpg',
    'Samsung Galaxy S24 Ultra với bút S Pen tích hợp và camera 200MP đỉnh cao.',
    30
),
(
    'iPhone 14 128GB Chính hãng VN/A',
    18590000, 22590000, 18,
    '6 GB', '128 GB', 'Super Retina XDR OLED, 6.1"',
    'Chính 12 MP & Phụ 12 MP', '12 MP',
    'Apple A15 Bionic 6 nhân', '3279 mAh, 20 W', 'Lightning', 'iOS 17',
    'Đen,Trắng,Đỏ,Xanh Dương,Vàng,Tím',
    '128GB,256GB',
    'images/iphone14.jpg',
    'iPhone 14 với hiệu năng mạnh mẽ từ chip A15 Bionic và hệ thống camera được cải tiến.',
    80
);

 (
    'Xiaomi 15 Ultra 512GB Chính hãng VN/A',
    31990000, 36990000, 12,
    '16 GB', '512 GB',
    'AMOLED, 6.73", 120Hz, LTPO',
    'Chính 50 MP & Phụ 50 MP, 50 MP, 200 MP (Leica)',
    '32 MP',
    'Snapdragon 8 Elite 8 nhân',
    '5500 mAh, 90W có dây / 80W không dây',
    'Type-C (USB 3.2)',
    'HyperOS 2 (Android 15)',
    'Đen,Trắng',
    '512GB,1TB',
    'images/xiaomi15ultra.jpg',
    'Xiaomi 15 Ultra hợp tác Leica với hệ thống 4 camera đỉnh cao, chip Snapdragon 8 Elite và pin 5500 mAh sạc nhanh 90W.',
    40
);
