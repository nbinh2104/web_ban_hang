<?php
include('../config/database.php');
mysqli_set_charset($conn, "utf8mb4");

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID sản phẩm không hợp lệ");
}

/* =========================
   LẤY THÔNG TIN SẢN PHẨM
========================= */
$product_sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $product_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$product_result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    die("Không tìm thấy sản phẩm");
}

/* =========================
   LẤY CÁC PHIÊN BẢN DUNG LƯỢNG
   Bảng product_variants hiện có:
   id, product_id, storage, old_price, new_price, stock
========================= */
$variant_sql = "
    SELECT *
    FROM product_variants
    WHERE product_id = ?
    ORDER BY 
        CASE
            WHEN UPPER(storage) LIKE '%TB%' 
                THEN CAST(REPLACE(UPPER(storage), 'TB', '') AS UNSIGNED) * 1024
            WHEN UPPER(storage) LIKE '%GB%' 
                THEN CAST(REPLACE(UPPER(storage), 'GB', '') AS UNSIGNED)
            ELSE 999999
        END ASC
";

$stmt2 = mysqli_prepare($conn, $variant_sql);
mysqli_stmt_bind_param($stmt2, "i", $id);
mysqli_stmt_execute($stmt2);
$variant_result = mysqli_stmt_get_result($stmt2);

$variants = [];

while ($row = mysqli_fetch_assoc($variant_result)) {
    $variants[] = $row;
}

if (empty($variants)) {
    die("Sản phẩm này chưa có phiên bản dung lượng trong bảng product_variants");
}

$defaultVariant = $variants[0];

$specs = [];
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'product_specs'");

if ($table_check && mysqli_num_rows($table_check) > 0) {
    $spec_sql = "
        SELECT spec_name, spec_value
        FROM product_specs
        WHERE product_id = ?
        ORDER BY sort_order ASC, id ASC
    ";

    $stmt_specs = mysqli_prepare($conn, $spec_sql);

    if ($stmt_specs) {
        mysqli_stmt_bind_param($stmt_specs, "i", $id);
        mysqli_stmt_execute($stmt_specs);

        $spec_result = mysqli_stmt_get_result($stmt_specs);

        while ($row = mysqli_fetch_assoc($spec_result)) {
            $specs[] = $row;
        }

        mysqli_stmt_close($stmt_specs);
    }
}


/* =========================
   LẤY TOÀN BỘ ẢNH TRONG FOLDER
========================= */

/*
Ví dụ:
image_url    = ../public/images/iPhone/iPhoneX/cover.jpg
image_folder = ../public/images/iPhone/iPhoneX/
*/

$imageFolderUrl = '';

if (isset($product['image_folder']) && !empty($product['image_folder'])) {
    $imageFolderUrl = $product['image_folder'];
} else {
    $imageFolderUrl = dirname($product['image_url']) . '/';
}

/* Encode đường dẫn ảnh có dấu cách/ký tự đặc biệt */
function encodeUrlPath($path) {
    $path = str_replace('\\', '/', $path);
    $parts = explode('/', $path);
    $parts = array_map('rawurlencode', $parts);
    return implode('/', $parts);
}

/* Quét toàn bộ ảnh trong folder, kể cả folder con */
function getGalleryImageUrls($folderUrl) {
    $folderPath = realpath(__DIR__ . '/' . $folderUrl);

    if (!$folderPath || !is_dir($folderPath)) {
        return [];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif', 'avif'];
    $imageUrls = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());

            if (in_array($ext, $allowedExtensions)) {
                $fullPath = str_replace('\\', '/', $file->getPathname());
                $basePath = str_replace('\\', '/', $folderPath);

                $relativePath = ltrim(str_replace($basePath, '', $fullPath), '/');

                $imageUrls[] = rtrim($folderUrl, '/') . '/' . encodeUrlPath($relativePath);
            }
        }
    }

    /* Đưa cover.* lên đầu */
    usort($imageUrls, function($a, $b) {
        $aName = strtolower(basename($a));
        $bName = strtolower(basename($b));

        if (preg_match('/^cover\./', $aName)) return -1;
        if (preg_match('/^cover\./', $bName)) return 1;

        return strcmp($aName, $bName);
    });

    return $imageUrls;
}

$galleryImages = getGalleryImageUrls($imageFolderUrl);

/* Nếu không lấy được ảnh trong folder thì dùng ảnh đại diện */
if (empty($galleryImages) && !empty($product['image_url'])) {
    $galleryImages[] = $product['image_url'];
}

