<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_id = "1000" . rand(10,99);
    $stmt = $conn->prepare("INSERT INTO irb_branches (branch_id, name, phone, email, street, pcode, city, state)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss",
        $branch_id, $_POST['name'], $_POST['phone'], $_POST['email'],
        $_POST['street'], $_POST['pcode'], $_POST['city'], $_POST['state']);
    
    if ($stmt->execute()) {
        $success = "IRB Branch added successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add IRB Branch - IRB Letter Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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

                  <h4 class="page-title">Add IRB Branch</h4>
                  <p class="card-description"> Fill in branch details below </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= $success ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= $error ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3">
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label>Branch Name</label>
                        <input type="text" name="name" class="form-control" required>
                      </div>
                      <div class="col-md-6 form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
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
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-map-marker-plus"></i> Add Branch
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
