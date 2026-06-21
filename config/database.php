<?php
$host_name = $_SERVER['HTTP_HOST'] ?? '';

$is_local = (
    strpos($host_name, 'localhost') !== false ||
    strpos($host_name, '127.0.0.1') !== false
);

if ($is_local) {
    $conn = mysqli_connect(
        "localhost",
        "root",
        "",
        "aba_mobile"
    );
} else {
    $conn = mysqli_connect(
        "sql204.infinityfree.com",
        "if0_42230726",
        "binh2104",
        "if0_42230726_aba_mobile"
    );
}

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>