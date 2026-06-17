<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/database.php");

mysqli_set_charset($conn, "utf8mb4");

/* ================================
   LẤY ID SẢN PHẨM
================================ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

/* ================================
   LẤY SẢN PHẨM TỪ DATABASE
================================ */
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Lỗi SQL: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$sp = mysqli_fetch_assoc($result);

if (!$sp) {
    die("Không tìm thấy sản phẩm này trên hệ thống!");
}

$product_name = htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8');
$product_image = htmlspecialchars($sp['image_url'], ENT_QUOTES, 'UTF-8');
$product_description = isset($sp['description']) 
    ? nl2br(htmlspecialchars($sp['description'], ENT_QUOTES, 'UTF-8')) 
    : 'Đang cập nhật mô tả sản phẩm...';

$new_price = isset($sp['new_price']) ? (int)$sp['new_price'] : 0;
$old_price = isset($sp['old_price']) ? (int)$sp['old_price'] : 0;
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= $product_name ?> - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>" />
    <link rel="stylesheet" href="../public/css/detail.css?v=<?= time(); ?>" />
</head>

<body>

<header class="modern-header">
    <div class="container header-inner">

        <div class="header-left">
            <a href="tel:1900xxxx" class="btn-phone-icon">📞</a>

            <a href="index.php" class="modern-logo">
                ABA Mobile<span class="dot">.</span>
            </a>
        </div>

        <nav class="header-center">
            <ul class="modern-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="dienthoai.php" class="active">Điện thoại</a></li>
                <li><a href="suachua.php">Sửa chữa</a></li>
                <li><a href="tincongnghe.php">Tin công nghệ</a></li>
            </ul>
        </nav>

        <div class="header-right">

            <form action="index.php" method="GET" class="search-form" autocomplete="off">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tìm điện thoại..." 
                    class="search-input"
                >

                <button type="submit" class="search-btn">🔍</button>

                <div id="search-results" class="search-results"></div>
            </form>

            <a href="cart.php" class="btn-cart-modern">
                🛒 Giỏ hàng
                <span id="cart-badge" class="cart-badge-hidden">0</span>
            </a>

            <a href="#" class="icon-action" title="Tài khoản">👤</a>

        </div>

    </div>
</header>

