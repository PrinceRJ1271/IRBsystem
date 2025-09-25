<?php
// ai/data_helpers.php
require_once __DIR__ . '/../config/db.php';

/**
 * Get pending follow-ups snapshot.
 * $type: 'received' | 'sent' | 'all'
 */
function ai_db_pending_followups(string $type = 'all'): array {
  global $conn;

  $out = [
    'type' => $type,
    'received' => 0,
    'sent' => 0,
    'total' => 0,
    'samples' => [], // a few rows to show in chat
  ];

  // Counts
  $sqlRec = "SELECT COUNT(*) c FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending'";
  $sqlSent= "SELECT COUNT(*) c FROM letters_sent_followup WHERE LOWER(TRIM(followup_status))='pending'";
  $rec = $conn->query($sqlRec); $sent = $conn->query($sqlSent);
  $out['received'] = (int)($rec->fetch_assoc()['c'] ?? 0);
  $out['sent']     = (int)($sent->fetch_assoc()['c'] ?? 0);
  $out['total']    = $out['received'] + $out['sent'];

  // Some details (limit 5)
  if ($type === 'received' || $type === 'all') {
    $q = "
      SELECT lrf.letter_received_id AS id, lr.company_name, lr.received_date AS date, lrf.followup_status AS status
      FROM letters_received_followup lrf
      JOIN letters_received lr ON lr.letter_received_id = lrf.letter_received_id
      WHERE LOWER(TRIM(lrf.followup_status))='pending'
      ORDER BY lr.received_date DESC
      LIMIT 5
    ";
    if ($r = $conn->query($q)) {
      while ($row = $r->fetch_assoc()) { $out['samples'][] = $row; }
    }
  }
  if ($type === 'sent' || $type === 'all') {
    $q = "
      SELECT lsf.letter_sent_id AS id, ls.company_name, ls.sent_date AS date, lsf.followup_status AS status
      FROM letters_sent_followup lsf
      JOIN letters_sent ls ON ls.letter_sent_id = lsf.letter_sent_id
      WHERE LOWER(TRIM(lsf.followup_status))='pending'
      ORDER BY ls.sent_date DESC
      LIMIT 5
    ";
    if ($r = $conn->query($q)) {
      while ($row = $r->fetch_assoc()) { $out['samples'][] = $row; }
    }
  }
  return $out;
}

/** Recent letters (received + sent) mixed, latest first */
function ai_db_recent_letters(int $limit = 8): array {
  global $conn;
  $limit = max(1, min($limit, 25));

  $items = [];
  $res = $conn->query("
    SELECT 'Received' as typ, letter_received_id as id, company_name, received_date as d
    FROM letters_received
    UNION ALL
    SELECT 'Sent' as typ, letter_sent_id as id, company_name, sent_date as d
    FROM letters_sent
    ORDER BY d DESC
    LIMIT $limit
  ");
  if ($res) while($row=$res->fetch_assoc()) $items[] = $row;
  return $items;
}

/** Quick client search by company or id */
function ai_db_search_clients(string $q, int $limit=10): array {
  global $conn;
  $limit = max(1, min($limit, 25));
  $q = trim($q);
  if ($q === '') return [];

  $stmt = $conn->prepare("
    SELECT client_id, company_name, created_at
    FROM clients
    WHERE company_name LIKE CONCAT('%', ?, '%') OR client_id LIKE CONCAT('%', ?, '%')
    ORDER BY created_at DESC
    LIMIT $limit
  ");
  $stmt->bind_param('ss', $q, $q);
  $stmt->execute();
  $r = $stmt->get_result();
  $out = [];
  while ($row = $r->fetch_assoc()) $out[] = $row;
  $stmt->close();
  return $out;
}

/** Simple KPI snapshot */
function ai_db_kpis(): array {
  global $conn;

  $today          = date('Y-m-d');
  $monthStart     = date('Y-m-01');
  $prevMonthStart = date('Y-m-01', strtotime('-1 month'));

  $total_clients = (int)($conn->query("SELECT COUNT(*) c FROM clients")->fetch_assoc()['c'] ?? 0);

  $recv_month = 0; $sent_month = 0;
  if ($st = $conn->prepare("SELECT COUNT(*) c FROM letters_received WHERE received_date >= ?")) {
    $st->bind_param("s", $monthStart); $st->execute();
    $recv_month = (int)($st->get_result()->fetch_assoc()['c'] ?? 0); $st->close();
  }
  if ($st = $conn->prepare("SELECT COUNT(*) c FROM letters_sent WHERE sent_date >= ?")) {
    $st->bind_param("s", $monthStart); $st->execute();
    $sent_month = (int)($st->get_result()->fetch_assoc()['c'] ?? 0); $st->close();
  }

  $pending = 0;
  $res = $conn->query("
    SELECT
    (SELECT COUNT(*) FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending') +
    (SELECT COUNT(*) FROM letters_sent_followup WHERE LOWER(TRIM(followup_status))='pending') as c
  ");
  $pending = (int)($res->fetch_assoc()['c'] ?? 0);

  return [
    'as_of' => $today,
    'total_clients' => $total_clients,
    'received_this_month' => $recv_month,
    'sent_this_month' => $sent_month,
    'pending_followups' => $pending,
  ];
}
