<?php
include('../config/database.php');
require_once('../config/auth.php');
require_once('../config/send_order_mail.php');

mysqli_set_charset($conn, "utf8mb4");

$checkout_error = '';

/* ================================
   XỬ LÝ ĐẶT HÀNG
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = is_logged_in() ? (int)current_user_id() : null;

    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cod';
    $cart_data = isset($_POST['cart_data']) ? $_POST['cart_data'] : '';

    if ($customer_name == '' || $phone == '' || $email == '' || $address == '') {
        $checkout_error = 'Vui lòng nhập đầy đủ thông tin giao hàng.';
    } elseif ($cart_data == '') {
        $checkout_error = 'Giỏ hàng đang trống.';
    } else {
        $cart = json_decode($cart_data, true);

        if (!$cart || !is_array($cart) || count($cart) == 0) {
            $checkout_error = 'Dữ liệu giỏ hàng không hợp lệ.';
        } else {
            mysqli_begin_transaction($conn);

            try {
                $total_amount = 0;
                $order_items = [];

                foreach ($cart as $cart_key => $item) {
    $product_id = isset($item['id']) ? (int)$item['id'] : (int)$cart_key;
    $variant_id = isset($item['variant_id']) ? (int)$item['variant_id'] : 0;
    $quantity = isset($item['so_luong']) ? (int)$item['so_luong'] : 0;

    if ($product_id <= 0 || $variant_id <= 0 || $quantity <= 0) {
        continue;
    }

    $sql_product = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        v.id AS variant_id,
        v.storage,
        v.new_price
    FROM products p
    JOIN product_variants v ON v.product_id = p.id
    WHERE p.id = ? AND v.id = ?
    LIMIT 1
";

    $stmt_product = mysqli_prepare($conn, $sql_product);

    if (!$stmt_product) {
        throw new Exception("Lỗi SQL sản phẩm: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_product, "ii", $product_id, $variant_id);
    mysqli_stmt_execute($stmt_product);

    $product_result = mysqli_stmt_get_result($stmt_product);
    $product = mysqli_fetch_assoc($product_result);

    if (!$product) {
        continue;
    }

    $price = (int)$product['new_price'];

    // Dự phòng: nếu database bị thiếu giá thì lấy giá đang lưu trong giỏ
    if ($price <= 0 && isset($item['gia'])) {
        $price = (int)$item['gia'];
    }

    if ($price <= 0) {
        continue;
    }

    $subtotal = $price * $quantity;
    $total_amount += $subtotal;

$storage = trim($product['storage'] ?? '');
$display_name = trim($product['name'] . ($storage != '' ? ' ' . $storage : ''));

$order_items[] = [
    'product_id' => $product['id'],
    'variant_id' => $product['variant_id'],
    'variant_storage' => $storage,
    'product_name' => $display_name,
    'product_image' => $product['image_url'],
    'price' => $price,
    'quantity' => $quantity,
    'subtotal' => $subtotal
];
}

                if (empty($order_items)) {
                    throw new Exception("Không có sản phẩm hợp lệ trong giỏ hàng.");
                }

                $sql_order = "INSERT INTO orders 
                (user_id, customer_name, phone, email, address, note, payment_method, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

                $stmt_order = mysqli_prepare($conn, $sql_order);

                if (!$stmt_order) {
                    throw new Exception("Lỗi SQL đơn hàng: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param(
                    $stmt_order,
                    "issssssi",
                    $user_id,
                    $customer_name,
                    $phone,
                    $email,
                    $address,
                    $note,
                    $payment_method,
                    $total_amount
                );

                mysqli_stmt_execute($stmt_order);

                $order_id = mysqli_insert_id($conn);

$sql_item = "INSERT INTO order_items 
    (order_id, product_id, variant_id, product_name, variant_storage, product_image, price, quantity, subtotal)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_item = mysqli_prepare($conn, $sql_item);

                if (!$stmt_item) {
                    throw new Exception("Lỗi SQL chi tiết đơn hàng: " . mysqli_error($conn));
                }

                foreach ($order_items as $order_item) {
mysqli_stmt_bind_param(
    $stmt_item,
    "iiisssiii",
    $order_id,
    $order_item['product_id'],
    $order_item['variant_id'],
    $order_item['product_name'],
    $order_item['variant_storage'],
    $order_item['product_image'],
    $order_item['price'],
    $order_item['quantity'],
    $order_item['subtotal']
);

                    mysqli_stmt_execute($stmt_item);
                }

                mysqli_commit($conn);
                sendOrderSuccessMail($conn, $order_id);

                header("Location: order_success.php?order_id=" . $order_id);
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $checkout_error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Thanh toán - ABA Mobile</title>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../public/css/checkout.css?v=<?= time(); ?>">
</head>

<body>

<?php include('components/header.php'); ?>

<section class="checkout-steps">
    <div class="step done">
        <div class="step-dot">✓</div>
        <span class="step-label">Giỏ hàng</span>
    </div>

    <div class="step-line done"></div>

    <div class="step active">
        <div class="step-dot">2</div>
        <span class="step-label">Thanh toán</span>
    </div>

    <div class="step-line"></div>

    <div class="step">
        <div class="step-dot">3</div>
        <span class="step-label">Hoàn tất</span>
    </div>
</section>

<main class="container checkout-main">

    <section class="form-card">

        <div class="form-section-title">
            📦 Thông tin giao hàng
        </div>

        <?php if ($checkout_error != ''): ?>
            <div class="checkout-error">
                <?= htmlspecialchars($checkout_error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form id="checkout-form" method="POST" action="checkout.php" onsubmit="return submitOrder();">

            <div class="form-row">
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input 
                        type="text" 
                        name="customer_name" 
                        id="f-ten" 
                        placeholder="Nguyễn Văn A" 
                        required 
                    />
                </div>

                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="f-dienthoai" 
                        placeholder="0901 234 567" 
                        required 
                    />
                </div>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input 
                    type="email" 
                    name="email" 
                    id="f-email" 
                    placeholder="example@email.com" 
                    required 
                />
            </div>

            <div class="form-group">
                <label>Địa chỉ giao hàng *</label>
                <input 
                    type="text" 
                    name="address" 
                    id="f-diachi" 
                    placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành" 
                    required 
                />
            </div>

            <div class="form-group">
                <label>Ghi chú</label>
                <textarea 
                    name="note" 
                    id="f-ghichu" 
                    rows="3" 
                    placeholder="Ví dụ: Giao giờ hành chính..."
                ></textarea>
            </div>

            <div class="form-section-title payment-title">
                💳 Phương thức thanh toán
            </div>

            <div class="payment-options">

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cod" checked />
                    <span class="pay-icon">💵</span>

                    <div>
                        <div class="pay-label">Thanh toán khi nhận hàng (COD)</div>
                        <div class="pay-sub">Trả tiền mặt khi nhận hàng</div>
                    </div>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="bank" />
                    <span class="pay-icon">🏦</span>

                    <div>
                        <div class="pay-label">Chuyển khoản ngân hàng</div>
                        <div class="pay-sub">Thông tin tài khoản gửi qua email</div>
                    </div>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="momo" />
                    <span class="pay-icon">💜</span>

                    <div>
                        <div class="pay-label">Ví MoMo</div>
                        <div class="pay-sub">Thanh toán qua ví điện tử MoMo</div>
                    </div>
                </label>

            </div>

            <input type="hidden" name="cart_data" id="cart_data">

            <button type="submit" id="hidden-submit" class="hidden-submit"></button>

        </form>

    </section>

    <aside class="order-summary">

        <div class="summary-title">
            🧾 Đơn hàng
        </div>

        <div class="order-items" id="order-items-list">
            <div class="empty-warning">Đang tải...</div>
        </div>

        <hr class="order-divider" />

        <div class="order-row">
            <span>Tạm tính</span>
            <span id="sub-total">0 đ</span>
        </div>

        <div class="order-row">
            <span>Phí giao hàng</span>
            <span class="free-shipping">Miễn phí</span>
        </div>

        <div class="order-row total">
            <span>Tổng cộng</span>
            <span id="grand-total">0 đ</span>
        </div>

        <button class="btn-order" onclick="document.getElementById('hidden-submit').click()">
            ĐẶT HÀNG NGAY →
        </button>

        <div class="secure-note">
            🔒 Thông tin được bảo mật an toàn
        </div>

    </aside>

</main>

<div id="toast"></div>

<script src="../public/js/cart.js?v=<?= time(); ?>"></script>

<script>
/* =========================================
   MỞ / ĐÓNG MENU MOBILE
========================================= */

