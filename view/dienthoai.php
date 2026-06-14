<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/dienthoai.css" />
    <title>Điện thoại - ABA Mobile</title>

    <style>
      .product-card {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
      }
      .card-link {
        text-decoration: none !important;
        display: flex !important;
        flex-direction: column !important;
        flex-grow: 1 !important; /* Ép phần link phình to ra chiếm hết chỗ trống */
      }
      .price-group,
      .product-price {
        margin-top: auto !important; /* Tự động đẩy giá xuống sát đáy của phần Link */
        margin-bottom: 15px !important;
      }
      .btn-add-cart {
        margin-top: 0 !important;
      }
    </style>
  </head>
  <body>
    <header class="modern-header">
      <div class="container header-inner">
        <div class="header-left">
          <a href="tel:1900xxxx" class="btn-phone-icon">
            <svg
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
              ></path>
            </svg>
          </a>
          <a href="index.html" class="modern-logo"
            >ABA Mobile<span class="dot">.</span></a
          >
        </div>

        <nav class="header-center">
          <ul class="modern-menu">
            <li><a href="index.html">Trang chủ</a></li>
            <li class="has-dropdown">
              <a
                href="dienthoai.html"
                class="active"
                style="display: flex; align-items: center; gap: 5px"
              >
                Điện thoại
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
              </a>
            </li>
            <li><a href="suachua.html">Sửa chữa</a></li>
            <li><a href="news.html">Tin công nghệ</a></li>
          </ul>
        </nav>

        <div class="header-right">
          <a href="#" class="icon-action" title="Tìm kiếm">
            <svg
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </a>
          <a href="#" class="icon-action" title="Tài khoản">
            <svg
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </a>
          <a href="cart.html" class="btn-cart-modern">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path
                d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
              ></path>
            </svg>
            Giỏ hàng
          </a>
        </div>
      </div>
    </header>

    <main class="container" style="padding: 30px 0; min-height: 600px">
      <div class="breadcrumb" style="margin-bottom: 30px">
        <a href="index.html">Trang chủ</a> &raquo;
        <span style="color: #00a8ff">Điện thoại</span>
      </div>

      <div class="category-layout">
        <aside class="sidebar-filter">
          <h3 class="filter-title">BỘ LỌC SẢN PHẨM</h3>
          <form action="" method="GET">
            <div class="filter-group">
              <h4>Thương hiệu</h4>
              <label
                ><input type="checkbox" name="brand" value="apple" /> Apple
                (iPhone)</label
              >
              <label
                ><input type="checkbox" name="brand" value="samsung" />
                Samsung</label
              >
            </div>
            <div class="filter-group">
              <h4>Mức giá</h4>
              <label
                ><input type="radio" name="price" value="duoi-5-trieu" /> Dưới 5
                triệu</label
              >
              <label
                ><input type="radio" name="price" value="5-15-trieu" /> Từ 5 -
                15 triệu</label
              >
              <label
                ><input type="radio" name="price" value="tren-15-trieu" /> Trên
                15 triệu</label
              >
            </div>
            <button type="submit" class="btn-filter">Áp dụng bộ lọc</button>
          </form>
        </aside>

        <section class="category-main">
          <div class="category-header">
            <h1 style="color: #fff; font-size: 28px; margin: 0">
              Điện thoại di động
            </h1>
            <br />
            <select
              class="sort-box"
              style="
                background-color: #1a2235;
                color: #fff;
                border: 1px solid #2d3748;
                padding: 8px 12px;
                border-radius: 6px;
                outline: none;
              "
            >
              <option value="newest">Mới nhất</option>
              <option value="price-asc">Giá: Thấp đến Cao</option>
              <option value="price-desc">Giá: Cao xuống Thấp</option>
            </select>
          </div>
          <br />
          <div class="product-grid category-grid">
            <div class="product-card">
  <a href="detail.php?id=4" class="card-link">
    <span class="badge badge-sale">-15%</span>

    <img
      src="https://placehold.co/250x250/1a2235/e2e8f0?text=iPhone+15"
      alt="iPhone 15"
    />

    <h3 class="product-title">
      iPhone 15 Pro Max 256GB
    </h3>

    <div class="price-group">
      <p class="old-price">34.990.000 đ</p>
      <p class="product-price">29.990.000 đ</p>
    </div>
  </a>
</div>

    <button class="btn-add-cart">
        🛒 Thêm vào giỏ
    </button>
</div>
=======
              <a href="detail.html" class="card-link">
                <span class="badge badge-sale">-15%</span>
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=iPhone+15"
                  alt="iPhone 15"
                />
                <h3 class="product-title">iPhone 15 Pro Max 256GB</h3>
                <div class="price-group">
                  <p class="old-price">34.990.000 đ</p>
                  <p class="product-price">29.990.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>
