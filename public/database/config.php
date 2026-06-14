<?php
// ===========================
// config.php - Kết nối database
// ===========================

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "phone_shop";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>