<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'revenue';

/* ===============================
   NGÀY LỌC
================================ */
$today = date('Y-m-d');
$first_day_month = date('Y-m-01');

$start_date = $_GET['start_date'] ?? $first_day_month;
$end_date = $_GET['end_date'] ?? $today;

if (strtotime($start_date) > strtotime($end_date)) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

/* ===============================
   TẠO MẢNG NGÀY ĐỦ KHOẢNG
================================ */
$days = [];

$current = strtotime($start_date);
$end = strtotime($end_date);

while ($current <= $end) {
    $key = date('Y-m-d', $current);

    $days[$key] = [
        'date' => $key,
        'label' => date('d/m', $current),
        'revenue' => 0,
        'orders' => 0
    ];

    $current = strtotime('+1 day', $current);
}

/* ===============================
   LẤY DOANH THU THEO NGÀY
================================ */
$sql = "
    SELECT 
        DATE(created_at) AS order_date,
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
      AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $key = $row['order_date'];

    if (isset($days[$key])) {
        $days[$key]['revenue'] = (int)$row['revenue'];
        $days[$key]['orders'] = (int)$row['total_orders'];
    }
}

/* ===============================
   TÍNH TỔNG
================================ */
$total_revenue = 0;
$total_orders = 0;
$best_day = '';
$best_day_revenue = 0;

$chart_labels = [];
$chart_revenues = [];
$chart_orders = [];

foreach ($days as $day) {
    $total_revenue += $day['revenue'];
    $total_orders += $day['orders'];

    if ($day['revenue'] > $best_day_revenue) {
        $best_day_revenue = $day['revenue'];
        $best_day = $day['label'];
    }

    $chart_labels[] = $day['label'];
    $chart_revenues[] = $day['revenue'];
    $chart_orders[] = $day['orders'];
}

$average_order_value = $total_orders > 0 ? floor($total_revenue / $total_orders) : 0;

/* ===============================
   THỐNG KÊ THEO PHƯƠNG THỨC THANH TOÁN
================================ */
$sql_payment = "
    SELECT 
        payment_method,
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE status IN ('confirmed', 'shipping', 'completed')
      AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY payment_method
    ORDER BY revenue DESC
";

$stmt_payment = mysqli_prepare($conn, $sql_payment);
mysqli_stmt_bind_param($stmt_payment, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_payment);
$payment_result = mysqli_stmt_get_result($stmt_payment);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Doanh thu - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">

        <div class="admin-topbar revenue-page-head">
            <div>
                <h1 class="admin-title">Kiểm soát doanh thu</h1>
            </div>

            <div class="admin-user-box">
                <div class="admin-user-avatar">📈</div>
                <span>Thống kê</span>
            </div>
        </div>

        <div class="admin-card">
            <form method="GET" class="revenue-filter">

                <div class="form-group" style="margin-bottom:0;">
                    <label>Từ ngày</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        class="form-control"
                        value="<?= h($start_date) ?>"
                    >
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>Đến ngày</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        class="form-control"
                        value="<?= h($end_date) ?>"
                    >
                </div>

                <button type="submit" class="btn btn-blue">
                    Xem thống kê
                </button>

                <a href="revenue.php" class="btn btn-gray">
                    Tháng này
                </a>

                <a href="revenue.php?start_date=<?= date('Y-m-d', strtotime('-6 days')) ?>&end_date=<?= date('Y-m-d') ?>" class="btn btn-gray">
                    7 ngày gần nhất
                </a>
            </form>
        </div>

        <div class="revenue-note">
            Doanh thu chỉ tính các đơn có trạng thái: Đã xác nhận, Đang giao hoặc Hoàn thành. Không tính đơn đã hủy.
        </div>

        <div class="kpi-grid">

            <div class="kpi-card success">
                <div class="kpi-icon">💰</div>
                <div class="kpi-value"><?= money_vn($total_revenue) ?></div>
                <div class="kpi-label">Tổng doanh thu</div>
                <div class="kpi-note">
                    Từ <?= date('d/m/Y', strtotime($start_date)) ?> đến <?= date('d/m/Y', strtotime($end_date)) ?>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon">📦</div>
                <div class="kpi-value"><?= $total_orders ?></div>
                <div class="kpi-label">Số đơn được tính</div>
            </div>

            <div class="kpi-card purple">
                <div class="kpi-icon">🧾</div>
                <div class="kpi-value"><?= money_vn($average_order_value) ?></div>
                <div class="kpi-label">Giá trị trung bình / đơn</div>
            </div>

            <div class="kpi-card warning">
                <div class="kpi-icon">🏆</div>
                <div class="kpi-value"><?= $best_day !== '' ? h($best_day) : '-' ?></div>
                <div class="kpi-label">Ngày cao nhất</div>
                <div class="kpi-note"><?= money_vn($best_day_revenue) ?></div>
            </div>

        </div>

        <div class="admin-card revenue-chart-card">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Biểu đồ doanh thu theo ngày</h2>
                </div>
            </div>

            <?php if ($total_revenue <= 0): ?>
                <div class="alert-error">
                    Không có dữ liệu doanh thu trong khoảng ngày này.
                </div>
            <?php else: ?>
                <div class="revenue-chart-wrap">
                    <canvas id="revenueDetailChart"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-grid-2">

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Bảng doanh thu theo ngày</h2>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Số đơn</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($days as $day): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                                <td><?= (int)$day['orders'] ?></td>
                                <td>
                                    <strong style="color:#ef4444;">
                                        <?= money_vn($day['revenue']) ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-card">
                <div class="card-title-row">
                    <div>
                        <h2 class="card-title">Theo phương thức thanh toán</h2>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Thanh toán</th>
                            <th>Đơn</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($payment_result && mysqli_num_rows($payment_result) > 0): ?>
                            <?php while ($payment = mysqli_fetch_assoc($payment_result)): ?>
                                <tr>
                                    <td><?= h($payment['payment_method']) ?></td>
                                    <td><?= (int)$payment['total_orders'] ?></td>
                                    <td>
                                        <strong style="color:#ef4444;">
                                            <?= money_vn($payment['revenue']) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Chưa có dữ liệu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>
