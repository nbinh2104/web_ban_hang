<?php
include('../config/database.php');
require_once('../config/auth.php');
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
   XỬ LÝ ĐÁNH GIÁ SẢN PHẨM
========================= */
$review_error = '';
$review_success = isset($_GET['review']) && $_GET['review'] === 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!is_logged_in()) {
        $review_error = 'Vui lòng đăng nhập để đánh giá sản phẩm.';
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $review_error = 'Vui lòng chọn số sao từ 1 đến 5.';
        } elseif ($comment === '') {
            $review_error = 'Vui lòng nhập nội dung đánh giá.';
        } else {
            $user_id = current_user_id();
            $customer_name = current_user_name();

            $sql_review = "
                INSERT INTO product_reviews
                (product_id, user_id, customer_name, rating, comment, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ";

            $stmt_review = mysqli_prepare($conn, $sql_review);

            if ($stmt_review) {
                mysqli_stmt_bind_param(
                    $stmt_review,
                    "iisis",
                    $id,
                    $user_id,
                    $customer_name,
                    $rating,
                    $comment
                );

                if (mysqli_stmt_execute($stmt_review)) {
                    header("Location: detail.php?id=" . $id . "&review=success#product-reviews");
                    exit;
                } else {
                    $review_error = 'Không thể gửi đánh giá. Vui lòng thử lại.';
                }
            } else {
                $review_error = 'Lỗi hệ thống. Không thể gửi đánh giá.';
            }
        }
    }
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
/* =========================
   LẤY ĐÁNH GIÁ ĐÃ DUYỆT
========================= */
$review_count = 0;
$review_avg = 0;
$approved_reviews = [];

$sql_review_summary = "
    SELECT 
        COUNT(*) AS total_reviews,
        AVG(rating) AS avg_rating
    FROM product_reviews
    WHERE product_id = ?
      AND status = 'approved'
";

$stmt_review_summary = mysqli_prepare($conn, $sql_review_summary);

if ($stmt_review_summary) {
    mysqli_stmt_bind_param($stmt_review_summary, "i", $id);
    mysqli_stmt_execute($stmt_review_summary);

    $summary_result = mysqli_stmt_get_result($stmt_review_summary);
    $summary = mysqli_fetch_assoc($summary_result);

    $review_count = (int)($summary['total_reviews'] ?? 0);
    $review_avg = round((float)($summary['avg_rating'] ?? 0), 1);
}

$sql_reviews = "
    SELECT *
    FROM product_reviews
    WHERE product_id = ?
      AND status = 'approved'
    ORDER BY id DESC
    LIMIT 10
";

$stmt_reviews = mysqli_prepare($conn, $sql_reviews);

if ($stmt_reviews) {
    mysqli_stmt_bind_param($stmt_reviews, "i", $id);
    mysqli_stmt_execute($stmt_reviews);

    $reviews_result = mysqli_stmt_get_result($stmt_reviews);

    while ($review = mysqli_fetch_assoc($reviews_result)) {
        $approved_reviews[] = $review;
    }
}

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

<?php include('components/header.php'); ?>

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
<section id="product-reviews" class="product-reviews-section">

    <div class="review-header">
        <div>
            <h2>Đánh giá sản phẩm</h2>

            <?php if ($review_count > 0): ?>
                <p>
                    <span class="review-score"><?= $review_avg ?>/5</span>
                    <span class="review-stars">
                        <?= str_repeat('★', (int)round($review_avg)) ?>
                        <?= str_repeat('☆', 5 - (int)round($review_avg)) ?>
                    </span>
                    <span class="review-count">
                        Dựa trên <?= $review_count ?> đánh giá
                    </span>
                </p>
            <?php else: ?>
                <p class="review-count">
                    Chưa có đánh giá nào cho sản phẩm này.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($review_success): ?>
        <div class="review-success">
            Cảm ơn bạn đã gửi đánh giá. Đánh giá sẽ hiển thị sau khi admin duyệt.
        </div>
    <?php endif; ?>

    <?php if ($review_error !== ''): ?>
        <div class="review-error">
            <?= h($review_error) ?>
        </div>
    <?php endif; ?>

    <div class="review-layout">

        <div class="review-list">
            <h3>Nhận xét từ khách hàng</h3>

            <?php if (!empty($approved_reviews)): ?>
                <?php foreach ($approved_reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-item-top">
                            <strong><?= h($review['customer_name']) ?></strong>

                            <span class="review-stars">
                                <?= str_repeat('★', (int)$review['rating']) ?>
                                <?= str_repeat('☆', 5 - (int)$review['rating']) ?>
                            </span>
                        </div>

                        <p>
                            <?= nl2br(h($review['comment'])) ?>
                        </p>

                        <small>
                            <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="review-empty">
                    Chưa có nhận xét nào được hiển thị.
                </div>
            <?php endif; ?>
        </div>

        <div class="review-form-box">
            <h3>Gửi đánh giá của bạn</h3>

            <?php if (is_logged_in()): ?>
                <form method="POST" action="detail.php?id=<?= (int)$id ?>#product-reviews">
                    <input type="hidden" name="review_submit" value="1">

                    <div class="review-form-group">
                        <label>Chọn số sao</label>

                        <select name="rating" required>
                            <option value="">-- Chọn đánh giá --</option>
                            <option value="5">★★★★★ - Rất hài lòng</option>
                            <option value="4">★★★★☆ - Hài lòng</option>
                            <option value="3">★★★☆☆ - Bình thường</option>
                            <option value="2">★★☆☆☆ - Chưa hài lòng</option>
                            <option value="1">★☆☆☆☆ - Không hài lòng</option>
                        </select>
                    </div>

                    <div class="review-form-group">
                        <label>Nội dung đánh giá</label>

                        <textarea 
                            name="comment" 
                            rows="5" 
                            placeholder="Nhập cảm nhận của bạn về sản phẩm..."
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-review-submit">
                        Gửi đánh giá
                    </button>
                </form>
            <?php else: ?>
                <div class="review-login-note">
                    Vui lòng 
                    <a href="login.php">đăng nhập</a> 
                    để đánh giá sản phẩm.
                </div>
            <?php endif; ?>
        </div>

    </div>

</section>

</main>

<div id="toast"></div>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

<script>
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
</script>

</body>
</html>