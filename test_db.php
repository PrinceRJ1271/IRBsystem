<?php
$host = "localhost";
$db = "irb_system";
$user = "irbuser";
$pass = "strongpassword";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connected successfully!";
?>
