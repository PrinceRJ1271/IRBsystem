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
        .auth .auth-form-light {
            background: #fff;
            border-radius: 1rem;
        }
        .bg-side-img {
            background: url('assets/images/auth/register-bg.jpg') no-repeat center center;
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
                    <h4>Create an Account</h4>
                    <h6 class="fw-light mb-4">Register to continue.</h6>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mt-2"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post" class="pt-3">
                        <div class="form-group">
                            <input type="text" name="user_id" class="form-control form-control-lg" placeholder="User ID" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <select name="level_id" class="form-control form-control-lg" required>
                                <option value="">Select Role</option>
                                <option value="1">Developer</option>
                                <option value="2">Tax Manager</option>
                                <option value="3">Tax Senior</option>
                                <option value="4">Admin Staff</option>
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">REGISTER</button>
                        </div>
                        <div class="text-center mt-4 fw-light">
                            Already have an account? <a href="login.php" class="text-primary">Login</a>
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
