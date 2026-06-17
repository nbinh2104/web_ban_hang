<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

/* ================================
   XỬ LÝ TÌM KIẾM TRANG CHỦ
================================ */
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($keyword != '') {
    $search = "%" . $keyword . "%";

    $sql = "SELECT * FROM products 
            WHERE name LIKE ? 
            ORDER BY id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
    $result = mysqli_query($conn, $sql);
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
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
                <li><a href="index.php" class="active">Trang chủ</a></li>
                <li><a href="dienthoai.php">Điện thoại</a></li>
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
                    value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
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

<main class="container">

    <section class="hero">
        <div class="hero-content">
            <div class="brand">Galaxy AI<span>✦</span></div>

            <div class="buttons">
                <a href="#" class="learn-more">Tìm hiểu thêm</a>
                <a href="#" class="buy-now">Mua ngay</a>
            </div>
        </div>

        <div class="hero-image">
            <img src="../public/images/Fold7.webp" alt="Galaxy AI" />
        </div>
    </section>

    <section class="service-stats">
        <div class="service-top">
            <div class="service-top-item">🛡️ <span>Bảo hành 12 tháng</span></div>
            <div class="service-top-item">🚚 <span>Giao hàng miễn phí</span></div>
            <div class="service-top-item">💳 <span>Trả góp 0%</span></div>
        </div>

        <div class="stats-grid">
            <div class="stats-item">
                <h3>500+</h3>
                <p>Sản phẩm</p>
            </div>

            <div class="stats-item">
                <h3>200K+</h3>
                <p>Khách hàng</p>
            </div>

            <div class="stats-item">
                <h3>4.9★</h3>
                <p>Đánh giá</p>
            </div>

            <div class="stats-item">
                <h3>63</h3>
                <p>Tỉnh thành</p>
            </div>
        </div>
    </section>

    <h2 class="section-title home-section-title">
        <?php if ($keyword != ''): ?>
            KẾT QUẢ TÌM KIẾM: "<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
        <?php else: ?>
            SẢN PHẨM NỔI BẬT
        <?php endif; ?>
    </h2>

    <div class="product-grid">

        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>

            <div class="product-card">

                <a href="detail.php?id=<?= $row['id'] ?>" class="card-link">
                    <span class="badge badge-sale">-15%</span>

                    <img 
                        src="<?= htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8') ?>" 
                        alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>" 
                    />

                    <h3 class="product-title">
                        <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <div class="price-group">
                        <p class="old-price">
                            <?= number_format($row['old_price'], 0, ',', '.') ?> đ
                        </p>

                        <p class="product-price">
                            <?= number_format($row['new_price'], 0, ',', '.') ?> đ
                        </p>
                    </div>
                </a>

                <button 
                    class="btn-add-cart"
                    onclick='addToCart(
                        <?= json_encode($row["id"]) ?>,
                        <?= json_encode($row["name"], JSON_UNESCAPED_UNICODE) ?>,
                        <?= json_encode($row["new_price"]) ?>,
                        <?= json_encode($row["image_url"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                    )'
                >
                    🛒 Thêm vào giỏ
                </button>

            </div>

        <?php
            }
        } else {
            if ($keyword != '') {
                echo "<p class='no-products-message'>Không tìm thấy sản phẩm phù hợp với từ khóa: <strong>" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "</strong></p>";
            } else {
                echo "<p class='no-products-message'>Hệ thống đang cập nhật sản phẩm mới...</p>";
            }
        }
        ?>

    </div>

</main>

<div class="floating-contact">
    <a href="tel:1900xxxx" class="contact-item">
        <div class="icon-circle">📞</div>
        <span>Gọi ngay</span>
    </a>

    <a href="https://zalo.me/xxxx" target="_blank" class="contact-item">
        <div class="icon-circle">💬</div>
        <span>Zalo OA</span>
    </a>

    <a href="https://m.me/xxxx" target="_blank" class="contact-item">
        <div class="icon-circle">⚡</div>
        <span>Messenger</span>
    </a>
</div>

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