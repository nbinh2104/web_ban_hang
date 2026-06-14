<?php
// ===========================
// index.php - Trang chủ
// Đổ dữ liệu từ database ra trang chủ
// Hiển thị danh sách sản phẩm theo danh mục
// Có ô tìm kiếm theo tên
// ===========================
include 'config.php';

// Lấy danh sách danh mục (hãng) để hiển thị menu
$catQuery = $conn->query("SELECT DISTINCT danh_muc FROM san_pham ORDER BY danh_muc");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Trang bán điện thoại</title>
<style>
  body{font-family:Arial,sans-serif;margin:0;background:#f5f5f5;color:#333;}
  header{background:#2c3e50;color:#fff;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;}
  header h1{margin:0;font-size:22px;}
  form.search{display:flex;}
  form.search input{padding:8px;border:none;border-radius:4px 0 0 4px;width:220px;}
  form.search button{padding:8px 15px;border:none;background:#e67e22;color:#fff;border-radius:0 4px 4px 0;cursor:pointer;}
  nav{background:#34495e;padding:10px 30px;}
  nav a{color:#fff;text-decoration:none;margin-right:20px;font-size:14px;}
  nav a:hover{text-decoration:underline;}
  .container{padding:20px 30px;}
  .category-block{margin-bottom:35px;}
  .category-block h2{border-left:5px solid #e67e22;padding-left:10px;margin-bottom:15px;}
  .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;}
  .product-card{background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;text-align:center;transition:0.2s;}
  .product-card:hover{box-shadow:0 4px 10px rgba(0,0,0,0.1);}
  .product-card img{max-width:100%;border-radius:4px;}
  .product-card h3{font-size:15px;margin:10px 0 5px;}
  .product-card .price{color:#e67e22;font-weight:bold;}
  .product-card a{display:inline-block;margin-top:8px;padding:6px 12px;background:#2c3e50;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;}
  .empty{color:#999;font-style:italic;}
</style>
</head>
<body>

<header>
  <h1>📱 PhoneShop</h1>
  <form class="search" method="GET" action="index.php">
    <input type="text" name="q" placeholder="Tìm sản phẩm theo tên..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    <button type="submit">Tìm</button>
  </form>
</header>

<nav>
  <a href="index.php">Tất cả</a>
  <?php
  // Hiển thị menu danh mục
  while ($cat = $catQuery->fetch_assoc()) {
      echo '<a href="index.php?danh_muc=' . urlencode($cat['danh_muc']) . '">' . htmlspecialchars($cat['danh_muc']) . '</a>';
  }
  ?>
</nav>

<div class="container">

<?php
// ===========================
// XỬ LÝ TÌM KIẾM THEO TÊN
// ===========================
if (isset($_GET['q']) && trim($_GET['q']) != '') {
    $keyword = "%" . $conn->real_escape_string($_GET['q']) . "%";
    $sql = "SELECT * FROM san_pham WHERE ten_sp LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<div class="category-block"><h2>Kết quả tìm kiếm cho: "' . htmlspecialchars($_GET['q']) . '"</h2>';
    echo '<div class="product-grid">';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            renderProductCard($row);
        }
    } else {
        echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
    }
    echo '</div></div>';
}

// ===========================
// LỌC THEO DANH MỤC (NẾU CÓ)
// ===========================
elseif (isset($_GET['danh_muc']) && trim($_GET['danh_muc']) != '') {
    $danhmuc = $conn->real_escape_string($_GET['danh_muc']);
    $sql = "SELECT * FROM san_pham WHERE danh_muc = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $danhmuc);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<div class="category-block"><h2>' . htmlspecialchars($danhmuc) . '</h2>';
    echo '<div class="product-grid">';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            renderProductCard($row);
        }
    } else {
        echo '<p class="empty">Chưa có sản phẩm trong danh mục này.</p>';
    }
    echo '</div></div>';
}

// ===========================
// HIỂN THỊ TẤT CẢ - GOM NHÓM THEO DANH MỤC
// ===========================
else {
    $catList = $conn->query("SELECT DISTINCT danh_muc FROM san_pham ORDER BY danh_muc");
    while ($cat = $catList->fetch_assoc()) {
        $danhmuc = $cat['danh_muc'];
        echo '<div class="category-block"><h2>' . htmlspecialchars($danhmuc) . '</h2>';
        echo '<div class="product-grid">';

        $stmt = $conn->prepare("SELECT * FROM san_pham WHERE danh_muc = ?");
        $stmt->bind_param("s", $danhmuc);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            renderProductCard($row);
        }

        echo '</div></div>';
    }
}

// ===========================
// HÀM HIỂN THỊ 1 SẢN PHẨM (THẺ)
// ===========================
function renderProductCard($row) {
    echo '<div class="product-card">';
    echo '<img src="' . htmlspecialchars($row['hinh_anh']) . '" alt="' . htmlspecialchars($row['ten_sp']) . '">';
    echo '<h3>' . htmlspecialchars($row['ten_sp']) . '</h3>';
    echo '<p class="price">' . number_format($row['gia'], 0, ',', '.') . ' đ</p>';
    echo '<a href="chi_tiet.php?id=' . $row['id_sp'] . '">Xem chi tiết</a>';
    echo '</div>';
}
?>

</div>
</body>
</html>