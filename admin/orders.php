<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'orders';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($order_id > 0) {
        if ($action === 'confirm') {
            $sql = "UPDATE orders 
                    SET status = 'confirmed' 
                    WHERE id = ? AND status = 'pending'";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã chấp nhận đơn hàng #' . $order_id;
        }

        if ($action === 'cancel') {
            $sql = "UPDATE orders 
                    SET status = 'cancelled',
                        cancel_reason = 'Admin hủy đơn',
                        cancelled_at = NOW()
                    WHERE id = ? 
                      AND status NOT IN ('completed', 'cancelled')";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã hủy đơn hàng #' . $order_id;
        }

        if ($action === 'shipping') {
            $sql = "UPDATE orders 
                    SET status = 'shipping' 
                    WHERE id = ? AND status = 'confirmed'";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đơn hàng #' . $order_id . ' đã chuyển sang đang giao hàng.';
        }

        if ($action === 'complete') {
            $sql = "UPDATE orders 
                    SET status = 'completed' 
                    WHERE id = ? AND status IN ('confirmed', 'shipping')";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đơn hàng #' . $order_id . ' đã hoàn thành.';
        }
    }
}

$status_filter = $_GET['status'] ?? '';

$allowed_status = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];

$where = "";
$params = [];
$types = "";

if (in_array($status_filter, $allowed_status)) {
    $where = "WHERE status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "SELECT * FROM orders $where ORDER BY id DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $orders = mysqli_stmt_get_result($stmt);
} else {
    $orders = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-title">Quản lý đơn hàng</h1>
            <strong><?= h(current_admin_name()) ?></strong>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert-success">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="GET" class="filter-form">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Lọc trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="shipping" <?= $status_filter === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-blue">Lọc</button>
                <a href="orders.php" class="btn btn-gray">Xóa lọc</a>
            </form>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                            <?php $status = $order['status'] ?? 'pending'; ?>

                            <tr>
                                <td>#<?= (int)$order['id'] ?></td>

                                <td>
                                    <strong><?= h($order['customer_name']) ?></strong><br>
                                    <small><?= h($order['email']) ?></small>
                                </td>

                                <td>
                                    <a class="btn btn-blue" href="tel:<?= h($order['phone']) ?>">
                                        Gọi
                                    </a>
                                    
                                </td>

                                <td>
                                    <strong style="color:#ef4444;">
                                        <?= money_vn($order['total_amount']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="badge <?= status_class($status) ?>">
                                        <?= h(status_text($status)) ?>
                                    </span>
                                </td>

                                <td><?= h($order['created_at']) ?></td>

                                <td>
                                    <a href="order_detail.php?id=<?= (int)$order['id'] ?>" class="btn btn-dark">
                                        Chi tiết
                                    </a>

                                    <?php if ($status === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn btn-green">
                                                Chấp nhận
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($status === 'confirmed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <input type="hidden" name="action" value="shipping">
                                            <button type="submit" class="btn btn-blue">
                                                Đang giao
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($status === 'confirmed' || $status === 'shipping'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="btn btn-green">
                                                Hoàn thành
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($status !== 'cancelled' && $status !== 'completed'): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-red">
                                                Hủy
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Không có đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>