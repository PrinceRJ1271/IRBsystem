<?php
// dashboard.php – StarAdmin2 dashboard with KPI sparklines & deltas
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
function pct_delta($now, $prev){
  if ($prev == 0) return $now > 0 ? 100 : 0;
  return round((($now - $prev) / $prev) * 100);
}

// ---------- Dates ----------
$today        = date('Y-m-d');
$monthStart   = date('Y-m-01');
$prevMonthStart = date('Y-m-01', strtotime('-1 month'));
$prevMonthEnd   = $monthStart; // exclusive

// ---------- KPIs ----------
$kpis = [
  'total_clients'     => 0,
  'recv_month'        => 0,
  'sent_month'        => 0,
  'pending_fu'        => 0,
  'deliveries_today'  => 0,
];

// Total clients
if ($res = $conn->query("SELECT COUNT(*) AS c FROM clients")) {
  $kpis['total_clients'] = (int)($res->fetch_assoc()['c'] ?? 0);
}

// Letters received this month
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE received_date >= ?")) {
  $stmt->bind_param("s", $monthStart);
  $stmt->execute();
  $kpis['recv_month'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
}

// Letters received previous month
$recv_prev = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE received_date >= ? AND received_date < ?")) {
  $stmt->bind_param("ss", $prevMonthStart, $prevMonthEnd);
  $stmt->execute();
  $recv_prev = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
}
$recv_delta = pct_delta($kpis['recv_month'], $recv_prev);

// Letters sent this month
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE sent_date >= ?")) {
  $stmt->bind_param("s", $monthStart);
  $stmt->execute();
  $kpis['sent_month'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
}

// Letters sent previous month
$sent_prev = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE sent_date >= ? AND sent_date < ?")) {
  $stmt->bind_param("ss", $prevMonthStart, $prevMonthEnd);
  $stmt->execute();
  $sent_prev = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
}
$sent_delta = pct_delta($kpis['sent_month'], $sent_prev);

// Pending follow-ups (received + sent)
if ($res = $conn->query("
  SELECT 
    (SELECT COUNT(*) FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending') +
    (SELECT COUNT(*) FROM letters_sent_followup     WHERE LOWER(TRIM(followup_status))='pending') AS c
")) {
  $kpis['pending_fu'] = (int)($res->fetch_assoc()['c'] ?? 0);
}

// Deliveries today (DB table is 'letters_delivered' with 'delivered_date')
if ($conn->query("SHOW TABLES LIKE 'letters_delivered'")->num_rows > 0) {
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_delivered WHERE DATE(delivered_date)=?")) {
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $kpis['deliveries_today'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  }
}

// ---------- Sparkline series (last 6 months) ----------
$miniMonths = []; $recv6 = []; $sent6 = []; $pend6 = []; $clients6 = []; $clients_this_month = 0; $clients_prev = 0;
$hasClientCreated = $conn->query("SHOW COLUMNS FROM clients LIKE 'created_at'")->num_rows > 0;

for ($i = 5; $i >= 0; $i--) {
  $ts = strtotime("-$i months");
  $label = date('M', $ts);
  $y = (int)date('Y', $ts);
  $m = (int)date('m', $ts);
  $miniMonths[] = $label;

  // received
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE YEAR(received_date)=? AND MONTH(received_date)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $recv6[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $recv6[] = 0; }

  // sent
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE YEAR(sent_date)=? AND MONTH(sent_date)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $sent6[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $sent6[] = 0; }

  // pending snapshot by creation month of follow-ups (best available proxy)
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending' AND YEAR(created_at)=? AND MONTH(created_at)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $p1 = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $p1 = 0; }
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent_followup WHERE LOWER(TRIM(followup_status))='pending' AND YEAR(created_at)=? AND MONTH(created_at)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $p2 = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $p2 = 0; }
  $pend6[] = $p1 + $p2;

  // clients (only if clients.created_at exists)
  if ($hasClientCreated) {
    if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM clients WHERE YEAR(created_at)=? AND MONTH(created_at)=?")) {
      $stmt->bind_param("ii", $y, $m);
      $stmt->execute();
      $clients6[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
      $stmt->close();
    } else { $clients6[] = 0; }
  } else {
    $clients6[] = 0; // safe fallback
  }
}

// deltas for sparklines
$pend_prev = count($pend6) >= 2 ? $pend6[count($pend6)-2] : 0;
$pend_now  = count($pend6) >= 1 ? $pend6[count($pend6)-1] : 0;
$pend_delta = pct_delta($pend_now, $pend_prev);

if ($hasClientCreated) {
  // this month vs previous month new clients
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM clients WHERE created_at >= ?")) {
    $stmt->bind_param("s", $monthStart);
    $stmt->execute();
    $clients_this_month = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  }
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM clients WHERE created_at >= ? AND created_at < ?")) {
    $stmt->bind_param("ss", $prevMonthStart, $prevMonthEnd);
    $stmt->execute();
    $clients_prev = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  }
}
$clients_delta = pct_delta($clients_this_month, $clients_prev);

