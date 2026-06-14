<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Sản phẩm không hợp lệ.");
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM san_pham WHERE id_sp = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Không tìm thấy sản phẩm.");
}

$sp = $result->fetch_assoc();
?>

<!DOCTYPE html>

<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($sp['ten_sp']) ?></title>

<style>

body{
    font-family:Arial,sans-serif;
    margin:0;
    background:#f5f5f5;
}

header{
    background:#2c3e50;
    color:white;
    padding:15px 30px;
}

header a{
    color:white;
    text-decoration:none;
}

.container{
    max-width:1200px;
    margin:30px auto;
    background:white;
    padding:25px;
    border-radius:10px;

    display:flex;
    gap:40px;
    flex-wrap:wrap;
}

.product-image{
    flex:1;
    min-width:300px;
}

.product-image img{
    width:100%;
    max-width:450px;
    border-radius:10px;
}

.product-info{
    flex:1;
    min-width:320px;
}

.price{
    color:#e74c3c;
    font-size:32px;
    font-weight:bold;
    margin:15px 0;
}

.stock{
    margin:10px 0;
    font-weight:bold;
}

.in-stock{
    color:green;
}

.out-stock{
    color:red;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

table td{
    padding:10px;
    border-bottom:1px solid #eee;
}

table td:first-child{
    width:160px;
    color:#666;
}

.description{
    margin-top:20px;
    line-height:1.6;
}

.btn{
    border:none;
    padding:12px 25px;
    border-radius:5px;
    cursor:pointer;
    margin-top:20px;
}

.btn-back{
    background:#2c3e50;
    color:white;
    text-decoration:none;
    display:inline-block;
}

.btn-buy{
    background:#e67e22;
    color:white;
}

</style>

</head>

<body>

<header>
    <a href="index.php">← Quay lại trang chủ</a>
</header>

<div class="container">

```
<div class="product-image">

    <img
        src="<?= htmlspecialchars($sp['hinh_anh']) ?>"
        alt="<?= htmlspecialchars($sp['ten_sp']) ?>"
        onerror="this.src='https://placehold.co/500x500?text=No+Image'"
    >

</div>

<div class="product-info">

    <h1><?= htmlspecialchars($sp['ten_sp']) ?></h1>

    <div class="price">
        <?= number_format($sp['gia'],0,",",".") ?> đ
    </div>

    <?php if($sp['so_luong_kho'] > 0): ?>

        <div class="stock in-stock">
            ✓ Còn hàng (<?= $sp['so_luong_kho'] ?> sản phẩm)
        </div>

    <?php else: ?>

        <div class="stock out-stock">
            ✗ Hết hàng
        </div>

    <?php endif; ?>

    <table>

        <tr>
            <td>Hãng</td>
            <td><?= htmlspecialchars($sp['hang_sx']) ?></td>
        </tr>

        <tr>
            <td>Danh mục</td>
            <td><?= htmlspecialchars($sp['danh_muc']) ?></td>
        </tr>

        <tr>
            <td>RAM</td>
            <td><?= htmlspecialchars($sp['ram'] ?? '') ?></td>
        </tr>

        <tr>
            <td>Dung lượng</td>
            <td><?= htmlspecialchars($sp['dung_luong']) ?></td>
        </tr>

        <tr>
            <td>Màn hình</td>
            <td><?= htmlspecialchars($sp['man_hinh'] ?? '') ?></td>
        </tr>

        <tr>
            <td>Camera</td>
            <td><?= htmlspecialchars($sp['camera'] ?? '') ?></td>
        </tr>

        <tr>
            <td>Pin</td>
            <td><?= htmlspecialchars($sp['pin'] ?? '') ?></td>
        </tr>

        <tr>
            <td>Hệ điều hành</td>
            <td><?= htmlspecialchars($sp['he_dieu_hanh'] ?? '') ?></td>
        </tr>

        <tr>
            <td>Màu sắc</td>
            <td><?= htmlspecialchars($sp['mau_sac']) ?></td>
        </tr>

    </table>

    <div class="description">
        <h3>Mô tả sản phẩm</h3>
        <?= nl2br(htmlspecialchars($sp['mo_ta'])) ?>
    </div>

    <a href="index.php" class="btn btn-back">
        Quay lại
    </a>

    <button
        class="btn btn-buy"
        onclick="addToCart(
            '<?= $sp['id_sp'] ?>',
            '<?= htmlspecialchars($sp['ten_sp']) ?>',
            <?= $sp['gia'] ?>,
            '<?= htmlspecialchars($sp['hinh_anh']) ?>'
        )"
    >
        🛒 Thêm vào giỏ hàng
    </button>

</div>
```

</div>

<script src="js/cart.js"></script>

</body>
</html>
