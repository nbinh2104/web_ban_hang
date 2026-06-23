<?php
require_once('auth_admin.php');
require_admin_login();

require_once('../config/database.php');

$activeAdminPage = 'reviews';
$message = '';

function review_status_text($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ duyệt';
        case 'approved':
            return 'Đã duyệt';
        case 'hidden':
            return 'Đã ẩn';
        default:
            return 'Không rõ';
    }
}

function review_status_class($status) {
    switch ($status) {
        case 'pending':
            return 'badge-pending';
        case 'approved':
            return 'badge-completed';
        case 'hidden':
            return 'badge-cancelled';
        default:
            return 'badge-pending';
    }
}

/* ===============================
   XỬ LÝ DUYỆT / ẨN / XÓA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($review_id > 0) {
        if ($action === 'approve') {
            $sql = "
                UPDATE product_reviews
                SET status = 'approved',
                    updated_at = NOW()
                WHERE id = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $review_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã duyệt đánh giá.';
        }

        if ($action === 'hide') {
            $sql = "
                UPDATE product_reviews
                SET status = 'hidden',
                    updated_at = NOW()
                WHERE id = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $review_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã ẩn đánh giá.';
        }

        if ($action === 'delete') {
            $sql = "DELETE FROM product_reviews WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $review_id);
            mysqli_stmt_execute($stmt);

            $message = 'Đã xóa đánh giá.';
        }
    }
}

/* ===============================
   LỌC TRẠNG THÁI
================================ */
$status_filter = $_GET['status'] ?? '';

$allowed_status = ['pending', 'approved', 'hidden'];

$where = "";
$params = [];
$types = "";

if (in_array($status_filter, $allowed_status)) {
    $where = "WHERE pr.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "
    SELECT 
        pr.*,
        p.name AS product_name,
        p.image_url,
        u.email AS user_email
    FROM product_reviews pr
    LEFT JOIN products p ON pr.product_id = p.id
    LEFT JOIN users u ON pr.user_id = u.id
    $where
    ORDER BY 
        CASE 
            WHEN pr.status = 'pending' THEN 1
            WHEN pr.status = 'approved' THEN 2
            ELSE 3
        END ASC,
        pr.id DESC
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $reviews = mysqli_stmt_get_result($stmt);
} else {
    $reviews = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đánh giá - Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= time(); ?>">
</head>

<body>

<div class="admin-layout">
    <?php include('components/sidebar.php'); ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <div>
                <h1 class="admin-title">Quản lý đánh giá</h1>
            </div>

            <div class="admin-user-box">
                <div class="admin-user-avatar">⭐</div>
                <span>Đánh giá</span>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert-success">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="GET" class="filter-form">

                <div class="form-group" style="margin-bottom:0;">
                    <label>Lọc trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>
                            Chờ duyệt
                        </option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>
                            Đã duyệt
                        </option>
                        <option value="hidden" <?= $status_filter === 'hidden' ? 'selected' : '' ?>>
                            Đã ẩn
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-blue">
                    Lọc
                </button>

                <a href="reviews.php" class="btn btn-gray">
                    Xóa lọc
                </a>

            </form>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th>Sao</th>
                        <th>Nhận xét</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($reviews && mysqli_num_rows($reviews) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews)): ?>
                            <tr>
                                <td>#<?= (int)$review['id'] ?></td>

                                <td>
                                    <div class="table-product">
                                        <?php if (!empty($review['image_url'])): ?>
                                            <img src="<?= h($review['image_url']) ?>" alt="">
                                        <?php endif; ?>

                                        <div>
                                            <strong><?= h($review['product_name'] ?? 'Không rõ sản phẩm') ?></strong><br>

                                            <?php if (!empty($review['product_id'])): ?>
                                                <a 
                                                    href="../page/detail.php?id=<?= (int)$review['product_id'] ?>" 
                                                    target="_blank"
                                                    style="color:#00a8ff;font-weight:800;"
                                                >
                                                    Xem sản phẩm
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <strong><?= h($review['customer_name']) ?></strong><br>
                                    <small style="color:#64748b;">
                                        <?= h($review['user_email'] ?? '') ?>
                                    </small>
                                </td>

                                <td>
                                    <span style="color:#f59e0b;font-weight:900;">
                                        <?= str_repeat('★', (int)$review['rating']) ?>
                                        <?= str_repeat('☆', 5 - (int)$review['rating']) ?>
                                    </span>
                                </td>

                                <td style="max-width:320px;">
                                    <?= nl2br(h($review['comment'])) ?>
                                </td>

                                <td>
                                    <span class="badge <?= review_status_class($review['status']) ?>">
                                        <?= h(review_status_text($review['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= h($review['created_at']) ?>
                                </td>

                                <td>
                                    <?php if ($review['status'] !== 'approved'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                            <input type="hidden" name="action" value="approve">

                                            <button type="submit" class="btn btn-green">
                                                Duyệt
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($review['status'] !== 'hidden'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                            <input type="hidden" name="action" value="hide">

                                            <button type="submit" class="btn btn-gray">
                                                Ẩn
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form 
                                        method="POST" 
                                        style="display:inline;"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa đánh giá này?');"
                                    >
                                        <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                        <input type="hidden" name="action" value="delete">

                                        <button type="submit" class="btn btn-red">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Chưa có đánh giá nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>