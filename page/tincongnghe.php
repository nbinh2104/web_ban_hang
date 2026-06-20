<?php
include('../config/database.php');

mysqli_set_charset($conn, 'utf8mb4');

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function timeLabel($created_at) {
    if (empty($created_at)) {
        return 'Mới đây';
    }

    $timestamp = strtotime($created_at);
    if (!$timestamp) {
        return 'Mới đây';
    }

    $diff = time() - $timestamp;

    if ($diff < 3600) {
        return 'Mới đây';
    }

    if ($diff < 86400) {
        return floor($diff / 3600) . ' giờ trước';
    }

    if ($diff < 604800) {
        return floor($diff / 86400) . ' ngày trước';
    }

    return date('d/m/Y', $timestamp);
}

function newsLink($slug) {
    // Chưa làm trang chi tiết tin, nên để # để tránh lỗi Not Found.
    // Khi làm trang chi tiết, đổi thành: return 'chitiettintuc.php?slug=' . urlencode($slug);
    return '#';
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 3;
$offset = ($page - 1) * $per_page;

$featured = null;
$featured_id = 0;

$featured_sql = "SELECT id, title, slug, thumbnail, summary, author, created_at
                 FROM tech_news
                 WHERE status = 1
                 ORDER BY created_at DESC, id DESC
                 LIMIT 1";

$featured_result = mysqli_query($conn, $featured_sql);
if ($featured_result && mysqli_num_rows($featured_result) > 0) {
    $featured = mysqli_fetch_assoc($featured_result);
    $featured_id = (int)$featured['id'];
}

$total_sql = "SELECT COUNT(*) AS total
              FROM tech_news
              WHERE status = 1 AND id <> $featured_id";
$total_result = mysqli_query($conn, $total_sql);
$total_row = $total_result ? mysqli_fetch_assoc($total_result) : ['total' => 0];
$total_news = (int)$total_row['total'];
$total_pages = max(1, (int)ceil($total_news / $per_page));

$news_list = [];
$news_sql = "SELECT id, title, slug, thumbnail, summary, author, created_at
             FROM tech_news
             WHERE status = 1 AND id <> $featured_id
             ORDER BY created_at DESC, id DESC
             LIMIT $per_page OFFSET $offset";
$news_result = mysqli_query($conn, $news_sql);

if ($news_result) {
    while ($row = mysqli_fetch_assoc($news_result)) {
        $news_list[] = $row;
    }
}

$trending_news = [];
$trending_sql = "SELECT id, title, slug
                 FROM tech_news
                 WHERE status = 1
                 ORDER BY id DESC
                 LIMIT 4";
$trending_result = mysqli_query($conn, $trending_sql);

if ($trending_result) {
    while ($row = mysqli_fetch_assoc($trending_result)) {
        $trending_news[] = $row;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../public/css/style.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="../public/css/tincongnghe.css?v=<?php echo time(); ?>" />
  <title>Tin Công Nghệ - ABA Mobile</title>
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

      <!-- NÚT 3 SỌC CHO MOBILE -->
      <button type="button" class="mobile-menu-btn" onclick="toggleMobileMenu()">
        ☰
      </button>

      <nav class="header-center">
        <ul class="modern-menu">
          <li><a href="index.php">Trang chủ</a></li>
          <li><a href="dienthoai.php">Điện thoại</a></li>
          <li><a href="suachua.php">Sửa chữa</a></li>
          <li><a href="tincongnghe.php" class="active">Tin công nghệ</a></li>

          <!-- Chỉ hiện trong menu mobile -->
          <li class="mobile-menu-extra"><a href="cart.php">🛒 Giỏ hàng</a></li>
        </ul>
      </nav>

      <div class="header-right">
        <form action="dienthoai.php" method="GET" class="search-form" autocomplete="off">
          <input
            type="text"
            name="q"
            placeholder="Tìm kiếm"
            class="search-input"
            value="<?php echo h($_GET['q'] ?? ''); ?>"
          >
          <button type="submit" class="search-btn">🔍</button>
          <div id="search-results" class="search-results"></div>
        </form>

        <a href="cart.php" class="btn-cart-modern">
          🛒 Giỏ hàng
          <span id="cart-badge" class="cart-badge-hidden">0</span>
        </a>
      </div>
    </div>
  </header>

  <main class="container news-page">
    <div class="news-breadcrumb">
      <a href="index.php">Trang chủ</a> &raquo;
      <span>Tin công nghệ</span>
    </div>

    <h1 class="news-page-title">TIN TỨC 24H</h1>

    <div class="news-layout">
      <div class="news-main">
        <?php if ($featured): ?>
          <article class="featured-news">
            <a href="<?php echo h(newsLink($featured['slug'])); ?>" class="featured-img">
              <img
                src="<?php echo h($featured['thumbnail']); ?>"
                alt="<?php echo h($featured['title']); ?>"
                onerror="this.style.display='none'; this.parentElement.classList.add('no-img');"
              />
            </a>

            <div class="news-content">
              <h2>
                <a href="<?php echo h(newsLink($featured['slug'])); ?>">
                  <?php echo h($featured['title']); ?>
                </a>
              </h2>

              <div class="news-meta">
                <?php echo h(timeLabel($featured['created_at'])); ?> &bull; <?php echo h($featured['author'] ?? 'ABA Mobile'); ?>
              </div>

              <p><?php echo h($featured['summary']); ?></p>
            </div>
          </article>
        <?php else: ?>
          <div class="empty-news">Chưa có bài viết công nghệ nào.</div>
        <?php endif; ?>

        <div class="news-list">
          <?php if (!empty($news_list)): ?>
            <?php foreach ($news_list as $news): ?>
              <article class="news-card">
                <a href="<?php echo h(newsLink($news['slug'])); ?>" class="news-thumb">
                  <img
                    src="<?php echo h($news['thumbnail']); ?>"
                    alt="<?php echo h($news['title']); ?>"
                    onerror="this.style.display='none'; this.parentElement.classList.add('no-img');"
                  />
                </a>

                <div class="news-info">
                  <h3>
                    <a href="<?php echo h(newsLink($news['slug'])); ?>">
                      <?php echo h($news['title']); ?>
                    </a>
                  </h3>

                  <div class="news-meta">
                    <?php echo h(timeLabel($news['created_at'])); ?> &bull; <?php echo h($news['author'] ?? 'ABA Mobile'); ?>
                  </div>

                  <p><?php echo h($news['summary']); ?></p>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
          <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <a href="tincongnghe.php?page=<?php echo $i; ?>" class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      </div>

      <aside class="news-sidebar">
        <div class="sidebar-widget">
          <h3>Tin Đọc Nhiều Nhất</h3>

          <ul class="trending-list">
            <?php if (!empty($trending_news)): ?>
              <?php foreach ($trending_news as $index => $item): ?>
                <li>
                  <span class="rank"><?php echo $index + 1; ?></span>
                  <a href="<?php echo h(newsLink($item['slug'])); ?>">
                    <?php echo h($item['title']); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>Chưa có tin đọc nhiều.</li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="sidebar-widget promo-banner">
          <div class="promo-content">
            <span>Ưu đãi đặc biệt</span>
            <strong>Giảm giá phụ kiện</strong>
            <p>Khi sửa chữa hoặc mua điện thoại tại ABA Mobile</p>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container footer-content">
      <div class="footer-col">
        <h3>ABA MOBILE</h3>
        <p>
          Hệ thống bán lẻ điện thoại di động chính hãng, uy tín hàng đầu với giá cả cạnh tranh.
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
  <script src="../public/js/cart.js?v=<?php echo time(); ?>"></script>

<script>
/* =========================================
   MỞ / ĐÓNG MENU MOBILE
========================================= */
function toggleMobileMenu() {
    const header = document.querySelector('.modern-header');

    if (header) {
        header.classList.toggle('mobile-open');
    }
}

/* =========================================
   TÌM KIẾM AJAX
========================================= */
document.addEventListener('DOMContentLoaded', function () {
    const searchForms = document.querySelectorAll('.search-form');

    searchForms.forEach(function (form) {
        const searchInput = form.querySelector('.search-input');
        const resultDiv = form.querySelector('.search-results');

        if (!searchInput || !resultDiv) return;

        searchInput.addEventListener('input', function () {
            const q = this.value.trim();

            if (q.length > 0) {
                fetch('search_ajax.php?q=' + encodeURIComponent(q))
                    .then(response => response.text())
                    .then(data => {
                        resultDiv.innerHTML = data;
                        resultDiv.style.display = data.trim() ? 'block' : 'none';
                    })
                    .catch(() => {
                        resultDiv.innerHTML = '<div class="search-empty">Lỗi tìm kiếm</div>';
                        resultDiv.style.display = 'block';
                    });
            } else {
                resultDiv.innerHTML = '';
                resultDiv.style.display = 'none';
            }
        });

        form.addEventListener('submit', function (e) {
            const firstResult = resultDiv.querySelector('.search-item');

            if (firstResult) {
                e.preventDefault();
                window.location.href = firstResult.getAttribute('href');
            }
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                resultDiv.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function (e) {
        const header = document.querySelector('.modern-header');

        if (!header) return;

        const clickInsideHeader = header.contains(e.target);

        if (!clickInsideHeader) {
            header.classList.remove('mobile-open');
        }
    });

    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
</script>
</body>
</html>
