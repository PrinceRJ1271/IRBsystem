<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$level_id = $_SESSION['level_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - IRB Letter Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
  <style>
    .hero-welcome {
        background: linear-gradient(to right, #4e54c8, #8f94fb);
        color: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .dashboard-card {
        transition: all 0.3s ease-in-out;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .card-title {
        font-weight: 600;
        color: #343a40;
    }
    ul.list-arrow {
        padding-left: 0;
        list-style-type: none;
    }
    ul.list-arrow li {
        margin-bottom: 12px;
    }
    ul.list-arrow li a {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #444;
        font-weight: 500;
    }
    ul.list-arrow li a:hover {
        color: #4e54c8;
    }
    ul.list-arrow li i {
        font-size: 18px;
        margin-right: 10px;
        color: #4e54c8;
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper d-flex">
      
      <!-- Sidebar -->
      <?php include 'includes/sidebar.php'; ?>

      <!-- Main Panel -->
      <div class="main-panel flex-grow-1">
        
        <!-- Header -->
        <?php include 'includes/header.php'; ?>

        <div class="content-wrapper">

          <div class="hero-welcome shadow-sm">
            <h2>Welcome back, <strong><?php echo htmlspecialchars($user_id); ?></strong></h2>
            <p class="mb-0">You are logged in as <strong>Level <?php echo $level_id; ?></strong>.</p>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card dashboard-card">
                <div class="card-body">
                  
                  <?php if ($level_id == 1): ?>
                    <h4 class="card-title">Developer Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="#"><i class="mdi mdi-account-cog"></i>Manage Users</a></li>
                      <li><a href="#"><i class="mdi mdi-shield-lock"></i>Manage Security Levels</a></li>
                      <li><a href="#"><i class="mdi mdi-wrench"></i>System Debugging</a></li>
                      <li><a href="forms/client_form.php"><i class="mdi mdi-account-plus"></i>Register Client</a></li>
                      <li><a href="forms/irb_branch_form.php"><i class="mdi mdi-office-building"></i>Add IRB Branch</a></li>
                      <li><a href="forms/letter_type_form.php"><i class="mdi mdi-file-document-multiple"></i>Add Letter Type</a></li>
                      <li><a href="forms/letter_received_form.php"><i class="mdi mdi-email-receive"></i>IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php"><i class="mdi mdi-phone-incoming"></i>Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php"><i class="mdi mdi-email-send"></i>IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php"><i class="mdi mdi-phone-outgoing"></i>Follow-up Form (Sent)</a></li>
                      <li><a href="forms/letter_delivery_form.php"><i class="mdi mdi-truck-delivery"></i>Letter Delivery Form</a></li>
                      <li><a href="search/quick_search.php"><i class="mdi mdi-magnify"></i>Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank"><i class="mdi mdi-file-pdf-box"></i>Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank"><i class="mdi mdi-file-excel-box"></i>Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 2): ?>
                    <h4 class="card-title">Tax Manager Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="#"><i class="mdi mdi-account-plus"></i>Register Staff</a></li>
                      <li><a href="forms/client_form.php"><i class="mdi mdi-account-plus"></i>Register Client</a></li>
                      <li><a href="forms/irb_branch_form.php"><i class="mdi mdi-office-building"></i>Add IRB Branch</a></li>
                      <li><a href="forms/letter_type_form.php"><i class="mdi mdi-file-document-multiple"></i>Add Letter Type</a></li>
                      <li><a href="forms/letter_received_form.php"><i class="mdi mdi-email-receive"></i>IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php"><i class="mdi mdi-phone-incoming"></i>Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php"><i class="mdi mdi-email-send"></i>IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php"><i class="mdi mdi-phone-outgoing"></i>Follow-up Form (Sent)</a></li>
                      <li><a href="#"><i class="mdi mdi-chart-box-outline"></i>View Reports</a></li>
                      <li><a href="search/quick_search.php"><i class="mdi mdi-magnify"></i>Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank"><i class="mdi mdi-file-pdf-box"></i>Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank"><i class="mdi mdi-file-excel-box"></i>Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 3): ?>
                    <h4 class="card-title">Tax Senior Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="forms/client_form.php"><i class="mdi mdi-account-box-outline"></i>Client Engagement Form</a></li>
                      <li><a href="forms/letter_received_form.php"><i class="mdi mdi-email-receive"></i>IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php"><i class="mdi mdi-phone-incoming"></i>Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php"><i class="mdi mdi-email-send"></i>IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php"><i class="mdi mdi-phone-outgoing"></i>Follow-up Form (Sent)</a></li>
                      <li><a href="search/quick_search.php"><i class="mdi mdi-magnify"></i>Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank"><i class="mdi mdi-file-pdf-box"></i>Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank"><i class="mdi mdi-file-excel-box"></i>Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 4): ?>
                    <h4 class="card-title">Admin Staff Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="forms/letter_delivery_form.php"><i class="mdi mdi-truck-fast"></i>Letter Delivery Form</a></li>
                      <li><a href="#"><i class="mdi mdi-map-marker-path"></i>Track Deliveries</a></li>
                      <li><a href="search/quick_search.php"><i class="mdi mdi-magnify"></i>Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank"><i class="mdi mdi-file-pdf-box"></i>Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank"><i class="mdi mdi-file-excel-box"></i>Export Letters to Excel</a></li>
                    </ul>

                  <?php else: ?>
                    <p class="text-danger">Invalid role. Please contact admin.</p>
                  <?php endif; ?>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
      </div> <!-- main-panel -->
    </div> <!-- page-body-wrapper -->
  </div> <!-- container-scroller -->

  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/misc.js"></script>
</body>
</html>
