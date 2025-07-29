<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$level_id = $_SESSION['level_id'];
?>

<?php include 'includes/header.php'; ?>

<body>
  <div class="container-scroller">
    <?php include 'includes/sidebar.php'; ?>

    <div class="container-fluid page-body-wrapper">
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="page-header">
            <h3 class="page-title">Welcome, <?php echo htmlspecialchars($user_id); ?>!</h3>
            <p>Your Role Level: <?php echo $level_id; ?></p>
          </div>

          <div class="row">
            <div class="col-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <?php if ($level_id == 1): ?>
                    <h4 class="card-title">Developer Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="#">Manage Users</a></li>
                      <li><a href="#">Manage Security Levels</a></li>
                      <li><a href="#">System Debugging</a></li>
                      <li><a href="forms/client_form.php">Register Client</a></li>
                      <li><a href="forms/irb_branch_form.php">Add IRB Branch</a></li>
                      <li><a href="forms/letter_type_form.php">Add Letter Type</a></li>
                      <li><a href="forms/letter_received_form.php">IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php">Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php">IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php">Follow-up Form (Sent)</a></li>
                      <li><a href="forms/letter_delivery_form.php">Letter Delivery Form</a></li>
                      <li><a href="search/quick_search.php">Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank">Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank">Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 2): ?>
                    <h4 class="card-title">Tax Manager Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="#">Register Staff</a></li>
                      <li><a href="forms/client_form.php">Register Client</a></li>
                      <li><a href="forms/irb_branch_form.php">Add IRB Branch</a></li>
                      <li><a href="forms/letter_type_form.php">Add Letter Type</a></li>
                      <li><a href="forms/letter_received_form.php">IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php">Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php">IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php">Follow-up Form (Sent)</a></li>
                      <li><a href="#">View Reports</a></li>
                      <li><a href="search/quick_search.php">Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank">Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank">Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 3): ?>
                    <h4 class="card-title">Tax Senior Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="forms/client_form.php">Client Engagement Form</a></li>
                      <li><a href="forms/letter_received_form.php">IRB Letter Received Form</a></li>
                      <li><a href="forms/letter_received_followup_form.php">Follow-up Form (Received)</a></li>
                      <li><a href="forms/letter_sent_form.php">IRB Letter Sent Form</a></li>
                      <li><a href="forms/letter_sent_followup_form.php">Follow-up Form (Sent)</a></li>
                      <li><a href="search/quick_search.php">Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank">Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank">Export Letters to Excel</a></li>
                    </ul>

                  <?php elseif ($level_id == 4): ?>
                    <h4 class="card-title">Admin Staff Dashboard</h4>
                    <ul class="list-arrow">
                      <li><a href="forms/letter_delivery_form.php">Letter Delivery Form</a></li>
                      <li><a href="#">Track Deliveries</a></li>
                      <li><a href="search/quick_search.php">Quick Search</a></li>
                      <li><a href="export/export_letters_pdf.php" target="_blank">Export Letters to PDF</a></li>
                      <li><a href="export/export_letters_excel.php" target="_blank">Export Letters to Excel</a></li>
                    </ul>

                  <?php else: ?>
                    <p>Invalid role. Please contact admin.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php include 'includes/footer.php'; ?>
      </div>
    </div>
  </div>
</body>
</html>
