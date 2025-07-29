<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$level_id = $_SESSION['level_id'];

include 'header.php';
include 'sidebar.php';
?>

<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h3>Welcome, <?php echo $user_id; ?>!</h3>
            <p>Your Role Level: <?php echo $level_id; ?></p>
            <h4 class="mt-4">
              <?php
              switch ($level_id) {
                case 1: echo "Developer Dashboard"; break;
                case 2: echo "Tax Manager Dashboard"; break;
                case 3: echo "Tax Senior Dashboard"; break;
                case 4: echo "Admin Staff Dashboard"; break;
                default: echo "Invalid role. Please contact admin."; break;
              }
              ?>
            </h4>

            <ul class="list-group mt-3">
              <?php if ($level_id == 1): ?>
                <li class="list-group-item"><a href="#">Manage Users</a></li>
                <li class="list-group-item"><a href="#">Manage Security Levels</a></li>
                <li class="list-group-item"><a href="#">System Debugging</a></li>
                <li class="list-group-item"><a href="forms/client_form.php">Register Client</a></li>
                <li class="list-group-item"><a href="forms/irb_branch_form.php">Add IRB Branch</a></li>
                <li class="list-group-item"><a href="forms/letter_type_form.php">Add Letter Type</a></li>
                <li class="list-group-item"><a href="forms/letter_received_form.php">IRB Letter Received</a></li>
                <li class="list-group-item"><a href="forms/letter_received_followup_form.php">Follow-up (Received)</a></li>
                <li class="list-group-item"><a href="forms/letter_sent_form.php">IRB Letter Sent</a></li>
                <li class="list-group-item"><a href="forms/letter_sent_followup_form.php">Follow-up (Sent)</a></li>
                <li class="list-group-item"><a href="forms/letter_delivery_form.php">Letter Delivery</a></li>
                <li class="list-group-item"><a href="search/quick_search.php">Quick Search</a></li>
                <li class="list-group-item"><a href="export/export_letters_pdf.php" target="_blank">Export to PDF</a></li>
                <li class="list-group-item"><a href="export/export_letters_excel.php" target="_blank">Export to Excel</a></li>
              <?php elseif ($level_id == 2): ?>
                <li class="list-group-item"><a href="#">Register Staff</a></li>
                <!-- Remaining same as above minus Developer-specific -->
              <?php elseif ($level_id == 3): ?>
                <li class="list-group-item"><a href="forms/client_form.php">Client Engagement Form</a></li>
                <!-- Remaining same -->
              <?php elseif ($level_id == 4): ?>
                <li class="list-group-item"><a href="forms/letter_delivery_form.php">Letter Delivery</a></li>
                <li class="list-group-item"><a href="#">Track Deliveries</a></li>
              <?php endif; ?>
              <li class="list-group-item"><a href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include 'footer.php'; ?>
