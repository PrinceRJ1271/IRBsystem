<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 Not Found - IRB Letter Management System</title>
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
  <style>
    .page-title {
      font-weight: 600;
      color: #4B49AC;
    }
    .error-card {
      text-align: center;
      padding: 3rem 2rem;
    }
    .error-card h1 {
      font-size: 6rem;
      font-weight: bold;
      color: #ff5e5e;
    }
    .error-icon {
      font-size: 5rem;
      color: #ff5e5e;
    }
    .btn-back {
      margin-top: 1.5rem;
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper">

      <?php include 'includes/sidebar.php'; ?>

      <div class="main-panel">
        <?php include 'includes/header.php'; ?>

        <div class="content-wrapper d-flex align-items-center justify-content-center">
          <div class="row w-100">
            <div class="col-lg-8 mx-auto">
              <div class="card error-card shadow-lg">
                <div class="card-body">
                  <i class="mdi mdi-alert-circle-outline error-icon"></i>
                  <h1>404</h1>
                  <h4 class="mb-3">Page Not Found</h4>
                  <p class="text-muted mb-4">Oops! The page you are looking for doesn't exist or may have been moved.</p>
                  <a href="dashboard.php" class="btn btn-primary btn-back">
                    <i class="mdi mdi-home"></i> Back to Dashboard
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php include 'includes/footer.php'; ?>
      </div>
    </div>
  </div>

  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/misc.js"></script>
</body>
</html>
