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
    font-size: 14px;
  }

  .clock {
    font-size: 14px;
    color: #6c757d;
    display: flex;
    align-items: center;
    white-space: nowrap;
    margin-right: 1rem;
  }

  .logout-link {
    font-size: 14px;
    color: #dc3545;
    font-weight: 500;
    text-decoration: none;
  }

  .logout-link i {
    margin-right: 4px;
  }

  .company-logo {
    height: 36px;
    width: auto;
    margin-right: 1rem;
  }

  .search-form {
    width: 650px;
    margin-right: 1rem;
  }

  @media (max-width: 767.98px) {
    .navbar-menu-wrapper {
      flex-direction: column !important;
      align-items: flex-start !important;
      padding: 0.5rem 1rem;
    }

    .navbar-nav {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 0.5rem;
    }

    .logout-link {
      margin-left: 0 !important;
    }

    .nav-profile-img img {
      width: 50px;
      height: 50px;
    }

    .company-logo {
      height: 30px;
      margin-bottom: 0.5rem;
    }

    .nav-profile-text p,
    .clock {
      font-size: 13px;
    }

    .search-form {
      width: 100%;
      margin: 0.5rem 0;
    }
  }
</style>

<nav class="navbar p-0 fixed-top d-flex align-items-center justify-content-between px-3" style="background-color: #fff; height: 80px;">
  <!-- Left Section: Logo -->
  <div class="d-flex align-items-center">
    <a href="/dashboard.php">
      <img src="/assets/images/KPMG-logo.png" alt="KPMG Logo" class="company-logo" />
    </a>
  </div>

  <!-- Right Section: Search, Clock, Profile, Logout -->
  <div class="d-flex align-items-center">
    <!-- Search Bar -->
    <form class="d-none d-md-flex input-group search-form" action="/search/quick_search.php" method="get">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search ID, Client, Branch" />
      <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
    </form>

    <!-- Clock -->
    <div class="clock" id="liveClock">
      <i class="mdi mdi-clock-outline me-1"></i> --
    </div>

    <!-- Profile -->
    <a href="/profile.php" class="d-flex align-items-center text-decoration-none me-3">
      <div class="nav-profile-img me-2">
        <img src="<?= '/' . htmlspecialchars($_SESSION['profile_pic'] ?? 'assets/images/uploads/default.png') ?>" alt="profile" />
      </div>
      <div class="nav-profile-text">
        <p class="text-black mb-0">Hello, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
      </div>
    </a>

    <!-- Logout -->
    <a class="logout-link" href="/logout.php" title="Logout">
      <i class="mdi mdi-logout"></i> Logout
    </a>
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
