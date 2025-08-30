<?php
// forms/letter_received_form.php

include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Dev, Manager, Senior

$success = $error = "";

/* -------------------- Load dropdown data -------------------- */
// Company Name dropdown (stores clients.client_id)
$clients_rs = $conn->query("SELECT client_id, company_name FROM clients ORDER BY company_name ASC");

// IRB Branch Name dropdown (stores irb_branches.branch_id)
$branches_rs = $conn->query("SELECT branch_id, name FROM irb_branches ORDER BY name ASC");

// Letter Type (REQUIRED here to match your old form)
$types_rs = $conn->query("
    SELECT id, letter_id, description 
    FROM letter_types 
    WHERE letter_type = 'Received' OR letter_type IS NULL 
    ORDER BY description ASC
");

/* -------------------- Handle form submission -------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Generate business key for received letter
    $letter_received_id = "LR" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    // Normalize inputs
    $client_id      = trim($_POST['client_id'] ?? '');
    $branch_id      = trim($_POST['branch_id'] ?? '');
    $letter_type_id = trim($_POST['letter_type_id'] ?? '');
    $received_date  = trim($_POST['received_date'] ?? '');

    // Flags (ensure 0/1 even if checkbox is unchecked and not posted)
    $scanned_copy_required     = isset($_POST['scanned_copy_required']) ? 1 : 0;
    $email_to_client_required  = isset($_POST['email_to_client_required']) ? 1 : 0;
    $filed                     = isset($_POST['filed']) ? 1 : 0;
    $follow_up_required        = isset($_POST['follow_up_required']) ? 1 : 0;

    $sic_id        = trim($_POST['sic_id'] ?? '');
    $sic_signature = trim($_POST['sic_signature'] ?? '');
    $mic_id        = trim($_POST['mic_id'] ?? '');
    $mic_signature = trim($_POST['mic_signature'] ?? '');
    $remark        = trim($_POST['remark'] ?? '');

    // Validation (match your old behavior)
    if ($client_id === '') {
        $error = "Please select a Company Name.";
    } elseif ($branch_id === '') {
        $error = "Please select an IRB Branch Name.";
    } elseif ($letter_type_id === '') {
        $error = "Please select a Letter Type.";
    } elseif ($received_date === '') {
        $error = "Please select the Received Date.";
    } elseif ($sic_id === '') {
        $error = "Please enter SIC ID.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO letters_received 
                (letter_received_id, client_id, branch_id, letter_type_id, received_date, 
                 scanned_copy_required, email_to_client_required, filed, 
                 sic_id, sic_signature, mic_id, mic_signature, 
                 follow_up_required, remark)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Types: s(1) s(2) s(3) s(4) s(5) i(6) i(7) i(8) s(9) s(10) s(11) s(12) i(13) s(14)
        $stmt->bind_param(
            "sssssiiissssis",
            $letter_received_id,
            $client_id,
            $branch_id,
            $letter_type_id,
            $received_date,
            $scanned_copy_required,
            $email_to_client_required,
            $filed,
            $sic_id,
            $sic_signature,
            $mic_id,
            $mic_signature,
            $follow_up_required,
            $remark
        );

        if ($stmt->execute()) {
            $success = "Letter received successfully!";
            $_POST = []; // reset form
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}

/* -------------------- Build arrays for selects -------------------- */
$clients = [];
if ($clients_rs) {
    while ($row = $clients_rs->fetch_assoc()) {
        $clients[] = $row; // client_id, company_name
    }
}
$branches = [];
if ($branches_rs) {
    while ($row = $branches_rs->fetch_assoc()) {
        $branches[] = $row; // branch_id, name
    }
}
$types = [];
if ($types_rs) {
    while ($row = $types_rs->fetch_assoc()) {
        $types[] = $row; // id, letter_id, description
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

                    <!-- Letter Type dropdown (required) -->
                    <div class="col-md-6 form-group">
                      <label><strong>Letter Type</strong></label>
                      <select name="letter_type_id" class="form-control" required>
                        <option value="">-- Select Type --</option>
                        <?php foreach ($types as $t): ?>
                          <?php
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
                      <label><strong>Date Received</strong></label>
                      <input type="date" name="received_date" class="form-control" required
                             value="<?= htmlspecialchars($_POST['received_date'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row pt-3 ps-2">
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="scanned_copy_required" value="1" id="scannedCopy"
                               <?= isset($_POST['scanned_copy_required']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="scannedCopy">Scanned Copy Required</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="email_to_client_required" value="1" id="emailToClient"
                               <?= isset($_POST['email_to_client_required']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="emailToClient">Email to Client Required</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filed" value="1" id="filed"
                               <?= isset($_POST['filed']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="filed">Filed</label>
                      </div>
                    </div>
                  </div>

                  <div class="row pt-3">
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
                  </div>

                  <div class="row pt-3 ps-2">
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="follow_up_required" value="1" id="followUp"
                               <?= isset($_POST['follow_up_required']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="followUp">Follow-up Required</label>
                      </div>
                    </div>
                  </div>

                  <div class="row pt-3">
                    <div class="col-md-12 form-group">
                      <label>Remarks</label>
                      <textarea name="remark" class="form-control" rows="3"><?= htmlspecialchars($_POST['remark'] ?? '') ?></textarea>
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
