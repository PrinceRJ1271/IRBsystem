<?php
// ai/data_helpers.php
// Server-side DB helpers used by chat_api.php tools.
// All functions MUST return simple arrays/scalars that can be JSON-encoded.

if (!function_exists('dh_list_companies_needing_followups')) {
  function dh_list_companies_needing_followups(mysqli $conn): array {
    // Aggregate "pending" follow-ups from both sides and group by company
    $sql = "
      SELECT company_name, SUM(pending_count) AS total_pending
      FROM (
        SELECT COALESCE(lr.company_name, 'Unknown') AS company_name, COUNT(*) AS pending_count
        FROM letters_received_followup rf
        JOIN letters_received lr ON lr.letter_received_id = rf.letter_received_id
        WHERE LOWER(TRIM(rf.followup_status)) = 'pending'
        GROUP BY COALESCE(lr.company_name, 'Unknown')
        UNION ALL
        SELECT COALESCE(ls.company_name, 'Unknown') AS company_name, COUNT(*) AS pending_count
        FROM letters_sent_followup sf
        JOIN letters_sent ls ON ls.letter_sent_id = sf.letter_sent_id
        WHERE LOWER(TRIM(sf.followup_status)) = 'pending'
        GROUP BY COALESCE(ls.company_name, 'Unknown')
      ) x
      GROUP BY company_name
      ORDER BY total_pending DESC, company_name ASC
      LIMIT 50
    ";
    $out = [];
    if ($res = $conn->query($sql)) {
      while ($row = $res->fetch_assoc()) {
        $out[] = [
          'company' => $row['company_name'],
          'pending' => (int)$row['total_pending'],
        ];
      }
      $res->close();
    }
    return $out;
  }
}

if (!function_exists('dh_pending_followups_count')) {
  function dh_pending_followups_count(mysqli $conn): array {
    $sql = "
      SELECT
        (SELECT COUNT(*) FROM letters_received_followup WHERE LOWER(TRIM(followup_status))='pending') +
        (SELECT COUNT(*) FROM letters_sent_followup     WHERE LOWER(TRIM(followup_status))='pending') AS c
    ";
    $c = 0;
    if ($res = $conn->query($sql)) {
      $row = $res->fetch_assoc();
      $c = (int)($row['c'] ?? 0);
      $res->close();
    }
    return ['pending_followups' => $c];
  }
}

if (!function_exists('dh_latest_letters')) {
  function dh_latest_letters(mysqli $conn, int $count = 8): array {
    if ($count < 1)  $count = 1;
    if ($count > 50) $count = 50;

    $sql = "
      SELECT * FROM (
        SELECT 'Received' AS typ, lr.letter_received_id AS id, lr.company_name, lr.received_date AS d
        FROM letters_received lr
        UNION ALL
        SELECT 'Sent' AS typ, ls.letter_sent_id AS id, ls.company_name, ls.sent_date AS d
        FROM letters_sent ls
      ) x
      ORDER BY d DESC
      LIMIT ?
    ";
    $out = [];
    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param('i', $count);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($row = $res->fetch_assoc()) {
        $out[] = [
          'type'    => $row['typ'],
          'id'      => $row['id'],
          'company' => $row['company_name'] ?: 'Unknown',
          'date'    => $row['d'],
        ];
      }
      $stmt->close();
    }
    return $out;
  }
}

if (!function_exists('dh_find_client')) {
  function dh_find_client(mysqli $conn, string $name, int $limit = 5): array {
    $name = trim($name);
    if ($name === '') return [];

    $sql = "SELECT client_id, company_name, created_at
            FROM clients
            WHERE company_name LIKE CONCAT('%', ?, '%')
            ORDER BY company_name ASC
            LIMIT ?";
    $out = [];
    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param('si', $name, $limit);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($row = $res->fetch_assoc()) {
        $out[] = [
          'client_id'    => $row['client_id'],
          'company_name' => $row['company_name'] ?: 'Unknown',
          'created_at'   => $row['created_at'] ?? null,
        ];
      }
      $stmt->close();
    }
    return $out;
  }
}
