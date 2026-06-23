<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'products';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$product = [
    'name' => '',
    'image_url' => '',
    'image_folder' => '',
    'description' => ''
];

$variants_text = '';

if ($is_edit) {
    $sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $product_db = mysqli_fetch_assoc($result);

    if (!$product_db) {
        die('Không tìm thấy sản phẩm.');
    }

    $product = $product_db;

    $sql_variants = "
        SELECT * 
        FROM product_variants 
        WHERE product_id = ? 
        ORDER BY id ASC
    ";

    $stmt_v = mysqli_prepare($conn, $sql_variants);
    mysqli_stmt_bind_param($stmt_v, "i", $id);
    mysqli_stmt_execute($stmt_v);

    $result_v = mysqli_stmt_get_result($stmt_v);

    $lines = [];

    while ($variant = mysqli_fetch_assoc($result_v)) {
        $lines[] = $variant['storage'] . '|' . 
                   (int)$variant['old_price'] . '|' . 
                   (int)$variant['new_price'] . '|' . 
                   (int)$variant['stock'];
    }

    $variants_text = implode("\n", $lines);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $image_folder = trim($_POST['image_folder'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $variants_text = trim($_POST['variants_text'] ?? '');

    if ($name === '') {
        $error = 'Vui lòng nhập tên sản phẩm.';
    } elseif ($image_url === '') {
        $error = 'Vui lòng nhập đường dẫn ảnh đại diện.';
    } elseif ($image_folder === '') {
        $error = 'Vui lòng nhập thư mục ảnh.';
    } elseif ($variants_text === '') {
        $error = 'Vui lòng nhập ít nhất một dung lượng sản phẩm.';
    } else {
        mysqli_begin_transaction($conn);

        try {
            if ($is_edit) {
                $sql_update = "
                    UPDATE products
                    SET name = ?,
                        image_url = ?,
                        image_folder = ?,
                        description = ?
                    WHERE id = ?
                ";

                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param(
                    $stmt_update,
                    "ssssi",
                    $name,
                    $image_url,
                    $image_folder,
                    $description,
                    $id
                );

                mysqli_stmt_execute($stmt_update);

                $product_id = $id;

                $delete_sql = "DELETE FROM product_variants WHERE product_id = ?";
                $stmt_delete = mysqli_prepare($conn, $delete_sql);
                mysqli_stmt_bind_param($stmt_delete, "i", $product_id);
                mysqli_stmt_execute($stmt_delete);
            } else {
                $sql_insert = "
                    INSERT INTO products 
                    (name, old_price, new_price, image_url, image_folder, description)
                    VALUES (?, NULL, NULL, ?, ?, ?)
                ";

                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param(
                    $stmt_insert,
                    "ssss",
                    $name,
                    $image_url,
                    $image_folder,
                    $description
                );

                mysqli_stmt_execute($stmt_insert);

                $product_id = mysqli_insert_id($conn);
            }

            $lines = preg_split('/\r\n|\r|\n/', $variants_text);

            $sql_variant = "
                INSERT INTO product_variants
                (product_id, storage, storage_value, old_price, new_price, stock)
                VALUES (?, ?, NULL, ?, ?, ?)
            ";

            $stmt_variant = mysqli_prepare($conn, $sql_variant);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $parts = array_map('trim', explode('|', $line));

                if (count($parts) < 4) {
                    throw new Exception('Dòng dung lượng sai định dạng: ' . $line);
                }

                $storage = $parts[0];
                $old_price = (int)$parts[1];
                $new_price = (int)$parts[2];
                $stock = (int)$parts[3];

                if ($storage === '' || $new_price <= 0) {
                    throw new Exception('Dung lượng hoặc giá mới không hợp lệ: ' . $line);
                }

                mysqli_stmt_bind_param(
                    $stmt_variant,
                    "isiii",
                    $product_id,
                    $storage,
                    $old_price,
                    $new_price,
                    $stock
                );

                mysqli_stmt_execute($stmt_variant);
            }

            mysqli_commit($conn);

            header("Location: products.php");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }

    $product = [
        'name' => $name,
        'image_url' => $image_url,
        'image_folder' => $image_folder,
        'description' => $description
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?> - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-title">
                <?= $is_edit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?>
            </h1>

            <a href="products.php" class="btn btn-gray">
                ← Quay lại
            </a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert-error">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        value="<?= h($product['name']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <input 
                        type="text" 
                        name="image_url" 
                        class="form-control"
                        value="<?= h($product['image_url']) ?>"
                        placeholder="../public/images/iPhone/iPhone16/16Prm/cover.jpg"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Thư mục ảnh</label>
                    <input 
                        type="text" 
                        name="image_folder" 
                        class="form-control"
                        value="<?= h($product['image_folder']) ?>"
                        placeholder="../public/images/iPhone/iPhone16/16Prm/"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control"><?= h($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Dung lượng / giá / tồn kho</label>

                    <p style="color:#64748b;margin-bottom:8px;">
                        Mỗi dòng nhập theo dạng:
                        <strong>Dung lượng | Giá cũ | Giá mới | Tồn kho</strong>
                    </p>

                    <textarea 
                        name="variants_text" 
                        class="form-control"
                        style="min-height:180px;"
                        placeholder="128GB|19990000|17990000|10&#10;256GB|21990000|19990000|8"
                        required
                    ><?= h($variants_text) ?></textarea>
                </div>

                <button type="submit" class="btn btn-blue">
                    Lưu sản phẩm
                </button>
            </form>
        </div>

    </main>
</div>

</body>
</html>