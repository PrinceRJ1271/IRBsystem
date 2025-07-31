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
    white-space: nowrap;
  }

  .logout-link {
    margin-left: 1rem;
    font-size: 14px;
    color: #dc3545;
    font-weight: 500;
    text-decoration: none;
  }

  .logout-link i {
    margin-right: 4px;
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

      <!-- Clock -->
      <li class="nav-item clock" id="liveClock">
        <i class="mdi mdi-clock-outline me-1"></i> --
      </li>

      <!-- Profile Link -->
      <li class="nav-item d-flex align-items-center me-3">
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
        <a class="logout-link" href="/logout.php" title="Logout">
          <i class="mdi mdi-logout"></i> Logout
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
