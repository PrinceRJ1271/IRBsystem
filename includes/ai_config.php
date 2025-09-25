<?php
// includes/ai_config.php
// Minimal config loader (no Composer dependency). Reads OPENAI_API_KEY from env or /var/www/html/.env

if (!isset($_SESSION)) session_start();

/**
 * Very small .env parser: KEY=VALUE lines, no quotes, ignores comments.
 */
function load_env_file($path) {
  if (!is_readable($path)) return [];
  $vars = [];
  foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(ltrim($line), '#') === 0) continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $k = trim(substr($line, 0, $pos));
    $v = trim(substr($line, $pos + 1));
    $vars[$k] = $v;
  }
  return $vars;
}

$ENV = array_merge(load_env_file(__DIR__ . '/../.env'), $_ENV, $_SERVER);

define('OPENAI_API_KEY', $ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_PROJECT_ID', $ENV['OPENAI_PROJECT_ID'] ?? getenv('OPENAI_PROJECT_ID') ?: ''); // optional
define('OPENAI_API_BASE', 'https://api.openai.com/v1');

// Choose a small, fast model that’s good for chat UX
define('OPENAI_MODEL', 'gpt-4o-mini'); // can change later without touching UI

// House style/system instruction the bot will use for better answers
define('AI_SYSTEM_PROMPT', <<<PROMPT
You are the helpful assistant for a private IRB Letter Management System (StarAdmin2 UI).
Be concise, accurate, and friendly. If asked about internal data, be cautious: you
can explain how to find or compute it in the app, but you do not guess unknown facts.
Default to brief answers with bullet points; expand only if asked.
PROMPT);

if (OPENAI_API_KEY === '') {
  // Fail loudly in dev, silent in production if you prefer
  error_log('OPENAI_API_KEY is not set. Create /var/www/html/.env and add it there.');
}
