<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['level_id'] = $user['level_id'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Invalid credentials.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - IRB Letter Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0 justify-content-center">
          <div class="col-lg-4">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <h4 class="text-center">IRB Letter Management System</h4>
              <h6 class="font-weight-light text-center mb-4">Sign in to continue.</h6>

              <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                  <?= $error ?>
                </div>
              <?php endif; ?>

              <form method="post" class="pt-3">
                <div class="form-group">
                  <input type="text" name="user_id" class="form-control form-control-lg" placeholder="User ID" required>
                </div>
                <div class="form-group">
                  <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    LOGIN
                  </button>
                </div>
              </form>
              <div class="text-center mt-4 font-weight-light">
                Don't have an account? <a href="register.php" class="text-primary">Create one</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS dependencies -->
  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/hoverable-collapse.js"></script>
  <script src="assets/js/misc.js"></script>
</body>
</html>
