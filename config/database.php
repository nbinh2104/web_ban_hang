<?php
$servername = "localhost";
$username = "root"; // Tài khoản mặc định của XAMPP
$password = "";     // XAMPP mặc định không có mật khẩu
$dbname = "aba_mobile";

// Khởi tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Bắt buộc dùng dòng này để tiếng Việt không bị lỗi font (chữ ô vuông)
$conn->set_charset("utf8mb4");
?>