function hienThiOrderSummary() {
    let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
    let list = document.getElementById("order-items-list");
    let tong = 0;

    if (!list) return;

    list.innerHTML = "";

    const keys = Object.keys(gio);

    if (keys.length === 0) {
        list.innerHTML = `
            <div class="empty-warning">
                Giỏ hàng trống. 
                <a href="dienthoai.php" class="empty-link">Mua sắm ngay</a>
            </div>
        `;
        return;
    }

    keys.forEach((id) => {
        let sp = gio[id];
        let thanhTien = Number(sp.gia || 0) * Number(sp.so_luong || 0);
        tong += thanhTien;

        list.innerHTML += `
            <div class="order-item">
                <img 
                    src="${sp.hinh}" 
                    alt="${sp.ten}" 
                    onerror="this.src='https://placehold.co/48x48/e8f0fe/1a73e8?text=📱'"
                >

                <div class="order-item-info">
                    <div class="order-item-name">${sp.ten}</div>
                    <div class="order-item-qty">x${sp.so_luong}</div>
                </div>

                <div class="order-item-price">
                    ${thanhTien.toLocaleString("vi-VN")} đ
                </div>
            </div>
        `;
    });

    document.getElementById("sub-total").innerText =
        tong.toLocaleString("vi-VN") + " đ";

    document.getElementById("grand-total").innerText =
        tong.toLocaleString("vi-VN") + " đ";
}

/* =========================================
   GỬI ĐƠN HÀNG
========================================= */
function submitOrder() {
    let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};

    if (Object.keys(gio).length === 0) {
        alert("Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng.");
        return false;
    }

    document.getElementById("cart_data").value = JSON.stringify(gio);

    return true;
}

</script>

</body>
</html>