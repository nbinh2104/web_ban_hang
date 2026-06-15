<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

if (!isset($_GET['q']) || trim($_GET['q']) == '') {
    exit;
}

$q = trim($_GET['q']);
$search = "%" . $q . "%";

$sql = "SELECT id, name, image_url, new_price 
        FROM products 
        WHERE name LIKE ? 
        ORDER BY id DESC 
        LIMIT 5";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $search);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <a href="detail.php?id=<?= $row['id'] ?>" class="search-item">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <div>
                <span><?= htmlspecialchars($row['name']) ?></span>
                <strong><?= number_format($row['new_price'], 0, ',', '.') ?> đ</strong>
            </div>
        </a>
        <?php
    }
} else {
    echo '<div class="search-empty">Không tìm thấy sản phẩm</div>';
}
?>