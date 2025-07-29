<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior only

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = "40000" . rand(100,999); // auto-ID style
    $stmt = $conn->prepare("INSERT INTO clients (client_id, company_name, pic_name, company_phone, pic_phone, pic_email, street, pcode, city, state, sic_id, mic_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss",
        $client_id, $_POST['company_name'], $_POST['pic_name'], $_POST['company_phone'],
        $_POST['pic_phone'], $_POST['pic_email'], $_POST['street'], $_POST['pcode'],
        $_POST['city'], $_POST['state'], $_POST['sic_id'], $_POST['mic_id']);

    if ($stmt->execute()) {
        $success = "Client added successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Client - IRB Letter Management System</title>
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

                  <h4 class="page-title">Register New Client</h4>
                  <p class="card-description"> Fill in all required details </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= $success ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= $error ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3">
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>PIC Name</label>
                        <input type="text" name="pic_name" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Company Phone</label>
                        <input type="text" name="company_phone" class="form-control">
                      </div>
                      <div class="col-md-6 form-group">
                        <label>PIC Phone</label>
                        <input type="text" name="pic_phone" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>PIC Email</label>
                        <input type="email" name="pic_email" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>Street</label>
                        <input type="text" name="street" class="form-control">
                      </div>

                      <div class="col-md-4 form-group">
                        <label>Postal Code</label>
                        <input type="text" name="pcode" class="form-control">
                      </div>
                      <div class="col-md-4 form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control">
                      </div>
                      <div class="col-md-4 form-group">
                        <label>State</label>
                        <input type="text" name="state" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>SIC ID <small>(e.g., 20001)</small></label>
                        <input type="text" name="sic_id" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>MIC ID <small>(e.g., 10002)</small></label>
                        <input type="text" name="mic_id" class="form-control" required>
                      </div>
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-account-plus"></i> Register Client
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
