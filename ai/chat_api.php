<?php
// ai/chat_api.php
// Receives {message:string, history?:[{role,content}], meta?:{…}} and returns {ok, reply}
// Adds OpenAI "tools" (function calling) that read the IRB database server-side.

require_once __DIR__ . '/../includes/ai_config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/data_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

// ---- Read JSON body safely ----
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
  exit;
}

$message = trim((string)($data['message'] ?? ''));
$history = $data['history'] ?? [];
if ($message === '' && empty($history)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Empty message']);
  exit;
}

// ---- Base system instruction ----
$messages = [
  [
    'role' => 'system',
    'content' =>
      "You are an AI assistant for the IRB Letter System (StarAdmin2 dashboard). ".
      "You can call server-side tools to query the database and summarize results. ".
      "If a direct answer is unclear, ask a brief clarifying question. ".
      "Be concise and always cite counts, dates, or IDs when helpful.",
  ],
];

// Thread history (only user/assistant)
if (is_array($history)) {
  foreach ($history as $m) {
    if (!isset($m['role'], $m['content'])) continue;
    $r = strtolower($m['role']);
    if (!in_array($r, ['user','assistant'], true)) continue;
    $messages[] = ['role' => $r, 'content' => (string)$m['content']];
  }
}

// Append the latest user message
$messages[] = ['role' => 'user', 'content' => $message];

// ---- OpenAI credentials ----
$cfg     = ai_config();
$apiKey  = $cfg['api_key'];
$model   = $cfg['model'];
$project = $cfg['project_id'];

// ---- Define tools (functions) with correct JSON schemas ----
// NOTE: Even when a function has no parameters, `parameters` MUST be an object.
$tools = [
  [
    'type' => 'function',
    'function' => [
      'name' => 'list_companies_needing_followups',
      'description' => 'Return a grouped list of companies that currently have pending follow-ups, with counts.',
      'parameters' => [
        'type'       => 'object',
        'properties' => [],
        'additionalProperties' => false,
      ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'pending_followups_count',
      'description' => 'Return the total number of pending follow-ups right now.',
      'parameters' => [
        'type'       => 'object',
        'properties' => [],
        'additionalProperties' => false,
      ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'latest_letters',
      'description' => 'List the latest letters (received and sent) with type, id, company, and date.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'count' => [
            'type' => 'integer',
            'description' => 'How many items to return (1-50). Default 8.',
            'minimum' => 1,
            'maximum' => 50,
            'default' => 8,
          ],
        ],
        'additionalProperties' => false,
      ],
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'find_client',
      'description' => 'Search clients by (partial) company name and return top matches.',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'Partial company name to search for.',
            'minLength' => 1,
          ],
          'limit' => [
            'type' => 'integer',
            'description' => 'Max results (1-20). Default 5.',
            'minimum' => 1,
            'maximum' => 20,
            'default' => 5,
          ],
        ],
        'required' => ['name'],
        'additionalProperties' => false,
      ],
    ],
  ],
];

// ---- First call: let the model choose a tool if needed ----
$reqBody = [
  'model'     => $model,
  'messages'  => $messages,
  'tools'     => $tools,
  'tool_choice' => 'auto',
  'temperature' => 0.2,
];

$headers = array_filter([
  'Content-Type: application/json',
  'Authorization: Bearer '.$apiKey,
  $project ? 'OpenAI-Project: '.$project : null,
]);

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => $headers,
  CURLOPT_POSTFIELDS     => json_encode($reqBody),
  CURLOPT_TIMEOUT        => 45,
]);

$resp = curl_exec($ch);
$err  = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>"cURL error: $err"]);
  exit;
}
if ($http < 200 || $http >= 300) {
  $body = json_decode($resp, true);
  $detail = $body['error']['message'] ?? "HTTP $http";
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>"OpenAI error: $detail"]);
  exit;
}

$body = json_decode($resp, true);
$msg  = $body['choices'][0]['message'] ?? null;

// If the model called tools, execute them and send a follow-up call
if (!empty($msg['tool_calls'])) {
  $messages[] = [
    'role' => $msg['role'] ?? 'assistant',
    'content' => $msg['content'] ?? '',
    'tool_calls' => $msg['tool_calls'],
  ];

  foreach ($msg['tool_calls'] as $toolCall) {
    $fnName = $toolCall['function']['name'] ?? '';
    $args   = $toolCall['function']['arguments'] ?? '{}';
    $args   = json_decode($args, true) ?: [];

    try {
      switch ($fnName) {
        case 'list_companies_needing_followups':
          $result = dh_list_companies_needing_followups($conn);
          break;

        case 'pending_followups_count':
          $result = dh_pending_followups_count($conn);
          break;

        case 'latest_letters':
          $count  = isset($args['count']) ? (int)$args['count'] : 8;
          if ($count < 1) $count = 1;
          if ($count > 50) $count = 50;
          $result = dh_latest_letters($conn, $count);
          break;

        case 'find_client':
          $name  = (string)($args['name'] ?? '');
          $limit = isset($args['limit']) ? (int)$args['limit'] : 5;
          if ($limit < 1) $limit = 1;
          if ($limit > 20) $limit = 20;
          $result = dh_find_client($conn, $name, $limit);
          break;

        default:
          $result = ['error' => "Unknown function: $fnName"];
      }
    } catch (Throwable $e) {
      $result = ['error' => 'Server error: '.$e->getMessage()];
    }

    // Append tool result back to the conversation
    $messages[] = [
      'role' => 'tool',
      'tool_call_id' => $toolCall['id'],
      'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
    ];
  }

  // Second call: let the model compose the final answer using tool output
  $reqBody2 = [
    'model'       => $model,
    'messages'    => $messages,
    'temperature' => 0.2,
  ];

  $ch2 = curl_init('https://api.openai.com/v1/chat/completions');
  curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => json_encode($reqBody2),
    CURLOPT_TIMEOUT        => 45,
  ]);
  $resp2 = curl_exec($ch2);
  $err2  = curl_error($ch2);
  $http2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
  curl_close($ch2);

  if ($err2) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>"cURL error: $err2"]);
    exit;
  }
  if ($http2 < 200 || $http2 >= 300) {
    $body2 = json_decode($resp2, true);
    $detail2 = $body2['error']['message'] ?? "HTTP $http2";
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>"OpenAI error: $detail2"]);
    exit;
  }

  $body2 = json_decode($resp2, true);
  $reply = $body2['choices'][0]['message']['content'] ?? null;

} else {
  // No tool calls; just use the model's direct reply
  $reply = $msg['content'] ?? null;
}

if (!$reply) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>'No completion returned']);
  exit;
}

echo json_encode(['ok' => true, 'reply' => $reply]);
