<?php
// Bật hiển thị lỗi để dễ fix (tùy chọn)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/database.php");

// Lấy ID từ URL, nếu không có thì mặc định là 1
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Gọi đúng tên bảng là "products"
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);

$sp = mysqli_fetch_assoc($result);

// Nếu gõ ID bậy bạ trên URL thì báo lỗi
if (!$sp) {
    die("Không tìm thấy sản phẩm này trên hệ thống!");
}
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/detail.css" />
    <title><?= $sp['name']; ?> - ABA Mobile</title>
  </head>
  <body>
    <header class="modern-header">
      <div class="container header-inner">
        <div class="header-left">
          <a href="tel:1900xxxx" class="btn-phone-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </a>
          <a href="index.php" class="modern-logo">ABA Mobile<span class="dot">.</span></a>
        </div>
        <nav class="header-center">
          <ul class="modern-menu">
            <li><a href="index.php">Trang chủ</a></li>
            <li class="has-dropdown">
              <a href="dienthoai.php" class="active" style="display: flex; align-items: center; gap: 5px">Điện thoại
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
              </a>
            </li>
            <li><a href="suachua.html">Sửa chữa</a></li>
            <li><a href="news.html">Tin công nghệ</a></li>
          </ul>
        </nav>
        <div class="header-right">
          <a href="cart.html" class="btn-cart-modern">Giỏ hàng</a>
        </div>
      </div>
    </header>

    <main class="container" style="padding: 30px 0">
      <div class="breadcrumb" style="margin-bottom: 30px">
        <a href="index.php">Trang chủ</a> &raquo;
        <a href="dienthoai.php">Điện thoại</a> &raquo;
        <span style="color: #00a8ff">
            <?= $sp['name']; ?>
        </span>
      </div>

      <div class="product-top-section">
        
        <div class="product-gallery">
          <div class="main-image-box">
            <span class="badge-sale-detail">-15%</span>
            <img id="main-image" src="<?= $sp['image_url']; ?>" alt="<?= $sp['name']; ?>" />
          </div>
          <div class="thumbnail-list">
            <div class="thumb-item active" onclick="changeImage('<?= $sp['image_url']; ?>', this)">
              <img src="<?= $sp['image_url']; ?>" alt="Thumb 1" />
            </div>
            <div class="thumb-item" onclick="changeImage('https://placehold.co/600x600/1a2235/e2e8f0?text=Goc+Nghieng', this)">
              <img src="https://placehold.co/100x100/1a2235/e2e8f0?text=2" alt="Thumb 2" />
            </div>
            <div class="thumb-item" onclick="changeImage('https://placehold.co/600x600/1a2235/e2e8f0?text=Mat+Lung', this)">
              <img src="https://placehold.co/100x100/1a2235/e2e8f0?text=3" alt="Thumb 3" />
            </div>
            <div class="thumb-item" onclick="changeImage('https://placehold.co/600x600/1a2235/e2e8f0?text=Phu+Kien', this)">
              <img src="https://placehold.co/100x100/1a2235/e2e8f0?text=4" alt="Thumb 4" />
            </div>
          </div>
        </div>

        <div class="product-info-detail">
          <h1 class="detail-title"><?= $sp['name']; ?></h1>

          <div class="detail-rating">
            ⭐⭐⭐⭐⭐ <span class="review-count">(1.2k đánh giá)</span>
          </div>

          <div class="detail-price-box">
            <span class="detail-current-price">
                  <?= number_format($sp['new_price'], 0, ',', '.'); ?> đ
            </span>
            <span class="detail-old-price">
                  <?= number_format($sp['old_price'], 0, ',', '.'); ?> đ
            </span>
          </div>

          <div class="option-group">
            <h4>Chọn dung lượng:</h4>
            <div class="option-list">
              <label class="option-btn active"><input type="radio" name="storage" checked /> 256GB</label>
              <label class="option-btn"><input type="radio" name="storage" /> 512GB</label>
              <label class="option-btn"><input type="radio" name="storage" /> 1TB</label>
            </div>
          </div>

          <div class="option-group">
            <h4>Chọn màu sắc:</h4>
            <div class="option-list">
              <label class="option-btn active"><input type="radio" name="color" checked /> Titan Tự Nhiên</label>
              <label class="option-btn"><input type="radio" name="color" /> Titan Xanh</label>
              <label class="option-btn"><input type="radio" name="color" /> Titan Đen</label>
            </div>
          </div>

          <div class="promo-box">
            <h4 class="promo-title">🎁 ƯU ĐÃI KHI MUA HÀNG</h4>
            <ul class="promo-list">
              <li>✔️ Tặng củ sạc nhanh 20W chính hãng.</li>
              <li>✔️ Giảm thêm 5% tối đa 500k khi thanh toán qua VNPAY.</li>
              <li>✔️ Hỗ trợ thu cũ đổi mới trợ giá lên đến 2 triệu đồng.</li>
            </ul>
          </div>

          <div class="detail-actions">
            <button class="btn-buy-now">
              MUA NGAY <br /><span>(Giao tận nơi hoặc nhận tại cửa hàng)</span>
            </button>
            <button class="btn-add-to-cart-large">🛒 THÊM VÀO GIỎ</button>
          </div>
        </div>
      </div>

      <div class="product-bottom-section">
        
        <div class="product-article">
          <h2 style="color: #fff; margin-bottom: 20px; border-bottom: 2px solid #00a8ff; display: inline-block; padding-bottom: 5px;">
            Đặc điểm nổi bật
          </h2>
          <p>
            <?= $sp['description']; ?>
          </p>
          <img src="https://placehold.co/800x400/1a2235/e2e8f0?text=Khuyen+Mai" alt="Tính năng" style="width: 100%; border-radius: 8px; margin: 20px 0" />
        </div>

        <div class="product-specs">
          <h2 style="color: #fff; margin-bottom: 20px; border-bottom: 2px solid #00a8ff; display: inline-block; padding-bottom: 5px;">
            Thông số kỹ thuật
          </h2>
          <table class="spec-table">
            <tr>
              <td>Màn hình:</td>
              <td><?= isset($sp['man_hinh']) ? $sp['man_hinh'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Hệ điều hành:</td>
              <td><?= isset($sp['he_dieu_hanh']) ? $sp['he_dieu_hanh'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Camera sau:</td>
              <td><?= isset($sp['camera_sau']) ? $sp['camera_sau'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Camera trước:</td>
              <td><?= isset($sp['camera_truoc']) ? $sp['camera_truoc'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Chip (CPU):</td>
              <td><?= isset($sp['cpu']) ? $sp['cpu'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>RAM:</td>
              <td><?= isset($sp['ram']) ? $sp['ram'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Dung lượng:</td>
              <td><?= isset($sp['dung_luong']) ? $sp['dung_luong'] : 'Đang cập nhật...'; ?></td>
            </tr>
            <tr>
              <td>Pin:</td>
              <td><?= isset($sp['pin']) ? $sp['pin'] : 'Đang cập nhật...'; ?></td>
            </tr>
          </table>
        </div>
      </div>
    </main>

    <footer>
      <div style="background-color: #0b0f19; padding: 15px 0; text-align: center; border-top: 1px solid #1f2937;">
        <p style="color: #64748b; font-size: 14px; margin: 0">
          © 2026 ABA Mobile. All rights reserved.
        </p>
      </div>
    </footer>

    <script>
      function changeImage(imageUrl, thumbElement) {
        document.getElementById("main-image").src = imageUrl;
        let thumbs = document.getElementsByClassName("thumb-item");
        for (let i = 0; i < thumbs.length; i++) {
          thumbs[i].classList.remove("active");
        }
        thumbElement.classList.add("active");
      }
    </script>
  </body>
</html>