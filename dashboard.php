<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
echo "Welcome, " . $_SESSION['user_id'] . "<br>";
echo "Security Level: " . $_SESSION['level_id'] . "<br>";
echo "<a href='logout.php'>Logout</a>";
?>
