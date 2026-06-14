CREATE DATABASE IF NOT EXISTS aba_mobile
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE aba_mobile;

DROP TABLE IF EXISTS sanpham;

CREATE TABLE sanpham (
id INT AUTO_INCREMENT PRIMARY KEY,
ten_sp VARCHAR(255) NOT NULL,
gia BIGINT NOT NULL,
gia_cu BIGINT NOT NULL,
hinh_anh VARCHAR(255) NOT NULL,
mo_ta TEXT,
man_hinh VARCHAR(100),
he_dieu_hanh VARCHAR(50),
camera_sau VARCHAR(100),
camera_truoc VARCHAR(100),
cpu VARCHAR(100),
ram VARCHAR(50),
dung_luong VARCHAR(50),
pin VARCHAR(50),
cong_ket_noi VARCHAR(50)
);

INSERT INTO sanpham
(
ten_sp,
gia,
gia_cu,
hinh_anh,
mo_ta,
man_hinh,
he_dieu_hanh,
camera_sau,
camera_truoc,
cpu,
ram,
dung_luong,
pin,
cong_ket_noi
)

VALUES

(
'iPhone 14 128GB Chính Hãng',
18590000,
22590000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+14',
'iPhone 14 với chip A15 Bionic mạnh mẽ và thiết kế cao cấp.',
'OLED 6.1 inch',
'iOS 16',
'12MP + 12MP',
'12MP',
'Apple A15 Bionic',
'6GB',
'128GB',
'3279mAh',
'Lightning'
),

(
'iPhone 14 Pro Max 256GB',
27990000,
31990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+14+Pro+Max',
'iPhone 14 Pro Max với Dynamic Island và camera 48MP.',
'OLED 6.7 inch',
'iOS 16',
'48MP + 12MP + 12MP',
'12MP',
'Apple A16 Bionic',
'6GB',
'256GB',
'4323mAh',
'Lightning'
),

(
'iPhone 15 128GB',
19990000,
22990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+15',
'iPhone 15 trang bị cổng USB-C và camera 48MP.',
'OLED 6.1 inch',
'iOS 17',
'48MP + 12MP',
'12MP',
'Apple A16 Bionic',
'6GB',
'128GB',
'3349mAh',
'USB-C'
),

(
'iPhone 15 Pro Max 256GB Chính Hãng VN/A',
29990000,
34990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+15+Pro+Max',
'iPhone 15 Pro Max là mẫu flagship cao cấp nhất của Apple với khung Titan và chip A17 Pro.',
'OLED 6.7 inch Super Retina XDR',
'iOS 17',
'48MP + 12MP + 12MP',
'12MP',
'Apple A17 Pro',
'8GB',
'256GB',
'4422mAh',
'USB-C'
),

(
'iPhone 15 Pro 256GB',
27990000,
30990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+15+Pro',
'iPhone 15 Pro sử dụng khung Titan và chip Apple A17 Pro.',
'OLED 6.1 inch',
'iOS 17',
'48MP + 12MP + 12MP',
'12MP',
'Apple A17 Pro',
'8GB',
'256GB',
'3274mAh',
'USB-C'
),

(
'iPhone 16 128GB',
24990000,
27990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+16',
'iPhone 16 thế hệ mới với hiệu năng nâng cấp.',
'OLED 6.1 inch',
'iOS 18',
'48MP + 12MP',
'12MP',
'Apple A18',
'8GB',
'128GB',
'3561mAh',
'USB-C'
),

(
'iPhone 16 Pro 256GB',
32990000,
35990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+16+Pro',
'iPhone 16 Pro với Apple Intelligence và A18 Pro.',
'OLED 6.3 inch',
'iOS 18',
'48MP + 48MP + 12MP',
'12MP',
'Apple A18 Pro',
'8GB',
'256GB',
'3582mAh',
'USB-C'
),

(
'iPhone 16 Pro Max 256GB',
36990000,
39990000,
'https://placehold.co/600x600/1a2235/e2e8f0?text=iPhone+16+Pro+Max',
'iPhone 16 Pro Max là mẫu cao cấp nhất của dòng iPhone 16.',
'OLED 6.9 inch',
'iOS 18',
'48MP + 48MP + 12MP',
'12MP',
'Apple A18 Pro',
'8GB',
'256GB',
'4685mAh',
'USB-C'
);
