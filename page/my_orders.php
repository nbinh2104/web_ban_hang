<?php
include('../config/database.php');
require_once('../config/auth.php');

mysqli_set_charset($conn, "utf8mb4");

require_login();

$user_id = current_user_id();

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

function status_color($status) {
    switch ($status) {
        case 'pending':
            return '#f59e0b';
        case 'confirmed':
            return '#00a8ff';
        case 'shipping':
            return '#6366f1';
        case 'completed':
            return '#16a34a';
        case 'cancelled':
            return '#ef4444';
        default:
            return '#64748b';
    }
}

$sql = "
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng đã đặt - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <?php 
$activePage = '';
include('components/header.php'); 
?>
    <main class="container" style="padding:40px 16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <h1 class="section-title" style="margin-bottom:10px;"
<?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
    <div id="cancel-success-message" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:14px;padding:14px;margin:16px 0;font-weight:800;">
        Đơn hàng đã được hủy thành công.
    </div>
<?php endif; ?>
                <p style="color:#64748b;">
                    Xin chào <strong><?= h(current_user_name()) ?></strong>
                </p>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="index.php" style="padding:10px 16px;border-radius:999px;border:1px solid #e5e7eb;text-decoration:none;color:#0f172a;font-weight:700;">
                    Trang chủ
                </a>

                <a href="logout.php" style="padding:10px 16px;border-radius:999px;background:#ef4444;color:#ffffff;text-decoration:none;font-weight:700;">
                    Đăng xuất
                </a>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:26px;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <h3 style="margin-bottom:10px;">Bạn chưa có đơn hàng nào</h3>
                <p style="color:#64748b;margin-bottom:18px;">
                    Các đơn hàng bạn đặt bằng email này sẽ xuất hiện tại đây.
                </p>

                <a href="dienthoai.php" class="btn-add-cart" style="display:inline-block;text-decoration:none;text-align:center;width:auto;padding:12px 22px;">
                    Mua hàng ngay
                </a>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:18px;">
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <?php
                        $status = $order['status'] ?? 'pending';
                        $created_at = $order['created_at'] ?? '';
                    ?>

                    <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                            <div>
                                <h3 style="font-size:20px;margin-bottom:8px;">
                                    Đơn hàng #<?= (int)$order['id'] ?>
                                </h3>

                                <p style="margin:5px 0;color:#64748b;">
                                    Ngày đặt: <?= h($created_at !== '' ? $created_at : 'Không có dữ liệu') ?>
                                </p>

                                <p style="margin:5px 0;color:#64748b;">
                                    Người nhận: <?= h($order['customer_name'] ?? '') ?>
                                </p>

                                <p style="margin:5px 0;color:#64748b;">
                                    SĐT: <?= h($order['phone'] ?? '') ?>
                                </p>
                            </div>

                            <div style="text-align:right;">
                                <span style="display:inline-block;padding:8px 13px;border-radius:999px;background:<?= status_color($status) ?>;color:#ffffff;font-weight:800;font-size:13px;">
                                    <?= h(status_text($status)) ?>
                                </span>

                                <h3 style="margin-top:12px;color:#ef4444;">
                                    <?= money_vn($order['total_amount'] ?? 0) ?>
                                </h3>
                            </div>
                        </div>

                        <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                            <a href="order_detail.php?id=<?= (int)$order['id'] ?>"
                               style="display:inline-block;padding:10px 16px;border-radius:999px;background:#00a8ff;color:#ffffff;font-weight:800;text-decoration:none;">
                                Xem chi tiết
                            </a>

                        <?php if ($status === 'pending'): ?>
                            <a href="cancel_order.php?id=<?= (int)$order['id'] ?>"
                            style="display:inline-block;padding:10px 16px;border-radius:999px;background:#ef4444;color:#ffffff;font-weight:800;text-decoration:none;">
                                Hủy đơn
                            </a>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const cancelMessage = document.getElementById('cancel-success-message');

    if (cancelMessage) {
        setTimeout(function () {
            cancelMessage.style.transition = '0.4s ease';
            cancelMessage.style.opacity = '0';
            cancelMessage.style.transform = 'translateY(-8px)';

            setTimeout(function () {
                cancelMessage.remove();

                const url = new URL(window.location.href);
                url.searchParams.delete('cancel');
                window.history.replaceState({}, document.title, url.toString());
            }, 400);
        }, 3000);
    }
});
</script>
</body>
</html>