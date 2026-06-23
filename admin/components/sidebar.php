<?php
if (!isset($activeAdminPage)) {
    $activeAdminPage = '';
}

function admin_active($page, $activeAdminPage) {
    return $page === $activeAdminPage ? 'active' : '';
}
?>

<aside class="admin-sidebar">
    <div class="admin-logo">
        ABA Admin<span>.</span>
    </div>

    <nav class="admin-menu">
        <a href="index.php" class="<?= admin_active('dashboard', $activeAdminPage) ?>">
            📊 Tổng quan
        </a>

        <a href="orders.php" class="<?= admin_active('orders', $activeAdminPage) ?>">
            📦 Quản lý đơn hàng
        </a>

        <a href="repairs.php" class="<?= admin_active('repairs', $activeAdminPage) ?>">
            🛠️ Lịch sửa chữa
        </a>

        <a href="revenue.php" class="<?= admin_active('revenue', $activeAdminPage) ?>">
            📈 Doanh thu
        </a>

        <a href="products.php" class="<?= admin_active('products', $activeAdminPage) ?>">
            📱 Sản phẩm
        </a>

        <a href="reviews.php" class="<?= admin_active('reviews', $activeAdminPage) ?>">
            ⭐ Đánh giá
        </a>

        <a href="users.php" class="<?= admin_active('users', $activeAdminPage) ?>">
            👤 Người dùng
        </a>

        <a href="../page/index.php" target="_blank">
            🌐 Xem website
        </a>

        <a href="logout.php">
            🚪 Đăng xuất
        </a>
    </nav>
</aside>