<main class="container detail-page">

    <div class="detail-breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>&raquo;</span>
        <a href="dienthoai.php">Điện thoại</a>
        <span>&raquo;</span>
        <strong><?= $product_name ?></strong>
    </div>

    <section class="product-top-section">

        <div class="product-gallery">

            <div class="main-image-box">
                <span class="badge-sale-detail">-15%</span>

                <img 
                    id="main-image" 
                    src="<?= $product_image ?>" 
                    alt="<?= $product_name ?>" 
                />
            </div>

            <div class="thumbnail-list">
                <div 
                    class="thumb-item active" 
                    onclick="changeImage('<?= $product_image ?>', this)"
                >
                    <img src="<?= $product_image ?>" alt="Ảnh chính" />
                </div>

                <div 
                    class="thumb-item" 
                    onclick="changeImage('https://placehold.co/600x600/f8fafc/00a8ff?text=Goc+Nghieng', this)"
                >
                    <img src="https://placehold.co/100x100/f8fafc/00a8ff?text=2" alt="Ảnh 2" />
                </div>

                <div 
                    class="thumb-item" 
                    onclick="changeImage('https://placehold.co/600x600/f8fafc/00a8ff?text=Mat+Lung', this)"
                >
                    <img src="https://placehold.co/100x100/f8fafc/00a8ff?text=3" alt="Ảnh 3" />
                </div>

                <div 
                    class="thumb-item" 
                    onclick="changeImage('https://placehold.co/600x600/f8fafc/00a8ff?text=Phu+Kien', this)"
                >
                    <img src="https://placehold.co/100x100/f8fafc/00a8ff?text=4" alt="Ảnh 4" />
                </div>
            </div>

        </div>

        <div class="product-info-detail">

            <h1 class="detail-title"><?= $product_name ?></h1>

            <div class="detail-rating">
                ⭐⭐⭐⭐⭐ 
                <span class="review-count">(1.2k đánh giá)</span>
            </div>

            <div class="detail-price-box">
                <span class="detail-current-price">
                    <?= number_format($new_price, 0, ',', '.'); ?> đ
                </span>

                <?php if ($old_price > 0): ?>
                    <span class="detail-old-price">
                        <?= number_format($old_price, 0, ',', '.'); ?> đ
                    </span>
                <?php endif; ?>
            </div>

            <div class="option-group">
                <h4>Chọn dung lượng:</h4>

                <div class="option-list">
                    <label class="option-btn active">
                        <input type="radio" name="storage" checked />
                        256GB
                    </label>

                    <label class="option-btn">
                        <input type="radio" name="storage" />
                        512GB
                    </label>

                    <label class="option-btn">
                        <input type="radio" name="storage" />
                        1TB
                    </label>
                </div>
            </div>

            <div class="option-group">
                <h4>Chọn màu sắc:</h4>

                <div class="option-list">
                    <label class="option-btn active">
                        <input type="radio" name="color" checked />
                        Titan Tự Nhiên
                    </label>

                    <label class="option-btn">
                        <input type="radio" name="color" />
                        Titan Xanh
                    </label>

                    <label class="option-btn">
                        <input type="radio" name="color" />
                        Titan Đen
                    </label>
                </div>
            </div>

            <div class="promo-box">
                <h4 class="promo-title">🎁 ƯU ĐÃI KHI MUA HÀNG</h4>

                <ul class="promo-list">
                    <li>✔️ Tặng củ sạc nhanh 20W chính hãng.</li>
                    <li>✔️ Giảm thêm 5% tối đa 500k khi thanh toán qua VNPAY.</li>
                    <li>✔️ Hỗ trợ thu cũ đổi mới trợ giá lên đến 2 triệu đồng.</li>
                </ul>
            </div>

            <div class="detail-actions">

                <button 
                    class="btn-buy-now"
                    onclick='buyNow(
                        <?= json_encode($sp["id"]) ?>,
                        <?= json_encode($sp["name"], JSON_UNESCAPED_UNICODE) ?>,
                        <?= json_encode($new_price) ?>,
                        <?= json_encode($sp["image_url"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                    )'
                >
                    MUA NGAY 
                    <span>Giao tận nơi hoặc nhận tại cửa hàng</span>
                </button>

                <button 
                    class="btn-add-to-cart-large"
                    onclick='addToCart(
                        <?= json_encode($sp["id"]) ?>,
                        <?= json_encode($sp["name"], JSON_UNESCAPED_UNICODE) ?>,
                        <?= json_encode($new_price) ?>,
                        <?= json_encode($sp["image_url"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                    )'
                >
                    🛒 THÊM VÀO GIỎ
                </button>

            </div>

        </div>

    </section>

    <section class="product-bottom-section">

        <div class="product-article">

            <h2 class="detail-section-heading">
                Đặc điểm nổi bật
            </h2>

            <p>
                <?= $product_description ?>
            </p>

            <img 
                src="https://placehold.co/800x400/f8fafc/00a8ff?text=Khuyen+Mai" 
                alt="Khuyến mãi" 
                class="article-image"
            />

        </div>

        <div class="product-specs">

            <h2 class="detail-section-heading">
                Thông số kỹ thuật
            </h2>

            <table class="spec-table">
                <tr>
                    <td>Màn hình:</td>
                    <td><?= isset($sp['man_hinh']) && $sp['man_hinh'] != '' ? htmlspecialchars($sp['man_hinh'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Hệ điều hành:</td>
                    <td><?= isset($sp['he_dieu_hanh']) && $sp['he_dieu_hanh'] != '' ? htmlspecialchars($sp['he_dieu_hanh'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Camera sau:</td>
                    <td><?= isset($sp['camera_sau']) && $sp['camera_sau'] != '' ? htmlspecialchars($sp['camera_sau'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Camera trước:</td>
                    <td><?= isset($sp['camera_truoc']) && $sp['camera_truoc'] != '' ? htmlspecialchars($sp['camera_truoc'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Chip CPU:</td>
                    <td><?= isset($sp['cpu']) && $sp['cpu'] != '' ? htmlspecialchars($sp['cpu'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>RAM:</td>
                    <td><?= isset($sp['ram']) && $sp['ram'] != '' ? htmlspecialchars($sp['ram'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Dung lượng:</td>
                    <td><?= isset($sp['dung_luong']) && $sp['dung_luong'] != '' ? htmlspecialchars($sp['dung_luong'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>

                <tr>
                    <td>Pin:</td>
                    <td><?= isset($sp['pin']) && $sp['pin'] != '' ? htmlspecialchars($sp['pin'], ENT_QUOTES, 'UTF-8') : 'Đang cập nhật...'; ?></td>
                </tr>
            </table>

        </div>

    </section>

</main>

<footer>
    <div class="container footer-content">

        <div class="footer-col">
            <h3>ABA Mobile</h3>
            <p>Hệ thống bán lẻ điện thoại di động chính hãng, uy tín hàng đầu với giá cả cạnh tranh.</p>
        </div>

        <div class="footer-col">
            <h3>Thông Tin Liên Hệ</h3>
            <p>📍 Địa chỉ: Hà Nội, Việt Nam</p>
            <p>📞 Điện thoại: 1900 xxxx</p>
            <p>✉️ Email: cskh@abamobile.com</p>
        </div>

        <div class="footer-col">
            <h3>Chính Sách</h3>
            <p><a href="#">Chính sách bảo hành</a></p>
            <p><a href="#">Chính sách đổi trả 1-1</a></p>
            <p><a href="#">Hướng dẫn mua trả góp</a></p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 ABA Mobile. All rights reserved.</p>
    </div>
</footer>

<div id="toast"></div>

<script src="../public/js/cart.js"></script>

<script>
function changeImage(imageUrl, thumbElement) {
    document.getElementById("main-image").src = imageUrl;

    let thumbs = document.getElementsByClassName("thumb-item");

    for (let i = 0; i < thumbs.length; i++) {
        thumbs[i].classList.remove("active");
    }

    thumbElement.classList.add("active");
}

function buyNow(id, ten, gia, hinh) {
    addToCart(id, ten, gia, hinh);
    window.location.href = "checkout.php";
}

document.querySelectorAll(".option-list .option-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
        const parent = this.closest(".option-list");
        const buttons = parent.querySelectorAll(".option-btn");

        buttons.forEach(function(item) {
            item.classList.remove("active");
        });

        this.classList.add("active");
    });
});

if (typeof updateCartBadge === "function") {
    updateCartBadge();
}
</script>

<script>
const searchInput = document.querySelector('.search-input');
const resultDiv = document.getElementById('search-results');

if (searchInput && resultDiv) {
    searchInput.addEventListener('input', function() {
        const q = this.value.trim();

        if (q.length > 0) {
            fetch('search_ajax.php?q=' + encodeURIComponent(q))
                .then(response => response.text())
                .then(data => {
                    resultDiv.innerHTML = data;
                    resultDiv.style.display = data.trim() ? 'block' : 'none';
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="search-empty">Lỗi tìm kiếm sản phẩm</div>';
                    resultDiv.style.display = 'block';
                });
        } else {
            resultDiv.innerHTML = '';
            resultDiv.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-form')) {
            resultDiv.style.display = 'none';
        }
    });
}
</script>

</body>
</html>