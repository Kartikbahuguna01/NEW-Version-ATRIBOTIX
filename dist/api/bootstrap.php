<?php
declare(strict_types=1);
/* bootstrap.php — shared helpers for the form endpoints (CSRF, rate limit, validation, storage). */

/* ---- configuration (config.php lives outside the public folder; see config/config.sample.php) ---- */
$configPath = getenv('ATRIBOTIX_CONFIG') ?: dirname(__DIR__, 2) . '/atribotix-config/config.php';
$CONFIG = is_file($configPath) ? require $configPath : require dirname(__DIR__) . '/config/config.sample.php';

ini_set('display_errors', '0');                       // never show PHP errors to visitors
error_reporting(E_ALL);

/* ---- secure session ---- */
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('atribotix_sid');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

/* ---- responses ---- */
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---- CSRF ---- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---- same-site check: Origin or Referer must be our own host ---- */
function same_site_request(string $siteHost): bool
{
    $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if ($source === '') { return false; }
    $host = parse_url($source, PHP_URL_HOST);
    $siteHost = strtolower($siteHost);
    $host = strtolower((string) $host);
    return $host === $siteHost || $host === 'www.' . $siteHost || $host === 'localhost' || $host === '127.0.0.1';
}

/* ---- storage (outside the web root) ---- */
function storage_path(string $sub = ''): string
{
    global $CONFIG;
    $dir = rtrim($CONFIG['storage_dir'], '/') . ($sub !== '' ? '/' . $sub : '');
    if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    return $dir;
}

/* ---- rate limit: $max attempts per $window seconds per visitor IP ---- */
function rate_limited(string $bucket, int $max, int $window): bool
{
    $file = storage_path('ratelimit') . '/' . hash('sha256', $bucket . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) . '.json';
    $now = time();
    $hits = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $hits = array_values(array_filter($hits, fn ($t) => $t > $now - $window));
    if (count($hits) >= $max) { return true; }
    $hits[] = $now;
    file_put_contents($file, json_encode($hits), LOCK_EX);
    return false;
}

/* ---- input cleaning + validation ---- */
function clean_line(?string $value, int $max): string
{
    $value = trim((string) $value);
    $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';     // strips newlines too (header injection)
    return mb_substr($value, 0, $max);
}
function clean_text(?string $value, int $max): string
{
    $value = trim((string) $value);
    $value = preg_replace('/[^\P{C}\n]+/u', '', $value) ?? '';           // remove control chars, keep newlines
    return mb_substr($value, 0, $max);
}
function validate_common(array $input): array
{
    $errors = [];
    if (mb_strlen($input['name']) < 2) { $errors['name'] = 'Please enter your name.'; }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Please enter a valid email address.'; }
    if (!preg_match('/^\+?[0-9][0-9\s\-]{6,14}$/', $input['mobile'])) { $errors['mobile'] = 'Please enter a valid mobile number.'; }
    return $errors;
}

/* ---- save + notify ---- */
function save_submission(string $type, array $data): void
{
    $line = json_encode(['time' => gmdate('c'), 'type' => $type] + $data, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents(storage_path() . '/submissions.log', $line, FILE_APPEND | LOCK_EX);
}
function notify(string $subject, string $body, string $replyTo): bool
{
    global $CONFIG;
    $headers = [
        'From: AtriBotix Website <' . $CONFIG['mail_from'] . '>',
        'Reply-To: ' . $replyTo,                       // already validated as an email address
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    return @mail($CONFIG['mail_to'], '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}

/* ---- common request gate used by both form endpoints ---- */
function guard_post(string $bucket): void
{
    global $CONFIG;
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); json_response(['ok' => false, 'message' => 'Method not allowed.'], 405); }
    if (!same_site_request($CONFIG['site_host'])) { json_response(['ok' => false, 'message' => 'Request not allowed.'], 403); }
    if (!csrf_valid($_POST['csrf_token'] ?? null)) { json_response(['ok' => false, 'message' => 'Your session expired. Please try again.'], 403); }
    if (rate_limited($bucket, 5, 600)) { json_response(['ok' => false, 'message' => 'Too many attempts. Please wait a few minutes and try again.'], 429); }
    if (!empty($_POST['website'] ?? '')) {                                  // honeypot filled in -> pretend success
        json_response(['ok' => true, 'redirect' => 'thank-you.php']);
    }
}
