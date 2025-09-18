<?php
// register.php – styled like client_form.php, with sidebar + header
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Allow ONLY Developer (1)
check_access([1]);

$success = '';
$error   = '';

/** Helper: escape text safely */
function safe($v) {
  return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect inputs
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['user_email'] ?? '');
    $phone       = trim($_POST['user_phonenumber'] ?? '');
    $level_id    = (int)($_POST['level_id'] ?? 0);
    $password    = $_POST['password'] ?? '';
    $password2   = $_POST['confirm_password'] ?? '';

    // Basic validation
    if ($username === '') {
        $error = 'Please enter a username.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($level_id < 1 || $level_id > 4) {
        $error = 'Please select a valid role.';
    } elseif ($password === '' || strlen($password) < 6) {
        $error = 'Please enter a password with at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        // Default profile pic (if none uploaded)
        $profile_path = 'assets/images/default.png';

        // Optional profile image upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 5 * 1024 * 1024;
            $file_type     = $_FILES['profile_pic']['type'];
            $file_size     = $_FILES['profile_pic']['size'];
            $tmp_name      = $_FILES['profile_pic']['tmp_name'];
            $filename      = time() . '_' . basename($_FILES['profile_pic']['name']);
            $upload_dir    = __DIR__ . '/assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0775, true);
            }
            $upload_path   = $upload_dir . $filename;
            $db_rel_path   = 'assets/images/uploads/' . $filename; // what we store in DB

            if (!in_array($file_type, $allowed_types)) {
                $error = "❌ Only JPG, PNG, or GIF images are allowed.";
            } elseif ($file_size > $max_file_size) {
                $error = "❌ Image must be less than 5MB.";
            } elseif (!move_uploaded_file($tmp_name, $upload_path)) {
                $error = "❌ Failed to upload image.";
            } else {
                $profile_path = $db_rel_path;
            }
        }

        // Only proceed if no upload/validation error
        if (empty($error)) {
            // Hash password; store in `password` column (NOT user_password)
            $hashed  = password_hash($password, PASSWORD_DEFAULT);
            // Generate custom user_id like your old code
            $user_id = uniqid('usr_');

            // Check duplicates (email OR username)
            $dup = $conn->prepare("SELECT 1 FROM users WHERE user_email = ? OR username = ? LIMIT 1");
            $dup->bind_param("ss", $email, $username);
            $dup->execute();
            $dup->store_result();

            if ($dup->num_rows > 0) {
                $error = 'An account with that email or username already exists.';
            } else {
                // Insert new user (explicit columns incl. user_id + profile_pic)
                $stmt = $conn->prepare("
                    INSERT INTO users (user_id, username, user_email, user_phonenumber, profile_pic, password, level_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("ssssssi", $user_id, $username, $email, $phone, $profile_path, $hashed, $level_id);

                if ($stmt->execute()) {
                    // Redirect to login like your original behavior
                    header("Location: login.php");
                    exit;
                } else {
                    $error = '❌ Failed to register user. ' . safe($stmt->error);
                }
            }
            $dup->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register User - IRB System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Star Admin / Vendors / Theme CSS (same as forms pages) -->
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
  <style>
    .card { border-radius: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .page-title { font-weight: 600; color: #4B49AC; }
    .form-group label { font-weight: 500; }
  </style>
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper">

      <!-- Sidebar -->
      <?php include __DIR__ . '/includes/sidebar.php'; ?>

      <div class="main-panel">
        <!-- Header -->
        <?php include __DIR__ . '/includes/header.php'; ?>

        <div class="content-wrapper">
          <div class="row justify-content-center">
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="page-title">Register User</h4>
                  <p class="card-description"> Create a new user account </p>

                  <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= safe($error) ?></div>
                  <?php endif; ?>

                  <form method="post" enctype="multipart/form-data" autocomplete="off" class="pt-2">
                    <div class="form-group">
                      <label for="username">Username</label>
                      <input type="text" name="username" id="username" class="form-control"
                             placeholder="Enter username" required
                             value="<?= safe($_POST['username'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                      <label for="user_email">Email</label>
                      <input type="email" name="user_email" id="user_email" class="form-control"
                             placeholder="Enter email" required
                             value="<?= safe($_POST['user_email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                      <label for="user_phonenumber">Phone Number</label>
                      <input type="text" name="user_phonenumber" id="user_phonenumber" class="form-control"
                             placeholder="Enter phone number"
                             value="<?= safe($_POST['user_phonenumber'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                      <label for="level_id">Role</label>
                      <select name="level_id" id="level_id" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <option value="1" <?= (($_POST['level_id'] ?? '') == '1') ? 'selected' : '' ?>>Developer</option>
                        <option value="2" <?= (($_POST['level_id'] ?? '') == '2') ? 'selected' : '' ?>>Tax Manager</option>
                        <option value="3" <?= (($_POST['level_id'] ?? '') == '3') ? 'selected' : '' ?>>Tax Senior</option>
                        <option value="4" <?= (($_POST['level_id'] ?? '') == '4') ? 'selected' : '' ?>>Admin Staff</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="password">Password</label>
                      <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                      <label for="confirm_password">Confirm Password</label>
                      <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
                    </div>

                    <div class="form-group">
                      <label for="profile_pic">Profile Picture (optional)</label>
                      <input type="file" name="profile_pic" id="profile_pic" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                      <small class="text-muted d-block mt-1">Max size: 5MB | Types: JPG, PNG, GIF</small>
                    </div>

                    <div class="text-center mt-4">
                      <button type="submit" class="btn btn-success btn-lg font-weight-medium">
                        <i class="mdi mdi-account-plus-outline"></i> Create User
                      </button>
                      <a href="/dashboard.php" class="btn btn-light btn-lg font-weight-medium">Cancel</a>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include __DIR__ . '/includes/footer.php'; ?>

      </div>
    </div>
  </div>

  <!-- JS bundle (same as other forms) -->
  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/hoverable-collapse.js"></script>
  <script src="assets/js/misc.js"></script>
</body>
</html>
