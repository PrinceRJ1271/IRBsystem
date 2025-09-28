<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3, 4]); // All roles allowed

$filter = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

/* -------------------- Letters Received (with names) -------------------- */
// We join on clients, branches, and letter_types.
// letter_type_id may contain either letter_types.letter_id (string) OR letter_types.id (int),
// so we join with an OR to cover both possibilities.
$sql_received = "
  SELECT
    lr.letter_received_id,
    lr.client_id,
    c.company_name,
    lr.branch_id,
    b.name AS branch_name,
    lr.letter_type_id,
    lt.description AS letter_type_desc,
    lr.received_date,
    lr.status
  FROM letters_received lr
  LEFT JOIN clients c
    ON c.client_id = lr.client_id
  LEFT JOIN irb_branches b
    ON b.branch_id = lr.branch_id
  LEFT JOIN letter_types lt
    ON (lt.letter_id = lr.letter_type_id OR lt.id = lr.letter_type_id)
  WHERE lr.letter_received_id LIKE ?
     OR lr.client_id LIKE ?
     OR lr.branch_id LIKE ?
  ORDER BY lr.received_date DESC, lr.letter_received_id DESC
";
$stmt1 = $conn->prepare($sql_received);
$stmt1->bind_param("sss", $filter, $filter, $filter);
$stmt1->execute();
$result_received = $stmt1->get_result();

/* -------------------- Letters Sent (with names) -------------------- */
$sql_sent = "
  SELECT
    ls.letter_sent_id,
    ls.client_id,
    c.company_name,
    ls.branch_id,
    b.name AS branch_name,
    ls.letter_type_id,
    lt.description AS letter_type_desc,
    ls.sent_date,
    ls.status
  FROM letters_sent ls
  LEFT JOIN clients c
    ON c.client_id = ls.client_id
  LEFT JOIN irb_branches b
    ON b.branch_id = ls.branch_id
  LEFT JOIN letter_types lt
    ON (lt.letter_id = ls.letter_type_id OR lt.id = ls.letter_type_id)
  WHERE ls.letter_sent_id LIKE ?
     OR ls.client_id LIKE ?
     OR ls.branch_id LIKE ?
  ORDER BY ls.sent_date DESC, ls.letter_sent_id DESC
";
$stmt2 = $conn->prepare($sql_sent);
$stmt2->bind_param("sss", $filter, $filter, $filter);
$stmt2->execute();
$result_sent = $stmt2->get_result();

/* -------------------- Letters Delivered (kept as-is) -------------------- */
$result_delivery = null;
if ($_SESSION['level_id'] == 1 || $_SESSION['level_id'] == 4) {
    $sql_delivery = "
      SELECT
        delivery_id,
        letter_sent_id,
        delivery_method,
        tracking_number,
        status,
        ad_staff_id
      FROM letters_delivered
      WHERE delivery_id LIKE ?
         OR letter_sent_id LIKE ?
         OR ad_staff_id LIKE ?
      ORDER BY delivered_date DESC, delivery_id DESC
    ";
    $stmt3 = $conn->prepare($sql_delivery);
    $stmt3->bind_param("sss", $filter, $filter, $filter);
    $stmt3->execute();
    $result_delivery = $stmt3->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quick Search - IRB System</title>
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
            <div class="col-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">

                  <h4 class="card-title">Quick Search</h4>
                  <form method="get" class="form-inline mb-4">
                    <input type="text" name="q" class="form-control mr-2" placeholder="Enter ID, Client ID, or Branch ID" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="width:300px;">
                    <button type="submit" class="btn btn-primary mr-2">Search</button>
                    <a href="quick_search.php" class="btn btn-secondary">Reset</a>
                  </form>

                  <!-- Letters Received -->
                  <h5>Letters Received</h5>
                  <div class="table-responsive mb-4">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Client</th>
                          <th>Branch</th>
                          <th>Letter Type</th>
                          <th>Date</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $result_received->fetch_assoc()): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['letter_received_id']) ?></td>
                          <td>
                            <?= htmlspecialchars($row['company_name'] ?: $row['client_id']) ?>
                            <?php if (!empty($row['company_name'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['client_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?= htmlspecialchars($row['branch_name'] ?: $row['branch_id']) ?>
                            <?php if (!empty($row['branch_name'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['branch_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?= htmlspecialchars($row['letter_type_desc'] ?: $row['letter_type_id']) ?>
                            <?php if (!empty($row['letter_type_desc']) && !empty($row['letter_type_id'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['letter_type_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($row['received_date']) ?></td>
                          <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>

                  <!-- Letters Sent -->
                  <h5>Letters Sent</h5>
                  <div class="table-responsive mb-4">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Client</th>
                          <th>Branch</th>
                          <th>Letter Type</th>
                          <th>Date</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $result_sent->fetch_assoc()): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['letter_sent_id']) ?></td>
                          <td>
                            <?= htmlspecialchars($row['company_name'] ?: $row['client_id']) ?>
                            <?php if (!empty($row['company_name'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['client_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?= htmlspecialchars($row['branch_name'] ?: $row['branch_id']) ?>
                            <?php if (!empty($row['branch_name'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['branch_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?= htmlspecialchars($row['letter_type_desc'] ?: $row['letter_type_id']) ?>
                            <?php if (!empty($row['letter_type_desc']) && !empty($row['letter_type_id'])): ?>
                              <small class="text-muted">(<?= htmlspecialchars($row['letter_type_id']) ?>)</small>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($row['sent_date']) ?></td>
                          <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>

                  <!-- Letters Delivered (unchanged columns) -->
                  <?php if ($result_delivery): ?>
                    <h5>Letters Delivered</h5>
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Letter Sent</th>
                            <th>Method</th>
                            <th>Tracking</th>
                            <th>Status</th>
                            <th>Admin</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($row = $result_delivery->fetch_assoc()): ?>
                          <tr>
                            <td><?= htmlspecialchars($row['delivery_id']) ?></td>
                            <td><?= htmlspecialchars($row['letter_sent_id']) ?></td>
                            <td><?= htmlspecialchars($row['delivery_method']) ?></td>
                            <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['ad_staff_id']) ?></td>
                          </tr>
                          <?php endwhile; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>

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
