<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'dashboard';

/* ===============================
   HÀM LẤY 1 GIÁ TRỊ
================================ */
function get_scalar($conn, $sql, $types = "", ...$params) {
    if ($types !== "") {
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return 0;
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $sql);
    }

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int)($row['value'] ?? 0);
}

/* ===============================
   THỐNG KÊ CHÍNH
================================ */
$total_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders");

$pending_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders WHERE status = 'pending'");

$confirmed_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders WHERE status = 'confirmed'");

$shipping_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders WHERE status = 'shipping'");

$completed_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders WHERE status = 'completed'");

$cancelled_orders = get_scalar($conn, "SELECT COUNT(*) AS value FROM orders WHERE status = 'cancelled'");

$total_revenue = get_scalar($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS value
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
");

$today_revenue = get_scalar($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS value
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
      AND DATE(created_at) = CURDATE()
");

$month_revenue = get_scalar($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS value
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
      AND YEAR(created_at) = YEAR(CURDATE())
      AND MONTH(created_at) = MONTH(CURDATE())
");

$today_orders = get_scalar($conn, "
    SELECT COUNT(*) AS value
    FROM orders
    WHERE DATE(created_at) = CURDATE()
");

$total_products = get_scalar($conn, "SELECT COUNT(*) AS value FROM products");

$total_users = get_scalar($conn, "SELECT COUNT(*) AS value FROM users");

$total_repair_requests = get_scalar($conn, "SELECT COUNT(*) AS value FROM repair_requests");

$pending_repair_requests = get_scalar($conn, "SELECT COUNT(*) AS value FROM repair_requests WHERE status = 'pending'");

/* ===============================
   DOANH THU 7 NGÀY GẦN NHẤT
================================ */
$chart_start = date('Y-m-d', strtotime('-6 days'));
$chart_end = date('Y-m-d');

$days = [];
$revenues_by_day = [];

for ($i = 6; $i >= 0; $i--) {
    $day_key = date('Y-m-d', strtotime("-$i days"));
    $days[$day_key] = [
        'label' => date('d/m', strtotime($day_key)),
        'revenue' => 0
    ];
}

$sql_chart = "
    SELECT 
        DATE(created_at) AS order_date,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
      AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC
";

$stmt_chart = mysqli_prepare($conn, $sql_chart);
mysqli_stmt_bind_param($stmt_chart, "ss", $chart_start, $chart_end);
mysqli_stmt_execute($stmt_chart);
$result_chart = mysqli_stmt_get_result($stmt_chart);

while ($row = mysqli_fetch_assoc($result_chart)) {
    $date_key = $row['order_date'];

    if (isset($days[$date_key])) {
        $days[$date_key]['revenue'] = (int)$row['revenue'];
    }
}

$chart_labels = [];
$chart_revenues = [];

foreach ($days as $day) {
    $chart_labels[] = $day['label'];
    $chart_revenues[] = $day['revenue'];
}

/* ===============================
   ĐƠN HÀNG MỚI NHẤT
================================ */
$latest_orders = mysqli_query($conn, "
    SELECT *
    FROM orders
    ORDER BY id DESC
    LIMIT 8
");

/* ===============================
   SẢN PHẨM BÁN CHẠY
================================ */
$top_products = mysqli_query($conn, "
    SELECT 
        product_name,
        product_image,
        SUM(quantity) AS total_quantity,
        SUM(subtotal) AS total_revenue
    FROM order_items
    GROUP BY product_name, product_image
    ORDER BY total_quantity DESC
    LIMIT 5
");

/* ===============================
   YÊU CẦU SỬA CHỮA MỚI
================================ */
$latest_repairs = mysqli_query($conn, "
    SELECT 
        rr.*,
        rs.service_name
    FROM repair_requests rr
    LEFT JOIN repair_services rs ON rr.service_id = rs.id
    ORDER BY rr.id DESC
    LIMIT 5
");

function percent_value($part, $total) {
    if ($total <= 0) {
        return 0;
    }

    return round(($part / $total) * 100);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - ABA Mobile</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">

        <!-- TOPBAR -->
        <div class="admin-topbar">
            <div>
                <h1 class="admin-title">Dashboard quản trị</h1>
                <p class="admin-subtitle">
                    Theo dõi đơn hàng, doanh thu, sản phẩm và khách hàng của ABA Mobile
                </p>
            </div>

            <div class="admin-user-box">
                <div class="admin-user-avatar">👤</div>
                <span><?= h(current_admin_name()) ?></span>
            </div>
        </div>

<!-- KPI CHÍNH -->
<div class="kpi-grid">

    <div class="kpi-card success">
        <div class="kpi-head">
            <div class="kpi-icon">💰</div>
            <div class="kpi-title">Tổng doanh thu</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= money_vn($total_revenue) ?></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-head">
            <div class="kpi-icon">📦</div>
            <div class="kpi-title">Tổng đơn hàng</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= $total_orders ?></div>
            <div class="kpi-note">Hôm nay có <?= $today_orders ?> đơn mới</div>
        </div>
    </div>

    <div class="kpi-card warning">
        <div class="kpi-head">
            <div class="kpi-icon">⏳</div>
            <div class="kpi-title">Đơn chờ xác nhận</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= $pending_orders ?></div>
        </div>
    </div>

    <div class="kpi-card purple">
        <div class="kpi-head">
            <div class="kpi-icon">👥</div>
            <div class="kpi-title">Người dùng</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= $total_users ?></div>
        </div>
    </div>

</div>

<!-- KPI PHỤ -->
<div class="kpi-grid">

    <div class="kpi-card">
        <div class="kpi-head">
            <div class="kpi-icon">📅</div>
            <div class="kpi-title">Doanh thu hôm nay</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= money_vn($today_revenue) ?></div>
            <div class="kpi-note"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="kpi-card success">
        <div class="kpi-head">
            <div class="kpi-icon">📈</div>
            <div class="kpi-title">Doanh thu tháng này</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= money_vn($month_revenue) ?></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-head">
            <div class="kpi-icon">📱</div>
            <div class="kpi-title">Sản phẩm</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= $total_products ?></div>
        </div>
    </div>

    <div class="kpi-card danger">
        <div class="kpi-head">
            <div class="kpi-icon">🛠️</div>
            <div class="kpi-title">Yêu cầu sửa chữa mới</div>
        </div>

        <div class="kpi-content">
            <div class="kpi-value"><?= $pending_repair_requests ?></div>
            <div class="kpi-note">Tổng yêu cầu: <?= $total_repair_requests ?></div>
        </div>
    </div>

</div>

        <!-- BIỂU ĐỒ + TRẠNG THÁI ĐƠN -->
        <div class="dashboard-grid">

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Doanh thu 7 ngày gần nhất</h2>
                    </div>

                    <a href="revenue.php" class="btn btn-blue">
                        Xem chi tiết
                    </a>
                </div>

                <canvas id="dashboardRevenueChart" class="chart-box"></canvas>
            </div>

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Tình trạng đơn hàng</h2>
                    </div>
                </div>

                <div class="status-list">

                    <div class="status-item">
                        <div class="status-name">Chờ xác nhận</div>
                        <div class="progress">
                            <div class="progress-fill pending" style="width: <?= percent_value($pending_orders, $total_orders) ?>%;"></div>
                        </div>
                        <div class="status-number"><?= $pending_orders ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-name">Đã xác nhận</div>
                        <div class="progress">
                            <div class="progress-fill confirmed" style="width: <?= percent_value($confirmed_orders, $total_orders) ?>%;"></div>
                        </div>
                        <div class="status-number"><?= $confirmed_orders ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-name">Đang giao</div>
                        <div class="progress">
                            <div class="progress-fill shipping" style="width: <?= percent_value($shipping_orders, $total_orders) ?>%;"></div>
                        </div>
                        <div class="status-number"><?= $shipping_orders ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-name">Hoàn thành</div>
                        <div class="progress">
                            <div class="progress-fill completed" style="width: <?= percent_value($completed_orders, $total_orders) ?>%;"></div>
                        </div>
                        <div class="status-number"><?= $completed_orders ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-name">Đã hủy</div>
                        <div class="progress">
                            <div class="progress-fill cancelled" style="width: <?= percent_value($cancelled_orders, $total_orders) ?>%;"></div>
                        </div>
                        <div class="status-number"><?= $cancelled_orders ?></div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ĐƠN HÀNG + THAO TÁC NHANH -->
        <div class="dashboard-grid-2">

            <div class="admin-card orders-full-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Đơn hàng mới nhất</h2>
                    </div>

                    <a href="orders.php" class="btn btn-gray">
                        Xem tất cả
                    </a>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($latest_orders && mysqli_num_rows($latest_orders) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($latest_orders)): ?>
                                <tr>
                                    <td>#<?= (int)$order['id'] ?></td>

                                    <td>
                                        <strong><?= h($order['customer_name']) ?></strong><br>
                                        <small style="color:#64748b;"><?= h($order['phone']) ?></small>
                                    </td>

                                    <td>
                                        <strong style="color:#ef4444;">
                                            <?= money_vn($order['total_amount']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="badge <?= status_class($order['status']) ?>">
                                            <?= h(status_text($order['status'])) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a href="order_detail.php?id=<?= (int)$order['id'] ?>" class="btn btn-blue">
                                            Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Chưa có đơn hàng.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SẢN PHẨM BÁN CHẠY + YÊU CẦU SỬA CHỮA -->
        <div class="dashboard-grid-2">

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Sản phẩm bán chạy</h2>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đã bán</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($top_products && mysqli_num_rows($top_products) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($top_products)): ?>
                                <tr>
                                    <td>
                                        <div class="table-product">
                                            <?php if (!empty($product['product_image'])): ?>
                                                <img src="<?= h($product['product_image']) ?>" alt="">
                                            <?php endif; ?>

                                            <strong><?= h($product['product_name']) ?></strong>
                                        </div>
                                    </td>

                                    <td><?= (int)$product['total_quantity'] ?></td>

                                    <td>
                                        <strong style="color:#ef4444;">
                                            <?= money_vn($product['total_revenue']) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Chưa có dữ liệu bán hàng.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Yêu cầu sửa chữa mới</h2>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Khách</th>
                            <th>Máy</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($latest_repairs && mysqli_num_rows($latest_repairs) > 0): ?>
                            <?php while ($repair = mysqli_fetch_assoc($latest_repairs)): ?>
                                <tr>
                                    <td>
                                        <strong><?= h($repair['customer_name']) ?></strong><br>
                                        <small style="color:#64748b;"><?= h($repair['phone']) ?></small>
                                    </td>

                                    <td>
                                        <?= h($repair['device_name']) ?><br>
                                        <small style="color:#64748b;">
                                            <?= h($repair['service_name'] ?? 'Chưa rõ dịch vụ') ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?php if (($repair['status'] ?? '') === 'pending'): ?>
                                            <span class="badge badge-pending">Chờ xử lý</span>
                                        <?php elseif (($repair['status'] ?? '') === 'done'): ?>
                                            <span class="badge badge-completed">Hoàn thành</span>
                                        <?php else: ?>
                                            <span class="badge badge-confirmed">
                                                <?= h($repair['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Chưa có yêu cầu sửa chữa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>
</div>

<script>
const labels = <?= json_encode($chart_labels ?? [], JSON_UNESCAPED_UNICODE) ?>;
const revenues = <?= json_encode($chart_revenues ?? []) ?>;

const canvas = document.getElementById('dashboardRevenueChart');

function formatMoneyShort(value) {
    value = Number(value || 0);

    if (value >= 1000000000) {
        return (value / 1000000000).toFixed(1).replace('.0', '') + ' tỷ';
    }

    if (value >= 1000000) {
        return (value / 1000000).toFixed(1).replace('.0', '') + ' tr';
    }

    if (value >= 1000) {
        return (value / 1000).toFixed(0) + 'k';
    }

    return value.toString();
}

function roundRect(ctx, x, y, width, height, radius) {
    if (height < radius) {
        radius = height;
    }

    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x, y + height);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
}

function drawDashboardRevenueChart() {
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    canvas.width = canvas.offsetWidth;
    canvas.height = 340;

    const width = canvas.width;
    const height = canvas.height;

    const paddingLeft = 105;
    const paddingRight = 28;
    const paddingTop = 28;
    const paddingBottom = 48;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    if (!labels.length || !revenues.length) {
        ctx.fillStyle = '#64748b';
        ctx.font = '15px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Chưa có dữ liệu doanh thu', width / 2, height / 2);
        return;
    }

    const maxValue = Math.max(...revenues.map(Number), 1);

    // Vẽ grid ngang
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;
    ctx.font = '12px Arial';
    ctx.fillStyle = '#64748b';

    for (let i = 0; i <= 4; i++) {
        const y = paddingTop + (chartHeight / 4) * i;
        const value = Math.round(maxValue - (maxValue / 4) * i);

        ctx.beginPath();
        ctx.moveTo(paddingLeft, y);
        ctx.lineTo(width - paddingRight, y);
        ctx.stroke();

        ctx.textAlign = 'right';
        ctx.fillText(formatMoneyShort(value), paddingLeft - 14, y + 4);
    }

    // Trục X
    ctx.strokeStyle = '#cbd5e1';
    ctx.beginPath();
    ctx.moveTo(paddingLeft, paddingTop + chartHeight);
    ctx.lineTo(width - paddingRight, paddingTop + chartHeight);
    ctx.stroke();

    const itemWidth = chartWidth / revenues.length;
    const barWidth = Math.max(18, Math.min(46, itemWidth * 0.5));

    revenues.forEach((rawValue, index) => {
        const value = Number(rawValue || 0);
        const barHeight = value / maxValue * chartHeight;

        const x = paddingLeft + index * itemWidth + (itemWidth - barWidth) / 2;
        const y = paddingTop + chartHeight - barHeight;

        if (value > 0) {
            const gradient = ctx.createLinearGradient(0, y, 0, paddingTop + chartHeight);
            gradient.addColorStop(0, '#00a8ff');
            gradient.addColorStop(1, '#007bff');

            ctx.fillStyle = gradient;
            roundRect(ctx, x, y, barWidth, barHeight, 10);
            ctx.fill();
        }

        ctx.fillStyle = '#64748b';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(labels[index], x + barWidth / 2, height - 18);
    });
}

drawDashboardRevenueChart();

window.addEventListener('resize', drawDashboardRevenueChart);
</script>

</body>
</html>