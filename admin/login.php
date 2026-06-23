<?php
require_once('../config/database.php');
require_once('auth_admin.php');

if (admin_is_logged_in()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        $sql = "SELECT id, full_name, email, password_hash, role, status 
                FROM users 
                WHERE email = ? 
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            $error = 'Lỗi hệ thống.';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Email hoặc mật khẩu không đúng.';
            } elseif (($user['role'] ?? '') !== 'admin') {
                $error = 'Tài khoản này không có quyền admin.';
            } elseif (($user['status'] ?? 'active') !== 'active') {
                $error = 'Tài khoản admin đang bị khóa.';
            } else {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_email'] = $user['email'];

                header("Location: index.php");
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
    <title>Đăng nhập Admin - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body class="login-page">

<div class="login-box">
    <h1>ABA Admin</h1>

    <?php if ($error !== ''): ?>
        <div class="alert-error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input 
                type="email" 
                name="email" 
                class="form-control" 
                value="<?= h($_POST['email'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input 
                type="password" 
                name="password" 
                class="form-control" 
                required
            >
        </div>

        <button type="submit" class="btn btn-blue" style="width:100%;padding:13px;">
            Đăng nhập admin
        </button>
    </form>
</div>

</body>
</html>