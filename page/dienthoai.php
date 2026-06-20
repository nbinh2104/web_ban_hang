<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$brands = isset($_GET['brand']) ? $_GET['brand'] : [];
if (!is_array($brands)) {
    $brands = [$brands];
}

$price = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where = [];
$params = [];
$types = "";

/* Tìm kiếm theo tên sản phẩm */
if ($q !== '') {
    $where[] = "p.name LIKE ?";
    $params[] = "%" . $q . "%";
    $types .= "s";
}

/* Lọc thương hiệu theo tên */
if (!empty($brands)) {
    $brandConditions = [];

    foreach ($brands as $brand) {
        if ($brand === 'apple') {
            $brandConditions[] = "(p.name LIKE ? OR p.name LIKE ?)";
            $params[] = "%iPhone%";
            $params[] = "%Apple%";
            $types .= "ss";
        }

        if ($brand === 'samsung') {
            $brandConditions[] = "p.name LIKE ?";
            $params[] = "%Samsung%";
            $types .= "s";
        }
    }

    if (!empty($brandConditions)) {
        $where[] = "(" . implode(" OR ", $brandConditions) . ")";
    }
}

/* Lọc mức giá theo giá bản dung lượng thấp nhất */
if ($price === 'duoi-5-trieu') {
    $where[] = "v.new_price < ?";
    $params[] = 5000000;
    $types .= "i";
} elseif ($price === '5-15-trieu') {
    $where[] = "v.new_price BETWEEN ? AND ?";
    $params[] = 5000000;
    $params[] = 15000000;
    $types .= "ii";
} elseif ($price === 'tren-15-trieu') {
    $where[] = "v.new_price > ?";
    $params[] = 15000000;
    $types .= "i";
}

/* Sắp xếp */
switch ($sort) {
    case 'price_asc':
        $orderBy = "v.new_price ASC, p.id DESC";
        break;

    case 'price_desc':
        $orderBy = "v.new_price DESC, p.id DESC";
        break;

    case 'newest':
    default:
        $orderBy = "p.id DESC";
        break;
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

/* Lấy mỗi dòng máy 1 card, giá là bản dung lượng thấp nhất */
$sql = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        p.image_folder,
        p.description,
        v.id AS variant_id,
        v.storage,
        v.old_price,
        v.new_price,
        v.stock
    FROM products p
    JOIN product_variants v 
        ON v.id = (
            SELECT v2.id
            FROM product_variants v2
            WHERE v2.product_id = p.id
            ORDER BY 
                CASE
                    WHEN UPPER(v2.storage) LIKE '%TB%' 
                        THEN CAST(REPLACE(UPPER(v2.storage), 'TB', '') AS UNSIGNED) * 1024
                    WHEN UPPER(v2.storage) LIKE '%GB%' 
                        THEN CAST(REPLACE(UPPER(v2.storage), 'GB', '') AS UNSIGNED)
                    ELSE 999999
                END ASC
            LIMIT 1
        )
    $whereSql
    ORDER BY $orderBy
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Điện thoại - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="../public/css/dienthoai.css?v=<?= time() ?>" />
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

        <!-- NÚT 3 SỌC CHO MOBILE -->
        <button type="button" class="mobile-menu-btn" onclick="toggleMobileMenu()">
            ☰
        </button>

        <nav class="header-center">
            <ul class="modern-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="dienthoai.php" class="active">Điện thoại</a></li>
                <li><a href="suachua.php">Sửa chữa</a></li>
                <li><a href="tincongnghe.php">Tin công nghệ</a></li>

                <!-- Chỉ hiện trong menu mobile -->
                <li class="mobile-menu-extra"><a href="cart.php">🛒 Giỏ hàng</a></li>
            </ul>
        </nav>

        <div class="header-right">

            <form action="dienthoai.php" method="GET" class="search-form" autocomplete="off">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tìm kiếm" 
                    class="search-input"
                    value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                >

                <?php foreach ($brands as $brand): ?>
                    <input 
                        type="hidden" 
                        name="brand[]" 
                        value="<?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endforeach; ?>

                <?php if ($price != ''): ?>
                    <input 
                        type="hidden" 
                        name="price" 
                        value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>

                <?php if ($sort != ''): ?>
                    <input 
                        type="hidden" 
                        name="sort" 
                        value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>

                <button type="submit" class="search-btn">🔍</button>

                <div id="search-results" class="search-results"></div>
            </form>

            <a href="cart.php" class="btn-cart-modern">
                🛒 Giỏ hàng
                <span id="cart-badge" class="cart-badge-hidden">0</span>
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

            <form action="dienthoai.php" method="GET" class="filter-form">

                <?php if ($q != ''): ?>
                    <input 
                        type="hidden" 
                        name="q" 
                        value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>

                <input 
                    type="hidden" 
                    name="sort" 
                    value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="filter-group">
                    <h4>Thương hiệu</h4>

                    <label>
                        <input 
                            type="checkbox" 
                            name="brand[]" 
                            value="apple"
                            <?= in_array('apple', $brands) ? 'checked' : '' ?>
                        >
                        Apple (iPhone)
                    </label>

                    <label>
                        <input 
                            type="checkbox" 
                            name="brand[]" 
                            value="samsung"
                            <?= in_array('samsung', $brands) ? 'checked' : '' ?>
                        >
                        Samsung
                    </label>
                </div>

                <div class="filter-group">
                    <h4>Mức giá</h4>

                    <label>
                        <input 
                            type="radio" 
                            name="price" 
                            value="duoi-5-trieu"
                            <?= $price == 'duoi-5-trieu' ? 'checked' : '' ?>
                        >
                        Dưới 5 triệu
                    </label>

                    <label>
                        <input 
                            type="radio" 
                            name="price" 
                            value="5-15-trieu"
                            <?= $price == '5-15-trieu' ? 'checked' : '' ?>
                        >
                        Từ 5 - 15 triệu
                    </label>

                    <label>
                        <input 
                            type="radio" 
                            name="price" 
                            value="tren-15-trieu"
                            <?= $price == 'tren-15-trieu' ? 'checked' : '' ?>
                        >
                        Trên 15 triệu
                    </label>
                </div>

                <button type="submit" class="btn-filter">
                    Áp dụng bộ lọc
                </button>

                <a href="dienthoai.php" class="btn-clear-filter">
                    Xóa bộ lọc
                </a>

            </form>
        </aside>

        <!-- DANH SÁCH SẢN PHẨM -->
        <section class="category-main">

            <div class="category-header">
                <div>
                    <h1>
                        <?php if ($q != ''): ?>
                            Kết quả tìm kiếm: "<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                        <?php else: ?>
                              Điện thoại di động
                        <?php endif; ?>
                    </h1>

                    <?php if (!empty($brands) || $price != '' || $q != ''): ?>
                        <p class="filter-note">
                            Đang hiển thị sản phẩm theo điều kiện đã chọn
                        </p>
                    <?php endif; ?>
                </div>

                <form action="dienthoai.php" method="GET" class="sort-form">

                    <?php if ($q != ''): ?>
                        <input 
                            type="hidden" 
                            name="q" 
                            value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    <?php endif; ?>

                    <?php foreach ($brands as $brand): ?>
                        <input 
                            type="hidden" 
                            name="brand[]" 
                            value="<?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    <?php endforeach; ?>

                    <?php if ($price != ''): ?>
                        <input 
                            type="hidden" 
                            name="price" 
                            value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    <?php endif; ?>

                    <select name="sort" class="sort-box" onchange="this.form.submit()">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>
                            Mới nhất
                        </option>

                        <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>
                            Giá: Thấp đến Cao
                        </option>

                        <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>
                            Giá: Cao xuống Thấp
                        </option>
                    </select>
                </form>
            </div>

            <div class="product-grid category-grid">

                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>

                    <div class="product-card">

                        <a href="detail.php?id=<?= (int)$row['id'] ?>" class="card-link">

                            <div class="product-image-box">
                                <img 
                                    src="<?= htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8') ?>" 
                                    alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>"
                                />
                            </div>

                            <h3 class="product-title">
                                <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <div class="price-group">
                                <?php if (!empty($row['old_price'])): ?>
                                    <p class="old-price">
                                        <?= number_format((int)$row['old_price'], 0, ',', '.') ?> đ
                                    </p>
                                <?php endif; ?>

                                <p class="product-price">
                                    Từ <?= number_format((int)$row['new_price'], 0, ',', '.') ?> đ
                                </p>
                            </div>

                        </a>

                        <button 
                            class="btn-add-cart"
                            onclick='addToCartVariant(
                                <?= json_encode($row["id"]) ?>,
                                <?= json_encode($row["variant_id"]) ?>,
                                <?= json_encode($row["name"], JSON_UNESCAPED_UNICODE) ?>,
                                <?= json_encode($row["storage"], JSON_UNESCAPED_UNICODE) ?>,
                                <?= json_encode($row["new_price"]) ?>,
                                <?= json_encode($row["image_url"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                            )'
                        >
                            🛒 THÊM VÀO GIỎ
                        </button>

                    </div>

                <?php
                    }
                } else {
                    echo "<p class='no-product'>Không tìm thấy sản phẩm phù hợp.</p>";
                }
                ?>

            </div>

        </section>

    </div>

