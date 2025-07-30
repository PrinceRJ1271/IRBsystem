<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2]); // Developer & Manager

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_id = ($_POST['letter_type'] == 'Received') ? "2000" . rand(10,99) : "2100" . rand(10,99);
    $stmt = $conn->prepare("INSERT INTO letter_types (letter_id, description, letter_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $letter_id, $_POST['description'], $_POST['letter_type']);

    if ($stmt->execute()) {
        $success = "Letter type added successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Letter Type - IRB System</title>
  <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body>
  <div class="container-scroller">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid page-body-wrapper">
      <?php include '../includes/header.php'; ?>

      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row justify-content-center">
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card shadow">
                <div class="card-body">
                  <h4 class="card-title text-primary">Add Letter Type</h4>
                  <p class="card-description"> Use this form to create a new letter type record. </p>

                  <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                  <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-2">
                    <div class="form-group">
                      <label for="description">Letter Description</label>
                      <input type="text" name="description" id="description" class="form-control" placeholder="Enter description" required>
                    </div>

                    <div class="form-group">
                      <label for="letter_type">Letter Type</label>
                      <select name="letter_type" id="letter_type" class="form-control" required>
                        <option value="Received">Received</option>
                        <option value="Sent">Sent</option>
                      </select>
                    </div>

                    <div class="text-center mt-4">
                      <button type="submit" class="btn btn-success btn-lg font-weight-medium">
                        <i class="mdi mdi-plus-circle-outline"></i> Add Letter Type
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
  <script src="../assets/js/hoverable-collapse.js"></script>
  <script src="../assets/js/misc.js"></script>
</body>
</html>
