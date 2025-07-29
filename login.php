<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['level_id'] = $user['level_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - IRB Letter Management System</title>
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        .auth .auth-form-light {
            background: #fff;
            border-radius: 1rem;
        }
        .bg-side-img {
            background: url('assets/images/auth/login-bg.jpg') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>
<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="row w-100 mx-0">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                    <h3 class="mb-3">IRB Letter Management System</h3>
                    <h6 class="fw-light mb-4">Sign in to continue.</h6>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" class="pt-3">
                        <div class="form-group">
                            <input type="text" name="user_id" class="form-control form-control-lg" placeholder="User ID" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">LOGIN</button>
                        </div>
                        <div class="text-center mt-4 fw-light">
                            Don't have an account? <a href="register.php" class="text-primary">Create one</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6 bg-side-img d-none d-md-block"></div>
        </div>
    </div>
</div>
<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/misc.js"></script>
</body>
</html>
