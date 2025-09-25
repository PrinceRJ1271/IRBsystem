<?php
// ai/chat_api.php
// Receives {message:string, history?:[{role,content}], meta?:{…}} and returns {ok, reply}
// – Adds tool calling so the assistant can run server-side queries safely.

require_once __DIR__ . '/../includes/ai_config.php';
require_once __DIR__ . '/../config/db.php';          // gives $conn (mysqli)
require_once __DIR__ . '/data_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);

if (!is_array($in)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
  exit;
}

$message = trim((string)($in['message'] ?? ''));
$history = $in['history'] ?? [];

if ($message === '' && empty($history)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Empty message']);
  exit;
}

// ---- Model / credentials
$cfg     = ai_config();
$apiKey  = $cfg['api_key'];
$model   = $cfg['model'] ?: 'gpt-4o-mini';
$project = $cfg['project_id'] ?? '';

// ---- System prompt with high-level schema (tables/columns)
$system = <<<SYS
You are the AI assistant for the IRB Letter System (StarAdmin2 dashboard).
You can answer from the database via tools and summarise for the user.

Data model (simplified):
- clients(client_id, company_name, ...)
- letters_received(letter_received_id, client_id, received_date, follow_up_required, status, ...)
- letters_received_followup(letter_received_id, followup_status, ...)
- letters_sent(letter_sent_id, client_id, sent_date, follow_up_required, status, ...)
- letters_sent_followup(letter_sent_id, followup_status, ...)

General rules:
- Prefer concise answers with small tables or bullet points.
- If a tool returns arrays of rows, list company_name and relevant IDs/dates.
- If no results: say so clearly.
- Never guess values; only summarise tool results.
SYS;

// ---- Convert chat history to OpenAI format
$messages = [['role' => 'system', 'content' => $system]];
if (is_array($history)) {
  foreach ($history as $m) {
    if (!isset($m['role'], $m['content'])) continue;
    $r = strtolower($m['role']);
    if (!in_array($r, ['user', 'assistant'], true)) continue;
    $messages[] = ['role' => $r, 'content' => (string)$m['content']];
  }
}
$messages[] = ['role' => 'user', 'content' => $message];

// ---- Tool (function) definitions – NOTE: parameters are valid JSON Schemas (type=object)
$tools = [
  [
    'type' => 'function',
    'function' => [
      'name' => 'list_companies_needing_followups',
      'description' => 'Return companies with letters that require follow-ups (both Sent and Received) that are still pending.',
      'parameters' => [ 'type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'count_pending_followups',
      'description' => 'Return counts of pending follow-ups for Sent and Received letters.',
      'parameters' => [ 'type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'latest_letters',
      'description' => 'Fetch latest letters (both types) with company name and dates.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 8],
        ],
        'required' => [],
        'additionalProperties' => false,
      ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'find_client_by_name',
      'description' => 'Find matching clients by partial company name.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'query' => ['type' => 'string', 'description' => 'Partial or full company name'],
          'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 10],
        ],
        'required' => ['query'],
        'additionalProperties' => false,
      ],
    ],
  ],
];

// ---- Helper: call OpenAI
function call_openai($apiKey, $project, $model, $messages, $tools = null) {
  $payload = [
    'model'    => $model,
    'messages' => $messages,
    'temperature' => 0.2,
  ];
  if ($tools) {
    $payload['tools'] = $tools;
    $payload['tool_choice'] = 'auto';
  }

  $ch = curl_init('https://api.openai.com/v1/chat/completions');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => array_filter([
      'Content-Type: application/json',
      'Authorization: Bearer ' . $apiKey,
      $project ? 'OpenAI-Project: ' . $project : null,
    ]),
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 45,
  ]);
  $resp = curl_exec($ch);
  $err  = curl_error($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($err) return [null, "cURL error: $err"];
  if ($http < 200 || $http >= 300) {
    $body = json_decode($resp, true);
    $detail = $body['error']['message'] ?? "HTTP $http";
    return [null, "OpenAI error: $detail"];
  }
  return [json_decode($resp, true), null];
}

// ---- First call (let the model decide if it needs tools)
list($first, $err) = call_openai($apiKey, $project, $model, $messages, $tools);
if ($err) {
  http_response_code(502);
  echo json_encode(['ok' => false, 'error' => $err]);
  exit;
}

$choice = $first['choices'][0]['message'] ?? [];
$toolCalls = $choice['tool_calls'] ?? [];

// If tools are requested, execute them and re-ask the model with tool results.
$maxToolHops = 3;
while (!empty($toolCalls) && $maxToolHops-- > 0) {
  $messages[] = [
    'role' => 'assistant',
    'content' => $choice['content'] ?? '',
    'tool_calls' => $toolCalls,
  ];

  foreach ($toolCalls as $tc) {
    $name = $tc['function']['name'] ?? '';
    $args = $tc['function']['arguments'] ?? '{}';
    $args = json_decode($args, true) ?: [];

    try {
      switch ($name) {
        case 'list_companies_needing_followups':
          $data = dh_list_companies_needing_followups($conn);
          break;

        case 'count_pending_followups':
          $data = dh_count_pending_followups($conn);
          break;

        case 'latest_letters':
          $limit = isset($args['limit']) ? (int)$args['limit'] : 8;
          $data  = dh_latest_letters($conn, $limit);
          break;

        case 'find_client_by_name':
          $q     = (string)($args['query'] ?? '');
          $limit = isset($args['limit']) ? (int)$args['limit'] : 10;
          $data  = dh_find_client_by_name($conn, $q, $limit);
          break;

        default:
          $data = ['error' => "Unknown tool: $name"];
      }
    } catch (Throwable $e) {
      $data = ['error' => $e->getMessage()];
    }

    // Append tool result for the model
    $messages[] = [
      'role' => 'tool',
      'tool_call_id' => $tc['id'],
      'name' => $name,
      'content' => json_encode(['result' => $data], JSON_UNESCAPED_UNICODE),
    ];
  }

  // Ask the model again, now that it has the tool outputs
  list($again, $err) = call_openai($apiKey, $project, $model, $messages);
  if ($err) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $err]);
    exit;
  }
  $choice    = $again['choices'][0]['message'] ?? [];
  $toolCalls = $choice['tool_calls'] ?? [];
}

// Final assistant message (either from first call or the follow-up)
$final = trim((string)($choice['content'] ?? ''));
if ($final === '') $final = "Sorry — I couldn't generate a reply.";

echo json_encode(['ok' => true, 'reply' => $final]);
