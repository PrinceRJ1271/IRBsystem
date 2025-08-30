<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2]); // Developer & Manager

$success = $error = "";

// Handle create
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_id = ($_POST['letter_type'] == 'Received') ? "2000" . rand(10,99) : "2100" . rand(10,99);
    $stmt = $conn->prepare("INSERT INTO letter_types (letter_id, description, letter_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $letter_id, $_POST['description'], $_POST['letter_type']);

    if ($stmt->execute()) {
        $success = "Letter type added successfully!";
        // Clear form fields after success
        $_POST = [];
    } else {
        $error = "Error: " . htmlspecialchars($stmt->error);
    }
}

// Fetch current letter types for the table
$list_rs = $conn->query("SELECT letter_id, description, letter_type FROM letter_types ORDER BY letter_type ASC, description ASC");
$letter_types = [];
if ($list_rs) {
    while ($row = $list_rs->fetch_assoc()) {
        $letter_types[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Letter Type - IRB System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="shortcut icon" href="../assets/images/favicon.png" />
  <style>
    .page-title{font-weight:600;color:#4B49AC}
    .card{border-radius:1rem;box-shadow:0 4px 10px rgba(0,0,0,0.1)}
    .form-group label{font-weight:500}
    .table thead th{background:#f7f7fb;font-weight:600;border-top:none}
    .badge-soft{
      background: #eef2ff;
      color: #4B49AC;
      border: 1px solid #e0e7ff;
      padding: .35rem .5rem;
      border-radius: .5rem;
      font-weight: 600;
      font-size: .75rem;
    }
    .search-wrap {
      display:flex; gap:.5rem; align-items:center; justify-content: flex-end;
    }
    .search-wrap input {
      max-width: 260px;
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
            <!-- Create form -->
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="page-title">Add Letter Type</h4>
                  <p class="card-description"> Use this form to create a new letter type </p>

                  <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                  <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-2" autocomplete="off">
                    <div class="form-group">
                      <label for="description">Letter Description</label>
                      <input
                        type="text"
                        name="description"
                        id="description"
                        class="form-control"
                        placeholder="Enter description"
                        required
                        value="<?= htmlspecialchars($_POST['description'] ?? '') ?>"
                      >
                    </div>

                    <div class="form-group">
                      <label for="letter_type">Letter Type</label>
                      <select name="letter_type" id="letter_type" class="form-control" required>
                        <option value="Received" <?= (($_POST['letter_type'] ?? '')==='Received')?'selected':'' ?>>Received</option>
                        <option value="Sent" <?= (($_POST['letter_type'] ?? '')==='Sent')?'selected':'' ?>>Sent</option>
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

            <!-- Listing table -->
            <div class="col-md-10 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="page-title mb-0">Current Letter Types</h4>
                    <div class="search-wrap">
                      <input id="lt-search" type="text" class="form-control form-control-sm" placeholder="Search description/type...">
                    </div>
                  </div>
                  <p class="card-description"> Verify that your newly added type appears here. Use search to avoid duplicates. </p>

                  <div class="table-responsive">
                    <table id="lt-table" class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th style="width:80px;">Letter ID</th>
                          <th>Description</th>
                          <th style="width:120px;">Type</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php if (count($letter_types) > 0): ?>
                        <?php foreach ($letter_types as $row): ?>
                          <tr>
                            <td><code><?= htmlspecialchars($row['letter_id']) ?></code></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>
                              <span class="badge-soft">
                                <?= htmlspecialchars($row['letter_type']) ?>
                              </span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No letter types found.</td></tr>
                      <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

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
  <script>
    // Lightweight client-side filter for the table
    (function(){
      var input = document.getElementById('lt-search');
      var table = document.getElementById('lt-table');
      if (!input || !table) return;

      input.addEventListener('input', function(){
        var q = this.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(tr){
          var text = tr.innerText.toLowerCase();
          tr.style.display = text.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    })();
  </script>
</body>
</html>
