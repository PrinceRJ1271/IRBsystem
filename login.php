<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['level_id'] = $user['level_id'];
        $_SESSION['profile_pic'] = $user['profile_pic'];
        $_SESSION['user_id'] = $user['user_id'];
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
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        .position-relative {
            position: relative;
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

                    <form method="POST" class="pt-3 needs-validation" novalidate>
                        <div class="form-group">
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Username"
                                   aria-label="Username" autocomplete="username" required>
                            <div class="invalid-feedback">Please enter your Username.</div>
                        </div>
                        <div class="form-group position-relative">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password"
                                   aria-label="Password" autocomplete="current-password" required id="password">
                            <i class="mdi mdi-eye-off password-toggle" id="togglePassword"></i>
                            <div class="invalid-feedback">Please enter your password.</div>
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

<script>
    // Bootstrap validation
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        });
    })();

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('mdi-eye');
        this.classList.toggle('mdi-eye-off');
    });
</script>
</body>
</html>
