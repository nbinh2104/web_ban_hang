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
    die('Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.');
}

if ($order['status'] !== 'pending') {
    die('Đơn hàng này không thể hủy vì đã được xử lý.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_reason = 'Khách hàng tự hủy đơn';

    $sql_cancel = "
        UPDATE orders
        SET status = 'cancelled',
            cancel_reason = ?,
            cancelled_at = NOW()
        WHERE id = ?
          AND user_id = ?
          AND status = 'pending'
    ";

    $stmt_cancel = mysqli_prepare($conn, $sql_cancel);
    mysqli_stmt_bind_param($stmt_cancel, "sii", $cancel_reason, $order_id, $user_id);
    mysqli_stmt_execute($stmt_cancel);

    header("Location: my_orders.php?cancel=success");
    exit;
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

$activePage = '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hủy đơn hàng #<?= (int)$order['id'] ?> - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
</head>

<body>

<?php include('components/header.php'); ?>

<main class="container" style="padding:40px 16px;">

    <div style="max-width:850px;margin:0 auto;">
        <a href="my_orders.php" style="color:#00a8ff;font-weight:800;text-decoration:none;">
            ← Quay lại đơn hàng
        </a>

        <div style="margin-top:20px;background:#ffffff;border:1px solid #e5e7eb;border-radius:20px;padding:26px;box-shadow:0 10px 28px rgba(15,23,42,0.08);">
            <h1 style="font-size:28px;margin-bottom:12px;color:#0f172a;">
                Xác nhận hủy đơn hàng #<?= (int)$order['id'] ?>
            </h1>

            <p style="color:#64748b;margin-bottom:20px;">
                Bạn chỉ có thể hủy đơn hàng khi đơn đang ở trạng thái <strong>Chờ xác nhận</strong>.
            </p>

            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:14px;padding:16px;margin-bottom:22px;">
                Bạn có chắc chắn muốn hủy đơn hàng này không? Thao tác này không thể hoàn tác.
            </div>

            <div style="display:grid;gap:10px;margin-bottom:24px;">
                <p><strong>Người nhận:</strong> <?= h($order['customer_name'] ?? '') ?></p>
                <p><strong>Số điện thoại:</strong> <?= h($order['phone'] ?? '') ?></p>
                <p><strong>Email:</strong> <?= h($order['email'] ?? '') ?></p>
                <p><strong>Địa chỉ:</strong> <?= h($order['address'] ?? '') ?></p>
                <p><strong>Tổng tiền:</strong> <span style="color:#ef4444;font-weight:900;"><?= money_vn($order['total_amount'] ?? 0) ?></span></p>
            </div>

            <h3 style="margin-bottom:14px;">Sản phẩm trong đơn</h3>

            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:26px;">
                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                    <div style="display:grid;grid-template-columns:60px 1fr auto;gap:14px;align-items:center;border:1px solid #e5e7eb;border-radius:14px;padding:12px;">
                        <div>
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?= h($item['product_image']) ?>" 
                                     alt="<?= h($item['product_name'] ?? '') ?>"
                                     style="width:60px;height:60px;object-fit:contain;border-radius:10px;">
                            <?php else: ?>
                                <div style="width:60px;height:60px;background:#f8fafc;border-radius:10px;"></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <strong><?= h($item['product_name'] ?? '') ?></strong>

                            <?php if (!empty($item['variant_storage'])): ?>
                                <p style="color:#64748b;margin-top:4px;">
                                    Dung lượng: <?= h($item['variant_storage']) ?>
                                </p>
                            <?php endif; ?>

                            <p style="color:#64748b;margin-top:4px;">
                                Số lượng: <?= (int)($item['quantity'] ?? 0) ?>
                            </p>
                        </div>

                        <div style="text-align:right;color:#ef4444;font-weight:900;">
                            <?= money_vn($item['subtotal'] ?? 0) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;">
                <button type="submit"
                        style="border:none;border-radius:999px;background:#ef4444;color:#ffffff;padding:13px 22px;font-weight:900;cursor:pointer;">
                    Xác nhận hủy đơn
                </button>

                <a href="my_orders.php"
                   style="border-radius:999px;background:#f8fafc;border:1px solid #e5e7eb;color:#0f172a;padding:13px 22px;font-weight:800;text-decoration:none;">
                    Không hủy nữa
                </a>
            </form>
        </div>
    </div>

</main>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

</body>
</html>