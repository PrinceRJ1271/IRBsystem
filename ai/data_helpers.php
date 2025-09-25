<?php
// ai/data_helpers.php
// Safe, read-only helpers used by the assistant tools.

require_once __DIR__ . '/../config/db.php';

/**
 * Companies that have pending follow-ups (received or sent).
 * Returns: [{company_name, type, source_id, created_at}]
 */
function list_companies_needing_followups() : array {
  global $conn;

  // Pending in received_followup
  $sql1 = "
    SELECT
      COALESCE(c.company_name,'Unknown') AS company_name,
      'received' AS type,
      rf.letter_received_id AS source_id,
      rf.created_at
    FROM letters_received_followup rf
    LEFT JOIN letters_received lr ON lr.letter_received_id = rf.letter_received_id
    LEFT JOIN clients c ON c.client_id = lr.client_id
    WHERE LOWER(TRIM(rf.followup_status)) = 'pending'
  ";

  // Pending in sent_followup
  $sql2 = "
    SELECT
      COALESCE(c.company_name,'Unknown') AS company_name,
      'sent' AS type,
      sf.letter_sent_id AS source_id,
      sf.created_at
    FROM letters_sent_followup sf
    LEFT JOIN letters_sent ls ON ls.letter_sent_id = sf.letter_sent_id
    LEFT JOIN clients c ON c.client_id = ls.client_id
    WHERE LOWER(TRIM(sf.followup_status)) = 'pending'
  ";

  $rows = [];
  if ($rs = $conn->query($sql1)) {
    while ($r = $rs->fetch_assoc()) $rows[] = $r;
  }
  if ($rs = $conn->query($sql2)) {
    while ($r = $rs->fetch_assoc()) $rows[] = $r;
  }

  // Sort most recent first
  usort($rows, function ($a,$b) {
    return strcmp(($b['created_at'] ?? ''), ($a['created_at'] ?? ''));
  });

  return $rows;
}

/**
 * Total pending follow-ups (received + sent).
 * Returns: ['count' => int]
 */
function get_pending_followups_count() : array {
  global $conn;

  $count = 0;

  $q1 = "SELECT COUNT(*) AS c FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending'";
  if ($rs = $conn->query($q1)) $count += (int)($rs->fetch_assoc()['c'] ?? 0);

  $q2 = "SELECT COUNT(*) AS c FROM letters_sent_followup WHERE LOWER(TRIM(followup_status))='pending'";
  if ($rs = $conn->query($q2)) $count += (int)($rs->fetch_assoc()['c'] ?? 0);

  return ['count' => $count];
}

/**
 * Latest letters (mixed received/sent)
 * Returns: [{kind:'received'|'sent', id, company_name, date}]
 */
function latest_letters(int $limit = 8) : array {
  global $conn;
  $limit = max(1, min(50, $limit));

  $sql = "
    SELECT 'received' AS kind, lr.letter_received_id AS id,
           COALESCE(c.company_name,'Unknown') AS company_name,
           lr.received_date AS d
    FROM letters_received lr
    LEFT JOIN clients c ON c.client_id = lr.client_id
    UNION ALL
    SELECT 'sent'     AS kind, ls.letter_sent_id AS id,
           COALESCE(c.company_name,'Unknown') AS company_name,
           ls.sent_date AS d
    FROM letters_sent ls
    LEFT JOIN clients c ON c.client_id = ls.client_id
    ORDER BY d DESC
    LIMIT {$limit}
  ";

  $out = [];
  if ($rs = $conn->query($sql)) {
    while ($r = $rs->fetch_assoc()) {
      $out[] = [
        'kind'         => $r['kind'],
        'id'           => $r['id'],
        'company_name' => $r['company_name'],
        'date'         => $r['d']
      ];
    }
  }
  return $out;
}

/**
 * Fuzzy search a client by name.
 * Returns: [{client_id, company_name}]
 */
function find_client_by_name(string $query) : array {
  global $conn;
  $query = trim($query);
  if ($query === '') return [];

  $sql = "SELECT client_id, company_name
          FROM clients
          WHERE company_name LIKE CONCAT('%', ?, '%')
          ORDER BY company_name ASC
          LIMIT 20";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $query);
  $stmt->execute();
  $res = $stmt->get_result();

  $out = [];
  while ($r = $res->fetch_assoc()) {
    $out[] = $r;
  }
  $stmt->close();
  return $out;
}
