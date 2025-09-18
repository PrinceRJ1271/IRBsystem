<?php
if (!isset($_SESSION)) {
    session_start();
}

$level_id = $_SESSION['level_id'] ?? null;
?>

<!-- Sidebar -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">IRB System</span>
          <span class="text-secondary text-small">
            <?php
              switch ($level_id) {
                case 1: echo "Developer"; break;
                case 2: echo "Tax Manager"; break;
                case 3: echo "Tax Senior"; break;
                case 4: echo "Admin Staff"; break;
                default: echo "Unknown Role";
              }
            ?>
          </span>
        </div>
      </a>
    </li>

    <!-- Common Links for All Users -->
    <li class="nav-item">
      <a class="nav-link" href="/dashboard.php">
        <i class="mdi mdi-view-dashboard menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <!-- Developer Menu -->
    <?php if ($level_id == 1): ?>
      <li class="nav-item">
        <a class="nav-link" href="/register.php">
          <i class="mdi mdi-account-settings menu-icon"></i>
          <span class="menu-title">Manage Users</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="mdi mdi-shield menu-icon"></i>
          <span class="menu-title">Manage Security Levels</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="mdi mdi-bug menu-icon"></i>
          <span class="menu-title">System Debugging</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- Developer & Manager -->
    <?php if ($level_id == 1 || $level_id == 2): ?>
      <li class="nav-item">
        <a class="nav-link" href="/forms/client_form.php">
          <i class="mdi mdi-account-plus menu-icon"></i>
          <span class="menu-title">Register Client</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/irb_branch_form.php">
          <i class="mdi mdi-city menu-icon"></i>
          <span class="menu-title">Add IRB Branch</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_type_form.php">
          <i class="mdi mdi-note-plus menu-icon"></i>
          <span class="menu-title">Add Letter Type</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- Received/Sent Letters (Level 1,2,3) -->
    <?php if (in_array($level_id, [1, 2, 3])): ?>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_received_form.php">
          <i class="mdi mdi-inbox-arrow-down menu-icon"></i>
          <span class="menu-title">Letter Received</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_received_followup_form.php">
          <i class="mdi mdi-refresh menu-icon"></i>
          <span class="menu-title">Follow-up (Received)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_sent_form.php">
          <i class="mdi mdi-send menu-icon"></i>
          <span class="menu-title">Letter Sent</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_sent_followup_form.php">
          <i class="mdi mdi-reload menu-icon"></i>
          <span class="menu-title">Follow-up (Sent)</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- Admin Staff -->
    <?php if ($level_id == 1 || $level_id == 2 || $level_id == 4): ?>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_delivery_form.php">
          <i class="mdi mdi-truck-delivery menu-icon"></i>
          <span class="menu-title">Letter Delivery</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- Common Search & Export -->
    <li class="nav-item">
      <a class="nav-link" href="/search/quick_search.php">
        <i class="mdi mdi-magnify menu-icon"></i>
        <span class="menu-title">Quick Search</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/export/export_letters_pdf.php" target="_blank">
        <i class="mdi mdi-file-pdf menu-icon"></i>
        <span class="menu-title">Export PDF</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/export/export_letters_excel.php" target="_blank">
        <i class="mdi mdi-file-excel menu-icon"></i>
        <span class="menu-title">Export Excel</span>
      </a>
    </li>

    <!-- Logout -->
    <li class="nav-item">
      <a class="nav-link" href="/logout.php">
        <i class="mdi mdi-logout menu-icon"></i>
        <span class="menu-title">Logout</span>
      </a>
    </li>

  </ul>
</nav>
