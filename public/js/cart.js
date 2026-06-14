// thêm sản phẩm vào giỏ hàng

function addToCart(id, ten, gia, hinh) {
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  if (gio[id]) {
    gio[id].so_luong++;
  } else {
    gio[id] = { ten, gia, hinh, so_luong: 1 };
  }
  localStorage.setItem("gio_hang", JSON.stringify(gio));
  showToast("Đã thêm vào giỏ hàng!");
  updateCartBadge();
}

// xóa sản phẩm
function removeFromCart(id) {
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  delete gio[id];
  localStorage.setItem("gio_hang", JSON.stringify(gio));
  hienThiGio();
  updateCartBadge();
}

// cập nhật số lượng
function updateSoLuong(id, soLuong) {
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  soLuong = parseInt(soLuong);
  if (soLuong <= 0) {
    delete gio[id];
  } else {
    gio[id].so_luong = soLuong;
  }
  localStorage.setItem("gio_hang", JSON.stringify(gio));
  hienThiGio();
  updateCartBadge();
}

//
function hienThiGio() {
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  let tbody = document.getElementById("danh-sach-gio");
  let tong = 0;
  let soMon = 0;

  if (!tbody) return;
  tbody.innerHTML = "";

  const keys = Object.keys(gio);
  if (keys.length === 0) {
    tbody.innerHTML = `
            <tr><td colspan="5" class="empty-cart">
                <div class="empty-icon">🛒</div>
                <p>Giỏ hàng trống</p>
                <a href="index.html" class="btn-continue">Tiếp tục mua sắm</a>
            </td></tr>`;
    document.getElementById("tong-tien").innerText = "0 đ";
    document.getElementById("so-mon").innerText = "0 sản phẩm";
    return;
  }

  keys.forEach((id, i) => {
    let sp = gio[id];
    let thanhTien = sp.gia * sp.so_luong;
    tong += thanhTien;
    soMon += sp.so_luong;

    let row = document.createElement("tr");
    row.style.animationDelay = i * 0.07 + "s";
    row.classList.add("row-animate");
    row.innerHTML = `
            <td class="td-product">
                <img src="${sp.hinh}" alt="${sp.ten}" onerror="this.src='https://placehold.co/80x80/1a1a2e/e94560?text=📱'">
                <span>${sp.ten}</span>
            </td>
            <td class="td-price">${sp.gia.toLocaleString("vi-VN")} đ</td>
            <td class="td-qty">
                <div class="qty-control">
                    <button onclick="updateSoLuong('${id}', ${sp.so_luong - 1})">−</button>
                    <input type="number" value="${sp.so_luong}" min="1"
                        onchange="updateSoLuong('${id}', this.value)">
                    <button onclick="updateSoLuong('${id}', ${sp.so_luong + 1})">+</button>
                </div>
            </td>
            <td class="td-total">${thanhTien.toLocaleString("vi-VN")} đ</td>
            <td class="td-remove">
                <button class="btn-remove" onclick="removeFromCart('${id}')">✕</button>
            </td>`;
    tbody.appendChild(row);
  });

  document.getElementById("tong-tien").innerText =
    tong.toLocaleString("vi-VN") + " đ";
  document.getElementById("so-mon").innerText = soMon + " sản phẩm";
}

function updateCartBadge() {
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  let total = Object.values(gio).reduce((s, sp) => s + sp.so_luong, 0);
  let badge = document.getElementById("cart-badge");
  if (badge) {
    badge.innerText = total;
    badge.style.display = total > 0 ? "inline-flex" : "none";
  }
}

function showToast(msg) {
  let toast = document.getElementById("toast");
  if (!toast) return;
  toast.innerText = msg;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2500);
}

function submitOrder(event) {
  event.preventDefault();
  let gio = JSON.parse(localStorage.getItem("gio_hang")) || {};
  if (Object.keys(gio).length === 0) {
    alert("Giỏ hàng trống!");
    return;
  }

  let donHang = {
    ten: document.getElementById("f-ten").value,
    dia_chi: document.getElementById("f-diachi").value,
    dien_thoai: document.getElementById("f-dienthoai").value,
    email: document.getElementById("f-email").value,
    san_pham: gio,
    thoi_gian: new Date().toLocaleString("vi-VN"),
  };

  let dsDon = JSON.parse(localStorage.getItem("don_hang_list")) || [];
  dsDon.push(donHang);
  localStorage.setItem("don_hang_list", JSON.stringify(dsDon));
  localStorage.removeItem("gio_hang");
  window.location.href = "success.html";
}
document.addEventListener("DOMContentLoaded", () => {
    updateCartBadge();
    hienThiGio();
});