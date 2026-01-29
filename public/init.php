<?php
// Initialisierung: sichere Header, Session-Härtung, Helferfunktionen
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Sicherheitsheader
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Permissions-Policy: interest-cohort=()");

// HSTS nur bei HTTPS
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443) {
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
}

// Content Security Policy (konservativ, bei Bedarf anpassen)
$csp = "default-src 'self'; img-src 'self' data:; script-src 'self' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' https://cdnjs.cloudflare.com https://unpkg.com 'unsafe-inline';";
header("Content-Security-Policy: $csp");

// Session-Härtung
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

// Helferfunktionen
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function safe_src($s) {
    $s = trim((string)$s);
    if ($s === '') return 'datenbank/bilder/error.jpg';
    // Verwerfe alle schematischen oder daten-URIs und Protokoll-relative URLs
    if (preg_match('#^\s*(?:[a-z0-9]+:|//)#i', $s)) return 'datenbank/bilder/error.jpg';
    // Entferne Traversal-Teile
    $s = str_replace(['..\\','../','..'], '', $s);
    return e($s);
}

?>
