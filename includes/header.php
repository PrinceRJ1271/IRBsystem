<?php
if (!isset($_SESSION)) session_start();
?>
<style>
  /* --- keep existing look --- */
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
    white-space: nowrap;
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
    white-space: nowrap;
  }
  .logout-link i { margin-right: 4px; }

  .company-logo {
    height: 55px;
    width: auto;
    margin-right: 1rem;
    padding-right: 6rem; /* existing spacing */
  }

  /* -------- FIXES FOR ZOOM / WRAP -------- */
  /* Let the header grow vertically instead of clipping when zoomed */
  nav.fixed-top {
    height: auto !important;     /* override inline 80px */
    min-height: 80px;            /* keep original minimum height */
    z-index: 1030;
  }

  /* Allow the right-side cluster to wrap neatly on a new line */
  .navbar > .d-flex.align-items-center:last-child {
    flex: 1 1 auto;              /* let it take remaining space */
    justify-content: flex-end;   /* keep items right-aligned */
    gap: .75rem;                 /* consistent spacing between items */
    flex-wrap: wrap;             /* <— key: wrap instead of overflowing */
    row-gap: .25rem;
  }

  /* Make search compressible so it gives space to profile/clock first */
  .search-form {
    width: 945px;                /* baseline */
    max-width: 100%;
    flex: 1 1 420px;             /* shrink first, then wrap */
    min-width: 260px;            /* don’t get too tiny to tap */
    margin-right: 1rem;
  }
  /* When the search wraps under the logo/controls, add a little top space */
  .navbar .search-form.wrapped { margin-top: .25rem; }

  /* Keep the top row content aligned even as it wraps */
  .navbar-menu-wrapper { width: 100%; }

  @media (max-width: 991.98px) {
    /* Nudge earlier wrapping on smaller widths */
    .search-form { flex-basis: 360px; }
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
    .logout-link { margin-left: 0 !important; }
    .nav-profile-img img { width: 50px; height: 50px; }
    .company-logo { height: 30px; margin-bottom: 0.5rem; }
    .nav-profile-text p, .clock { font-size: 13px; }
    .search-form {
      width: 100%;
      margin: 0.5rem 0;
      min-width: 0;
      flex-basis: 100%;
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
  // Add a tiny hook to mark the search as wrapped (cosmetic top margin when it drops)
  (function () {
    const form = document.querySelector('.search-form');
    if (!form) return;
    const ro = new ResizeObserver(() => {
      const rectForm = form.getBoundingClientRect();
      const rectNav  = document.querySelector('nav.fixed-top').getBoundingClientRect();
      // If the top of the search sits below the nav's top line, it wrapped.
      if (rectForm.top - rectNav.top > 8) form.classList.add('wrapped');
      else form.classList.remove('wrapped');
    });
    ro.observe(form);
  })();

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
