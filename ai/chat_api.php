<?php
// ai/chat_api.php
// POST JSON: { message: string, history?: [{role:"user"|"assistant", content:string}] }
// RETURNS: { ok: true, reply: string } | { ok:false, error:string }

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid JSON body']);
  exit;
}

$message = trim((string)($body['message'] ?? ''));
$history = $body['history'] ?? [];
if ($message === '' && empty($history)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing message']);
  exit;
}

// --- Load config & DB helpers ---
require_once __DIR__ . '/../includes/ai_config.php';  // reads env + picks model/key
require_once __DIR__ . '/data_helpers.php';            // DB functions

$cfg     = ai_config();
$apiKey  = $cfg['api_key'];
$project = $cfg['project_id'];
$model   = $cfg['model'];        // keep it to gpt-4o-mini (or your choice)

// --- Build chat messages for the Chat Completions API ---
$messages = [[
  'role'    => 'system',
  'content' =>
    "You are the IRB System assistant embedded in a StarAdmin2 dashboard.\n".
    "You can call tools to query the IRB database. Always prefer concise, factual answers.\n".
    "If a user asks for lists, return a short summary followed by a compact bullet list.\n".
    "If a user asks for a date range you cannot access, say what range you used.\n".
    "If the tool returns zero rows, say so politely."
]];

if (is_array($history)) {
  foreach ($history as $m) {
    if (!isset($m['role'], $m['content'])) continue;
    $r = strtolower($m['role']);
    if ($r === 'user' || $r === 'assistant') {
      $messages[] = ['role' => $r, 'content' => (string)$m['content']];
    }
  }
}
$messages[] = ['role' => 'user', 'content' => $message];

// --- Define tool schemas for the model ---
$tools = [
  [
    'type' => 'function',
    'function' => [
      'name' => 'list_companies_needing_followups',
      'description' => 'Return companies that currently have pending follow-ups (received or sent).',
      'parameters' => [
        'type' => 'object',
        'properties' => [],
        'required' => []
      ]
    ]
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'get_pending_followups_count',
      'description' => 'Return a single number: total pending follow-ups (received + sent).',
      'parameters' => [
        'type' => 'object',
        'properties' => [],
        'required' => []
      ]
    ]
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'latest_letters',
      'description' => 'Return latest letters (received and/or sent) with company and date.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'limit' => ['type' => 'integer', 'description' => 'How many rows to return (1-50).']
        ],
        'required' => ['limit']
      ]
    ]
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'find_client_by_name',
      'description' => 'Fuzzy search client by company name and return basic info.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'query' => ['type' => 'string', 'description' => 'Company name search string']
        ],
        'required' => ['query']
      ]
    ]
  ]
];

// --- Call OpenAI (step 1): let model decide to call tools ---
$payload = [
  'model'       => $model,
  'messages'    => $messages,
  'temperature' => 0.2,
  'tools'       => $tools,
  'tool_choice' => 'auto',
];

$resp = openai_post('https://api.openai.com/v1/chat/completions', $payload, $apiKey, $project);
if (!$resp['ok']) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>$resp['error']]);
  exit;
}

$choice = $resp['data']['choices'][0] ?? null;
if (!$choice) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>'No completion returned']);
  exit;
}

// If the model directly replied, just return it.
if (empty($choice['message']['tool_calls'])) {
  $reply = trim((string)($choice['message']['content'] ?? ''));
  if ($reply === '') $reply = 'Sorry — I could not generate a response.';
  echo json_encode(['ok'=>true,'reply'=>$reply]);
  exit;
}

// --- Tool calls flow ---
$tool_messages = [];
foreach ($choice['message']['tool_calls'] as $call) {
  $fn   = $call['function']['name'] ?? '';
  $args = $call['function']['arguments'] ?? '{}';

  $decoded = json_decode($args, true);
  if (!is_array($decoded)) $decoded = [];

  $result = null;

  try {
    switch ($fn) {
      case 'list_companies_needing_followups':
        $result = list_companies_needing_followups();
        break;
      case 'get_pending_followups_count':
        $result = get_pending_followups_count();
        break;
      case 'latest_letters':
        $limit = isset($decoded['limit']) ? (int)$decoded['limit'] : 8;
        if ($limit < 1)  $limit = 1;
        if ($limit > 50) $limit = 50;
        $result = latest_letters($limit);
        break;
      case 'find_client_by_name':
        $q = (string)($decoded['query'] ?? '');
        $result = find_client_by_name($q);
        break;
      default:
        $result = ['error' => 'Unknown tool: '.$fn];
    }
  } catch (Throwable $e) {
    $result = ['error' => 'Tool failed: '.$e->getMessage()];
  }

  // Add the tool result back to the conversation
  $tool_messages[] = [
    'role'         => 'tool',
    'tool_call_id' => $call['id'],
    'name'         => $fn,
    'content'      => json_encode($result, JSON_UNESCAPED_UNICODE)
  ];
}

// --- Call OpenAI (step 2): ask the model to compose the final answer using tool outputs ---
$followup = array_merge(
  $messages,
  [ $choice['message'] ],   // the assistant message that requested tools
  $tool_messages
);

$payload2 = [
  'model'       => $model,
  'messages'    => $followup,
  'temperature' => 0.2
];

$resp2 = openai_post('https://api.openai.com/v1/chat/completions', $payload2, $apiKey, $project);
if (!$resp2['ok']) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>$resp2['error']]);
  exit;
}

$reply = trim((string)($resp2['data']['choices'][0]['message']['content'] ?? ''));
if ($reply === '') $reply = 'Sorry — I could not generate a response.';
echo json_encode(['ok'=>true,'reply'=>$reply]);
exit;


/* ---------- helpers ---------- */

function openai_post(string $url, array $payload, string $apiKey, ?string $project) : array {
  $ch = curl_init($url);
  $headers = [
    'Content-Type: application/json',
    'Authorization: Bearer '.$apiKey,
  ];
  if ($project) $headers[] = 'OpenAI-Project: '.$project;

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 40,
  ]);

  $raw = curl_exec($ch);
  $err = curl_error($ch);
  $http= curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($err) return ['ok'=>false,'error'=>"cURL: $err"];
  $data = json_decode($raw, true);
  if ($http < 200 || $http >= 300) {
    $detail = $data['error']['message'] ?? "HTTP $http";
    return ['ok'=>false,'error'=>"OpenAI error: $detail"];
  }
  return ['ok'=>true,'data'=>$data];
}
