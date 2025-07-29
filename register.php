<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level_id = $_POST['level_id'];

    $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, level_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $user_id, $username, $password, $level_id);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Registration failed. Try a different User ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - IRB Letter Management System</title>
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
  <style>
    .auth-form-light {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .form-title {
        font-weight: bold;
        font-size: 1.5rem;
    }
    .input-group-text {
        background-color: #f2f2f2;
        border: none;
    }
  </style>
</head>
<body>
  <div class="container-fluid page-body-wrapper full-page-wrapper d-flex">
    <div class="row w-100 m-0">
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="auth-form-light text-left py-5 px-4 px-sm-5 w-75">
          <h4 class="form-title">Create an Account</h4>
          <h6 class="fw-light mb-4">Register to continue.</h6>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-account-card-details"></i></span>
                <input type="text" name="user_id" class="form-control" placeholder="User ID" required>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>
            </div>
            <div class="form-group">
              <select name="level_id" class="form-control" required>
                <option value="">Select Role</option>
                <option value="1">Developer</option>
                <option value="2">Tax Manager</option>
                <option value="3">Tax Senior</option>
                <option value="4">Admin Staff</option>
              </select>
            </div>
            <div class="mt-3">
              <button class="btn btn-primary btn-block">REGISTER</button>
            </div>
            <div class="text-center mt-3 fw-light">
              Already have an account? <a href="login.php" class="text-primary">Login</a>
            </div>
          </form>
        </div>
      </div>
      <div class="col-md-6 d-none d-md-block p-0">
        <img src="assets/images/register-bg.jpg" alt="register side" style="width:100%; height:100vh; object-fit:cover;">
      </div>
    </div>
  </div>
</body>
</html>
