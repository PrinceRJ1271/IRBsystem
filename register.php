<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level_id = $_POST['level_id'];

    $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, level_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $user_id, $username, $password, $level_id);
    if ($stmt->execute()) {
        echo "Registered successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="post">
  <input name="user_id" placeholder="User ID (e.g., 10001)" required><br>
  <input name="username" placeholder="Username" required><br>
  <input name="password" type="password" placeholder="Password" required><br>
  <select name="level_id" required>
    <option value="1">Developer</option>
    <option value="2">Tax Manager</option>
    <option value="3">Tax Senior</option>
    <option value="4">Admin Staff</option>
  </select><br>
  <button type="submit">Register</button>
</form>
