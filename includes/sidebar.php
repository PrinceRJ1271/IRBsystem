<?php
$roleNames = [
    1 => 'Developer',
    2 => 'Tax Manager',
    3 => 'Tax Senior',
    4 => 'Admin Staff'
];
$role = $roleNames[$level_id] ?? 'Unknown';
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold"><?php echo $role; ?></span>
        </div>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <i class="mdi mdi-view-dashboard menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <?php if ($level_id == 1): ?>
      <li class="nav-item"><a class="nav-link" href="#"><i class="mdi mdi-account-multiple menu-icon"></i> Manage Users</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="mdi mdi-shield menu-icon"></i> Manage Security Levels</a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="mdi mdi-bug menu-icon"></i> System Debugging</a></li>
    <?php endif; ?>

    <?php if ($level_id <= 2): ?>
      <li class="nav-item"><a class="nav-link" href="forms/client_form.php"><i class="mdi mdi-account-plus menu-icon"></i> Register Client</a></li>
      <li class="nav-item"><a class="nav-link" href="forms/irb_branch_form.php"><i class="mdi mdi-domain menu-icon"></i> Add IRB Branch</a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_type_form.php"><i class="mdi mdi-file-document-box menu-icon"></i> Add Letter Type</a></li>
    <?php endif; ?>

    <?php if ($level_id <= 3): ?>
      <li class="nav-item"><a class="nav-link" href="forms/letter_received_form.php"><i class="mdi mdi-inbox-arrow-down menu-icon"></i> Letter Received</a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_received_followup_form.php"><i class="mdi mdi-refresh menu-icon"></i> Follow-up (Received)</a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_sent_form.php"><i class="mdi mdi-send menu-icon"></i> Letter Sent</a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_sent_followup_form.php"><i class="mdi mdi-repeat menu-icon"></i> Follow-up (Sent)</a></li>
    <?php endif; ?>

    <?php if (in_array($level_id, [1, 2, 4])): ?>
      <li class="nav-item"><a class="nav-link" href="forms/letter_delivery_form.php"><i class="mdi mdi-truck-delivery menu-icon"></i> Letter Delivery</a></li>
    <?php endif; ?>

    <li class="nav-item"><a class="nav-link" href="search/quick_search.php"><i class="mdi mdi-magnify menu-icon"></i> Quick Search</a></li>
    <li class="nav-item"><a class="nav-link" href="export/export_letters_pdf.php" target="_blank"><i class="mdi mdi-file-pdf menu-icon"></i> Export PDF</a></li>
    <li class="nav-item"><a class="nav-link" href="export/export_letters_excel.php" target="_blank"><i class="mdi mdi-file-excel menu-icon"></i> Export Excel</a></li>
    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="mdi mdi-logout menu-icon"></i> Logout</a></li>
  </ul>
</nav>
