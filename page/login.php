<?php
include('../config/database.php');
require_once('../config/auth.php');

mysqli_set_charset($conn, "utf8mb4");

if (is_logged_in()) {
    header("Location: my_orders.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            $error = 'Lỗi hệ thống, vui lòng thử lại.';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Email hoặc mật khẩu không đúng.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                $update_orders = "
                    UPDATE orders
                    SET user_id = ?
                    WHERE email = ?
                      AND (user_id IS NULL OR user_id = 0)
                ";

                $stmt_update = mysqli_prepare($conn, $update_orders);

                if ($stmt_update) {
                    mysqli_stmt_bind_param($stmt_update, "is", $user['id'], $user['email']);
                    mysqli_stmt_execute($stmt_update);
                }

                header("Location: my_orders.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <?php 
$activePage = '';
include('components/header.php'); 
?>
    <main class="container" style="max-width:480px;padding:50px 16px;">
        <h1 class="section-title">Đăng nhập</h1>

        <?php if ($error !== ''): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:12px;margin-bottom:18px;font-weight:700;">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display:flex;flex-direction:column;gap:14px;">
            <input 
                type="email" 
                name="email" 
                placeholder="Email" 
                value="<?= h($_POST['email'] ?? '') ?>"
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <input 
                type="password" 
                name="password" 
                placeholder="Mật khẩu" 
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <button type="submit" class="btn-add-cart">
                Đăng nhập
            </button>
        </form>

        <p style="margin-top:18px;text-align:center;">
            Chưa có tài khoản?
            <a href="register.php" style="color:#00a8ff;font-weight:800;text-decoration:none;">
                Đăng ký
            </a>
        </p>

        <p style="text-align:center;margin-top:10px;">
            <a href="index.php" style="color:#64748b;text-decoration:none;">
                ← Về trang chủ
            </a>
        </p>
    </main>
</body>
</html>