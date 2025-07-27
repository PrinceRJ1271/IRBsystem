<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$level_id = $_SESSION['level_id'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
</head>
<body>
  <h2>Welcome, <?php echo $user_id; ?>!</h2>
  <h4>Your Role Level: <?php echo $level_id; ?></h4>

  <?php if ($level_id == 1): ?>
    <h3>Developer Dashboard</h3>
    <ul>
      <li><a href="#">Manage Users</a></li>
      <li><a href="#">Manage Security Levels</a></li>
      <li><a href="#">System Debugging</a></li>
    </ul>
  <?php elseif ($level_id == 2): ?>
    <h3>Tax Manager Dashboard</h3>
    <ul>
      <li><a href="#">Register Staff</a></li>
      <li><a href="#">Fill IRB Letter Types</a></li>
      <li><a href="#">View Reports</a></li>
    </ul>
  <?php elseif ($level_id == 3): ?>
    <h3>Tax Senior Dashboard</h3>
    <ul>
      <li><a href="#">Client Engagement Form</a></li>
      <li><a href="#">Letter Received/Sent</a></li>
      <li><a href="#">Follow-up Forms</a></li>
    </ul>
  <?php elseif ($level_id == 4): ?>
    <h3>Admin Staff Dashboard</h3>
    <ul>
      <li><a href="#">Letter Delivery Form</a></li>
      <li><a href="#">Track Deliveries</a></li>
    </ul>
  <?php else: ?>
    <p>Invalid role. Please contact admin.</p>
  <?php endif; ?>

  <p><a href="logout.php">Logout</a></p>
</body>
</html>

<?php if ($level_id == 2): ?>
  <ul>
    <li><a href="forms/client_form.php">Register Client</a></li>
    <li><a href="forms/irb_branch_form.php">Add IRB Branch</a></li>
    <li><a href="forms/letter_type_form.php">Add Letter Type</a></li>
  </ul>
<?php endif; ?>
