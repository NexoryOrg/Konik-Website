<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Permissions-Policy: interest-cohort=()");

if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443) {
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
}

$csp = "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; img-src 'self' data: blob: https:; script-src 'self' https://unpkg.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdnjs.cloudflare.com https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' data: https://fonts.gstatic.com https:; connect-src 'self' https://tile.openstreetmap.org https://*.tile.openstreetmap.org https://unpkg.com https://api.emailjs.com;";
header("Content-Security-Policy: $csp");

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
];

$rawHost = (string)($_SERVER['HTTP_HOST'] ?? '');
$cookieHost = (string)parse_url(($secure ? 'https' : 'http') . '://' . $rawHost, PHP_URL_HOST);

if ($cookieHost !== '' && strpos($cookieHost, ':') === false && filter_var($cookieHost, FILTER_VALIDATE_IP) === false && substr_count($cookieHost, '.') >= 1) {
    $cookieParams['domain'] = $cookieHost;
}

session_set_cookie_params($cookieParams);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

require_once __DIR__ . '/language.php';
bootstrap_language();

if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $fallbackBytes = function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(32) : false;
        if ($fallbackBytes !== false) {
            $_SESSION['csrf_token'] = bin2hex($fallbackBytes);
        } else {
            $_SESSION['csrf_token'] = hash('sha256', uniqid((string)mt_rand(), true));
        }
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function safe_src($s) {
    $s = trim((string)$s);
    if ($s === '') return 'database/images/error.jpg';
    if (preg_match('#^\s*(?:[a-z0-9]+:|//)#i', $s)) return 'database/images/error.jpg';
    $s = str_replace(['..\\','../','..'], '', $s);
    return e($s);
}

function csrf_token() {
    return (string)($_SESSION['csrf_token'] ?? '');
}

function csrf_validate($token) {
    $token = (string)$token;
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    return $token !== '' && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

?>
