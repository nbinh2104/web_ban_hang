<?php
require_once('../config/auth.php');
$activePage = '';
?>
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

<?php include('components/header.php'); ?>

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

</body>
</html>