</main>

<div id="toast"></div>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

<script>
/* =========================================
   MỞ / ĐÓNG MENU MOBILE
========================================= */
function toggleMobileMenu() {
    const header = document.querySelector('.modern-header');

    if (header) {
        header.classList.toggle('mobile-open');
    }
}

/* =========================================
   TÌM KIẾM AJAX
========================================= */
document.addEventListener('DOMContentLoaded', function () {
    const searchForms = document.querySelectorAll('.search-form');

    searchForms.forEach(function (form) {
        const searchInput = form.querySelector('.search-input');
        const resultDiv = form.querySelector('.search-results');

        if (!searchInput || !resultDiv) return;

        searchInput.addEventListener('input', function () {
            const q = this.value.trim();

            if (q.length > 0) {
                fetch('search_ajax.php?q=' + encodeURIComponent(q))
                    .then(response => response.text())
                    .then(data => {
                        resultDiv.innerHTML = data;
                        resultDiv.style.display = data.trim() ? 'block' : 'none';
                    })
                    .catch(() => {
                        resultDiv.innerHTML = '<div class="search-empty">Lỗi tìm kiếm sản phẩm</div>';
                        resultDiv.style.display = 'block';
                    });
            } else {
                resultDiv.innerHTML = '';
                resultDiv.style.display = 'none';
            }
        });

        form.addEventListener('submit', function (e) {
            const firstResult = resultDiv.querySelector('.search-item');

            if (firstResult) {
                e.preventDefault();
                window.location.href = firstResult.getAttribute('href');
            }
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                resultDiv.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function (e) {
        const header = document.querySelector('.modern-header');

        if (!header) return;

        const clickInsideHeader = header.contains(e.target);

        if (!clickInsideHeader) {
            header.classList.remove('mobile-open');
        }
    });

    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
</script>

</body>
</html>