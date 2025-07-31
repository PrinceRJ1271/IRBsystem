<?php
if (!isset($_SESSION)) session_start();
?>
<style>
  .nav-profile-img img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
  }
  .nav-profile-text p {
    margin-bottom: 0;
    font-weight: 500;
  }
</style>

<nav class="navbar p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
    <a class="navbar-brand brand-logo-mini" href="/dashboard.php">
      <img src="assets/images/logo-mini.svg" alt="logo" />
    </a>
  </div>
  <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
    
    <!-- Search Bar -->
    <form class="d-none d-md-flex input-group w-50 mx-3" action="/search/quick_search.php" method="get">
      <input type="text" name="q" class="form-control" placeholder="Search ID, Client, Branch" />
      <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
    </form>

    <ul class="navbar-nav navbar-nav-right">
      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="/profile.php" title="View Profile">
          <div class="nav-profile-img me-2">
            <img src="<?= '/' . htmlspecialchars($_SESSION['profile_pic'] ?? 'assets/images/uploads/default.png') ?>" alt="profile" />
          </div>
          <div class="nav-profile-text">
            <p class="text-black">Hello, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
          </div>
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="/logout.php">
            <i class="mdi mdi-logout me-2"></i> Logout
          </a>
        </div>
      </li>
    </ul>
  </div>
</nav>
