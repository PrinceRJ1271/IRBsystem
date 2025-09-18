<?php
// dashboard.php – StarAdmin2 dashboard with role-aware quick links (matching sidebar.php)
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// All roles can access the dashboard
check_access([1,2,3,4]);

// Convenience
$level_id = (int)($_SESSION['level_id'] ?? 0);
$isDev    = ($level_id === 1);
$isMgr    = ($level_id === 2);
$isSenior = ($level_id === 3);
$isAdmin  = ($level_id === 4);

// Helper safe
function safe($v){ return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); }

// ---------- KPIs ----------
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$kpis = [
  'total_clients' => 0,
  'recv_month'    => 0,
  'sent_month'    => 0,
  'pending_fu'    => 0,
  'deliveries_today' => 0,
];

// Total clients
if ($res = $conn->query("SELECT COUNT(*) AS c FROM clients")) {
  $kpis['total_clients'] = (int)($res->fetch_assoc()['c'] ?? 0);
}

// Letters received this month
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE received_date >= ?");
$stmt->bind_param("s", $monthStart);
$stmt->execute();
$kpis['recv_month'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

// Letters sent this month
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE sent_date >= ?");
$stmt->bind_param("s", $monthStart);
$stmt->execute();
$kpis['sent_month'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

// Pending follow-ups (received + sent)
if ($res = $conn->query("
  SELECT 
    (SELECT COUNT(*) FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending') +
    (SELECT COUNT(*) FROM letters_sent_followup     WHERE LOWER(TRIM(followup_status))='pending') AS c
")) {
  $kpis['pending_fu'] = (int)($res->fetch_assoc()['c'] ?? 0);
}

// Deliveries today (only if table exists)
if ($conn->query("SHOW TABLES LIKE 'letter_delivery'")->num_rows > 0) {
  $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letter_delivery WHERE DATE(delivery_date)=?");
  $stmt->bind_param("s", $today);
  $stmt->execute();
  $kpis['deliveries_today'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
}

// ---------- 12-month series (Received vs Sent) ----------
$months = []; $recvSeries = []; $sentSeries = [];
for ($i = 11; $i >= 0; $i--) {
  $ts = strtotime("-$i months");
  $label = date('M Y', $ts);
  $y = (int)date('Y', $ts);
  $m = (int)date('m', $ts);
  $months[] = $label;

  $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE YEAR(received_date)=? AND MONTH(received_date)=?");
  $stmt->bind_param("ii", $y, $m);
  $stmt->execute();
  $recvSeries[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

  $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE YEAR(sent_date)=? AND MONTH(sent_date)=?");
  $stmt->bind_param("ii", $y, $m);
  $stmt->execute();
  $sentSeries[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
}

// ---------- Follow-up status donut ----------
$fu = ['pending'=>0,'completed'=>0];
if ($res = $conn->query("SELECT LOWER(TRIM(followup_status)) s, COUNT(*) c FROM letters_received_followup GROUP BY s")) {
  while($row = $res->fetch_assoc()){
    if ($row['s']==='pending')   $fu['pending']   += (int)$row['c'];
    if ($row['s']==='completed') $fu['completed'] += (int)$row['c'];
  }
}
if ($res = $conn->query("SELECT LOWER(TRIM(followup_status)) s, COUNT(*) c FROM letters_sent_followup GROUP BY s")) {
  while($row = $res->fetch_assoc()){
    if ($row['s']==='pending')   $fu['pending']   += (int)$row['c'];
    if ($row['s']==='completed') $fu['completed'] += (int)$row['c'];
  }
}

// ---------- Branch bar (top 5, last 90 days) ----------
$topLabels = []; $topCounts = [];
$since = date('Y-m-d', strtotime('-90 days'));
$q = "
  SELECT b.name AS branch_name, SUM(x.c) AS total_c FROM (
    SELECT branch_id, COUNT(*) c FROM letters_received WHERE received_date >= ? GROUP BY branch_id
    UNION ALL
    SELECT branch_id, COUNT(*) c FROM letters_sent     WHERE sent_date    >= ? GROUP BY branch_id
  ) x
  JOIN irb_branches b ON b.branch_id = x.branch_id
  GROUP BY b.name
  ORDER BY total_c DESC
  LIMIT 5
";
$stmt = $conn->prepare($q);
$stmt->bind_param("ss", $since, $since);
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()){
  $topLabels[] = $row['branch_name'] ?: 'Unknown';
  $topCounts[] = (int)$row['total_c'];
}

// ---------- Recent activity ----------
$activity = [];
if ($res = $conn->query("SELECT 'Received' AS typ, letter_received_id AS id, received_date AS d FROM letters_received ORDER BY received_date DESC LIMIT 5")) {
  while ($row = $res->fetch_assoc()){ $activity[] = ['type'=>'Received','id'=>$row['id'],'date'=>$row['d']]; }
}
if ($res = $conn->query("SELECT 'Sent' AS typ, letter_sent_id AS id, sent_date AS d FROM letters_sent ORDER BY sent_date DESC LIMIT 5")) {
  while ($row = $res->fetch_assoc()){ $activity[] = ['type'=>'Sent','id'=>$row['id'],'date'=>$row['d']]; }
}
usort($activity, fn($a,$b)=>strcmp($b['date'],$a['date']));
$activity = array_slice($activity, 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - IRB System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="shortcut icon" href="assets/images/favicon.png" />
  <style>
    .page-title { font-weight:600; color:#4B49AC; }
    .kpi-card{border-radius:1rem; box-shadow:0 4px 10px rgba(0,0,0,.06);}
    .kpi-title{font-size:.85rem; color:#6c757d; margin-bottom:.25rem;}
    .kpi-value{font-size:1.6rem; font-weight:700;}
    .chart-card{border-radius:1rem; box-shadow:0 4px 12px rgba(0,0,0,.06);}
    .list-activity li{display:flex; justify-content:space-between; padding:.5rem 0; border-bottom:1px dashed #eee;}
    .list-activity li:last-child{border-bottom:none;}
    .quick-link { display:flex; align-items:center; gap:.6rem; padding:.45rem 0; }
    .quick-link i { font-size:1.1rem; opacity:.85; }
  </style>
</head>
<body>
<div class="container-scroller">
  <div class="container-fluid page-body-wrapper">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-panel">
      <?php include __DIR__ . '/includes/header.php'; ?>

      <div class="content-wrapper">
        <!-- Welcome -->
        <div class="row mb-3">
          <div class="col-12">
            <h4 class="page-title">Welcome back</h4>
            <p class="text-muted mb-0">Here’s a quick overview of letters activity and follow-ups.</p>
          </div>
        </div>

        <!-- KPIs -->
        <div class="row">
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Total Clients</div>
                <div class="kpi-value"><?= safe($kpis['total_clients']) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Received (This Month)</div>
                <div class="kpi-value"><?= safe($kpis['recv_month']) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Sent (This Month)</div>
                <div class="kpi-value"><?= safe($kpis['sent_month']) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Pending Follow-ups</div>
                <div class="kpi-value"><?= safe($kpis['pending_fu']) ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts row -->
        <div class="row">
          <div class="col-lg-8 mb-4">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Letters (Last 12 Months)</h5>
                <canvas id="line12m" height="110"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-4 mb-4">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Follow-up Status</h5>
                <canvas id="donutFU" height="180"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Role-aware Quick Links + Branch chart / Activity -->
        <div class="row">
          <div class="col-lg-8 mb-4">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Top Branches (last 90 days)</h5>
                <canvas id="barBranches" height="110"></canvas>
              </div>
            </div>
          </div>

          <div class="col-lg-4 mb-4">
            <div class="card chart-card mb-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Quick Links</h5>

                <!-- Registration & Setup -->
                <?php if ($isDev || $isMgr || $isSenior || $isAdmin): ?>
                <p class="text-muted mb-2">Registration &amp; Setup</p>
                <?php endif; ?>
                <?php if ($isDev || $isMgr): ?>
                  <div class="quick-link"><i class="mdi mdi-account-plus"></i><a href="/forms/client_form.php">Register Client</a></div>
                <?php endif; ?>
                <?php if ($isDev): ?>
                  <div class="quick-link"><i class="mdi mdi-account-settings"></i><a href="/register.php">Register User</a></div>
                <?php endif; ?>
                <?php if ($isDev || $isMgr || $isSenior): ?>
                  <div class="quick-link"><i class="mdi mdi-city"></i><a href="/forms/irb_branch_form.php">Add IRB Branch</a></div>
                  <div class="quick-link"><i class="mdi mdi-note-plus"></i><a href="/forms/letter_type_form.php">Add Letter Type</a></div>
                <?php endif; ?>

                <hr>

                <!-- Letters & Follow-ups -->
                <?php if ($isDev || $isMgr || $isSenior): ?>
                  <p class="text-muted mb-2">Letters &amp; Follow-ups</p>
                  <div class="quick-link"><i class="mdi mdi-inbox-arrow-down"></i><a href="/forms/letter_received_form.php">Letter Received</a></div>
                  <div class="quick-link"><i class="mdi mdi-refresh"></i><a href="/forms/letter_received_followup_form.php">Follow-up (Received)</a></div>
                  <div class="quick-link"><i class="mdi mdi-send"></i><a href="/forms/letter_sent_form.php">Letter Sent</a></div>
                  <div class="quick-link"><i class="mdi mdi-reload"></i><a href="/forms/letter_sent_followup_form.php">Follow-up (Sent)</a></div>
                  <hr>
                <?php endif; ?>

                <!-- Delivery -->
                <?php if ($isDev || $isMgr || $isAdmin): ?>
                  <p class="text-muted mb-2">Delivery</p>
                  <div class="quick-link"><i class="mdi mdi-truck-delivery"></i><a href="/forms/letter_delivery_form.php">Letter Delivery</a></div>
                  <hr>
                <?php endif; ?>

                <!-- Utilities -->
                <p class="text-muted mb-2">Utilities</p>
                <div class="quick-link"><i class="mdi mdi-magnify"></i><a href="/search/quick_search.php">Quick Search</a></div>
              </div>
            </div>

            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Recent Activity</h5>
                <ul class="list-unstyled list-activity mb-0">
                  <?php if(empty($activity)): ?>
                    <li><span>No recent activity</span><span>—</span></li>
                  <?php else: foreach($activity as $a): ?>
                    <li>
                      <span><strong><?= safe($a['type']) ?></strong> — <?= safe($a['id']) ?></span>
                      <span class="text-muted"><?= safe($a['date']) ?></span>
                    </li>
                  <?php endforeach; endif; ?>
                </ul>
              </div>
            </div>

          </div>
        </div>
      </div>

      <?php include __DIR__ . '/includes/footer.php'; ?>
    </div>
  </div>
</div>

<!-- Vendor bundle -->
<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/misc.js"></script>

<!-- Chart.js (local; swap to CDN if needed) -->
<script src="assets/vendors/chart.js/Chart.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

<script>
  // Chart data from PHP
  const months      = <?= json_encode($months) ?>;
  const recvSeries  = <?= json_encode($recvSeries) ?>;
  const sentSeries  = <?= json_encode($sentSeries) ?>;
  const fuData      = <?= json_encode([$fu['pending'], $fu['completed']]) ?>;
  const branchLbls  = <?= json_encode($topLabels) ?>;
  const branchVals  = <?= json_encode($topCounts) ?>;

  // Line
  new Chart(document.getElementById('line12m'), {
    type: 'line',
    data: {
      labels: months,
      datasets: [
        { label: 'Received', data: recvSeries, borderWidth: 2, tension:.3 },
        { label: 'Sent',     data: sentSeries, borderWidth: 2, tension:.3 }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
      plugins:{ legend:{ display:true } }
    }
  });

  // Donut
  new Chart(document.getElementById('donutFU'), {
    type: 'doughnut',
    data: {
      labels:['Pending','Completed'],
      datasets:[{ data: fuData }]
    },
    options:{
      responsive:true, cutout:'65%',
      plugins:{ legend:{ position:'bottom' } }
    }
  });

  // Horizontal bar
  new Chart(document.getElementById('barBranches'), {
    type:'bar',
    data:{
      labels: branchLbls,
      datasets:[{ label:'Letters', data: branchVals, borderWidth:1 }]
    },
    options:{
      indexAxis:'y', responsive:true, maintainAspectRatio:false,
      scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } },
      plugins:{ legend:{ display:false } }
    }
  });
</script>
</body>
</html>
