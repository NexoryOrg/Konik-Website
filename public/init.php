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
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function safe_src($s) {
    $s = trim((string)$s);
    if ($s === '') return 'datenbank/bilder/error.jpg';
    if (preg_match('#^\s*(?:[a-z0-9]+:|//)#i', $s)) return 'datenbank/bilder/error.jpg';
    $s = str_replace(['..\\','../','..'], '', $s);
    return e($s);
}

?>