// ---------- Full 12-month series for main line chart ----------
$months = []; $recvSeries = []; $sentSeries = [];
for ($i = 11; $i >= 0; $i--) {
  $ts = strtotime("-$i months");
  $label = date('M Y', $ts);
  $y = (int)date('Y', $ts);
  $m = (int)date('m', $ts);
  $months[] = $label;

  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_received WHERE YEAR(received_date)=? AND MONTH(received_date)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $recvSeries[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $recvSeries[] = 0; }

  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM letters_sent WHERE YEAR(sent_date)=? AND MONTH(sent_date)=?")) {
    $stmt->bind_param("ii", $y, $m);
    $stmt->execute();
    $sentSeries[] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
  } else { $sentSeries[] = 0; }
}

// ---------- Top branches ----------
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
if ($stmt = $conn->prepare($q)) {
  $stmt->bind_param("ss", $since, $since);
  $stmt->execute();
  $r = $stmt->get_result();
  while ($row = $r->fetch_assoc()){
    $topLabels[] = $row['branch_name'] ?: 'Unknown';
    $topCounts[] = (int)$row['total_c'];
  }
  $stmt->close();
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
    .kpi-card{border-radius:1rem; box-shadow:0 4px 10px rgba(0,0,0,.06); overflow:hidden;}
    .kpi-title{font-size:.85rem; color:#6c757d; margin-bottom:.25rem;}
    .kpi-value{font-size:1.6rem; font-weight:700;}
    .kpi-sub { display:flex; gap:.5rem; align-items:center; }
    .delta.up{color:#00C19C;}
    .delta.down{color:#FF7A7A;}
    .spark-wrap{height:60px; margin-top:.35rem;}
    .chart-card{border-radius:1rem; box-shadow:0 4px 12px rgba(0,0,0,.06);}
    .list-activity li{display:flex; justify-content:space-between; padding:.5rem 0; border-bottom:1px dashed #eee;}
    .list-activity li:last-child{border-bottom:none;}
    .chart-card canvas{min-height:260px;}
    .pill{background:#f1f5ff; color:#4B49AC; border-radius:999px; padding:2px 8px; font-size:.75rem;}
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
          <div class="col-12 d-flex align-items-center justify-content-between">
            <div>
              <h4 class="page-title">Welcome back</h4>
              <p class="text-muted mb-0">Here’s a quick overview of letters activity and follow-ups.</p>
            </div>
            <div class="pill">Deliveries Today: <strong><?= safe($kpis['deliveries_today']) ?></strong></div>
          </div>
        </div>

        <!-- KPIs -->
        <div class="row">
          <!-- Total Clients (with optional new-clients delta if available) -->
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Total Clients</div>
                <div class="kpi-value"><?= safe($kpis['total_clients']) ?></div>
                <div class="kpi-sub">
                  <?php if ($hasClientCreated): ?>
                    <?php $cls = ($clients_delta>=0?'up':'down'); $arrow = $clients_delta>=0?'mdi-arrow-up-bold':'mdi-arrow-down-bold'; ?>
                    <span class="delta <?= $cls ?>"><i class="mdi <?= $arrow ?>"></i> <?= abs($clients_delta) ?>%</span>
                    <small class="text-muted">vs last month (new)</small>
                  <?php else: ?>
                    <small class="text-muted">as of today</small>
                  <?php endif; ?>
                </div>
                <?php if ($hasClientCreated): ?>
                <div class="spark-wrap"><canvas id="spClients"></canvas></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Received -->
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Received (This Month)</div>
                <div class="kpi-value"><?= safe($kpis['recv_month']) ?></div>
                <?php $cls = ($recv_delta>=0?'up':'down'); $arrow = $recv_delta>=0?'mdi-arrow-up-bold':'mdi-arrow-down-bold'; ?>
                <div class="kpi-sub"><span class="delta <?= $cls ?>"><i class="mdi <?= $arrow ?>"></i> <?= abs($recv_delta) ?>%</span><small class="text-muted"><?= safe(date('M Y')) ?></small></div>
                <div class="spark-wrap"><canvas id="spRecv"></canvas></div>
              </div>
            </div>
          </div>

          <!-- Sent -->
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Sent (This Month)</div>
                <div class="kpi-value"><?= safe($kpis['sent_month']) ?></div>
                <?php $cls = ($sent_delta>=0?'up':'down'); $arrow = $sent_delta>=0?'mdi-arrow-up-bold':'mdi-arrow-down-bold'; ?>
                <div class="kpi-sub"><span class="delta <?= $cls ?>"><i class="mdi <?= $arrow ?>"></i> <?= abs($sent_delta) ?>%</span><small class="text-muted"><?= safe(date('M Y')) ?></small></div>
                <div class="spark-wrap"><canvas id="spSent"></canvas></div>
              </div>
            </div>
          </div>

          <!-- Pending Follow-ups -->
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card kpi-card">
              <div class="card-body">
                <div class="kpi-title">Pending Follow-ups</div>
                <div class="kpi-value"><?= safe($kpis['pending_fu']) ?></div>
                <?php $cls = ($pend_delta>=0?'up':'down'); $arrow = $pend_delta>=0?'mdi-arrow-up-bold':'mdi-arrow-down-bold'; ?>
                <div class="kpi-sub"><span class="delta <?= $cls ?>"><i class="mdi <?= $arrow ?>"></i> <?= abs($pend_delta) ?>%</span><small class="text-muted">MoM</small></div>
                <div class="spark-wrap"><canvas id="spPend"></canvas></div>
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
                <canvas id="line12m" role="img" aria-label="Line chart for letters"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-4 mb-4">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Follow-up Status</h5>
                <canvas id="donutFU" role="img" aria-label="Follow-up donut"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Branch chart + Recent Activity -->
        <div class="row">
          <div class="col-lg-8 mb-4">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title mb-3">Top Branches (last 90 days)</h5>
                <canvas id="barBranches" role="img" aria-label="Branches bar"></canvas>
              </div>
            </div>
          </div>

          <div class="col-lg-4 mb-4">
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

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
  // PHP -> JS
  const months      = <?= json_encode($months) ?>;
  const recvSeries  = <?= json_encode($recvSeries) ?>;
  const sentSeries  = <?= json_encode($sentSeries) ?>;
  const fuData      = <?= json_encode([$kpis['pending_fu'] - 0,  // pending now
                                       max(0, ($kpis['recv_month'] + $kpis['sent_month']) - $kpis['pending_fu']) // rough completed share (visual only)
                                      ]) ?>;
  const branchLbls  = <?= json_encode($topLabels) ?>;
  const branchVals  = <?= json_encode($topCounts) ?>;

  const miniMonths  = <?= json_encode($miniMonths) ?>;
  const spRecv      = <?= json_encode($recv6) ?>;
  const spSent      = <?= json_encode($sent6) ?>;
  const spPend      = <?= json_encode($pend6) ?>;
  const spClients   = <?= json_encode($clients6) ?>;
  const hasClientCreated = <?= json_encode($hasClientCreated) ?>;

  if (typeof Chart !== 'undefined') {
    const makeGrad = (ctx, top='#6f7bf7', bottom='rgba(111,123,247,0)') => {
      const g = ctx.createLinearGradient(0, 0, 0, 60);
      g.addColorStop(0, top);
      g.addColorStop(1, bottom);
      return g;
    };

    // ===== Main charts =====
    // Line — Received vs Sent
    const lineCtx = document.getElementById('line12m').getContext('2d');
    new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Received',
            data: recvSeries,
            borderColor: '#4B49AC',
            backgroundColor: (()=>{const c=lineCtx.canvas;return makeGrad(lineCtx,'rgba(75,73,172,0.35)','rgba(75,73,172,0)')})(),
            fill: true, tension: .35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
          },
          {
            label: 'Sent',
            data: sentSeries,
            borderColor: '#00C19C',
            backgroundColor: (()=>{const c=lineCtx.canvas;return makeGrad(lineCtx,'rgba(0,193,156,0.35)','rgba(0,193,156,0)')})(),
            fill: true, tension: .35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
          }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        animation: { duration: 900, easing: 'easeOutQuart' },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { display: true } }
      }
    });

    // Donut — Follow-up status (visual mix)
    const donutCtx = document.getElementById('donutFU').getContext('2d');
    new Chart(donutCtx, {
      type: 'doughnut',
      data: { labels:['Pending','Completed-ish'], datasets:[{ data: fuData, backgroundColor: ['#FF7A7A','#00C19C'] }] },
      options:{ responsive:true, cutout:'65%', animation:{duration:800}, plugins:{ legend:{ position:'bottom' } } }
    });

    // Horizontal bar — Top branches
    const barCtx = document.getElementById('barBranches').getContext('2d');
    new Chart(barCtx, {
      type:'bar',
      data:{ labels: branchLbls, datasets:[{ label:'Letters', data: branchVals, backgroundColor:'#6f7bf7', borderRadius:6, borderSkipped:false }] },
      options:{
        indexAxis:'y', responsive:true, maintainAspectRatio:false, animation:{duration:900},
        scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } }, plugins:{ legend:{ display:false } }
      }
    });

    // ===== KPI Sparklines =====
    const sparkOpts = (ctx, color='#4B49AC') => ({
      type:'line',
      data:{ labels: miniMonths, datasets:[{
        data: ctx.series,
        borderColor: color,
        backgroundColor: makeGrad(ctx.gx, color.replace('1)','0.25)').replace('rgb','rgba'), 'rgba(0,0,0,0)'),
        borderWidth:2, tension:.35, pointRadius:0, fill:true
      }]},
      options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{enabled:false}},
        scales:{ x:{display:false}, y:{display:false} },
        elements:{ line:{capBezierPoints:true} },
        animation:{ duration:600 }
      }
    });

    // Received spark
    (()=>{
      const c = document.getElementById('spRecv'); if(!c) return;
      const gx = c.getContext('2d');
      new Chart(c, sparkOpts({gx,series:spRecv}, '#4B49AC'));
    })();
    // Sent spark
    (()=>{
      const c = document.getElementById('spSent'); if(!c) return;
      const gx = c.getContext('2d');
      new Chart(c, sparkOpts({gx,series:spSent}, '#00C19C'));
    })();
    // Pending spark
    (()=>{
      const c = document.getElementById('spPend'); if(!c) return;
      const gx = c.getContext('2d');
      new Chart(c, sparkOpts({gx,series:spPend}, '#FF7A7A'));
    })();
    // Clients spark (only if we have created_at)
    (()=>{
      if(!hasClientCreated) return;
      const c = document.getElementById('spClients'); if(!c) return;
      const gx = c.getContext('2d');
      new Chart(c, sparkOpts({gx,series:spClients}, '#6f7bf7'));
    })();
  }
</script>
</body>
</html>
