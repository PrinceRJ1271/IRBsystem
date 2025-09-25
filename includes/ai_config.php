<?php
// includes/ai_config.php
// Safe server-side config for OpenAI and small helpers

if (!isset($_SESSION)) session_start();

/**
 * Read OpenAI credentials from environment.
 * Make sure you've exported them on the server (your .env or systemd Env):
 *   OPENAI_API_KEY=sk-...
 *   OPENAI_PROJECT_ID=proj_...  (optional)
 */
function ai_get_openai_api_key(): string {
  $k = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');
  if (!$k) { http_response_code(500); die('[AI] Missing OPENAI_API_KEY'); }
  return $k;
}

function ai_get_openai_project_id(): ?string {
  $p = getenv('OPENAI_PROJECT_ID') ?: ($_ENV['OPENAI_PROJECT_ID'] ?? '');
  return $p ?: null;
}

function ai_openai_headers(): array {
  $h = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . ai_get_openai_api_key(),
  ];
  if ($pid = ai_get_openai_project_id()) {
    // Only add if you actually use projects
    $h[] = 'OpenAI-Project: ' . $pid;
  }
  return $h;
}

/** Curl POST helper (no streaming) */
function ai_http_post_json(string $url, array $headers, array $payload): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
  ]);
  $raw = curl_exec($ch);
  if ($raw === false) {
    http_response_code(500);
    die('[AI] cURL error: ' . curl_error($ch));
  }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code >= 300) {
    http_response_code($code);
    die('[AI] OpenAI error: ' . $raw);
  }
  $json = json_decode($raw, true);
  return is_array($json) ? $json : [];
}

/** Small sanitizer (display only) */
function ai_e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
