<?php
require_once('auth_admin.php');

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

header("Location: login.php");
exit;
?>