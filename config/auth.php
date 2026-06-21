<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id() {
        return is_logged_in() ? (int)$_SESSION['user_id'] : null;
    }
}

if (!function_exists('current_user_name')) {
    function current_user_name() {
        return $_SESSION['user_name'] ?? '';
    }
}

if (!function_exists('current_user_email')) {
    function current_user_email() {
        return $_SESSION['user_email'] ?? '';
    }
}

if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            header("Location: login.php");
            exit;
        }
    }
}
?>