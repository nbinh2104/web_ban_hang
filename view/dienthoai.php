<?php
include('../config/database.php');

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Điện thoại - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/dienthoai.css" />
</head>

<body>

<header class="modern-header">
    <div class="container header-inner">

        <div class="header-left">
            <a href="tel:1900xxxx" class="btn-phone-icon">📞</a>

            <a href="index.php" class="modern-logo">
                ABA Mobile<span class="dot">.</span>
            </a>
        </div>

        <nav class="header-center">
            <ul class="modern-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="dienthoai.php" class="active">Điện thoại</a></li>
                <li><a href="suachua.php">Sửa chữa</a></li>
                <li><a href="tincongnghe.php">Tin công nghệ</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <a href="#" class="icon-action" title="Tìm kiếm">🔍</a>
            <a href="#" class="icon-action" title="Tài khoản">👤</a>

            <a href="cart.html" class="btn-cart-modern">
                🛒 Giỏ hàng
                <span id="cart-badge" style="display: none;">0</span>
            </a>
        </div>

    </div>
</header>

<main class="container page-phone">

    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>&raquo;</span>
        <span>Điện thoại</span>
    </div>

    <div class="category-layout">

        <!-- SIDEBAR BỘ LỌC -->
        <aside class="sidebar-filter">
            <h3 class="filter-title">BỘ LỌC SẢN PHẨM</h3>

            <form action="" method="GET">

                <div class="filter-group">
                    <h4>Thương hiệu</h4>

                    <label>
                        <input type="checkbox" name="brand[]" value="apple">
                        Apple (iPhone)
                    </label>

                    <label>
                        <input type="checkbox" name="brand[]" value="samsung">
                        Samsung
                    </label>
                </div>

                <div class="filter-group">
                    <h4>Mức giá</h4>

                    <label>
                        <input type="radio" name="price" value="duoi-5-trieu">
                        Dưới 5 triệu
                    </label>

                    <label>
                        <input type="radio" name="price" value="5-15-trieu">
                        Từ 5 - 15 triệu
                    </label>

                    <label>
                        <input type="radio" name="price" value="tren-15-trieu">
                        Trên 15 triệu
                    </label>
                </div>

                <button type="submit" class="btn-filter">
                    Áp dụng bộ lọc
                </button>

            </form>
        </aside>

        <!-- DANH SÁCH SẢN PHẨM -->
        <section class="category-main">

            <div class="category-header">
                <h1>Điện thoại di động</h1>
<br>
                <select class="sort-box">
                    <option>Mới nhất</option>
                    <option>Giá: Thấp đến Cao</option>
                    <option>Giá: Cao xuống Thấp</option>
                </select>
            </div>
            <br>

            <div class="product-grid category-grid">

                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>

                    <div class="product-card">

                        <a href="detail.php?id=<?= $row['id'] ?>" class="card-link">

                            <div class="product-image-box">
                                <img 
                                    src="<?= $row['image_url'] ?>" 
                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                />
                            </div>

                            <h3 class="product-title">
                                <?= htmlspecialchars($row['name']) ?>
                            </h3>

                            <div class="price-group">
                                <p class="old-price">
                                    <?= number_format($row['old_price'], 0, ',', '.') ?> đ
                                </p>

                                <p class="product-price">
                                    <?= number_format($row['new_price'], 0, ',', '.') ?> đ
                                </p>
                            </div>

                        </a>

                        <button 
                            class="btn-add-cart"
                            onclick='addToCart(
                                <?= json_encode($row["id"]) ?>,
                                <?= json_encode($row["name"]) ?>,
                                <?= json_encode($row["new_price"]) ?>,
                                <?= json_encode($row["image_url"]) ?>
                            )'
                        >
                            🛒 THÊM VÀO GIỎ
                        </button>

                    </div>

                <?php
                    }
                } else {
                    echo "<p class='no-product'>Chưa có sản phẩm nào.</p>";
                }
                ?>

            </div>

        </section>

    </div>

</main>

</body>
</html>