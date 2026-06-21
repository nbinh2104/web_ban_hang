<?php
require_once(__DIR__ . '/../../config/auth.php');

if (!isset($activePage)) {
    $activePage = '';
}

if (!function_exists('menu_active')) {
    function menu_active($page, $activePage) {
        return $page === $activePage ? 'class="active"' : '';
    }
}
?>

<header class="modern-header">
    <div class="container header-inner">

        <div class="header-left">
            <a href="tel:1900xxxx" class="btn-phone-icon">📞</a>

            <a href="index.php" class="modern-logo">
                ABA Mobile<span class="dot">.</span>
            </a>
        </div>

        <button type="button" class="mobile-menu-btn" onclick="toggleMobileMenu()">
            ☰
        </button>

        <nav class="header-center">
            <ul class="modern-menu">
                <li>
                    <a href="index.php" <?= menu_active('index', $activePage) ?>>
                        Trang chủ
                    </a>
                </li>

                <li>
                    <a href="dienthoai.php" <?= menu_active('dienthoai', $activePage) ?>>
                        Điện thoại
                    </a>
                </li>

                <li>
                    <a href="suachua.php" <?= menu_active('suachua', $activePage) ?>>
                        Sửa chữa
                    </a>
                </li>

                <li>
                    <a href="tincongnghe.php" <?= menu_active('tincongnghe', $activePage) ?>>
                        Tin công nghệ
                    </a>
                </li>

                <li class="mobile-menu-extra">
                    <a href="cart.php">🛒 Giỏ hàng</a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="mobile-menu-extra">
                        <a href="my_orders.php">📦 Đơn hàng của tôi</a>
                    </li>

                    <li class="mobile-menu-extra">
                        <a href="logout.php">🚪 Đăng xuất</a>
                    </li>
                <?php else: ?>
                    <li class="mobile-menu-extra">
                        <a href="login.php">🔐 Đăng nhập</a>
                    </li>

                    <li class="mobile-menu-extra">
                        <a href="register.php">📝 Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-right">

            <form action="index.php" method="GET" class="search-form" autocomplete="off">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tìm kiếm" 
                    class="search-input"
                    value="<?= h($_GET['q'] ?? '') ?>"
                >

                <button type="submit" class="search-btn">🔍</button>

                <div id="search-results" class="search-results"></div>
            </form>

            <div class="account-menu">
                <button type="button" class="account-btn" onclick="toggleAccountMenu()" title="Tài khoản">
                    👤
                </button>

                <div class="account-dropdown">
                    <?php if (is_logged_in()): ?>
                        <div class="account-user">
                            Xin chào,<br>
                            <strong><?= h(current_user_name()) ?></strong>
                        </div>

                        <a href="my_orders.php">📦 Đơn hàng của tôi</a>
                        <a href="logout.php">🚪 Đăng xuất</a>
                    <?php else: ?>
                        <a href="login.php">🔐 Đăng nhập</a>
                        <a href="register.php">📝 Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>

            <a href="cart.php" class="btn-cart-modern">
                🛒 Giỏ hàng
                <span id="cart-badge" class="cart-badge-hidden">0</span>
            </a>
        </div>

    </div>
</header>

<script>
function toggleMobileMenu() {
    const header = document.querySelector('.modern-header');

    if (header) {
        header.classList.toggle('mobile-open');
    }
}

function toggleAccountMenu() {
    const accountMenu = document.querySelector('.account-menu');

    if (accountMenu) {
        accountMenu.classList.toggle('active');
    }
}

document.addEventListener('click', function(e) {
    const header = document.querySelector('.modern-header');
    const accountMenu = document.querySelector('.account-menu');

    if (header && !header.contains(e.target)) {
        header.classList.remove('mobile-open');
    }

    if (accountMenu && !accountMenu.contains(e.target)) {
        accountMenu.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const resultDiv = document.getElementById('search-results');

    if (searchInput && resultDiv) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim();

            if (q.length > 0) {
                fetch('search_ajax.php?q=' + encodeURIComponent(q))
                    .then(response => response.text())
                    .then(data => {
                        resultDiv.innerHTML = data;
                        resultDiv.style.display = data.trim() ? 'block' : 'none';
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<div class="search-empty">Lỗi tìm kiếm sản phẩm</div>';
                        resultDiv.style.display = 'block';
                    });
            } else {
                resultDiv.innerHTML = '';
                resultDiv.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-form')) {
                resultDiv.style.display = 'none';
            }
        });
    }

    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
</script>