<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_sent_id = "LS" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO letters_sent 
        (letter_sent_id, client_id, branch_id, letter_type_id, sent_date,
         sic_id, sic_signature, mic_id, mic_signature, follow_up_required, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssis",
        $letter_sent_id,
        $_POST['client_id'],
        $_POST['branch_id'],
        $_POST['letter_type_id'],
        $_POST['sent_date'],
        $_POST['sic_id'],
        $_POST['sic_signature'],
        $_POST['mic_id'],
        $_POST['mic_signature'],
        $_POST['follow_up_required'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Letter sent recorded successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Letter Sent Form - IRB Letter Management System</title>
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

                  <h4 class="page-title">Letter Sent Form</h4>
                  <p class="card-description"> Record a newly sent IRB letter </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= $success ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= $error ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3">
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label>Client ID</label>
                        <input type="text" name="client_id" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>IRB Branch ID</label>
                        <input type="text" name="branch_id" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>Letter Type ID</label>
                        <input type="text" name="letter_type_id" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>Sent Date</label>
                        <input type="date" name="sent_date" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>SIC ID</label>
                        <input type="text" name="sic_id" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>SIC Signature</label>
                        <input type="text" name="sic_signature" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>MIC ID</label>
                        <input type="text" name="mic_id" class="form-control">
                      </div>
                      <div class="col-md-6 form-group">
                        <label>MIC Signature</label>
                        <input type="text" name="mic_signature" class="form-control">
                      </div>

                      <div class="col-md-6 form-group d-flex align-items-center">
                        <label class="mr-3">Follow-up Required</label>
                        <div class="form-check form-check-primary">
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="follow_up_required" value="1"> Yes
                          </label>
                        </div>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Any additional comments..."></textarea>
                      </div>
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-send"></i> Submit
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
