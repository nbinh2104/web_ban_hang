<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'products';

$sql = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        MIN(v.new_price) AS min_price,
        COUNT(v.id) AS variant_count
    FROM products p
    LEFT JOIN product_variants v ON v.product_id = p.id
    GROUP BY p.id
    ORDER BY p.id DESC
";

$products = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-title">Quản lý sản phẩm</h1>
            <a href="product_form.php" class="btn btn-blue">
                + Thêm sản phẩm
            </a>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá từ</th>
                        <th>Số dung lượng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($products && mysqli_num_rows($products) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><?= (int)$product['id'] ?></td>

                                <td>
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img 
                                            src="<?= h($product['image_url']) ?>" 
                                            alt=""
                                            style="width:70px;height:70px;object-fit:contain;"
                                        >
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= h($product['name']) ?></strong>
                                </td>

                                <td>
                                    <?= money_vn($product['min_price'] ?? 0) ?>
                                </td>

                                <td>
                                    <?= (int)$product['variant_count'] ?>
                                </td>

                                <td>
                                    <a href="product_form.php?id=<?= (int)$product['id'] ?>" class="btn btn-blue">
                                        Sửa
                                    </a>

                                    <a href="../page/detail.php?id=<?= (int)$product['id'] ?>" target="_blank" class="btn btn-gray">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Chưa có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>