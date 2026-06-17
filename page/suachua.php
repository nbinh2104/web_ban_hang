<?php
include('../config/database.php');

mysqli_set_charset($conn, "utf8mb4");

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = isset($_GET['booking']) && $_GET['booking'] === 'success';

$customer_name = "";
$phone = "";
$device_name = "";
$service_id = "";
$issue_description = "";

// Xử lý khi khách gửi form yêu cầu sửa chữa điện thoại
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['repair_submit'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $device_name = trim($_POST['device_name'] ?? '');
    $service_id = trim($_POST['service_id'] ?? '');
    $issue_description = trim($_POST['issue_description'] ?? '');

    if ($customer_name === "") {
        $errors[] = "Vui lòng nhập họ và tên.";
    }

    if ($phone === "") {
        $errors[] = "Vui lòng nhập số điện thoại.";
    }

    if ($device_name === "") {
        $errors[] = "Vui lòng nhập dòng máy cần sửa.";
    }

    if ($service_id === "") {
        $errors[] = "Vui lòng chọn dịch vụ cần sửa.";
    }

    if ($issue_description === "") {
        $errors[] = "Vui lòng mô tả tình trạng lỗi của máy.";
    }

    if (empty($errors)) {
        $service_id_insert = (int)$service_id;

        $sql = "INSERT INTO repair_bookings 
                (customer_name, phone, device_name, service_id, issue_description, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssis",
                $customer_name,
                $phone,
                $device_name,
                $service_id_insert,
                $issue_description
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: suachua.php?booking=success#booking-form");
                exit;
            } else {
                $errors[] = "Không thể gửi yêu cầu. Lỗi: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Lỗi hệ thống. Không thể chuẩn bị câu lệnh SQL.";
        }
    }
}

// Lấy danh sách nhóm dịch vụ sửa chữa cho phần card và dropdown form
$services = [];

$sql_services = "SELECT * FROM repair_services 
                 WHERE is_active = 1 
                 ORDER BY id ASC";

$result_services = mysqli_query($conn, $sql_services);

if ($result_services) {
    while ($row = mysqli_fetch_assoc($result_services)) {
        $services[] = $row;
    }
}

// Lấy danh sách 20 dịch vụ sửa chữa iPhone, Samsung để hiển thị dạng card sản phẩm
$repair_items = [];

$sql_repair_items = "SELECT * FROM repair_items 
                     WHERE is_active = 1 
                     ORDER BY id ASC 
                     LIMIT 20";

$result_repair_items = mysqli_query($conn, $sql_repair_items);

