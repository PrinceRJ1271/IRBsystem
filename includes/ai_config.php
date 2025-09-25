<?php
// includes/ai_config.php
// Central place to read OpenAI creds safely from /var/www/html/.env (or env vars)

if (!function_exists('ai_config')) {

  function ai_config(): array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $env = [];

    // --- Try to read .env from common locations (do not error if missing) ---
    $candidates = [
      realpath(__DIR__ . '/../.env'),            // project root if repo is at document root
      '/var/www/html/.env',                      // typical Apache/Nginx docroot
      getenv('APP_DOTENV_PATH') ?: null,         // optional custom hint
    ];

    foreach ($candidates as $path) {
      if (!$path || !is_readable($path)) continue;
      $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if (!$lines) continue;

      foreach ($lines as $line) {
        // skip comments
        if (preg_match('/^\s*#/', $line)) continue;
        if (!preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/i', $line, $m)) continue;

        $key = strtoupper(trim($m[1]));
        $val = trim($m[2]);
        // strip surrounding quotes if present
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
          $val = substr($val, 1, -1);
        }
        $env[$key] = $val;

        // also expose to process env (won't override existing)
        if (getenv($key) === false) {
          putenv("$key=$val");
        }
      }
      // first readable .env wins
      break;
    }

    // --- Collect values (env vars take priority over parsed .env) ---
    $apiKey    = getenv('OPENAI_API_KEY') ?: ($env['OPENAI_API_KEY'] ?? '');
    $projectId = getenv('OPENAI_PROJECT_ID') ?: ($env['OPENAI_PROJECT_ID'] ?? '');
    // A small, cheap, chat-completions-capable model:
    $model     = getenv('OPENAI_MODEL') ?: ($env['OPENAI_MODEL'] ?? 'gpt-4o-mini');

    $cached = [
      'api_key'    => $apiKey,
      'project_id' => $projectId,
      'model'      => $model,
    ];
    return $cached;
  }
}