>>>>>>> a4371273755929b78ba42e1979d42f29c1bcb9a9

            <div class="product-card">
              <a href="detail.html" class="card-link">
                <span class="badge badge-sale">-10%</span>
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=Galaxy+S24"
                  alt="Samsung Galaxy S24"
                />
                <h3 class="product-title">Samsung Galaxy S24 Ultra</h3>
                <div class="price-group">
                  <p class="old-price">34.490.000 đ</p>
                  <p class="product-price">31.490.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>

            <div class="product-card">
              <a href="detail.html" class="card-link">
                <span class="badge badge-sale">-20%</span>
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=iPhone+14"
                  alt="iPhone 14"
                />
                <h3 class="product-title">iPhone 14 128GB Chính Hãng</h3>
                <div class="price-group">
                  <p class="old-price">22.590.000 đ</p>
                  <p class="product-price">18.590.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>
            

            <div class="product-card">
              <a href="detail.html" class="card-link">
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=Xiaomi+14"
                  alt="Xiaomi 14"
                />
                <h3 class="product-title">Xiaomi 14 5G (12GB/256GB)</h3>
                <div class="price-group">
                  <p class="old-price">22.990.000 đ</p>
                  <p class="product-price">19.990.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>

            <div class="product-card">
              <a href="detail.html" class="card-link">
                <span class="badge badge-sale">-5%</span>
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=OPPO+Find"
                  alt="OPPO"
                />
                <h3 class="product-title">OPPO Find X7 Ultra 5G</h3>
                <div class="price-group">
                  <p class="old-price">26.500.000 đ</p>
                  <p class="product-price">24.990.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>

            <div class="product-card">
              <a href="detail.html" class="card-link">
                <span class="badge badge-sale">-12%</span>
                <img
                  src="https://placehold.co/250x250/1a2235/e2e8f0?text=Redmi+Note"
                  alt="Redmi"
                />
                <h3 class="product-title">Xiaomi Redmi Note 13 Pro</h3>
                <div class="price-group">
                  <p class="old-price">7.390.000 đ</p>
                  <p class="product-price">6.490.000 đ</p>
                </div>
              </a>
              <button class="btn-add-cart">🛒 Thêm vào giỏ</button>
            </div>
          </div>

          <div class="pagination">
            <a
              href="#"
              class="page-link"
              style="
                background-color: #121826;
                color: #fff;
                border-color: #1f2937;
              "
              >&laquo; Trước</a
            >
            <a
              href="#"
              class="page-link active"
              style="
                background-color: #00a8ff;
                color: #fff;
                border-color: #00a8ff;
                font-weight: bold;
              "
              >1</a
            >
            <a
              href="#"
              class="page-link"
              style="
                background-color: #121826;
                color: #fff;
                border-color: #1f2937;
              "
              >2</a
            >
            <a
              href="#"
              class="page-link"
              style="
                background-color: #121826;
                color: #fff;
                border-color: #1f2937;
              "
              >3</a
            >
            <a
              href="#"
              class="page-link"
              style="
                background-color: #121826;
                color: #fff;
                border-color: #1f2937;
              "
              >Sau &raquo;</a
            >
          </div>
        </section>
      </div>
    </main>

    <footer>
      <div
        class="container footer-content"
        style="
          padding: 40px 0;
          color: #fff;
          display: flex;
          justify-content: space-between;
          flex-wrap: wrap;
        "
      >
        <div
          class="footer-col"
          style="flex: 1; min-width: 250px; margin-bottom: 20px"
        >
          <h3
            style="
              color: #fff;
              margin-bottom: 15px;
              border-bottom: 2px solid #00a8ff;
              display: inline-block;
              padding-bottom: 5px;
            "
          >
            ABA MOBILE
          </h3>
          <p style="color: #cbd5e1; line-height: 1.6; padding-right: 20px">
            Hệ thống bán lẻ điện thoại di động chính hãng, uy tín hàng đầu với
            giá cả cạnh tranh.
          </p>
        </div>
        <div
          class="footer-col"
          style="flex: 1; min-width: 250px; margin-bottom: 20px"
        >
          <h3
            style="
              color: #fff;
              margin-bottom: 15px;
              border-bottom: 2px solid #00a8ff;
              display: inline-block;
              padding-bottom: 5px;
            "
          >
            THÔNG TIN LIÊN HỆ
          </h3>
          <p style="color: #cbd5e1; margin-bottom: 10px">
            📍 Địa chỉ: Hà Nội, Việt Nam
          </p>
          <p style="color: #cbd5e1; margin-bottom: 10px">
            📞 Điện thoại: 1900 xxxx
          </p>
          <p style="color: #cbd5e1; margin-bottom: 10px">
            ✉️ Email: cskh@abamobile.com
          </p>
        </div>
        <div
          class="footer-col"
          style="flex: 1; min-width: 250px; margin-bottom: 20px"
        >
          <h3
            style="
              color: #fff;
              margin-bottom: 15px;
              border-bottom: 2px solid #00a8ff;
              display: inline-block;
              padding-bottom: 5px;
            "
          >
            CHÍNH SÁCH
          </h3>
          <ul style="list-style: none; padding: 0">
            <li style="margin-bottom: 10px">
              <a href="#" style="color: #cbd5e1; text-decoration: none"
                >Chính sách bảo hành</a
              >
            </li>
            <li style="margin-bottom: 10px">
              <a href="#" style="color: #cbd5e1; text-decoration: none"
                >Chính sách đổi trả 1-1</a
              >
            </li>
            <li style="margin-bottom: 10px">
              <a href="#" style="color: #cbd5e1; text-decoration: none"
                >Hướng dẫn mua trả góp</a
              >
            </li>
          </ul>
        </div>
      </div>
      <div
        style="
          background-color: #0b0f19;
          padding: 15px 0;
          text-align: center;
          border-top: 1px solid #1f2937;
        "
      >
        <p style="color: #64748b; font-size: 14px; margin: 0">
          © 2026 ABA Mobile. All rights reserved.
        </p>
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
