<?php
// ai/chat_api.php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/ai_config.php';
require_once __DIR__ . '/data_helpers.php';

/** Read incoming JSON */
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$userMessages = $in['messages'] ?? [];
if (!is_array($userMessages) || empty($userMessages)) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing messages']); exit;
}

/** Build messages with a good system prompt */
$messages = [
  [
    'role' => 'system',
    'content' =>
      "You are IRB System Assistant. Be helpful, concise, and friendly.\n" .
      "You can answer using live data by calling tools. When the user asks " .
      "about counts, KPIs, follow-ups, letters, clients, etc., prefer to " .
      "call the most relevant tool first. Show small tables/bullets when clear."
  ],
];

foreach ($userMessages as $m) {
  // accept only role/content
  if (!isset($m['role'],$m['content'])) continue;
  $messages[] = ['role'=>$m['role'],'content'=>substr((string)$m['content'],0,8000)];
}

/** Define our tools */
$tools = [
  [
    'type' => 'function',
    'function' => [
      'name' => 'get_pending_followups',
      'description' => 'Get current pending follow-ups; choose received/sent/all',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'type' => [
            'type' => 'string',
            'enum' => ['received','sent','all'],
            'description' => 'Which pool to summarize'
          ]
        ],
        'required' => ['type']
      ]
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'get_recent_letters',
      'description' => 'Latest received/sent letters merged, newest first',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'limit' => ['type'=>'integer','description'=>'Max items (<=25)']
        ]
      ]
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'search_clients',
      'description' => 'Lookup clients by company name or client_id',
      'parameters' => [
        'type' => 'object',
        'properties' => [
          'query' => ['type'=>'string','description'=>'Search text'],
          'limit' => ['type'=>'integer','description'=>'Max results (<=25)']
        ],
        'required' => ['query']
      ]
    ],
  ],
  [
    'type' => 'function',
    'function' => [
      'name' => 'get_kpis',
      'description' => 'Return today snapshot of KPIs (total clients, received/sent this month, pending followups)',
      'parameters' => ['type'=>'object','properties'=>[]]
    ],
  ],
];

$model = 'gpt-4o-mini'; // fast + supports tools well

/** Single/dual pass function-calling loop */
$maxPasses = 3;
$lastResponse = null;

for ($i=0; $i<$maxPasses; $i++) {
  $resp = ai_openai_headers();
  $payload = [
    'model' => $model,
    'messages' => $messages,
    'tools' => $tools,
    'tool_choice' => 'auto',
    'temperature' => 0.2
  ];
  $json = ai_http_post_json('https://api.openai.com/v1/chat/completions', $resp, $payload);
  $choice = $json['choices'][0] ?? null;
  if (!$choice) break;

  $msg = $choice['message'] ?? [];
  $toolCalls = $msg['tool_calls'] ?? [];

  if ($toolCalls && is_array($toolCalls)) {
    // Take the first tool call (simplify)
    $tc = $toolCalls[0];
    $fname = $tc['function']['name'] ?? '';
    $fargs = $tc['function']['arguments'] ?? '{}';
    $args  = json_decode($fargs, true) ?: [];

    // Execute
    $toolResult = null;
    try {
      switch ($fname) {
        case 'get_pending_followups':
          $toolResult = ai_db_pending_followups($args['type'] ?? 'all'); break;
        case 'get_recent_letters':
          $toolResult = ai_db_recent_letters((int)($args['limit'] ?? 8)); break;
        case 'search_clients':
          $toolResult = ai_db_search_clients((string)($args['query'] ?? ''), (int)($args['limit'] ?? 10)); break;
        case 'get_kpis':
          $toolResult = ai_db_kpis(); break;
        default:
          $toolResult = ['error'=>'unknown tool'];
      }
    } catch (\Throwable $e) {
      $toolResult = ['error' => 'Tool failed: '.$e->getMessage()];
    }

    // Feed back to the model
    $messages[] = $msg; // assistant w/ tool call
    $messages[] = [
      'role' => 'tool',
      'tool_call_id' => $tc['id'],
      'name' => $fname,
      'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE)
    ];
    // and continue loop for final natural language response
    continue;
  }

  // No tool calls -> final answer
  $lastResponse = $msg['content'] ?? '';
  break;
}

echo json_encode([
  'reply' => $lastResponse ?: "Sorry, I couldn't form a reply.",
]);
