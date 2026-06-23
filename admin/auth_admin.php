<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function admin_is_logged_in() {
    return isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] > 0;
}

function require_admin_login() {
    if (!admin_is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function current_admin_name() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function current_admin_id() {
    return isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
}

function status_text($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';
        case 'confirmed':
            return 'Đã xác nhận';
        case 'shipping':
            return 'Đang giao hàng';
        case 'completed':
            return 'Hoàn thành';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Không rõ';
    }
}

function status_class($status) {
    switch ($status) {
        case 'pending':
            return 'badge-pending';
        case 'confirmed':
            return 'badge-confirmed';
        case 'shipping':
            return 'badge-shipping';
        case 'completed':
            return 'badge-completed';
        case 'cancelled':
            return 'badge-cancelled';
        default:
            return 'badge-pending';
    }
}

function money_vn($number) {
    return number_format((int)$number, 0, ',', '.') . ' đ';
}
?>