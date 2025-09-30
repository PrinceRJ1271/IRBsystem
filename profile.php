<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* ---------- Security helpers ---------- */
function ensureCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
function cleanFilename($name)
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $base = pathinfo($name, PATHINFO_FILENAME);
    $base = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $base);
    return $base . '.' . $ext;
}

/* ---------- App state ---------- */
$user_id = (int)$_SESSION['user_id'];
$error = '';
$success = '';

/* ---------- Fetch user ---------- */
function getUserData($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT username, user_email, user_phonenumber, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$user = getUserData($conn, $user_id);

// Profile picture priority: session > db > default
if (!empty($_SESSION['profile_pic'])) {
    $display_pic = $_SESSION['profile_pic'];
} elseif (!empty($user['profile_pic'])) {
    $display_pic = $user['profile_pic'];
} else {
    $display_pic = 'assets/images/uploads/default.png';
}

ensureCsrfToken();

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "❌ Invalid session token. Please refresh and try again.";
    } else {
        $email = trim($_POST['user_email'] ?? '');
        $phone = trim($_POST['user_phonenumber'] ?? '');
        $new_password = trim($_POST['user_password'] ?? '');

        // Keep existing file value by default (from DB)
        $current_pic = $user['profile_pic'];

        // Are we uploading a new image this time?
        $uploading_new_image = (!empty($_FILES['profile_pic']['name']));

        $target_file = $current_pic; // default to what's already stored

        // --- Validate & process image upload (optional) ---
        if (empty($error) && $uploading_new_image) {
            $upload_dir = "assets/images/uploads";
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0750, true);
            }

            $max_file_size = 5 * 1024 * 1024;
            $tmp_name = $_FILES['profile_pic']['tmp_name'];
            $orig_name = $_FILES['profile_pic']['name'];
            $size = (int)($_FILES['profile_pic']['size'] ?? 0);

            if ($size <= 0 || $size > $max_file_size) {
                $error = "❌ Image must be between 1 byte and 5MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp_name);
                $allowed_mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                if (!array_key_exists($mime, $allowed_mimes)) {
                    $error = "❌ Only JPG, PNG, or GIF images are allowed.";
                } else {
                    $imgInfo = @getimagesize($tmp_name);
                    if ($imgInfo === false) {
                        $error = "❌ Uploaded file is not a valid image.";
                    } else {
                        [$w, $h] = $imgInfo;
                        if ($w < 16 || $h < 16 || $w > 8000 || $h > 8000) {
                            $error = "❌ Image dimensions are not acceptable.";
                        } else {
                            $safeOrig = cleanFilename($orig_name);
                            $rand = bin2hex(random_bytes(4));
                            $filename = time() . "_" . $rand . "_" . $safeOrig;
                            $dest = rtrim($upload_dir, '/') . '/' . $filename;

                            if (!move_uploaded_file($tmp_name, $dest)) {
                                $error = "❌ Failed to upload image.";
                            } else {
                                @chmod($dest, 0640);
                                $target_file = $dest; // New image path
                            }
                        }
                    }
                }
            }
        }

        // --- Update DB if no errors ---
        if (empty($error)) {
            // Build the UPDATE in simple cases to avoid touching profile_pic unless needed
            if ($uploading_new_image && !empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users 
                    SET user_email = ?, user_phonenumber = ?, profile_pic = ?, password = ? 
                    WHERE id = ?");
                $stmt->bind_param("ssssi", $email, $phone, $target_file, $hashed_password, $user_id);
            } elseif ($uploading_new_image && empty($new_password)) {
                $stmt = $conn->prepare("UPDATE users 
                    SET user_email = ?, user_phonenumber = ?, profile_pic = ? 
                    WHERE id = ?");
                $stmt->bind_param("sssi", $email, $phone, $target_file, $user_id);
            } elseif (!$uploading_new_image && !empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users 
                    SET user_email = ?, user_phonenumber = ?, password = ? 
                    WHERE id = ?");
                $stmt->bind_param("sssi", $email, $phone, $hashed_password, $user_id);
            } else {
                // No new image, no new password -> DO NOT touch profile_pic
                $stmt = $conn->prepare("UPDATE users 
                    SET user_email = ?, user_phonenumber = ? 
                    WHERE id = ?");
                $stmt->bind_param("ssi", $email, $phone, $user_id);
            }

            if ($stmt->execute()) {
                // Only update session image if a new image was uploaded
                if ($uploading_new_image) {
                    $_SESSION['profile_pic'] = $target_file;
                }

                $success = "✅ Profile updated successfully.";
                // Refresh user data for the form display
                $user = getUserData($conn, $user_id);

                // Recalculate display pic: session > db > default
                if (!empty($_SESSION['profile_pic'])) {
                    $display_pic = $_SESSION['profile_pic'];
                } elseif (!empty($user['profile_pic'])) {
                    $display_pic = $user['profile_pic'];
                } else {
                    $display_pic = 'assets/images/uploads/default.png';
                }

                // Rotate CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $error = "❌ Update failed. Please try again.";
            }
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
        .password-toggle {
            position: relative;
        }
        .password-toggle-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
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

                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                                               value="<?= htmlspecialchars($_POST['user_phonenumber'] ?? $user['user_phonenumber']) ?>" placeholder="Enter phone number">
                                    </div>

                                    <div class="form-group password-toggle">
                                        <label>New Password <small>(leave blank to keep current)</small></label>
                                        <input type="password" name="user_password" class="form-control" id="passwordInput" placeholder="Enter new password">
                                        <i class="mdi mdi-eye-off password-toggle-icon" id="togglePassword"></i>
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

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('passwordInput');
        const icon = this;
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("mdi-eye-off");
            icon.classList.add("mdi-eye");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("mdi-eye");
            icon.classList.add("mdi-eye-off");
        }
    });
</script>
</body>
</html>
