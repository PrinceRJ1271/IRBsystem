<?php
if (!isset($_SESSION)) session_start();
?>
<style>
  .nav-profile-img img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #fff;
  }

  .nav-profile-text p {
    margin: 0;
    font-weight: 500;
    font-size: 15px;
  }

  .clock,
  .logout-link {
    font-size: 15px;
    color: #6c757d;
    display: flex;
    align-items: center;
    white-space: nowrap;
  }

  .logout-link {
    color: #dc3545;
    text-decoration: none;
    font-weight: 500;
    gap: 4px;
  }

  @media (max-width: 767.98px) {
    .navbar-menu-wrapper {
      flex-direction: column !important;
      align-items: stretch !important;
      padding: 0.5rem 1rem;
    }

    .navbar-nav {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 0.75rem;
    }

    .nav-profile-img img {
      width: 44px;
      height: 44px;
    }

    .nav-profile-text p,
    .clock,
    .logout-link {
      font-size: 14px;
    }
  }
</style>

<nav class="navbar p-0 fixed-top d-flex flex-row">
  <!-- Mobile Brand -->
  <div class="navbar-brand-wrapper d-flex align-items-center justify-content-between w-100 w-lg-auto px-3">
    <a class="navbar-brand brand-logo-mini" href="/dashboard.php">
      <img src="/assets/images/logo-mini.svg" alt="logo" />
    </a>
    <!-- Mobile toggle icon -->
    <button class="navbar-toggler d-lg-none border-0" type="button" data-toggle="minimize">
      <span class="mdi mdi-menu text-primary"></span>
    </button>
  </div>

  <!-- Main Header Content -->
  <div class="navbar-menu-wrapper flex-grow d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between px-3 px-lg-0 py-2 py-lg-0">
    
    <!-- Search -->
    <form class="d-none d-md-flex input-group w-100 w-lg-50 mx-lg-3 mb-2 mb-lg-0" action="/search/quick_search.php" method="get">
      <input type="text" name="q" class="form-control" placeholder="Search ID, Client, Branch" />
      <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
    </form>

    <!-- Right section -->
    <ul class="navbar-nav d-flex flex-row align-items-center gap-3">
      <!-- Clock -->
      <li class="nav-item clock" id="liveClock">
        <i class="mdi mdi-clock-outline me-1"></i> --
      </li>

      <!-- Profile -->
      <li class="nav-item d-flex align-items-center">
        <a class="d-flex align-items-center text-decoration-none" href="/profile.php" title="View Profile">
          <div class="nav-profile-img me-2">
            <img src="<?= '/' . htmlspecialchars($_SESSION['profile_pic'] ?? 'assets/images/uploads/default.png') ?>" alt="profile" />
          </div>
          <div class="nav-profile-text">
            <p class="text-black">Hello, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
          </div>
        </a>
      </li>

      <!-- Logout -->
      <li class="nav-item">
        <a class="logout-link d-flex align-items-center" href="/logout.php" title="Logout">
          <i class="mdi mdi-logout"></i> <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>

<script>
  function updateClock() {
    const now = new Date();
    const optionsDate = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true };
    const formattedDate = now.toLocaleDateString('en-SG', optionsDate);
    const formattedTime = now.toLocaleTimeString('en-SG', optionsTime);

    const clockEl = document.getElementById('liveClock');
    clockEl.innerHTML = `<i class="mdi mdi-clock-outline me-1"></i> ${formattedDate} - ${formattedTime}`;
  }

  setInterval(updateClock, 1000);
  updateClock();
</script>
