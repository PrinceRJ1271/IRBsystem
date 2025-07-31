<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function check_access($allowed_levels = []) {
    if (!isset($_SESSION['level_id']) || !in_array($_SESSION['level_id'], $allowed_levels)) {
        echo "<h3>Access Denied. You do not have permission to view this page.</h3>";
        exit();
    }
}
?>
