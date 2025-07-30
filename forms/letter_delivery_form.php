<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 4]); // Developer or Admin Staff

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_id = "LD" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_delivered 
        (delivery_id, letter_sent_id, collection_date, delivered_date, delivery_method,
         tracking_number, ad_staff_id, ad_signature, status, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $delivery_id,
        $_POST['letter_sent_id'],
        $_POST['collection_date'],
        $_POST['delivered_date'],
        $_POST['delivery_method'],
        $_POST['tracking_number'],
        $_POST['ad_staff_id'],
        $_POST['ad_signature'],
        $_POST['status'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Letter delivery recorded successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Letter Delivery Form - IRB Letter Management System</title>
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

                  <h4 class="page-title">Letter Delivery Form (Admin Staff)</h4>
                  <p class="card-description"> Record the final delivery details of the letter </p>

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
                        <label>Collection Date</label>
                        <input type="date" name="collection_date" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Delivered Date</label>
                        <input type="date" name="delivered_date" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Delivery Method</label>
                        <select name="delivery_method" class="form-control" required>
                          <option value="Courier">Courier</option>
                          <option value="Dispatch">Dispatch</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Tracking Number (optional)</label>
                        <input type="text" name="tracking_number" class="form-control">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Admin Staff ID</label>
                        <input type="text" name="ad_staff_id" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Admin Signature</label>
                        <input type="text" name="ad_signature" class="form-control" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                          <option value="Pending">Pending</option>
                          <option value="Completed">Completed</option>
                        </select>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks (optional)</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
                      </div>
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-truck-delivery"></i> Submit Delivery
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
