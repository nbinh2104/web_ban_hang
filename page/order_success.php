<?php
include('../config/database.php');
require_once('../config/auth.php');

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

<?php include('components/header.php'); ?>

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
</script>

</body>
</html>