$mainImage = $galleryImages[0] ?? '';

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>" />
    <link rel="stylesheet" href="../public/css/detail.css?v=<?= time(); ?>" />
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
                    placeholder="Tìm sản phẩm, dịch vụ..." 
                    class="search-input"
                >

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

<main class="container product-detail-page">

    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>&raquo;</span>
        <a href="dienthoai.php">Điện thoại</a>
        <span>&raquo;</span>
        <span><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <section class="product-detail-box">

        <!-- CỘT TRÁI: SLIDER ẢNH SẢN PHẨM -->
        <div class="product-detail-image">

            <div class="gallery-slider">

                <?php if (count($galleryImages) > 1): ?>
                    <button 
                        type="button" 
                        class="gallery-nav gallery-prev" 
                        onclick="prevImage()"
                    >
                        ‹
                    </button>
                <?php endif; ?>

                <img 
                    id="mainProductImage"
                    src="<?= htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8') ?>" 
                    alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                    class="main-detail-img"
                >

                <?php if (count($galleryImages) > 1): ?>
                    <button 
                        type="button" 
                        class="gallery-nav gallery-next" 
                        onclick="nextImage()"
                    >
                        ›
                    </button>

                    <div class="gallery-count">
                        <span id="currentImageIndex">1</span>
                        /
                        <span><?= count($galleryImages) ?></span>
                    </div>
                <?php endif; ?>

            </div>

            <?php if (count($galleryImages) > 1): ?>
                <div class="thumb-list">
                    <?php foreach ($galleryImages as $index => $imgUrl): ?>
                        <img 
                            src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                            class="thumb-img <?= $index == 0 ? 'active' : '' ?>"
                            onclick="showImage(<?= $index ?>)"
                            alt="Ảnh <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- CỘT PHẢI: THÔNG TIN SẢN PHẨM -->
        <div class="product-detail-info">

            <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="price-box">
                <?php if (!empty($defaultVariant['old_price'])): ?>
                    <p class="old-price" id="oldPrice">
                        <?= number_format($defaultVariant['old_price'], 0, ',', '.') ?> đ
                    </p>
                <?php else: ?>
                    <p class="old-price" id="oldPrice"></p>
                <?php endif; ?>

                <p class="product-price" id="newPrice">
                    <?= number_format($defaultVariant['new_price'], 0, ',', '.') ?> đ
                </p>
            </div>

            <div class="storage-options">
                <h3>Chọn dung lượng</h3>

                <div class="storage-list">
                    <?php foreach ($variants as $index => $variant): ?>
                        <button 
                            type="button"
                            class="storage-btn <?= $index == 0 ? 'active' : '' ?>"
                            data-variant-id="<?= $variant['id'] ?>"
                            data-storage="<?= htmlspecialchars($variant['storage'], ENT_QUOTES, 'UTF-8') ?>"
                            data-old-price="<?= (int)$variant['old_price'] ?>"
                            data-new-price="<?= (int)$variant['new_price'] ?>"
                            data-stock="<?= (int)$variant['stock'] ?>"
                        >
                            <?= htmlspecialchars($variant['storage'], ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="stock-status" id="stockStatus">
                <?php if ((int)$defaultVariant['stock'] > 0): ?>
                    Còn hàng: <?= (int)$defaultVariant['stock'] ?> sản phẩm
                <?php else: ?>
                    Tạm hết hàng
                <?php endif; ?>
            </div>

            <div class="product-desc">
                <h3>Mô tả sản phẩm</h3>

                <p>
                    <?= nl2br(htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                </p>
            </div>

            <button 
                class="btn-add-cart"
                id="btnAddToCart"
                data-product-id="<?= $product['id'] ?>"
                data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                data-image="<?= htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8') ?>"
                data-variant-id="<?= $defaultVariant['id'] ?>"
                data-storage="<?= htmlspecialchars($defaultVariant['storage'], ENT_QUOTES, 'UTF-8') ?>"
                data-price="<?= $defaultVariant['new_price'] ?>"
                <?= (int)$defaultVariant['stock'] <= 0 ? 'disabled' : '' ?>
            >
                🛒 THÊM VÀO GIỎ
            </button>

        </div>

    </section>
    <section class="detail-extra-grid">
    <?php if (!empty($specs)): ?>
        <div class="spec-card">
            <h2>Thông số kỹ thuật</h2>

            <div class="spec-list">
                <?php foreach ($specs as $spec): ?>
                    <div class="spec-row">
                        <div class="spec-name">
                            <?= h($spec['spec_name']) ?>
                        </div>

                        <div class="spec-value">
                            <?= h($spec['spec_value']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="detail-policy-card">
        <h2>Cam kết tại ABA Mobile</h2>

        <div class="policy-item">
            <span>✅</span>
            <p>Bảo hành rõ ràng, hỗ trợ nhanh chóng.</p>
        </div>

        <div class="policy-item">
            <span>🚚</span>
            <p>Giao hàng toàn quốc, kiểm tra máy trước khi nhận.</p>
        </div>

        <div class="policy-item">
            <span>💳</span>
            <p>Hỗ trợ trả góp linh hoạt.</p>
        </div>

        <div class="policy-item">
            <span>🔄</span>
            <p>Hỗ trợ đổi trả theo chính sách cửa hàng.</p>
        </div>
    </div>
</section>

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

/* =========================
   SLIDER ẢNH SẢN PHẨM
========================= */
const galleryImages = <?= json_encode($galleryImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
let currentImage = 0;

function showImage(index) {
    if (!galleryImages.length) return;

    if (index < 0) {
        index = galleryImages.length - 1;
    }

    if (index >= galleryImages.length) {
        index = 0;
    }

    currentImage = index;

    const mainImage = document.getElementById('mainProductImage');
    const currentIndexEl = document.getElementById('currentImageIndex');
    const thumbs = document.querySelectorAll('.thumb-img');

    if (mainImage) {
        mainImage.src = galleryImages[currentImage];
    }

    if (currentIndexEl) {
        currentIndexEl.textContent = currentImage + 1;
    }

    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentImage);
    });
}

function nextImage() {
    showImage(currentImage + 1);
}

function prevImage() {
    showImage(currentImage - 1);
}

/* =========================
   FORMAT TIỀN
========================= */
function formatMoney(number) {
    return Number(number).toLocaleString('vi-VN') + ' đ';
}

/* =========================
   CHỌN DUNG LƯỢNG
========================= */
const storageButtons = document.querySelectorAll('.storage-btn');
const oldPriceEl = document.getElementById('oldPrice');
const newPriceEl = document.getElementById('newPrice');
const stockStatusEl = document.getElementById('stockStatus');
const btnAddToCart = document.getElementById('btnAddToCart');

storageButtons.forEach(button => {
    button.addEventListener('click', function () {
        storageButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        const variantId = this.dataset.variantId;
        const storage = this.dataset.storage;
        const oldPrice = Number(this.dataset.oldPrice);
        const newPrice = Number(this.dataset.newPrice);
        const stock = Number(this.dataset.stock);

        if (oldPriceEl) {
            oldPriceEl.textContent = oldPrice > 0 ? formatMoney(oldPrice) : '';
        }

        if (newPriceEl) {
            newPriceEl.textContent = formatMoney(newPrice);
        }

        if (stockStatusEl) {
            if (stock > 0) {
                stockStatusEl.textContent = 'Còn hàng: ' + stock + ' sản phẩm';
                stockStatusEl.classList.remove('out-of-stock');
            } else {
                stockStatusEl.textContent = 'Tạm hết hàng';
                stockStatusEl.classList.add('out-of-stock');
            }
        }

        if (btnAddToCart) {
            btnAddToCart.dataset.variantId = variantId;
            btnAddToCart.dataset.storage = storage;
            btnAddToCart.dataset.price = newPrice;
            btnAddToCart.disabled = stock <= 0;
        }
    });
});

/* =========================
   THÊM VÀO GIỎ
========================= */
if (btnAddToCart) {
    btnAddToCart.addEventListener('click', function () {
        const productId = this.dataset.productId;
        const variantId = this.dataset.variantId;
        const name = this.dataset.productName;
        const storage = this.dataset.storage;
        const price = Number(this.dataset.price);
        const image = this.dataset.image;

        if (typeof addToCartVariant === 'function') {
            addToCartVariant(productId, variantId, name, storage, price, image);
        } else {
            let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};

            const cartId = productId + "_" + variantId;
            const fullName = name + " " + storage;

            if (gio[cartId]) {
                gio[cartId].so_luong++;
            } else {
                gio[cartId] = {
                    id: productId,
                    variant_id: variantId,
                    ten: fullName,
                    gia: price,
                    hinh: image,
                    so_luong: 1
                };
            }

            localStorage.setItem("gio_hang", JSON.stringify(gio));

            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }

            alert("Đã thêm vào giỏ hàng!");
        }
    });
}

/* =========================
   CẬP NHẬT BADGE GIỎ HÀNG
========================= */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});

/* =========================
   SEARCH AJAX + ĐÓNG MENU MOBILE
========================= */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }

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
});
</script>

</body>
</html>