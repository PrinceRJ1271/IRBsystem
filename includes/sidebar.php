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

// Convenience booleans
$isDev    = ((int)$level_id === 1);
$isMgr    = ((int)$level_id === 2);
$isSenior = ((int)$level_id === 3);
$isAdmin  = ((int)$level_id === 4);
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

    <?php if ($isDev || $isMgr || $isSenior): ?>
      <li class="nav-item nav-category">REGISTRATION &amp; SETUP</li>
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
      <li class="nav-item nav-category">LETTERS &amp; FOLLOW-UPS</li>
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

    <?php if ($isDev || $isAdmin): ?>
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

<!-- StarAdmin-styled floating toggle + scrim for offcanvas -->
<button id="sidebarToggle" class="sidebar-toggle btn btn-primary shadow-sm" aria-label="Toggle sidebar">
  <i class="mdi mdi-menu"></i>
</button>
<div id="sidebarScrim" class="sidebar-scrim"></div>

<style>
  /* ---- Offcanvas behavior (only affects small/narrow view) ---- */
  #sidebar { transition: transform .28s cubic-bezier(.4,0,.2,1); will-change: transform; }
  #sidebar.collapsed { transform: translateX(-100%); }

  /* ---- Styled floating toggle (StarAdmin-ish) ---- */
  .sidebar-toggle {
    position: fixed;
    top: 92px;               /* just below header */
    left: 14px;
    z-index: 1101;
    display: none;           /* shown only when body has .sidebar-collapsed */
    width: 42px;
    height: 42px;
    padding: 0;
    border-radius: 12px;
    background: #4B49AC;     /* StarAdmin primary tone used in your UI */
    border: 0;
    color: #fff;
  }
  .sidebar-toggle i { font-size: 22px; line-height: 42px; }
  .sidebar-toggle:hover { filter: brightness(1.05); box-shadow: 0 6px 16px rgba(75,73,172,.25); }
  .sidebar-toggle:active { transform: translateY(1px); }

  /* Only show toggle in collapsed mode */
  body.sidebar-collapsed .sidebar-toggle { display: inline-flex; align-items: center; justify-content: center; }

  /* ---- Scrim (clickable backdrop) ---- */
  .sidebar-scrim {
    position: fixed; inset: 0;
    background: rgba(17,24,39,.35);
    backdrop-filter: blur(1px);
    z-index: 1100;           /* BELOW the sidebar */
    opacity: 0; pointer-events: none; transition: opacity .2s ease;
  }
  body.drawer-open .sidebar-scrim { opacity: 1; pointer-events: auto; }

  /* ---- Responsive trigger: collapse below 1200px ---- */
  @media (max-width: 1199.98px) {
    #sidebar {
      position: fixed;
      left: 0;
      top: 80px;
      height: calc(100% - 80px);
      z-index: 1102;                 /* <-- ABOVE scrim so it’s clickable */
      box-shadow: 8px 0 22px rgba(0,0,0,.08); /* subtle drawer shadow */
    }
  }
</style>

<script>
  (function () {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const scrim = document.getElementById('sidebarScrim');

    // Collapse logic when viewport gets narrow (e.g., user zooms in)
    function applyResponsiveSidebar() {
      if (window.innerWidth < 1200) {
        document.body.classList.add('sidebar-collapsed');
        sidebar.classList.add('collapsed');
      } else {
        document.body.classList.remove('sidebar-collapsed', 'drawer-open');
        sidebar.classList.remove('collapsed');
      }
    }

    // Open drawer (show sidebar temporarily) on narrow screens
    function openDrawer() {
      document.body.classList.add('drawer-open');
      sidebar.classList.remove('collapsed');
    }

    // Close drawer back to collapsed state
    function closeDrawer() {
      sidebar.classList.add('collapsed');
      document.body.classList.remove('drawer-open');
    }

    // Init + events
    applyResponsiveSidebar();
    window.addEventListener('resize', applyResponsiveSidebar);

    toggleBtn.addEventListener('click', () => {
      if (sidebar.classList.contains('collapsed')) openDrawer();
      else closeDrawer();
    });

    scrim.addEventListener('click', closeDrawer);
  })();
</script>
