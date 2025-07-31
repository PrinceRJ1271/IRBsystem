<?php
session_start();
include 'config/db.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT username, user_email, user_phonenumber, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Determine profile picture to display
$profile_path = $user['profile_pic'];
$default_pic = 'assets/images/uploads/default.png';
$display_pic = (!empty($profile_path) && file_exists($profile_path)) ? $profile_path : $default_pic;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['user_email']);
    $phone = trim($_POST['user_phonenumber']);
    $target_file = $user['profile_pic'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $file_type = $_FILES['profile_pic']['type'];
        $file_size = $_FILES['profile_pic']['size'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];
        $filename = basename($_FILES['profile_pic']['name']);
        $upload_path = "assets/images/uploads/" . time() . "_" . $filename;

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
        $update = $conn->prepare("UPDATE users SET user_email = ?, user_phonenumber = ?, profile_pic = ? WHERE user_id = ?");
        $update->bind_param("ssss", $email, $phone, $target_file, $user_id);

        if ($update->execute()) {
            $_SESSION['profile_pic'] = $target_file;
            $success = "✅ Profile updated successfully.";

            // Refresh user data
            $user['user_email'] = $email;
            $user['user_phonenumber'] = $phone;
            $user['profile_pic'] = $target_file;
            $display_pic = $target_file;
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
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #f3f6f9;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">My Profile</h4>

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
                            <input type="email" name="user_email" class="form-control"
                                   value="<?= htmlspecialchars($user['user_email']) ?>" placeholder="Enter email" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="user_phonenumber" class="form-control"
                                   value="<?= htmlspecialchars($user['user_phonenumber']) ?>" placeholder="Enter phone number" required>
                        </div>

                        <div class="form-group">
                            <label>Upload New Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png,.gif"
                                   onchange="previewImage(event)">
                            <small class="text-muted">Max size: 5MB | Types: JPG, PNG, GIF</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/misc.js"></script>

<!-- Live preview -->
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
