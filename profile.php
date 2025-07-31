<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data from DB
function getUserData($conn, $user_id) {
    $stmt = $conn->prepare("SELECT username, user_email, user_phonenumber, profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$user = getUserData($conn, $user_id);
$profile_path = $user['profile_pic'];
$display_pic = (!empty($profile_path) && file_exists($profile_path)) ? $profile_path : 'assets/images/uploads/default.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['user_email']);
    $phone = trim($_POST['user_phonenumber']);
    $target_file = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024;
        $file_type = $_FILES['profile_pic']['type'];
        $file_size = $_FILES['profile_pic']['size'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];
        $filename = time() . "_" . basename($_FILES['profile_pic']['name']);
        $upload_path = "assets/images/uploads/" . $filename;

        if (!in_array($file_type, $allowed_types)) {
            $error = "❌ Only JPG, PNG, or GIF images are allowed.";
        } elseif ($file_size > $max_file_size) {
            $error = "❌ Image must be less than 5MB.";
        } elseif (!move_uploaded_file($tmp_name, $upload_path)) {
            $error = "❌ Failed to upload image.";
        } else {
            $target_file = $upload_path;
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET user_email = ?, user_phonenumber = ?, profile_pic = ? WHERE user_id = ?");
        $stmt->bind_param("ssss", $email, $phone, $target_file, $user_id);

        if ($stmt->execute()) {
            $_SESSION['profile_pic'] = $target_file;
            $success = "✅ Profile updated successfully.";
            $user = getUserData($conn, $user_id);
            $display_pic = (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) ? $user['profile_pic'] : 'assets/images/uploads/default.png';
        } else {
            $error = "❌ Update failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - IRB System</title>
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #f3f6f9;
        }
        .page-title {
            font-weight: 600;
            color: #4B49AC;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-group label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper">

        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-panel">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>

            <div class="content-wrapper">
                <div class="row justify-content-center">
                    <div class="col-md-10 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="page-title">My Profile</h4>
                                <p class="card-description"> View or update your details below </p>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?= $success ?></div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data">
                                    <div class="text-center mb-4">
                                        <img id="previewImage" src="<?= htmlspecialchars($display_pic) ?>" class="profile-img mb-2" alt="Profile Picture">
                                        <h5 class="text-primary mt-2"><?= htmlspecialchars($user['username']) ?></h5>
                                    </div>

                                    <div class="form-group">
                                        <label>Email address</label>
                                        <input type="email" name="user_email" class="form-control" required
                                            value="<?= htmlspecialchars($user['user_email']) ?>" placeholder="Enter email">
                                    </div>

                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="user_phonenumber" class="form-control" required
                                            value="<?= htmlspecialchars($user['user_phonenumber']) ?>" placeholder="Enter phone number">
                                    </div>

                                    <div class="form-group">
                                        <label>Upload New Profile Picture</label>
                                        <input type="file" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png,.gif"
                                            onchange="previewImage(event)">
                                        <small class="text-muted">Max size: 5MB | Types: JPG, PNG, GIF</small>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                        <a href="dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
</div>

<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/misc.js"></script>
<script>
    function previewImage(event) {
        const preview = document.getElementById('previewImage');
        const file = event.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
        }
    }
</script>
</body>
</html>
