<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

/* -----------------------------------------------------------
   Build dropdown: only letters whose follow-up is still Pending
   Logic: include letters_sent rows for which there is NOT EXISTS
          a letters_sent_followup row with followup_status='Completed'.
   (We also join clients just to show a friendly label.)
----------------------------------------------------------- */
$ls_options = [];
$q = "
  SELECT 
    ls.letter_sent_id,
    c.company_name,
    ls.sent_date
  FROM letters_sent ls
  LEFT JOIN clients c ON c.client_id = ls.client_id
  WHERE NOT EXISTS (
    SELECT 1
    FROM letters_sent_followup lsf
    WHERE lsf.letter_sent_id = ls.letter_sent_id
      AND lsf.followup_status = 'Completed'
  )
  ORDER BY ls.sent_date DESC, ls.letter_sent_id DESC
";
if ($res = $conn->query($q)) {
  while ($row = $res->fetch_assoc()) {
    $ls_options[] = $row; // ['letter_sent_id','company_name','sent_date']
  }
}

/* -----------------------------------------------------------
   Handle submit (original logic preserved)
----------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $followup_sent_id = "SF" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_sent_followup
        (followup_sent_id, letter_sent_id, phone_call_irb, call_date, irb_reply,
         ack_required, ack_received, followup_status, change_of_sic, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $followup_sent_id,
        $_POST['letter_sent_id'],
        $_POST['phone_call_irb'],
        $_POST['call_date'],
        $_POST['irb_reply'],
        $_POST['ack_required'],
        $_POST['ack_received'],
        $_POST['followup_status'],
        $_POST['change_of_sic'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Follow-up recorded successfully!";
        // Optional: clear POST so the form resets after success
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
  <title>Letter Sent Follow-up - IRB Letter Management System</title>
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

                  <h4 class="page-title">Letter Sent Follow-up Form</h4>
                  <p class="card-description"> Record any follow-up actions taken after sending a letter </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3" autocomplete="off">
                    <div class="row">
                      <!-- DROPDOWN: Letter Sent ID (only letters without a Completed follow-up) -->
                      <div class="col-md-6 form-group">
                        <label><strong>Letter Sent ID</strong></label>
                        <select name="letter_sent_id" class="form-control" required>
                          <option value="">-- Select Letter (Sent, Pending follow-up) --</option>
                          <?php foreach ($ls_options as $opt): ?>
                            <?php
                              $val = $opt['letter_sent_id'];
                              // e.g. "LS00045 — Acme Sdn Bhd — 2025-08-30"
                              $label = $val
                                     . ' — ' . ($opt['company_name'] ?: 'Unknown Company')
                                     . ' — ' . ($opt['sent_date'] ?: '');
                              $selected = (isset($_POST['letter_sent_id']) && $_POST['letter_sent_id'] === $val) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                              <?= htmlspecialchars($label) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Phone Call to IRB</label>
                        <select name="phone_call_irb" class="form-control">
                          <option value="Yes" <?= (($_POST['phone_call_irb'] ?? '')==='Yes')?'selected':''; ?>>Yes</option>
                          <option value="N/A" <?= (($_POST['phone_call_irb'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Call Date</label>
                        <input type="date" name="call_date" class="form-control"
                               value="<?= htmlspecialchars($_POST['call_date'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>IRB Reply</label>
                        <select name="irb_reply" class="form-control">
                          <option value="Success" <?= (($_POST['irb_reply'] ?? '')==='Success')?'selected':''; ?>>Success</option>
                          <option value="Fail" <?= (($_POST['irb_reply'] ?? '')==='Fail')?'selected':''; ?>>Fail</option>
                          <option value="Pending" <?= (($_POST['irb_reply'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                          <option value="N/A" <?= (($_POST['irb_reply'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Acknowledgment Required</label>
                        <select name="ack_required" class="form-control">
                          <option value="Yes" <?= (($_POST['ack_required'] ?? '')==='Yes')?'selected':''; ?>>Yes</option>
                          <option value="N/A" <?= (($_POST['ack_required'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Acknowledgment Received</label>
                        <select name="ack_received" class="form-control">
                          <option value="Yes" <?= (($_POST['ack_received'] ?? '')==='Yes')?'selected':''; ?>>Yes</option>
                          <option value="No" <?= (($_POST['ack_received'] ?? '')==='No')?'selected':''; ?>>No</option>
                          <option value="Pending" <?= (($_POST['ack_received'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                          <option value="N/A" <?= (($_POST['ack_received'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Follow-up Status</label>
                        <select name="followup_status" class="form-control">
                          <option value="Pending" <?= (($_POST['followup_status'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                          <option value="Completed" <?= (($_POST['followup_status'] ?? '')==='Completed')?'selected':''; ?>>Completed</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Change of SIC</label>
                        <select name="change_of_sic" class="form-control">
                          <option value="No" <?= (($_POST['change_of_sic'] ?? '')==='No')?'selected':''; ?>>No</option>
                          <option value="Yes" <?= (($_POST['change_of_sic'] ?? '')==='Yes')?'selected':''; ?>>Yes</option>
                        </select>
                      </div>

                      <div class="col-12 form-group">
                        <label>Remarks</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Any remarks..."><?= htmlspecialchars($_POST['remark'] ?? '') ?></textarea>
                      </div>
                    </div>

                    <div class="mt-4 text-center">
                      <button type="submit" class="btn btn-primary btn-lg font-weight-medium">
                        <i class="mdi mdi-check-circle"></i> Submit Follow-up
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
