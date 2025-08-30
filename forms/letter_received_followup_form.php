<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3]); // Developer, Manager, Senior

$success = $error = "";

/* -----------------------------------------------------------
   Build dropdown: only "Received" letters from letters_received
   (Joining clients for display label)
----------------------------------------------------------- */
$lr_options = [];
$q = "
  SELECT 
    lr.letter_received_id,
    c.company_name,
    lr.received_date
  FROM letters_received lr
  LEFT JOIN clients c ON c.client_id = lr.client_id
  ORDER BY lr.received_date DESC, lr.letter_received_id DESC
";
if ($res = $conn->query($q)) {
  while ($row = $res->fetch_assoc()) {
    $lr_options[] = $row; // ['letter_received_id','company_name','received_date']
  }
}

/* -----------------------------------------------------------
   Handle submit (unchanged logic)
----------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $followup_id = "RF" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO letters_received_followup
        (followup_id, letter_received_id, phone_call_client, email_to_client, email_date,
         client_reply, client_reply_date, followup_status, change_of_sic, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssss",
        $followup_id,
        $_POST['letter_received_id'],
        $_POST['phone_call_client'],
        $_POST['email_to_client'],
        $_POST['email_date'],
        $_POST['client_reply'],
        $_POST['client_reply_date'],
        $_POST['followup_status'],
        $_POST['change_of_sic'],
        $_POST['remark']
    );

    if ($stmt->execute()) {
        $success = "Follow-up recorded!";
        // Optional: clear POST so the form resets
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
  <title>Letter Received Follow-up - IRB Letter Management System</title>
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

                  <h4 class="page-title">Letter Received Follow-up Form</h4>
                  <p class="card-description"> Submit follow-up actions related to received letters </p>

                  <?php if (!empty($success)): ?>
                      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                  <?php elseif (!empty($error)): ?>
                      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form method="post" class="pt-3" autocomplete="off">
                    <div class="row">
                      <!-- DROPDOWN: Letter Received ID (only from letters_received) -->
                      <div class="col-md-6 form-group">
                        <label><strong>Letter Received ID</strong></label>
                        <select name="letter_received_id" class="form-control" required>
                          <option value="">-- Select Letter (Received) --</option>
                          <?php foreach ($lr_options as $opt): ?>
                            <?php
                              $val = $opt['letter_received_id'];
                              $label = $val
                                     . ' — ' . ($opt['company_name'] ?: 'Unknown Company')
                                     . ' — ' . ($opt['received_date'] ?: '');
                              $selected = (isset($_POST['letter_received_id']) && $_POST['letter_received_id'] === $val) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                              <?= htmlspecialchars($label) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Phone Call to Client</label>
                        <select name="phone_call_client" class="form-control">
                          <option value="Yes, called" <?= (($_POST['phone_call_client'] ?? '')==='Yes, called')?'selected':''; ?>>Yes, called</option>
                          <option value="Pending" <?= (($_POST['phone_call_client'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                          <option value="N/A" <?= (($_POST['phone_call_client'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Email to Client</label>
                        <select name="email_to_client" class="form-control">
                          <option value="Yes, emailed" <?= (($_POST['email_to_client'] ?? '')==='Yes, emailed')?'selected':''; ?>>Yes, emailed</option>
                          <option value="Pending" <?= (($_POST['email_to_client'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                          <option value="N/A" <?= (($_POST['email_to_client'] ?? '')==='N/A')?'selected':''; ?>>N/A</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Email Date</label>
                        <input type="date" name="email_date" class="form-control"
                               value="<?= htmlspecialchars($_POST['email_date'] ?? '') ?>">
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Client Reply</label>
                        <select name="client_reply" class="form-control">
                          <option value="Action needed" <?= (($_POST['client_reply'] ?? '')==='Action needed')?'selected':''; ?>>Action needed</option>
                          <option value="No action" <?= (($_POST['client_reply'] ?? '')==='No action')?'selected':''; ?>>No action</option>
                          <option value="Pending" <?= (($_POST['client_reply'] ?? '')==='Pending')?'selected':''; ?>>Pending</option>
                        </select>
                      </div>

                      <div class="col-md-6 form-group">
                        <label>Client Reply Date</label>
                        <input type="date" name="client_reply_date" class="form-control"
                               value="<?= htmlspecialchars($_POST['client_reply_date'] ?? '') ?>">
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
                        <i class="mdi mdi-email-send"></i> Submit Follow-up
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