if ($result_repair_items) {
    while ($row = mysqli_fetch_assoc($result_repair_items)) {
        $repair_items[] = $row;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../public/css/style.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="../public/css/suachua.css?v=<?php echo time(); ?>" />
  <title>Sửa chữa điện thoại - ABA Mobile</title>
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
          <li><a href="dienthoai.php">Điện thoại</a></li>
          <li><a href="suachua.php" class="active">Sửa chữa</a></li>
          <li><a href="tincongnghe.php">Tin công nghệ</a></li>
        </ul>
      </nav>

      <div class="header-right">
        <form action="dienthoai.php" method="GET" class="search-form" autocomplete="off">
          <input
            type="text"
            name="q"
            placeholder="Tìm điện thoại..."
            class="search-input"
            value="<?php echo h($_GET['q'] ?? ''); ?>"
          >
          <button type="submit" class="search-btn">🔍</button>
          <div id="search-results" class="search-results"></div>
        </form>

        <a href="cart.html" class="btn-cart-modern">
          🛒 Giỏ hàng
          <span id="cart-badge" class="cart-badge">0</span>
        </a>

        <a href="#" class="icon-action" title="Tài khoản">👤</a>
      </div>
    </div>
  </header>

  <main class="container repair-page">
    <div class="repair-breadcrumb">
      <a href="index.php">Trang chủ</a> &raquo;
      <span>Dịch vụ Sửa chữa</span>
    </div>

    <section class="services-section">
      <div class="service-grid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $service): ?>
            <div class="service-card">
              <div class="service-icon">
                <?php echo h($service['icon']); ?>
              </div>

              <h3><?php echo h($service['service_name']); ?></h3>
              <p><?php echo h($service['description']); ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-message">Chưa có dữ liệu dịch vụ sửa chữa.</p>
        <?php endif; ?>
      </div>
    </section>
        <section id="booking-form" class="booking-section">
      <div class="booking-box">
        <h2>Đặt lịch sửa chữa điện thoại</h2>

        <p>
          Nhập thông tin, ABA Mobile sẽ liên hệ tư vấn và báo giá sửa chữa cho bạn.
        </p>

        <form action="suachua.php#booking-form" method="POST" class="booking-form">
          <?php if ($success): ?>
            <div class="form-alert form-success">
              Gửi yêu cầu sửa chữa thành công! ABA Mobile sẽ liên hệ lại với bạn sớm nhất.
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="form-alert form-error">
              <?php foreach ($errors as $error): ?>
                <div><?php echo h($error); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <input
            type="text"
            name="customer_name"
            placeholder="Họ và tên của bạn"
            value="<?php echo h($customer_name); ?>"
            required
          />

          <input
            type="tel"
            name="phone"
            placeholder="Số điện thoại liên hệ"
            value="<?php echo h($phone); ?>"
            required
          />

          <input
            type="text"
            name="device_name"
            placeholder="Dòng máy cần sửa, VD: iPhone 11, Samsung S21..."
            value="<?php echo h($device_name); ?>"
            required
          />

          <select name="service_id" required>
            <option value="">-- Chọn dịch vụ cần sửa --</option>

            <?php foreach ($services as $service): ?>
              <option
                value="<?php echo (int)$service['id']; ?>"
                <?php echo ((string)($service_id ?? '') === (string)$service['id']) ? 'selected' : ''; ?>
              >
                <?php echo h($service['service_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <textarea
            name="issue_description"
            rows="3"
            placeholder="Mô tả tình trạng lỗi của máy, VD: rơi vỡ màn, chai pin, không sạc..."
            required
          ><?php echo h($issue_description); ?></textarea>

          <button type="submit" name="repair_submit" class="btn-submit">
            GỬI YÊU CẦU SỬA CHỮA
          </button>
        </form>
      </div>
    </section>

    <section class="repair-list-section">
      <div class="repair-product-grid">
        <?php if (!empty($repair_items)): ?>
          <?php foreach ($repair_items as $item): ?>
            <div class="repair-product-card">
              <div class="repair-product-img">
                <img
                  src="../public/images/repair/<?php echo h($item['image']); ?>"
                  alt="<?php echo h($item['title']); ?>"
                  onerror="this.style.display='none'; this.parentElement.classList.add('no-img');"
                />
              </div>

              <h3 class="repair-product-name">
                <?php echo h($item['title']); ?>
              </h3>

              <div class="repair-product-price">
                <?php echo number_format((int)$item['price'], 0, ',', '.'); ?> đ
              </div>

              <a href="#booking-form" class="repair-product-btn">
                ĐẶT SỬA
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-message">Chưa có dịch vụ sửa chữa nào.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="price-section">
      <h2 class="section-title">
        BẢNG GIÁ SỬA CHỮA THAM KHẢO
      </h2>

      <div class="price-table-wrap">
        <table class="repair-price-table">
          <thead>
            <tr>
              <th>Dịch vụ</th>
              <th>Bảo hành</th>
              <th>Giá từ</th>
            </tr>
          </thead>

          <tbody>
            <?php if (!empty($services)): ?>
              <?php foreach ($services as $service): ?>
                <tr>
                  <td><?php echo h($service['service_name']); ?></td>
                  <td><?php echo h($service['warranty']); ?></td>
                  <td class="price-cell">
                    <?php echo number_format((int)$service['price_from'], 0, ',', '.'); ?>đ
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="3" class="table-empty">
                  Chưa có bảng giá sửa chữa.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-content">
      <div class="footer-col">
        <h3>ABA MOBILE</h3>
        <p>
          Hệ thống bán lẻ điện thoại di động chính hãng, uy tín hàng đầu với
          giá cả cạnh tranh.
        </p>
      </div>

      <div class="footer-col">
        <h3>THÔNG TIN LIÊN HỆ</h3>
        <p>📍 Địa chỉ: Hà Nội, Việt Nam</p>
        <p>📞 Điện thoại: 1900 xxxx</p>
        <p>✉️ Email: cskh@abamobile.com</p>
      </div>

      <div class="footer-col">
        <h3>CHÍNH SÁCH</h3>
        <ul>
          <li><a href="#">Chính sách bảo hành</a></li>
          <li><a href="#">Chính sách đổi trả 1-1</a></li>
          <li><a href="#">Hướng dẫn mua trả góp</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 ABA Mobile. All rights reserved.</p>
    </div>
  </footer>

  <div class="floating-contact">
    <a href="tel:1900xxxx" class="contact-item">
      <div class="icon-circle">📞</div>
      <span>Gọi ngay</span>
    </a>

    <a href="https://zalo.me/xxxx" target="_blank" class="contact-item">
      <div class="icon-circle">💬</div>
      <span>Zalo OA</span>
    </a>

    <a href="https://m.me/xxxx" target="_blank" class="contact-item">
      <div class="icon-circle">⚡</div>
      <span>Messenger</span>
    </a>
  </div>
</body>
</html>
