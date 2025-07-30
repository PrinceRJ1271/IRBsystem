<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $followup_sent_id = "SF" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_sent_followup
        (followup_sent_id, letter_sent_id, phone_call_irb, call_date, irb_reply,
         ack_required, ack_received, followup_status, change_of_sic, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $followup_sent_id,
        $_POST['letter_sent_id'],
        $_POST['phone_call_irb'],
        $_POST['call_date'],
        $_POST['irb_reply'],
        $_POST['ack_required'],
        $_POST['ack_received'],
        $_POST['followup_status'],
        $_POST['change_of_sic'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Follow-up recorded successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Letter Sent Follow-up - IRB Letter Management System</title>
  <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="shortcut icon" href="../assets/images/favicon.png" />
  <style>
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

      <?php include '../includes/sidebar.php'; ?>

      <div class="main-panel">
        <?php include '../includes/header.php'; ?>

        <div class="content-wrapper">
          <div class="row justify-content-center">
            <div class="col-md-10 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">

                  <h4 class="page-title">Letter Sent Follow-up Form</h4>
                  <p class="card-description"> Record any follow-up actions taken after sending a letter </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= $success ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= $error ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3">
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label>Letter Sent ID</label>
                        <input type="text" name="letter_sent_id" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Phone Call to IRB</label>
                        <select name="phone_call_irb" class="form-control">
                          <option value="Yes">Yes</option>
                          <option value="N/A">N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Call Date</label>
                        <input type="date" name="call_date" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>IRB Reply</label>
                        <select name="irb_reply" class="form-control">
                          <option value="Success">Success</option>
                          <option value="Fail">Fail</option>
                          <option value="Pending">Pending</option>
                          <option value="N/A">N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Acknowledgment Required</label>
                        <select name="ack_required" class="form-control">
                          <option value="Yes">Yes</option>
                          <option value="N/A">N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Acknowledgment Received</label>
                        <select name="ack_received" class="form-control">
                          <option value="Yes">Yes</option>
                          <option value="No">No</option>
                          <option value="Pending">Pending</option>
                          <option value="N/A">N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Follow-up Status</label>
                        <select name="followup_status" class="form-control">
                          <option value="Pending">Pending</option>
                          <option value="Completed">Completed</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Change of SIC</label>
                        <select name="change_of_sic" class="form-control">
                          <option value="No">No</option>
                          <option value="Yes">Yes</option>
                        </select>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Any remarks..."></textarea>
                      </div>
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-check-circle"></i> Submit Follow-up
                      </button>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>

        <?php include '../includes/footer.php'; ?>
      </div>
    </div>
  </div>

  <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="../assets/js/off-canvas.js"></script>
  <script src="../assets/js/misc.js"></script>
</body>
</html>
