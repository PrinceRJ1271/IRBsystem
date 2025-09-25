<?php
// ai/data_helpers.php
// Small, read-only DB helpers for the AI tools (mysqli $conn expected).

/**
 * Companies that still need follow-ups:
 * - letters_received with follow_up_required=1 and no followup row OR followup_status='Pending'
 * - letters_sent with follow_up_required=1 and no followup row OR followup_status='Pending'
 * Returns: ['received'=>[...], 'sent'=>[...]]
 */
function dh_list_companies_needing_followups(mysqli $conn): array {
  // Received side
  $sqlR = "
    SELECT
      lr.letter_received_id    AS letter_id,
      lr.received_date         AS date,
      COALESCE(c.company_name,'Unknown') AS company_name,
      'Received'               AS letter_type
    FROM letters_received lr
    LEFT JOIN letters_received_followup fr
           ON fr.letter_received_id = lr.letter_received_id
    LEFT JOIN clients c
           ON c.client_id = lr.client_id
    WHERE lr.follow_up_required = 1
      AND (fr.followup_status IS NULL OR fr.followup_status = 'Pending')
    ORDER BY lr.received_date DESC, lr.letter_received_id DESC
    LIMIT 100
  ";

  // Sent side
  $sqlS = "
    SELECT
      ls.letter_sent_id        AS letter_id,
      ls.sent_date             AS date,
      COALESCE(c.company_name,'Unknown') AS company_name,
      'Sent'                   AS letter_type
    FROM letters_sent ls
    LEFT JOIN letters_sent_followup fs
           ON fs.letter_sent_id = ls.letter_sent_id
    LEFT JOIN clients c
           ON c.client_id = ls.client_id
    WHERE ls.follow_up_required = 1
      AND (fs.followup_status IS NULL OR fs.followup_status = 'Pending')
    ORDER BY ls.sent_date DESC, ls.letter_sent_id DESC
    LIMIT 100
  ";

  $received = fetch_all($conn, $sqlR);
  $sent     = fetch_all($conn, $sqlS);

  return ['received' => $received, 'sent' => $sent];
}

/** Count pending follow-ups for each side. */
function dh_count_pending_followups(mysqli $conn): array {
  $sqlR = "
    SELECT COUNT(*) AS cnt
    FROM letters_received lr
    LEFT JOIN letters_received_followup fr
           ON fr.letter_received_id = lr.letter_received_id
    WHERE lr.follow_up_required = 1
      AND (fr.followup_status IS NULL OR fr.followup_status = 'Pending')
  ";
  $sqlS = "
    SELECT COUNT(*) AS cnt
    FROM letters_sent ls
    LEFT JOIN letters_sent_followup fs
           ON fs.letter_sent_id = ls.letter_sent_id
    WHERE ls.follow_up_required = 1
      AND (fs.followup_status IS NULL OR fs.followup_status = 'Pending')
  ";

  $r = fetch_one($conn, $sqlR);
  $s = fetch_one($conn, $sqlS);

  return [
    'received_pending' => (int)($r['cnt'] ?? 0),
    'sent_pending'     => (int)($s['cnt'] ?? 0),
    'total_pending'    => (int)($r['cnt'] ?? 0) + (int)($s['cnt'] ?? 0),
  ];
}

/** Latest mixed letters (both types) by date desc, small normalized shape. */
function dh_latest_letters(mysqli $conn, int $limit = 8): array {
  $limit = max(1, min(50, $limit));
  $sql = "
    SELECT letter_id, date, company_name, letter_type FROM (
      SELECT lr.letter_received_id AS letter_id,
             lr.received_date      AS date,
             COALESCE(c.company_name,'Unknown') AS company_name,
             'Received'            AS letter_type
      FROM letters_received lr
      LEFT JOIN clients c ON c.client_id = lr.client_id
      UNION ALL
      SELECT ls.letter_sent_id     AS letter_id,
             ls.sent_date          AS date,
             COALESCE(c.company_name,'Unknown') AS company_name,
             'Sent'                AS letter_type
      FROM letters_sent ls
      LEFT JOIN clients c ON c.client_id = ls.client_id
    ) u
    ORDER BY date DESC, letter_type ASC, letter_id DESC
    LIMIT {$limit}
  ";
  return fetch_all($conn, $sql);
}

/** Find clients by (partial) company name. */
function dh_find_client_by_name(mysqli $conn, string $query, int $limit = 10): array {
  $limit = max(1, min(50, $limit));
  $like  = '%' . $conn->real_escape_string($query) . '%';
  $sql = "
    SELECT client_id, company_name
    FROM clients
    WHERE company_name LIKE '{$like}'
    ORDER BY company_name ASC
    LIMIT {$limit}
  ";
  return fetch_all($conn, $sql);
}

/* ---------- tiny fetch helpers ---------- */
function fetch_all(mysqli $conn, string $sql): array {
  $out = [];
  if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) $out[] = $row;
    $res->free();
  }
  return $out;
}
function fetch_one(mysqli $conn, string $sql): ?array {
  if ($res = $conn->query($sql)) {
    $row = $res->fetch_assoc();
    $res->free();
    return $row ?: null;
  }
  return null;
}
