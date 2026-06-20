<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['q']) || trim($_GET['q']) == '') {
    exit;
}

$q = trim($_GET['q']);
$search = "%" . $q . "%";

$hasResult = false;

/* =========================================
   1. TÌM SẢN PHẨM ĐIỆN THOẠI
========================================= */
$sql_product = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        v.new_price
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
    WHERE p.name LIKE ?
    ORDER BY p.id DESC
    LIMIT 5
";

$stmt_product = mysqli_prepare($conn, $sql_product);

if ($stmt_product) {
    mysqli_stmt_bind_param($stmt_product, "s", $search);
    mysqli_stmt_execute($stmt_product);
    $result_product = mysqli_stmt_get_result($stmt_product);

    if ($result_product && mysqli_num_rows($result_product) > 0) {
        $hasResult = true;

        echo '<div class="search-group-title">Sản phẩm</div>';

        while ($row = mysqli_fetch_assoc($result_product)) {
            ?>
            <a href="detail.php?id=<?= (int)$row['id'] ?>" class="search-item">
                <img 
                    src="<?= h($row['image_url']) ?>" 
                    alt="<?= h($row['name']) ?>"
                >

                <div>
                    <span><?= h($row['name']) ?></span>
                    <strong><?= number_format((int)$row['new_price'], 0, ',', '.') ?> đ</strong>
                </div>
            </a>
            <?php
        }
    }

    mysqli_stmt_close($stmt_product);
}

/* =========================================
   2. TÌM DỊCH VỤ SỬA CHỮA THEO repair_items
========================================= */
$sql_repair_item = "
    SELECT id, title, image, price
    FROM repair_items
    WHERE is_active = 1 
      AND title LIKE ?
    ORDER BY id DESC
    LIMIT 5
";

$stmt_repair_item = mysqli_prepare($conn, $sql_repair_item);

if ($stmt_repair_item) {
    mysqli_stmt_bind_param($stmt_repair_item, "s", $search);
    mysqli_stmt_execute($stmt_repair_item);
    $result_repair_item = mysqli_stmt_get_result($stmt_repair_item);

    if ($result_repair_item && mysqli_num_rows($result_repair_item) > 0) {
        $hasResult = true;

        echo '<div class="search-group-title">Dịch vụ sửa chữa</div>';

        while ($row = mysqli_fetch_assoc($result_repair_item)) {
            $image = '../public/images/repair/' . ($row['image'] ?? '');
            ?>
            <a href="suachua.php#booking-form" class="search-item">
                <img 
                    src="<?= h($image) ?>" 
                    alt="<?= h($row['title']) ?>"
                    onerror="this.style.display='none';"
                >

                <div>
                    <span><?= h($row['title']) ?></span>
                    <strong>
                        <?= ((int)$row['price'] > 0) 
                            ? number_format((int)$row['price'], 0, ',', '.') . ' đ' 
                            : 'Liên hệ' 
                        ?>
                    </strong>
                </div>
            </a>
            <?php
        }
    }

    mysqli_stmt_close($stmt_repair_item);
}

/* =========================================
   3. TÌM NHÓM DỊCH VỤ THEO repair_services
========================================= */
$sql_repair_service = "
    SELECT id, service_name, icon, price_from
    FROM repair_services
    WHERE is_active = 1 
      AND service_name LIKE ?
    ORDER BY id ASC
    LIMIT 5
";

$stmt_repair_service = mysqli_prepare($conn, $sql_repair_service);

if ($stmt_repair_service) {
    mysqli_stmt_bind_param($stmt_repair_service, "s", $search);
    mysqli_stmt_execute($stmt_repair_service);
    $result_repair_service = mysqli_stmt_get_result($stmt_repair_service);

    if ($result_repair_service && mysqli_num_rows($result_repair_service) > 0) {
        $hasResult = true;

        echo '<div class="search-group-title">Nhóm dịch vụ</div>';

        while ($row = mysqli_fetch_assoc($result_repair_service)) {
            ?>
            <a href="suachua.php#booking-form" class="search-item">
                <div class="search-service-icon">
                    <?= h($row['icon'] ?: '🛠️') ?>
                </div>

                <div>
                    <span><?= h($row['service_name']) ?></span>
                    <strong>
                        <?= ((int)$row['price_from'] > 0) 
                            ? 'Từ ' . number_format((int)$row['price_from'], 0, ',', '.') . ' đ' 
                            : 'Liên hệ' 
                        ?>
                    </strong>
                </div>
            </a>
            <?php
        }
    }

    mysqli_stmt_close($stmt_repair_service);
}

if (!$hasResult) {
    echo '<div class="search-empty">Không tìm thấy sản phẩm hoặc dịch vụ phù hợp</div>';
}
?>