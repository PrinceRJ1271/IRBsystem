<?php
// includes/ai_config.php
// Centralized, safe loader for the OpenAI credentials.

function ai_env($key, $default = null) {
  // 1) try getenv (systemd/apache)
  $val = getenv($key);
  if ($val !== false && $val !== '') return $val;

  // 2) try /var/www/html/.env (your current location)
  static $envCache = null;
  if ($envCache === null) {
    $envCache = [];
    $envPathCandidates = [
      __DIR__ . '/../.env',              // project root
      '/var/www/html/.env',              // common prod path
    ];
    foreach ($envPathCandidates as $p) {
      if (is_readable($p)) {
        $raw = file_get_contents($p);
        // Support KEY=value lines (no quotes required)
        foreach (preg_split("/\\r\\n|\\r|\\n/", $raw) as $line) {
          if (preg_match('/^\s*#/', $line) || trim($line)==='') continue;
          if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $envCache[trim($k)] = trim($v);
          }
        }
        break;
      }
    }
  }
  return $envCache[$key] ?? $default;
}

function ai_config() {
  $key = ai_env('OPENAI_API_KEY');
  $project = ai_env('OPENAI_PROJECT_ID'); // optional

  if (!$key) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
      'ok' => false,
      'error' => 'Missing OPENAI_API_KEY. Set it in /var/www/html/.env or as an env var.',
    ]);
    exit;
  }
  return [
    'api_key'    => $key,
    'project_id' => $project,
    // Choose a lightweight, smart, cheap model
    'model'      => 'gpt-4o-mini',
  ];
}
