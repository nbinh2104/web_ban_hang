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

        <nav class="header-center">
            <ul class="modern-menu">
                <li><a href="index.php">Trang chủ</a></li>
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

<main class="container cart-page">

    <section class="cart-heading">
        <div>
            <h1>Giỏ <span>hàng</span></h1>
            <p id="so-mon">0 sản phẩm</p>
        </div>

        <a href="dienthoai.php" class="btn-continue-top">
            ← Tiếp tục mua sắm
        </a>
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

<script src="../public/js/cart.js"></script>

<script>
    hienThiGio();
    updateCartBadge();
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