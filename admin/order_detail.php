<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'orders';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die('ID đơn hàng không hợp lệ.');
}

$sql_order = "SELECT * FROM orders WHERE id = ? LIMIT 1";
$stmt_order = mysqli_prepare($conn, $sql_order);
mysqli_stmt_bind_param($stmt_order, "i", $order_id);
mysqli_stmt_execute($stmt_order);
$order_result = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die('Không tìm thấy đơn hàng.');
}

$sql_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = mysqli_prepare($conn, $sql_items);
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);
$items = mysqli_stmt_get_result($stmt_items);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-title">Chi tiết đơn hàng #<?= (int)$order['id'] ?></h1>
            <a href="orders.php" class="btn btn-gray">← Quay lại</a>
        </div>

        <div class="admin-card">
            <h2 style="margin-bottom:16px;">Thông tin khách hàng</h2>

            <p><strong>Khách hàng:</strong> <?= h($order['customer_name']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= h($order['phone']) ?></p>
            <p><strong>Email:</strong> <?= h($order['email']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= h($order['address']) ?></p>
            <p><strong>Ghi chú:</strong> <?= h($order['note'] ?: 'Không có') ?></p>
            <p><strong>Thanh toán:</strong> <?= h($order['payment_method']) ?></p>

            <p>
                <strong>Trạng thái:</strong>
                <span class="badge <?= status_class($order['status']) ?>">
                    <?= h(status_text($order['status'])) ?>
                </span>
            </p>

            <?php if (($order['status'] ?? '') === 'cancelled'): ?>
                <p><strong>Lý do hủy:</strong> <?= h($order['cancel_reason'] ?? 'Không có') ?></p>
                <p><strong>Thời gian hủy:</strong> <?= h($order['cancelled_at'] ?? 'Không có') ?></p>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <h2 style="margin-bottom:16px;">Sản phẩm trong đơn</h2>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Dung lượng</th>
                        <th>Giá</th>
                        <th>SL</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['product_image'])): ?>
                                    <img 
                                        src="<?= h($item['product_image']) ?>" 
                                        alt=""
                                        style="width:70px;height:70px;object-fit:contain;"
                                    >
                                <?php endif; ?>
                            </td>

                            <td><?= h($item['product_name']) ?></td>
                            <td><?= h($item['variant_storage'] ?? '') ?></td>
                            <td><?= money_vn($item['price']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td>
                                <strong style="color:#ef4444;">
                                    <?= money_vn($item['subtotal']) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2 style="text-align:right;margin-top:22px;color:#ef4444;">
                Tổng tiền: <?= money_vn($order['total_amount']) ?>
            </h2>
        </div>

    </main>
</div>

</body>
</html>