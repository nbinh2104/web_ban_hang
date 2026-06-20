<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../public/css/cart.css?v=<?= time(); ?>">
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
                <li class="mobile-menu-extra"><a href="cart.php" class="active">🛒 Giỏ hàng</a></li>
            </ul>
        </nav>

        <div class="header-right">

            <form action="dienthoai.php" method="GET" class="search-form" autocomplete="off">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tìm kiếm" 
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

<main class="container cart-page">

    <section class="cart-heading">
        <div>
            <h1>Giỏ <span>hàng</span></h1>
            <p id="so-mon">0 sản phẩm</p>
        </div>
    </section>

    <section class="cart-table-wrap">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="danh-sach-gio"></tbody>
        </table>
    </section>

    <section class="cart-footer">
        <div class="summary-info">
            <span class="summary-label">Tổng thanh toán</span>
            <span class="summary-total" id="tong-tien">0 đ</span>
        </div>

        <div class="footer-actions">
            <a href="dienthoai.php" class="btn-back">← Tiếp tục mua</a>
            <a href="checkout.php" class="btn-checkout">Thanh toán →</a>
        </div>
    </section>

</main>

<div id="toast"></div>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

<script>
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
   GIỎ HÀNG + TÌM KIẾM AJAX
========================================= */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof hienThiGio === 'function') {
        hienThiGio();
    }

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