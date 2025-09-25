<?php
// ai/chat_ai.php
// Receives {message:string, history?:[{role,content}], meta?:{…}} and returns {ok, reply}

require_once __DIR__ . '/../includes/ai_config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

// Read JSON body safely
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

// Build messages: keep last few items to control token usage
$messages = [
  [
    'role' => 'system',
    'content' =>
      "You are an AI assistant for the IRB Letter System (StarAdmin2 dashboard). ".
      "Be concise, helpful and safe. If asked about specific data, guide users ".
      "to the relevant screen/filters; don’t fabricate database values.",
  ],
];

if (is_array($history)) {
  foreach ($history as $m) {
    if (!isset($m['role'], $m['content'])) continue;
    $r = strtolower($m['role']);
    if (!in_array($r, ['user','assistant'], true)) continue;
    $messages[] = ['role' => $r, 'content' => (string)$m['content']];
  }
}
$messages[] = ['role' => 'user', 'content' => $message];

// Load credentials & model
$cfg = ai_config();
$apiKey = $cfg['api_key'];
$model  = $cfg['model'];
$project= $cfg['project_id'];

// Prepare OpenAI request
$payload = [
  'model' => $model,
  'messages' => $messages,
  'temperature' => 0.2,
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => array_filter([
    'Content-Type: application/json',
    'Authorization: Bearer '.$apiKey,
    $project ? 'OpenAI-Project: '.$project : null,
  ]),
  CURLOPT_POSTFIELDS     => json_encode($payload),
  CURLOPT_TIMEOUT        => 30,
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
  // Try to forward OpenAI error
  $body = json_decode($resp, true);
  $detail = $body['error']['message'] ?? "HTTP $http";
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>"OpenAI error: $detail"]);
  exit;
}

$body = json_decode($resp, true);
$reply = $body['choices'][0]['message']['content'] ?? null;
if (!$reply) {
  http_response_code(502);
  echo json_encode(['ok'=>false,'error'=>'No completion returned']);
  exit;
}

echo json_encode([
  'ok'    => true,
  'reply' => $reply,
]);
