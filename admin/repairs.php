<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'repairs';
$message = '';

function repair_status_text($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ liên hệ';
        case 'contacted':
            return 'Đã liên hệ';
        case 'repairing':
            return 'Đang sửa';
        case 'done':
            return 'Hoàn thành';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Không rõ';
    }
}

function repair_status_class($status) {
    switch ($status) {
        case 'pending':
            return 'badge-pending';
        case 'contacted':
            return 'badge-contacted';
        case 'repairing':
            return 'badge-repairing';
        case 'done':
            return 'badge-done';
        case 'cancelled':
            return 'badge-repair-cancelled';
        default:
            return 'badge-pending';
    }
}

/* ===============================
   XỬ LÝ CẬP NHẬT TRẠNG THÁI
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $action = $_POST['action'] ?? '';

    $allowed_actions = ['contacted', 'repairing', 'done', 'cancelled'];

    if ($request_id > 0 && in_array($action, $allowed_actions)) {
        $sql = "
            UPDATE repair_requests
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $action, $request_id);
        mysqli_stmt_execute($stmt);

        $message = 'Đã cập nhật trạng thái lịch sửa chữa.';
    }
}

/* ===============================
   LỌC TRẠNG THÁI
================================ */
$status_filter = $_GET['status'] ?? '';

$allowed_status = ['pending', 'contacted', 'repairing', 'done', 'cancelled'];

$where = "";
$params = [];
$types = "";

if (in_array($status_filter, $allowed_status)) {
    $where = "WHERE rr.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "
    SELECT 
        rr.*,
        rs.service_name
    FROM repair_requests rr
    LEFT JOIN repair_services rs ON rr.service_id = rs.id
    $where
    ORDER BY rr.id DESC
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $repairs = mysqli_stmt_get_result($stmt);
} else {
    $repairs = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sửa chữa - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <div>
                <h1 class="admin-title">Quản lý lịch sửa chữa</h1>
                <p class="admin-subtitle">
                </p>
            </div>

            <div class="admin-user-box">
                <div class="admin-user-avatar">🛠️</div>
                <span>Sửa chữa</span>
            </div>
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
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ liên hệ</option>
                        <option value="contacted" <?= $status_filter === 'contacted' ? 'selected' : '' ?>>Đã liên hệ</option>
                        <option value="repairing" <?= $status_filter === 'repairing' ? 'selected' : '' ?>>Đang sửa</option>
                        <option value="done" <?= $status_filter === 'done' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-blue">
                    Lọc
                </button>

                <a href="repairs.php" class="btn btn-gray">
                    Xóa lọc
                </a>

            </form>
        </div>

        <div class="admin-card">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Danh sách khách đặt sửa chữa</h2>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Khách hàng</th>
                        <th>Dòng máy</th>
                        <th>Dịch vụ</th>
                        <th>Tình trạng lỗi</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th>Liên hệ / xử lý</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($repairs && mysqli_num_rows($repairs) > 0): ?>
                        <?php while ($repair = mysqli_fetch_assoc($repairs)): ?>
                            <?php
                                $phone_clean = preg_replace('/\D/', '', $repair['phone']);
                                $status = $repair['status'] ?? 'pending';
                            ?>

                            <tr>
                                <td>#<?= (int)$repair['id'] ?></td>

                                <td>
                                    <div class="repair-customer">
                                        <strong><?= h($repair['customer_name']) ?></strong>
                                        <small><?= h($repair['phone']) ?></small>
                                    </div>
                                </td>

                                <td>
                                    <strong><?= h($repair['device_name']) ?></strong>
                                </td>

                                <td>
                                    <?= h($repair['service_name'] ?? 'Chưa rõ dịch vụ') ?>
                                </td>

                                <td>
                                    <div class="repair-issue">
                                        <?= h($repair['issue_description']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge <?= repair_status_class($status) ?>">
                                        <?= h(repair_status_text($status)) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= h($repair['created_at']) ?>
                                </td>

                                <td>
                                    <div class="repair-actions">

                                        <a href="tel:<?= h($phone_clean) ?>" class="btn btn-blue">
                                            Gọi
                                        </a>

                                        <?php if ($status === 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?= (int)$repair['id'] ?>">
                                                <input type="hidden" name="action" value="contacted">
                                                <button type="submit" class="btn btn-green">
                                                    Đã liên hệ
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status === 'contacted'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?= (int)$repair['id'] ?>">
                                                <input type="hidden" name="action" value="repairing">
                                                <button type="submit" class="btn btn-blue">
                                                    Đang sửa
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status === 'repairing'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?= (int)$repair['id'] ?>">
                                                <input type="hidden" name="action" value="done">
                                                <button type="submit" class="btn btn-green">
                                                    Hoàn thành
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status !== 'done' && $status !== 'cancelled'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch sửa chữa này?');">
                                                <input type="hidden" name="request_id" value="<?= (int)$repair['id'] ?>">
                                                <input type="hidden" name="action" value="cancelled">
                                                <button type="submit" class="btn btn-red">
                                                    Hủy
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Chưa có lịch sửa chữa nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>