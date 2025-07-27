<?php
include '../config/db.php';
include '../includes/auth.php';
check_access([1, 2, 3, 4]); // All roles allowed

$filter = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

// Load letters_received
$stmt1 = $conn->prepare("SELECT * FROM letters_received WHERE letter_received_id LIKE ? OR client_id LIKE ? OR branch_id LIKE ?");
$stmt1->bind_param("sss", $filter, $filter, $filter);
$stmt1->execute();
$result_received = $stmt1->get_result();

// Load letters_sent
$stmt2 = $conn->prepare("SELECT * FROM letters_sent WHERE letter_sent_id LIKE ? OR client_id LIKE ? OR branch_id LIKE ?");
$stmt2->bind_param("sss", $filter, $filter, $filter);
$stmt2->execute();
$result_sent = $stmt2->get_result();

// Load letters_delivered (only for Admin/Developer)
$result_delivery = null;
if ($_SESSION['level_id'] == 4 || $_SESSION['level_id'] == 1) {
    $stmt3 = $conn->prepare("SELECT * FROM letters_delivered WHERE delivery_id LIKE ? OR letter_sent_id LIKE ? OR ad_staff_id LIKE ?");
    $stmt3->bind_param("sss", $filter, $filter, $filter);
    $stmt3->execute();
    $result_delivery = $stmt3->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Quick Search</title>
</head>
<body>
  <h2>Quick Search</h2>

  <form method="get">
    <input type="text" name="q" placeholder="Enter ID, Client ID, or Branch ID" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Search</button>
    <a href="quick_search.php">Reset</a>
  </form>

  <hr>

  <h3>Letters Received</h3>
  <table border="1">
    <tr>
      <th>ID</th><th>Client</th><th>Branch</th><th>Letter Type</th><th>Date</th><th>Status</th>
    </tr>
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
  </table>

  <h3>Letters Sent</h3>
  <table border="1">
    <tr>
      <th>ID</th><th>Client</th><th>Branch</th><th>Letter Type</th><th>Date</th><th>Status</th>
    </tr>
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
  </table>

  <?php if ($result_delivery): ?>
    <h3>Letters Delivered</h3>
    <table border="1">
      <tr>
        <th>ID</th><th>Letter Sent</th><th>Method</th><th>Tracking</th><th>Status</th><th>Admin</th>
      </tr>
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
    </table>
  <?php endif; ?>
</body>
</html>
