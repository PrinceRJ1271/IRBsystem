<?php
if (!isset($_SESSION)) session_start();
?>
<style>
  .nav-profile-img img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #fff;
  }

  .nav-profile-text p {
    margin-bottom: 0;
    font-weight: 500;
  }

  .clock {
    font-size: 14px;
    margin-right: 1rem;
    color: #6c757d;
    display: flex;
    align-items: center;
  }

  .toggle-theme {
    cursor: pointer;
    margin-right: 1.5rem;
    color: #6c757d;
    font-size: 18px;
  }
</style>

<nav class="navbar p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
    <a class="navbar-brand brand-logo-mini" href="/dashboard.php">
      <img src="/assets/images/logo-mini.svg" alt="logo" />
    </a>
  </div>

  <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch justify-content-between">
    <!-- Search Bar -->
    <form class="d-none d-md-flex input-group w-50 mx-3" action="/search/quick_search.php" method="get">
      <input type="text" name="q" class="form-control" placeholder="Search ID, Client, Branch" />
      <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
    </form>

    <ul class="navbar-nav d-flex align-items-center">

      <!-- Dark Mode Toggle -->
      <li class="nav-item toggle-theme" onclick="toggleTheme()" title="Toggle light/dark mode">
        <i class="mdi mdi-theme-light-dark"></i>
      </li>

      <!-- Clock -->
      <li class="nav-item clock" id="liveClock">
        <i class="mdi mdi-clock-outline me-1"></i> --
      </li>

      <!-- Profile Dropdown -->
      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="nav-profile-img me-2">
            <img src="<?= '/' . htmlspecialchars($_SESSION['profile_pic'] ?? 'assets/images/uploads/default.png') ?>" alt="profile" />
          </div>
          <div class="nav-profile-text">
            <p class="text-black">Hello, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="/profile.php">
            <i class="mdi mdi-account me-2"></i> View Profile
          </a>
          <a class="dropdown-item" href="/change_password.php">
            <i class="mdi mdi-lock me-2"></i> Change Password
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/logout.php">
            <i class="mdi mdi-logout me-2"></i> Logout
          </a>
        </div>
      </li>
    </ul>
  </div>
</nav>

<script>
  // Live Clock
  function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('liveClock');
    clockEl.innerHTML = '<i class="mdi mdi-clock-outline me-1"></i> ' +
      now.toLocaleTimeString('en-SG', { hour12: true });
  }
  setInterval(updateClock, 1000);
  updateClock();

  // Optional Dark Mode Toggle
  function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const themeIcon = document.querySelector('.toggle-theme i');
    if (document.body.classList.contains('dark-theme')) {
      themeIcon.classList.remove('mdi-theme-light-dark');
      themeIcon.classList.add('mdi-weather-night');
    } else {
      themeIcon.classList.remove('mdi-weather-night');
      themeIcon.classList.add('mdi-theme-light-dark');
    }
  }
</script>
