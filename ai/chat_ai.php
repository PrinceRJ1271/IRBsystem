<?php
// ai/chat_api.php
header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../includes/ai_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

if (OPENAI_API_KEY === '') {
  http_response_code(500);
  echo json_encode(['error' => 'OpenAI key missing on server.']);
  exit;
}

// Read the body
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?: [];

$userMsg = trim((string)($payload['message'] ?? ''));
$history = $payload['history'] ?? []; // array of {role, content}

if ($userMsg === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Empty message']);
  exit;
}

// Safeguard: trim history to last ~10 messages to keep prompt small
if (is_array($history) && count($history) > 20) {
  $history = array_slice($history, -20);
}

// Build chat messages
$messages = array_merge(
  [['role' => 'system', 'content' => AI_SYSTEM_PROMPT]],
  array_map(function($m){
    return [
      'role'    => in_array($m['role'] ?? '', ['user','assistant','system']) ? $m['role'] : 'user',
      'content' => substr((string)($m['content'] ?? ''), 0, 4000) // sanity limit
    ];
  }, $history),
  [['role' => 'user', 'content' => $userMsg]]
);

// Prepare request
$body = [
  'model'       => OPENAI_MODEL,
  'messages'    => $messages,
  'temperature' => 0.2,
  'max_tokens'  => 700
];

$ch = curl_init(OPENAI_API_BASE . '/chat/completions');
$headers = [
  'Authorization: Bearer ' . OPENAI_API_KEY,
  'Content-Type: application/json'
];

// Optional: bind to a project (helps with usage partitioning)
if (OPENAI_PROJECT_ID !== '') {
  $headers[] = 'OpenAI-Project: ' . OPENAI_PROJECT_ID;
}

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => $headers,
  CURLOPT_POST           => true,
  CURLOPT_TIMEOUT        => 60,
  CURLOPT_POSTFIELDS     => json_encode($body),
]);

$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($res === false) {
  http_response_code(502);
  echo json_encode(['error' => 'OpenAI request failed', 'detail' => $err]);
  exit;
}

$data = json_decode($res, true);
if ($code >= 400 || !isset($data['choices'][0]['message']['content'])) {
  http_response_code(502);
  echo json_encode(['error' => 'OpenAI API error', 'detail' => $data]);
  exit;
}

$reply = $data['choices'][0]['message']['content'];
$usage = $data['usage'] ?? null;

echo json_encode([
  'reply' => $reply,
  'usage' => $usage
]);