</div>

<script>
const labels = <?= json_encode($chart_labels, JSON_UNESCAPED_UNICODE) ?>;
const revenues = <?= json_encode($chart_revenues) ?>;
const orders = <?= json_encode($chart_orders) ?>;

const canvas = document.getElementById('revenueDetailChart');

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

function drawRevenueDetailChart() {
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight || 430;

    const width = canvas.width;
    const height = canvas.height;

    const paddingLeft = 110;
    const paddingRight = 34;
    const paddingTop = 42;
    const paddingBottom = 62;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    const maxValue = Math.max(...revenues, 1);

    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Grid + trục Y
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;
    ctx.font = '13px Arial';
    ctx.fillStyle = '#64748b';

    for (let i = 0; i <= 5; i++) {
        const y = paddingTop + chartHeight / 5 * i;
        const value = Math.round(maxValue - maxValue / 5 * i);

        ctx.beginPath();
        ctx.moveTo(paddingLeft, y);
        ctx.lineTo(width - paddingRight, y);
        ctx.stroke();

        ctx.textAlign = 'right';
        ctx.fillText(formatMoneyShort(value), paddingLeft - 16, y + 4);
    }

    // Trục X
    ctx.strokeStyle = '#cbd5e1';
    ctx.beginPath();
    ctx.moveTo(paddingLeft, paddingTop + chartHeight);
    ctx.lineTo(width - paddingRight, paddingTop + chartHeight);
    ctx.stroke();

    const itemWidth = chartWidth / labels.length;
    const barWidth = Math.max(14, Math.min(46, itemWidth * 0.52));
    const labelStep = Math.ceil(labels.length / 9);

    // Cột
    revenues.forEach((value, index) => {
        const barHeight = value / maxValue * chartHeight;
        const x = paddingLeft + index * itemWidth + (itemWidth - barWidth) / 2;
        const y = paddingTop + chartHeight - barHeight;

        const gradient = ctx.createLinearGradient(0, y, 0, paddingTop + chartHeight);
        gradient.addColorStop(0, '#00a8ff');
        gradient.addColorStop(1, '#007bff');

        ctx.fillStyle = gradient;

        roundRect(ctx, x, y, barWidth, barHeight, 10);
        ctx.fill();

        // Hiển thị giá trên cột nếu ít ngày
        if (labels.length <= 12 && value > 0) {
            ctx.fillStyle = '#0f172a';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(formatMoneyShort(value), x + barWidth / 2, y - 8);
        }

        // Label ngày
        if (index % labelStep === 0 || index === labels.length - 1) {
            ctx.fillStyle = '#64748b';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(labels[index], x + barWidth / 2, height - 24);
        }
    });

    // Đường xu hướng
    ctx.strokeStyle = '#ef4444';
    ctx.lineWidth = 3;
    ctx.beginPath();

    revenues.forEach((value, index) => {
        const x = paddingLeft + index * itemWidth + itemWidth / 2;
        const y = paddingTop + chartHeight - value / maxValue * chartHeight;

        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });

    ctx.stroke();

    // Điểm tròn
    revenues.forEach((value, index) => {
        const x = paddingLeft + index * itemWidth + itemWidth / 2;
        const y = paddingTop + chartHeight - value / maxValue * chartHeight;

        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#ef4444';
        ctx.lineWidth = 3;

        ctx.beginPath();
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
    });
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

drawRevenueDetailChart();
window.addEventListener('resize', drawRevenueDetailChart);
</script>

</body>
</html>