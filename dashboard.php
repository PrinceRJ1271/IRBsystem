<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$level_id = $_SESSION['level_id'];
include 'includes/header.php'; // Already contains head + assets loaded
?>

<!-- partial -->
<div class="container-scroller">
  <?php include 'includes/sidebar.php'; ?> <!-- Create based on your role, optional -->

  <div class="container-fluid page-body-wrapper">
    <div class="main-panel">
      <div class="content-wrapper">

        <!-- Dashboard Greeting -->
        <div class="row">
          <div class="col-12 grid-margin">
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Welcome, <?php echo htmlspecialchars($user_id); ?>!</h3>
                <p>Your Role Level: <?php echo $level_id; ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Role-Based Options -->
        <div class="row">
          <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">
                  <?php
                    switch ($level_id) {
                      case 1: echo "Developer Dashboard"; break;
                      case 2: echo "Tax Manager Dashboard"; break;
                      case 3: echo "Tax Senior Dashboard"; break;
                      case 4: echo "Admin Staff Dashboard"; break;
                      default: echo "Unknown Role";
                    }
                  ?>
                </h4>
                <ul class="list-arrow">
                  <?php if ($level_id == 1): ?>
                    <li><a href="#">Manage Users</a></li>
                    <li><a href="#">Manage Security Levels</a></li>
                    <li><a href="#">System Debugging</a></li>
                    <li><a href="forms/client_form.php">Register Client</a></li>
                    <li><a href="forms/irb_branch_form.php">Add IRB Branch</a></li>
                    <li><a href="forms/letter_type_form.php">Add Letter Type</a></li>
                    <li><a href="forms/letter_received_form.php">IRB Letter Received</a></li>
                    <li><a href="forms/letter_received_followup_form.php">Follow-up (Received)</a></li>
                    <li><a href="forms/letter_sent_form.php">IRB Letter Sent</a></li>
                    <li><a href="forms/letter_sent_followup_form.php">Follow-up (Sent)</a></li>
                    <li><a href="forms/letter_delivery_form.php">Letter Delivery</a></li>
                    <li><a href="search/quick_search.php">Quick Search</a></li>
                    <li><a href="export/export_letters_pdf.php" target="_blank">Export to PDF</a></li>
                    <li><a href="export/export_letters_excel.php" target="_blank">Export to Excel</a></li>
                  <?php elseif ($level_id == 2): ?>
                    <!-- Tax Manager List -->
                    <li><a href="#">Register Staff</a></li>
                    <!-- ...repeat appropriate links -->
                  <?php elseif ($level_id == 3): ?>
                    <!-- Tax Senior List -->
                    <!-- ... -->
                  <?php elseif ($level_id == 4): ?>
                    <!-- Admin Staff List -->
                    <!-- ... -->
                  <?php else: ?>
                    <li>Invalid role. Please contact admin.</li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- content-wrapper ends -->
      <?php include 'includes/footer.php'; ?>
    </div>
    <!-- main-panel ends -->
  </div>
  <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->
