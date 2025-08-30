<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

/* -------------------- Load dropdown data -------------------- */
// Company Name dropdown (stores clients.client_id)
$clients_rs = $conn->query("SELECT client_id, company_name FROM clients ORDER BY company_name ASC");

// IRB Branch Name dropdown (stores irb_branches.branch_id)
$branches_rs = $conn->query("SELECT branch_id, name FROM irb_branches ORDER BY name ASC");

// Letter Type for SENT letters (stores letter_types.letter_id or id; you use letter_type_id column)
$types_rs = $conn->query("
    SELECT id, letter_id, description 
    FROM letter_types 
    WHERE letter_type = 'Sent' OR letter_type IS NULL 
    ORDER BY description ASC
");

// Build arrays for easy rendering + repopulation
$clients = [];
if ($clients_rs) {
    while ($row = $clients_rs->fetch_assoc()) { $clients[] = $row; }
}
$branches = [];
if ($branches_rs) {
    while ($row = $branches_rs->fetch_assoc()) { $branches[] = $row; }
}
$types = [];
if ($types_rs) {
    while ($row = $types_rs->fetch_assoc()) { $types[] = $row; }
}

/* -------------------- Handle form submission -------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $letter_sent_id = "LS" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    // Normalize checkbox to 0/1 so bind types remain correct
    $follow_up_required = isset($_POST['follow_up_required']) ? 1 : 0;

    // Your original insert (unchanged columns/order)
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
        $follow_up_required,
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Letter sent recorded successfully!";
        // Reset POST so form clears nicely after success
        $_POST = [];
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
    .page-title { font-weight: 600; color: #4B49AC; }
    .card { border-radius: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .form-group label { font-weight: 500; }
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
                      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3" autocomplete="off">
                    <div class="row">
                      <!-- Company Name dropdown (stores client_id) -->
                      <div class="col-md-6 form-group">
                        <label><strong>Company Name</strong></label>
                        <select name="client_id" class="form-control" required>
                          <option value="">-- Select Company --</option>
                          <?php foreach ($clients as $c): ?>
                            <?php
                              $val = $c['client_id'];
                              $text = $c['company_name'] . ' (' . $c['client_id'] . ')';
                              $selected = (($_POST['client_id'] ?? '') === $val) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                              <?= htmlspecialchars($text) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <!-- IRB Branch Name dropdown (stores branch_id) -->
                      <div class="col-md-6 form-group">
                        <label><strong>IRB Branch Name</strong></label>
                        <select name="branch_id" class="form-control" required>
                          <option value="">-- Select Branch --</option>
                          <?php foreach ($branches as $b): ?>
                            <?php
                              $val = $b['branch_id'];
                              $text = $b['name'] . ' (' . $b['branch_id'] . ')';
                              $selected = (($_POST['branch_id'] ?? '') === $val) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                              <?= htmlspecialchars($text) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <!-- Letter Type dropdown (stores letter_type_id) -->
                      <div class="col-md-6 form-group">
                        <label><strong>Letter Type</strong></label>
                        <select name="letter_type_id" class="form-control" required>
                          <option value="">-- Select Type --</option>
                          <?php foreach ($types as $t): ?>
                            <?php
                              // Prefer business key letter_id if you use it in forms; else fallback to numeric id
                              $val = $t['letter_id'] ?: $t['id'];
                              $text = ($t['description'] ?: 'Type') . ($t['letter_id'] ? " ({$t['letter_id']})" : '');
                              $selected = (($_POST['letter_type_id'] ?? '') === $val) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                              <?= htmlspecialchars($text) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label><strong>Sent Date</strong></label>
                        <input type="date" name="sent_date" class="form-control" required
                               value="<?= htmlspecialchars($_POST['sent_date'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label><strong>SIC ID</strong></label>
                        <input type="text" name="sic_id" class="form-control" required
                               value="<?= htmlspecialchars($_POST['sic_id'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 form-group">
                        <label>SIC Signature</label>
                        <input type="text" name="sic_signature" class="form-control"
                               value="<?= htmlspecialchars($_POST['sic_signature'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>MIC ID</label>
                        <input type="text" name="mic_id" class="form-control"
                               value="<?= htmlspecialchars($_POST['mic_id'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 form-group">
                        <label>MIC Signature</label>
                        <input type="text" name="mic_signature" class="form-control"
                               value="<?= htmlspecialchars($_POST['mic_signature'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group d-flex align-items-center">
                        <label class="mr-3">Follow-up Required</label>
                        <div class="form-check form-check-primary">
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="follow_up_required" value="1"
                              <?= isset($_POST['follow_up_required']) ? 'checked' : '' ?>> Yes
                          </label>
                        </div>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Any additional comments..."><?= htmlspecialchars($_POST['remark'] ?? '') ?></textarea>
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
