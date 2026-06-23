<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'users';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($user_id > 0 && $user_id !== current_admin_id()) {
        if ($action === 'make_admin') {
            $sql = "UPDATE users SET role = 'admin' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã cấp quyền admin.';
        }

        if ($action === 'make_customer') {
            $sql = "UPDATE users SET role = 'customer' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã chuyển về quyền khách hàng.';
        }

        if ($action === 'lock') {
            $sql = "UPDATE users SET status = 'locked' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã khóa tài khoản.';
        }

        if ($action === 'unlock') {
            $sql = "UPDATE users SET status = 'active' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã mở khóa tài khoản.';
        }
    } else {
        $message = 'Không thể tự thay đổi tài khoản admin đang đăng nhập.';
    }
}

$users = mysqli_query($conn, "
    SELECT id, full_name, email, phone, role, status, created_at
    FROM users
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý user - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-title">Quản lý người dùng</h1>
            <strong><?= h(current_admin_name()) ?></strong>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert-success">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Quyền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($users && mysqli_num_rows($users) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?= (int)$user['id'] ?></td>
                                <td><?= h($user['full_name']) ?></td>
                                <td><?= h($user['email']) ?></td>
                                <td><?= h($user['phone']) ?></td>

                                <td>
                                    <strong><?= h($user['role'] ?? 'customer') ?></strong>
                                </td>

                                <td>
                                    <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                        <span class="badge badge-completed">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">Bị khóa</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= h($user['created_at']) ?></td>

                                <td>
                                    <?php if ((int)$user['id'] !== current_admin_id()): ?>

                                        <?php if (($user['role'] ?? 'customer') === 'customer'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                <input type="hidden" name="action" value="make_admin">
                                                <button type="submit" class="btn btn-blue">
                                                    Cấp admin
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                <input type="hidden" name="action" value="make_customer">
                                                <button type="submit" class="btn btn-gray">
                                                    Hạ quyền
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn muốn khóa tài khoản này?');">
                                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                <input type="hidden" name="action" value="lock">
                                                <button type="submit" class="btn btn-red">
                                                    Khóa
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                <input type="hidden" name="action" value="unlock">
                                                <button type="submit" class="btn btn-green">
                                                    Mở khóa
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span style="color:#64748b;">Tài khoản hiện tại</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Chưa có user nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>