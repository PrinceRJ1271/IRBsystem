<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Dev, Manager, Senior

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_received_id = "LR" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO letters_received 
        (letter_received_id, client_id, branch_id, letter_type_id, received_date, scanned_copy_required,
         email_to_client_required, filed, sic_id, sic_signature, mic_id, mic_signature, follow_up_required, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssiisssssis",
        $letter_received_id,
        $_POST['client_id'],
        $_POST['branch_id'],
        $_POST['letter_type_id'],
        $_POST['received_date'],
        $_POST['scanned_copy_required'],
        $_POST['email_to_client_required'],
        $_POST['filed'],
        $_POST['sic_id'],
        $_POST['sic_signature'],
        $_POST['mic_id'],
        $_POST['mic_signature'],
        $_POST['follow_up_required'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Letter received successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IRB Letter Received Form</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="shortcut icon" href="../assets/images/favicon.png" />
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
                <h4 class="page-title">IRB Letter Received Form</h4>
                <p class="card-description"> Fill in all required details </p>

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
                      <label>Date Received</label>
                      <input type="date" name="received_date" class="form-control" required>
                    </div>
                  </div>

                  <div class="row pt-3 ps-2">
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="scanned_copy_required" value="1" id="scannedCopy">
                        <label class="form-check-label" for="scannedCopy">Scanned Copy Required</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="email_to_client_required" value="1" id="emailToClient">
                        <label class="form-check-label" for="emailToClient">Email to Client Required</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filed" value="1" id="filed">
                        <label class="form-check-label" for="filed">Filed</label>
                      </div>
                    </div>
                  </div>

                  <div class="row pt-3">
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
                  </div>

                  <div class="row pt-3 ps-2">
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="follow_up_required" value="1" id="followUp">
                        <label class="form-check-label" for="followUp">Follow-up Required</label>
                      </div>
                    </div>
                  </div>

                  <div class="row pt-3">
                    <div class="col-md-12 form-group">
                      <label>Remarks</label>
                      <textarea name="remark" class="form-control" rows="3"></textarea>
                    </div>
                  </div>

                  <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg">
                      <i class="mdi mdi-email-outline"></i> Submit Form
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
