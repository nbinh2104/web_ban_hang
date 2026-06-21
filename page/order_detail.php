<?php
include('../config/database.php');
require_once('../config/auth.php');

mysqli_set_charset($conn, "utf8mb4");

require_login();

$user_id = current_user_id();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die('ID đơn hàng không hợp lệ.');
}

function money_vn($number) {
    return number_format((int)$number, 0, ',', '.') . ' đ';
}

function status_text($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';
        case 'confirmed':
            return 'Đã xác nhận';
        case 'shipping':
            return 'Đang giao hàng';
        case 'completed':
            return 'Hoàn thành';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Chờ xử lý';
    }
}

$sql_order = "
    SELECT *
    FROM orders
    WHERE id = ?
      AND user_id = ?
    LIMIT 1
";

$stmt_order = mysqli_prepare($conn, $sql_order);
mysqli_stmt_bind_param($stmt_order, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt_order);

$order_result = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die('Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.');
}

$sql_items = "
    SELECT *
    FROM order_items
    WHERE order_id = ?
";

$stmt_items = mysqli_prepare($conn, $sql_items);
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);

$items_result = mysqli_stmt_get_result($stmt_items);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= (int)$order['id'] ?> - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <?php 
$activePage = '';
include('components/header.php'); 
?>
    <main class="container" style="padding:40px 16px;">
        <div style="margin-bottom:20px;">
            <a href="my_orders.php" style="color:#00a8ff;font-weight:800;text-decoration:none;">
                ← Quay lại đơn hàng
            </a>
        </div>

        <h1 class="section-title">
            Chi tiết đơn hàng #<?= (int)$order['id'] ?>
        </h1>

        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,0.06);margin-bottom:22px;">
            <h3 style="margin-bottom:14px;">Thông tin nhận hàng</h3>

            <p><strong>Trạng thái:</strong> <?= h(status_text($order['status'] ?? 'pending')) ?></p>
            <p><strong>Người nhận:</strong> <?= h($order['customer_name'] ?? '') ?></p>
            <p><strong>Số điện thoại:</strong> <?= h($order['phone'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= h($order['email'] ?? '') ?></p>
            <p><strong>Địa chỉ:</strong> <?= h($order['address'] ?? '') ?></p>
            <p><strong>Thanh toán:</strong> <?= h($order['payment_method'] ?? '') ?></p>
            <p><strong>Ghi chú:</strong> <?= h(($order['note'] ?? '') !== '' ? $order['note'] : 'Không có') ?></p>
        </div>

        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
            <h3 style="margin-bottom:14px;">Sản phẩm đã đặt</h3>

            <div style="display:flex;flex-direction:column;gap:14px;">
                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                    <div style="display:grid;grid-template-columns:70px 1fr auto;gap:14px;align-items:center;border-bottom:1px solid #e5e7eb;padding-bottom:14px;">
                        <div>
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?= h($item['product_image']) ?>" alt="" style="width:70px;height:70px;object-fit:contain;border-radius:10px;border:1px solid #e5e7eb;">
                            <?php else: ?>
                                <div style="width:70px;height:70px;border-radius:10px;background:#f8fafc;border:1px solid #e5e7eb;"></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h4 style="margin-bottom:6px;">
                                <?= h($item['product_name'] ?? '') ?>
                            </h4>

                            <?php if (!empty($item['variant_storage'])): ?>
                                <p style="color:#64748b;margin:0;">
                                    Dung lượng: <?= h($item['variant_storage']) ?>
                                </p>
                            <?php endif; ?>

                            <p style="color:#64748b;margin:4px 0 0;">
                                Số lượng: <?= (int)($item['quantity'] ?? 0) ?>
                            </p>
                        </div>

                        <div style="text-align:right;">
                            <p style="color:#64748b;margin-bottom:5px;">
                                <?= money_vn($item['price'] ?? 0) ?>
                            </p>

                            <strong style="color:#ef4444;">
                                <?= money_vn($item['subtotal'] ?? 0) ?>
                            </strong>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <h2 style="text-align:right;color:#ef4444;margin-top:20px;">
                Tổng tiền: <?= money_vn($order['total_amount'] ?? 0) ?>
            </h2>
            <?php if (($order['status'] ?? '') === 'pending'): ?>
                <div style="text-align:right;margin-top:16px;">
                    <a href="cancel_order.php?id=<?= (int)$order['id'] ?>"
                    style="display:inline-block;padding:12px 20px;border-radius:999px;background:#ef4444;color:#ffffff;font-weight:900;text-decoration:none;">
                        Hủy đơn hàng
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>