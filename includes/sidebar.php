<?php
if (!isset($_SESSION)) {
    session_start();
}

$level_id = $_SESSION['level_id'] ?? null;
/*
Roles:
1 = Developer (all access)
2 = Manager   (dashboard, register client, add branch, add letter type, letters + follow-ups, quick search)
3 = Senior    (dashboard, add branch, add letter type, letters + follow-ups, quick search)
4 = Admin     (dashboard, register user, letter delivery, quick search)
*/
?>

<!-- Sidebar -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <!-- Profile -->
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">IRB System</span>
          <span class="text-secondary text-small">
            <?php
              switch ((int)$level_id) {
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

    <!-- Dashboard (all roles) -->
    <li class="nav-item">
      <a class="nav-link" href="/dashboard.php">
        <i class="mdi mdi-view-dashboard menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-divider"></li>

    <?php
      // Convenience booleans
      $isDev    = ((int)$level_id === 1);
      $isMgr    = ((int)$level_id === 2);
      $isSenior = ((int)$level_id === 3);
      $isAdmin  = ((int)$level_id === 4);
    ?>

    <?php if ($isDev || $isMgr || $isSenior): ?>
      <li class="nav-item nav-category">REGISTRATION & SETUP</li>
    <?php endif; ?>

    <?php if ($isDev || $isMgr): ?>
      <!-- Developer & Manager -->
      <li class="nav-item">
        <a class="nav-link" href="/forms/client_form.php">
          <i class="mdi mdi-account-plus menu-icon"></i>
          <span class="menu-title">Register Client</span>
        </a>
      </li>
      <?php if ($isDev): ?>
        <li class="nav-item">
          <a class="nav-link" href="/register.php">
            <i class="mdi mdi-account-settings menu-icon"></i>
            <span class="menu-title">Register User</span>
          </a>
        </li>
      <?php endif; ?>
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
      <li class="nav-divider"></li>
    <?php endif; ?>

    <?php if ($isSenior): ?>
      <!-- Senior -->
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
      <li class="nav-divider"></li>
    <?php endif; ?>

    <?php if ($isDev || $isMgr || $isSenior): ?>
      <li class="nav-item nav-category">LETTERS & FOLLOW-UPS</li>
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
      <li class="nav-divider"></li>
    <?php endif; ?>

    <?php if ($isDev || $isMgr || $isAdmin): ?>
      <li class="nav-item nav-category">DELIVERY</li>
      <li class="nav-item">
        <a class="nav-link" href="/forms/letter_delivery_form.php">
          <i class="mdi mdi-truck-delivery menu-icon"></i>
          <span class="menu-title">Letter Delivery</span>
        </a>
      </li>
      <li class="nav-divider"></li>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
      <li class="nav-item nav-category">USER MANAGEMENT</li>
      <li class="nav-item">
        <a class="nav-link" href="/register.php">
          <i class="mdi mdi-account-settings menu-icon"></i>
          <span class="menu-title">Register User</span>
        </a>
      </li>
      <li class="nav-divider"></li>
    <?php endif; ?>

    <li class="nav-item nav-category">UTILITIES</li>
    <!-- Quick Search (all roles) -->
    <li class="nav-item">
      <a class="nav-link" href="/search/quick_search.php">
        <i class="mdi mdi-magnify menu-icon"></i>
        <span class="menu-title">Quick Search</span>
      </a>
    </li>

  </ul>
</nav>
