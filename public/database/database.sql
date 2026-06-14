CREATE DATABASE IF NOT EXISTS phone_shop CHARACTER SET utf8mb4;
USE phone_shop;

-- ===========================
-- BẢNG SẢN PHẨM
-- ===========================
CREATE TABLE san_pham (
id_sp INT AUTO_INCREMENT PRIMARY KEY,
ten_sp VARCHAR(150) NOT NULL,
hang_sx VARCHAR(50) NOT NULL,
danh_muc VARCHAR(50) NOT NULL,

```
gia DECIMAL(12,2) NOT NULL,

ram VARCHAR(20),
dung_luong VARCHAR(20),
man_hinh VARCHAR(100),
camera VARCHAR(255),
pin VARCHAR(50),
he_dieu_hanh VARCHAR(50),
mau_sac VARCHAR(50),

so_luong_kho INT DEFAULT 0,

hinh_anh VARCHAR(255),
mo_ta TEXT,

ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP
```

);

-- ===========================
-- ĐƠN HÀNG
-- ===========================
CREATE TABLE don_hang (
id_dh INT AUTO_INCREMENT PRIMARY KEY,
ten_khach VARCHAR(100) NOT NULL,
email VARCHAR(100),
sdt_nhan VARCHAR(20) NOT NULL,
dia_chi_nhan VARCHAR(255) NOT NULL,

```
tong_tien DECIMAL(12,2) NOT NULL,

phuong_thuc_tt VARCHAR(50),

trang_thai ENUM(
    'Chờ xác nhận',
    'Đang giao',
    'Hoàn thành',
    'Đã hủy'
) DEFAULT 'Chờ xác nhận',

ngay_dat DATETIME DEFAULT CURRENT_TIMESTAMP
```

);

-- ===========================
-- CHI TIẾT ĐƠN HÀNG
-- ===========================
CREATE TABLE chi_tiet_don_hang (
id_ctdh INT AUTO_INCREMENT PRIMARY KEY,

```
id_dh INT NOT NULL,
id_sp INT NOT NULL,

so_luong INT NOT NULL,
gia_ban DECIMAL(12,2) NOT NULL,

FOREIGN KEY (id_dh)
    REFERENCES don_hang(id_dh),

FOREIGN KEY (id_sp)
    REFERENCES san_pham(id_sp)
```

);

-- ===========================
-- DỮ LIỆU MẪU
-- ===========================

INSERT INTO san_pham
(
ten_sp,
hang_sx,
danh_muc,
gia,
ram,
dung_luong,
man_hinh,
camera,
pin,
he_dieu_hanh,
mau_sac,
so_luong_kho,
hinh_anh,
mo_ta
)
VALUES

(
'iPhone 15 Pro Max',
'Apple',
'Apple',
32990000,
'8GB',
'256GB',
'6.7 inch OLED',
'48MP + 12MP + 12MP',
'4441mAh',
'iOS 18',
'Titan Đen',
10,
'https://via.placeholder.com/400x400?text=iPhone+15+Pro+Max',
'Flagship cao cấp của Apple với chip A17 Pro.'
),

(
'Samsung Galaxy S24 Ultra',
'Samsung',
'Samsung',
29990000,
'12GB',
'256GB',
'6.8 inch Dynamic AMOLED',
'200MP + 50MP + 12MP + 10MP',
'5000mAh',
'Android 15',
'Đen',
8,
'https://via.placeholder.com/400x400?text=Galaxy+S24+Ultra',
'Camera 200MP và bút S-Pen.'
),

(
'Xiaomi 14T',
'Xiaomi',
'Xiaomi',
11990000,
'12GB',
'256GB',
'6.67 inch AMOLED',
'50MP Leica',
'5000mAh',
'HyperOS',
'Xám',
15,
'https://via.placeholder.com/400x400?text=Xiaomi+14T',
'Hiệu năng mạnh, camera Leica.'
);
