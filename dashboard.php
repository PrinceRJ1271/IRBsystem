<?php include('header.php'); ?>
<div class="container-fluid page-body-wrapper">
  <?php include('sidebar.php'); ?>
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Welcome, <?php echo $user_id; ?>!</h3>
              <p>Your Role Level: <?php echo $level_id; ?></p>
              <h4 class="mt-4">Developer Dashboard</h4>
              <ul>
                <li><a href="forms/client_form.php">Register Client</a></li>
                <li><a href="forms/letter_received_form.php">IRB Letter Received Form</a></li>
                <li><a href="forms/letter_sent_form.php">IRB Letter Sent Form</a></li>
              </ul>
              <!-- Add more cards/rows here later if needed -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('footer.php'); ?>
  </div>
</div>
</body>
</html>
