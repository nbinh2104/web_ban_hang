<?php
include('../config/database.php');
require_once('../config/auth.php');

mysqli_set_charset($conn, "utf8mb4");
$activePage = 'index';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

$whereSql = "";
$params = [];
$types = "";

if ($keyword !== '') {
    $whereSql = "WHERE p.name LIKE ?";
    $params[] = "%" . $keyword . "%";
    $types .= "s";
}

$sql = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        p.image_folder,
        p.description,
        v.id AS variant_id,
        v.storage,
        v.old_price,
        v.new_price,
        v.stock
    FROM products p
    JOIN product_variants v 
        ON v.id = (
            SELECT v2.id
            FROM product_variants v2
            WHERE v2.product_id = p.id
            ORDER BY 
                CASE
                    WHEN UPPER(v2.storage) LIKE '%TB%' 
                        THEN CAST(REPLACE(UPPER(v2.storage), 'TB', '') AS UNSIGNED) * 1024
                    WHEN UPPER(v2.storage) LIKE '%GB%' 
                        THEN CAST(REPLACE(UPPER(v2.storage), 'GB', '') AS UNSIGNED)
                    ELSE 999999
                END ASC
            LIMIT 1
        )
    $whereSql
    ORDER BY p.id DESC
";

if ($keyword === '') {
    $sql .= " LIMIT 8";
}

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">

    <link rel="stylesheet" href="../public/css/account_header.css?v=<?= time(); ?>">
</head>

<body>

<?php include('components/header.php'); ?>

<main class="container">

    <section class="home-banner">
        <a href="dienthoai.php" class="home-banner-link">
            <img 
                src="../public/images/Banner/Banner.jpg" 
                alt="Banner khuyến mãi ABA Mobile"
            >
        </a>
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
            KẾT QUẢ TÌM KIẾM: "<?= h($keyword) ?>"
        <?php else: ?>
            SẢN PHẨM NỔI BẬT
        <?php endif; ?>
    </h2>

    <div class="product-grid">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <div class="product-card">

                    <a href="detail.php?id=<?= (int)$row['id'] ?>" class="card-link">
                        <span class="badge badge-sale">-15%</span>

                        <img 
                            src="<?= h($row['image_url']) ?>" 
                            alt="<?= h($row['name']) ?>" 
                        />

                        <h3 class="product-title">
                            <?= h($row['name']) ?>
                        </h3>

                        <div class="price-group">
                            <?php if (!empty($row['old_price'])): ?>
                                <p class="old-price">
                                    <?= number_format((int)$row['old_price'], 0, ',', '.') ?> đ
                                </p>
                            <?php endif; ?>

                            <p class="product-price">
                                Từ <?= number_format((int)$row['new_price'], 0, ',', '.') ?> đ
                            </p>
                        </div>
                    </a>

                    <button 
                        class="btn-add-cart"
                        onclick='addToCartVariant(
                            <?= json_encode((int)$row["id"]) ?>,
                            <?= json_encode((int)$row["variant_id"]) ?>,
                            <?= json_encode($row["name"], JSON_UNESCAPED_UNICODE) ?>,
                            <?= json_encode($row["storage"], JSON_UNESCAPED_UNICODE) ?>,
                            <?= json_encode((int)$row["new_price"]) ?>,
                            <?= json_encode($row["image_url"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                        )'
                    >
                        🛒 Thêm vào giỏ
                    </button>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <?php if ($keyword != ''): ?>
                <p class="no-products-message">
                    Không tìm thấy sản phẩm phù hợp với từ khóa:
                    <strong><?= h($keyword) ?></strong>
                </p>
            <?php else: ?>
                <p class="no-products-message">
                    Hệ thống đang cập nhật sản phẩm mới...
                </p>
            <?php endif; ?>
        <?php endif; ?>

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

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

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

<script>

function toggleAccountMenu() {
    const accountMenu = document.querySelector('.account-menu');

    if (accountMenu) {
        accountMenu.classList.toggle('active');
    }
}

document.addEventListener('click', function(e) {
    const header = document.querySelector('.modern-header');
    const accountMenu = document.querySelector('.account-menu');

    if (header && !header.contains(e.target)) {
        header.classList.remove('mobile-open');
    }

    if (accountMenu && !accountMenu.contains(e.target)) {
        accountMenu.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
</script>

</body>
</html>
