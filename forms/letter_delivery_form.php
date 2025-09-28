<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 4]); // Developer or Admin Staff

$success = $error = "";

/* ---------------- Lookup options (ONLY Letters Sent) ---------------- */
$ls_options = [];
$sql_sent = "
  SELECT ls.letter_sent_id, c.company_name, ls.sent_date
  FROM letters_sent ls
  LEFT JOIN clients c ON c.client_id = ls.client_id
  ORDER BY ls.sent_date DESC
";
if ($res = $conn->query($sql_sent)) {
  while ($row = $res->fetch_assoc()) { $ls_options[] = $row; }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $delivery_id = "LD" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

  // We only accept letters from "Letters Sent".
  // The select stores values like "S|<id>" (or just "<id>" if you ever change it).
  $letter_sent_id = null;
  if (!empty($_POST['letter_ref'])) {
    $raw = $_POST['letter_ref'];
    if (strpos($raw, '|') !== false) {
      list($type, $id) = explode('|', $raw, 2);
      if ($type === 'S') $letter_sent_id = $id;
    } else {
      // Fallback if you ever post just the ID
      $letter_sent_id = $raw;
    }
  }

  if (!$letter_sent_id) {
    $error = "Please select a letter from the 'Letters Sent' list.";
  } else {
    // Insert WITHOUT letter_received_id (matches your DB schema)
    $sql = "INSERT INTO letters_delivered
      (delivery_id, letter_sent_id, collection_date, delivered_date, delivery_method,
       tracking_number, ad_staff_id, ad_signature, status, remark)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      $error = "Prepare failed: " . $conn->error;
    } else {
      $stmt->bind_param(
        "ssssssssss",
        $delivery_id,
        $letter_sent_id,
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
        $_POST = []; // reset form after success
      } else {
        $error = "Error saving delivery: " . $stmt->error;
      }
      $stmt->close();
    }
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

                  <h4 class="page-title">Letter Delivery Form (Admin Staff)</h4>
                  <p class="card-description"> Record the final delivery details of the letter </p>

                  <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                  <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3" autocomplete="off">
                    <div class="row">

                      <!-- Letters Sent ONLY -->
                      <div class="col-md-6 form-group">
                        <label>Letter (Sent)</label>
                        <select name="letter_ref" class="form-control" required>
                          <option value="">-- Select Letter --</option>
                          <?php foreach ($ls_options as $opt):
                            $val = 'S|' . $opt['letter_sent_id']; // keep "S|" so back-compat parsing still works
                            $label = $opt['letter_sent_id']
                                   . ' — ' . ($opt['company_name'] ?: 'Unknown Company')
                                   . ' — ' . ($opt['sent_date'] ?: '');
                            $sel = (($_POST['letter_ref'] ?? '') === $val) ? 'selected' : '';
                          ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $sel ?>>
                              <?= htmlspecialchars($label) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Collection Date</label>
                        <input type="date" name="collection_date" class="form-control"
                               value="<?= htmlspecialchars($_POST['collection_date'] ?? '') ?>" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Delivered Date</label>
                        <input type="date" name="delivered_date" class="form-control"
                               value="<?= htmlspecialchars($_POST['delivered_date'] ?? '') ?>" required>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Delivery Method</label>
                        <select name="delivery_method" class="form-control" required>
                          <?php
                            $dm = $_POST['delivery_method'] ?? '';
                            foreach (['Courier','Dispatch'] as $o) {
                              $sel = ($dm === $o) ? 'selected' : '';
                              echo "<option value=\"".htmlspecialchars($o)."\" $sel>".htmlspecialchars($o)."</option>";
                            }
                          ?>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Tracking Number (optional)</label>
                        <input type="text" name="tracking_number" class="form-control"
                               value="<?= htmlspecialchars($_POST['tracking_number'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Admin Staff ID</label>
                        <input type="text" name="ad_staff_id" class="form-control" required
                               value="<?= htmlspecialchars($_POST['ad_staff_id'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Admin Signature</label>
                        <input type="text" name="ad_signature" class="form-control" required
                               value="<?= htmlspecialchars($_POST['ad_signature'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                          <?php
                            $st = $_POST['status'] ?? 'Pending';
                            foreach (['Pending','Completed'] as $o) {
                              $sel = ($st === $o) ? 'selected' : '';
                              echo "<option value=\"".htmlspecialchars($o)."\" $sel>".htmlspecialchars($o)."</option>";
                            }
                          ?>
                        </select>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks (optional)</label>
                        <textarea name="remark" class="form-control" rows="3"><?= htmlspecialchars($_POST['remark'] ?? '') ?></textarea>
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
