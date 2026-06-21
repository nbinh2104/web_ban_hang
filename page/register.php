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
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu nhập lại không khớp.';
    } else {
        $check_sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt_check = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);

        $check_result = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $error = 'Lỗi hệ thống, vui lòng thử lại.';
            } else {
                mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $phone, $password_hash);

                if (mysqli_stmt_execute($stmt)) {
                    $user_id = mysqli_insert_id($conn);

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_email'] = $email;

                    $update_orders = "
                        UPDATE orders
                        SET user_id = ?
                        WHERE email = ?
                          AND (user_id IS NULL OR user_id = 0)
                    ";

                    $stmt_update = mysqli_prepare($conn, $update_orders);

                    if ($stmt_update) {
                        mysqli_stmt_bind_param($stmt_update, "is", $user_id, $email);
                        mysqli_stmt_execute($stmt_update);
                    }

                    header("Location: my_orders.php");
                    exit;
                } else {
                    $error = 'Đăng ký thất bại, vui lòng thử lại.';
                }
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
    <title>Đăng ký - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <?php 
$activePage = '';
include('components/header.php'); 
?>
    <main class="container" style="max-width:520px;padding:50px 16px;">
        <h1 class="section-title">Đăng ký tài khoản</h1>

        <?php if ($error !== ''): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:12px;margin-bottom:18px;font-weight:700;">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display:flex;flex-direction:column;gap:14px;">
            <input 
                type="text" 
                name="full_name" 
                placeholder="Họ và tên" 
                value="<?= h($_POST['full_name'] ?? '') ?>"
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <input 
                type="email" 
                name="email" 
                placeholder="Email" 
                value="<?= h($_POST['email'] ?? '') ?>"
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <input 
                type="text" 
                name="phone" 
                placeholder="Số điện thoại" 
                value="<?= h($_POST['phone'] ?? '') ?>"
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <input 
                type="password" 
                name="password" 
                placeholder="Mật khẩu" 
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <input 
                type="password" 
                name="confirm_password" 
                placeholder="Nhập lại mật khẩu" 
                required
                style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;font-size:15px;"
            >

            <button type="submit" class="btn-add-cart">
                Đăng ký
            </button>
        </form>

        <p style="margin-top:18px;text-align:center;">
            Đã có tài khoản?
            <a href="login.php" style="color:#00a8ff;font-weight:800;text-decoration:none;">
                Đăng nhập
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