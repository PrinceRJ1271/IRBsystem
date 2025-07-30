<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3, 4]);

$filter = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

$stmt1 = $conn->prepare("SELECT * FROM letters_received WHERE letter_received_id LIKE ? OR client_id LIKE ? OR branch_id LIKE ?");
$stmt1->bind_param("sss", $filter, $filter, $filter);
$stmt1->execute();
$result_received = $stmt1->get_result();

$stmt2 = $conn->prepare("SELECT * FROM letters_sent WHERE letter_sent_id LIKE ? OR client_id LIKE ? OR branch_id LIKE ?");
$stmt2->bind_param("sss", $filter, $filter, $filter);
$stmt2->execute();
$result_sent = $stmt2->get_result();

$result_delivery = null;
if ($_SESSION['level_id'] == 4 || $_SESSION['level_id'] == 1) {
    $stmt3 = $conn->prepare("SELECT * FROM letters_delivered WHERE delivery_id LIKE ? OR letter_sent_id LIKE ? OR ad_staff_id LIKE ?");
    $stmt3->bind_param("sss", $filter, $filter, $filter);
    $stmt3->execute();
    $result_delivery = $stmt3->get_result();
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container-fluid page-body-wrapper">
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Quick Search</h4>
              <form method="get" class="mb-3">
                <div class="input-group">
                  <input type="text" name="q" class="form-control" placeholder="Enter ID, Client ID, or Branch ID" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn btn-light" href="quick_search.php">Reset</a>
                  </div>
                </div>
              </form>

              <h5>Letters Received</h5>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th><th>Client</th><th>Branch</th><th>Letter Type</th><th>Date</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $result_received->fetch_assoc()): ?>
                      <tr>
                        <td><?= $row['letter_received_id'] ?></td>
                        <td><?= $row['client_id'] ?></td>
                        <td><?= $row['branch_id'] ?></td>
                        <td><?= $row['letter_type_id'] ?></td>
                        <td><?= $row['received_date'] ?></td>
                        <td><?= $row['status'] ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>

              <h5>Letters Sent</h5>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th><th>Client</th><th>Branch</th><th>Letter Type</th><th>Date</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $result_sent->fetch_assoc()): ?>
                      <tr>
                        <td><?= $row['letter_sent_id'] ?></td>
                        <td><?= $row['client_id'] ?></td>
                        <td><?= $row['branch_id'] ?></td>
                        <td><?= $row['letter_type_id'] ?></td>
                        <td><?= $row['sent_date'] ?></td>
                        <td><?= $row['status'] ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>

              <?php if ($result_delivery): ?>
              <h5>Letters Delivered</h5>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th><th>Letter Sent</th><th>Method</th><th>Tracking</th><th>Status</th><th>Admin</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $result_delivery->fetch_assoc()): ?>
                      <tr>
                        <td><?= $row['delivery_id'] ?></td>
                        <td><?= $row['letter_sent_id'] ?></td>
                        <td><?= $row['delivery_method'] ?></td>
                        <td><?= $row['tracking_number'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= $row['ad_staff_id'] ?></td>
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
