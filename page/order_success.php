<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    die("Mã đơn hàng không hợp lệ.");
}

$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Lỗi SQL: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Không tìm thấy đơn hàng.");
}

$sql_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = mysqli_prepare($conn, $sql_items);

if (!$stmt_items) {
    die("Lỗi SQL: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);

$items = mysqli_stmt_get_result($stmt_items);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../public/css/checkout.css?v=<?= time(); ?>">
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

        <!-- NÚT 3 SỌC CHO MOBILE -->
        <button type="button" class="mobile-menu-btn" onclick="toggleMobileMenu()">
            ☰
        </button>

        <nav class="header-center">
            <ul class="modern-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="dienthoai.php">Điện thoại</a></li>
                <li><a href="suachua.php">Sửa chữa</a></li>
                <li><a href="tincongnghe.php">Tin công nghệ</a></li>

                <!-- Chỉ hiện trong menu mobile -->
                <li class="mobile-menu-extra"><a href="cart.php">🛒 Giỏ hàng</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <form action="dienthoai.php" method="GET" class="search-form" autocomplete="off">
                <input
                    type="text"
                    name="q"
                    placeholder="Tìm sản phẩm, dịch vụ..."
                    class="search-input"
                >

                <button type="submit" class="search-btn">🔍</button>

                <div id="search-results" class="search-results"></div>
            </form>

            <a href="cart.php" class="btn-cart-modern">
                🛒 Giỏ hàng
                <span id="cart-badge" class="cart-badge-hidden">0</span>
            </a>
        </div>

    </div>
</header>

<main class="container success-page">

    <div class="success-box">
        <div class="success-icon">✅</div>

        <h1>Đặt hàng thành công!</h1>

        <p>
            Cảm ơn bạn đã mua hàng tại <strong>ABA Mobile</strong>.
            Đơn hàng của bạn đã được ghi nhận.
        </p>

        <div class="success-info">
            <p><strong>Mã đơn hàng:</strong> #<?= $order['id'] ?></p>
            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Thanh toán:</strong> <?= htmlspecialchars($order['payment_method'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Tổng tiền:</strong> <?= number_format($order['total_amount'], 0, ',', '.') ?> đ</p>
        </div>

        <h2>Sản phẩm đã đặt</h2>

        <div class="success-items">
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                <div class="success-item">
                    <img 
                        src="<?= htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8') ?>" 
                        alt="<?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div>
                        <h3><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>
                            <?= number_format($item['price'], 0, ',', '.') ?> đ 
                            x <?= $item['quantity'] ?>
                        </p>
                    </div>

                    <strong>
                        <?= number_format($item['subtotal'], 0, ',', '.') ?> đ
                    </strong>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="success-actions">
            <a href="index.php" class="btn-success-home">Về trang chủ</a>
            <a href="dienthoai.php" class="btn-success-buy">Tiếp tục mua hàng</a>
        </div>
    </div>

</main>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

<script>
/* =========================================
   XÓA GIỎ HÀNG SAU KHI ĐẶT HÀNG THÀNH CÔNG
========================================= */
localStorage.removeItem("gio_hang");

/* =========================================
   MỞ / ĐÓNG MENU MOBILE
========================================= */
function toggleMobileMenu() {
    const header = document.querySelector('.modern-header');

    if (header) {
        header.classList.toggle('mobile-open');
    }
}

/* =========================================
   TÌM KIẾM AJAX
========================================= */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }

    const searchForms = document.querySelectorAll('.search-form');

    searchForms.forEach(function (form) {
        const searchInput = form.querySelector('.search-input');
        const resultDiv = form.querySelector('.search-results');

        if (!searchInput || !resultDiv) return;

        searchInput.addEventListener('input', function () {
            const q = this.value.trim();

            if (q.length > 0) {
                fetch('search_ajax.php?q=' + encodeURIComponent(q))
                    .then(response => response.text())
                    .then(data => {
                        resultDiv.innerHTML = data;
                        resultDiv.style.display = data.trim() ? 'block' : 'none';
                    })
                    .catch(() => {
                        resultDiv.innerHTML = '<div class="search-empty">Lỗi tìm kiếm</div>';
                        resultDiv.style.display = 'block';
                    });
            } else {
                resultDiv.innerHTML = '';
                resultDiv.style.display = 'none';
            }
        });

        form.addEventListener('submit', function (e) {
            const firstResult = resultDiv.querySelector('.search-item');

            if (firstResult) {
                e.preventDefault();
                window.location.href = firstResult.getAttribute('href');
            }
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                resultDiv.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function (e) {
        const header = document.querySelector('.modern-header');

        if (!header) return;

        const clickInsideHeader = header.contains(e.target);

        if (!clickInsideHeader) {
            header.classList.remove('mobile-open');
        }
    });
});
</script>

</body>
</html>