<?php
require_once __DIR__ . '/../../init.php';

$userDataFile = __DIR__ . '/../../database/data/user.json';
$resetDataFile = __DIR__ . '/../../database/data/password_resets.json';
$rateLimitFile = __DIR__ . '/../../database/data/password_reset_rate_limit.json';
$resetLinkLog = __DIR__ . '/../../../logs/password-reset-links.log';

function getResetSecret(): string
{
    $secret = trim((string)getenv('KONIK_PASSWORD_RESET_SECRET'));
    return $secret;
}

function hashResetToken(string $token, string $secret): string
{
    return hash_hmac('sha256', $token, $secret);
}

function loadJsonArray(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return [];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return [];
    }

    return $data;
}

function saveJsonArray(string $path, array $payload): bool
{
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }

    return file_put_contents($path, $encoded, LOCK_EX) !== false;
}

function collectUserEmails(string $userFile): array
{
    $payload = loadJsonArray($userFile);
    $emailToUser = [];

    if (!isset($payload['users']) || !is_array($payload['users'])) {
        return $emailToUser;
    }

    foreach ($payload['users'] as $row) {
        if (!is_array($row)) {
            continue;
        }

        $username = trim((string)($row['username'] ?? ''));
        $email = strtolower(trim((string)($row['email'] ?? '')));
        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }

        if (!isset($emailToUser[$email])) {
            $emailToUser[$email] = $username;
        }
    }

    return $emailToUser;
}

function cleanupRateEntries(array $entries, int $now, int $windowSeconds): array
{
    $clean = [];
    foreach ($entries as $ts) {
        $ts = (int)$ts;
        if ($ts > 0 && ($now - $ts) <= $windowSeconds) {
            $clean[] = $ts;
        }
    }
    return $clean;
}

function buildAbsoluteResetUrl(string $token): string
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $secure ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host . '/admin-panel/password_managment/reset-password.php?token=' . urlencode($token);
}

function appendResetLinkLog(string $logPath, string $email, string $username, string $url): void
{
    $line = sprintf("%s reset requested for user '%s' (%s): %s%s", date('c'), $username, $email, $url, PHP_EOL);
    @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
}

$csrfToken = csrf_token();
$message = '';
$error = '';
$genericMessage = 'If the account exists, a reset link has been sent.';
$resetSecret = getResetSecret();
$resetEnabled = strlen($resetSecret) >= 32;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token.';
    } elseif (!$resetEnabled) {
        $error = 'Password reset is temporarily unavailable.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $now = time();

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = $genericMessage;
        } else {
            $rateData = loadJsonArray($rateLimitFile);
            if (!isset($rateData['entries']) || !is_array($rateData['entries'])) {
                $rateData['entries'] = [];
            }

            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $key = hash('sha256', $ip . '|' . $email);
            $windowSeconds = 15 * 60;
            $maxAttempts = 5;

            $entries = $rateData['entries'][$key] ?? [];
            $entries = cleanupRateEntries(is_array($entries) ? $entries : [], $now, $windowSeconds);

            if (count($entries) < $maxAttempts) {
                $entries[] = $now;
                $rateData['entries'][$key] = $entries;
                saveJsonArray($rateLimitFile, $rateData);

                $emailToUser = collectUserEmails($userDataFile);
                if (isset($emailToUser[$email])) {
                    $username = $emailToUser[$email];
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hashResetToken($token, $resetSecret);
                    $expiresAt = $now + (30 * 60);

                    $resetData = loadJsonArray($resetDataFile);
                    if (!isset($resetData['tokens']) || !is_array($resetData['tokens'])) {
                        $resetData['tokens'] = [];
                    }

                    $cleanTokens = [];
                    foreach ($resetData['tokens'] as $row) {
                        if (!is_array($row)) {
                            continue;
                        }

                        $rowExpires = (int)($row['expires_at'] ?? 0);
                        $rowUsed = (bool)($row['used'] ?? false);
                        if ($rowExpires <= 0) {
                            continue;
                        }
                        if ($rowUsed && ($now - $rowExpires) > (12 * 60 * 60)) {
                            continue;
                        }
                        if (!$rowUsed && $rowExpires < ($now - (2 * 60 * 60))) {
                            continue;
                        }
                        $cleanTokens[] = $row;
                    }

                    $cleanTokens[] = [
                        'username' => $username,
                        'email' => $email,
                        'token_hash' => $tokenHash,
                        'created_at' => $now,
                        'expires_at' => $expiresAt,
                        'used' => false
                    ];

                    $resetData['tokens'] = $cleanTokens;
                    saveJsonArray($resetDataFile, $resetData);

                    $resetUrl = buildAbsoluteResetUrl($token);
                    appendResetLinkLog($resetLinkLog, $email, $username, $resetUrl);

                    $subject = 'Password reset request';
                    $body = "A password reset was requested for your account.\n\n" .
                        "If this was you, open this link within 30 minutes:\n" .
                        $resetUrl . "\n\n" .
                        "If this was not you, ignore this message.";

                    @mail($email, $subject, $body, "Content-Type: text/plain; charset=UTF-8\r\n");
                }
            }

            $message = $genericMessage;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin-panel/password_managment/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Forgot Password</h1>

        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php elseif ($message): ?>
            <p class="success"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="email" name="email" placeholder="Your account email" autocomplete="email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <a class="helper-link" href="../admin-panel/dashboard/admin-panel.php">Back to login</a>
    </div>
</body>
</html>
