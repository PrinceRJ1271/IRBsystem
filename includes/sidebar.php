<?php
$level_id = $_SESSION['level_id'];
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-category">Main Menu</li>
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <i class="icon-grid menu-icon"></i><span class="menu-title">Dashboard</span>
      </a>
    </li>

    <?php if ($level_id == 1): ?>
      <li class="nav-item"><a class="nav-link" href="#"><i class="icon-head menu-icon"></i><span class="menu-title">Manage Users</span></a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="icon-columns menu-icon"></i><span class="menu-title">Manage Security Levels</span></a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="icon-cog menu-icon"></i><span class="menu-title">System Debugging</span></a></li>
    <?php endif; ?>

    <?php if (in_array($level_id, [1, 2, 3])): ?>
      <li class="nav-item"><a class="nav-link" href="forms/client_form.php"><i class="icon-plus menu-icon"></i><span class="menu-title">Register Client</span></a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_received_form.php"><i class="icon-paper menu-icon"></i><span class="menu-title">Letter Received</span></a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_received_followup_form.php"><i class="icon-reload menu-icon"></i><span class="menu-title">Follow-up (Received)</span></a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_sent_form.php"><i class="icon-envelope menu-icon"></i><span class="menu-title">Letter Sent</span></a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_sent_followup_form.php"><i class="icon-share menu-icon"></i><span class="menu-title">Follow-up (Sent)</span></a></li>
    <?php endif; ?>

    <?php if ($level_id == 1 || $level_id == 2): ?>
      <li class="nav-item"><a class="nav-link" href="forms/irb_branch_form.php"><i class="icon-briefcase menu-icon"></i><span class="menu-title">Add IRB Branch</span></a></li>
      <li class="nav-item"><a class="nav-link" href="forms/letter_type_form.php"><i class="icon-book menu-icon"></i><span class="menu-title">Add Letter Type</span></a></li>
    <?php endif; ?>

    <?php if ($level_id == 1 || $level_id == 4): ?>
      <li class="nav-item"><a class="nav-link" href="forms/letter_delivery_form.php"><i class="icon-truck menu-icon"></i><span class="menu-title">Letter Delivery</span></a></li>
    <?php endif; ?>

    <li class="nav-item"><a class="nav-link" href="search/quick_search.php"><i class="icon-magnifier menu-icon"></i><span class="menu-title">Quick Search</span></a></li>
    <li class="nav-item"><a class="nav-link" href="export/export_letters_pdf.php" target="_blank"><i class="icon-doc menu-icon"></i><span class="menu-title">Export PDF</span></a></li>
    <li class="nav-item"><a class="nav-link" href="export/export_letters_excel.php" target="_blank"><i class="icon-paper menu-icon"></i><span class="menu-title">Export Excel</span></a></li>
    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="icon-logout menu-icon"></i><span class="menu-title">Logout</span></a></li>
  </ul>
</nav>
