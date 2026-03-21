<?php
require_once __DIR__ . '/../../init.php';

$userDataFile = __DIR__ . '/../../database/data/user.json';
$resetDataFile = __DIR__ . '/../../database/data/password_resets.json';

function getResetSecret(): string
{
    return trim((string)getenv('KONIK_PASSWORD_RESET_SECRET'));
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

function isHashedPassword($password): bool
{
    return is_string($password) && preg_match('/^\$(2y|argon2)/', $password) === 1;
}

function normalizePassword(string $password): string
{
    if (isHashedPassword($password)) {
        return $password;
    }

    return password_hash($password, PASSWORD_DEFAULT);
}

$csrfToken = csrf_token();
$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$error = '';
$success = '';
$resetSecret = getResetSecret();
$resetEnabled = strlen($resetSecret) >= 32;

if (!$resetEnabled) {
    $error = 'Password reset is temporarily unavailable.';
}

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    if ($error === '') {
        $error = 'This reset link is invalid or expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token.';
    } else {
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($newPassword === '' || $confirmPassword === '') {
            $error = 'Please fill out all fields.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation do not match.';
        } elseif (strlen($newPassword) < 12) {
            $error = 'Password must be at least 12 characters long.';
        } else {
            $now = time();
            $tokenHash = hashResetToken($token, $resetSecret);
            $resetData = loadJsonArray($resetDataFile);
            if (!isset($resetData['tokens']) || !is_array($resetData['tokens'])) {
                $resetData['tokens'] = [];
            }

            $matchedIndex = null;
            $matchedRow = null;
            foreach ($resetData['tokens'] as $idx => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $rowHash = (string)($row['token_hash'] ?? '');
                $rowUsed = (bool)($row['used'] ?? false);
                $rowExp = (int)($row['expires_at'] ?? 0);

                if ($rowHash === '' || $rowUsed || $rowExp < $now) {
                    continue;
                }

                if (hash_equals($rowHash, $tokenHash)) {
                    $matchedIndex = $idx;
                    $matchedRow = $row;
                    break;
                }
            }

            if ($matchedIndex === null || !is_array($matchedRow)) {
                $error = 'This reset link is invalid or expired.';
            } else {
                $username = trim((string)($matchedRow['username'] ?? ''));
                if ($username === '') {
                    $error = 'This reset link is invalid or expired.';
                } else {
                    $userPayload = loadJsonArray($userDataFile);
                    if (!isset($userPayload['users']) || !is_array($userPayload['users'])) {
                        $userPayload['users'] = [];
                    }

                    $updated = false;
                    foreach ($userPayload['users'] as &$entry) {
                        if (!is_array($entry)) {
                            continue;
                        }

                        if (($entry['username'] ?? '') !== $username) {
                            continue;
                        }

                        $entry['password'] = normalizePassword($newPassword);
                        $updated = true;
                        break;
                    }
                    unset($entry);

                    if (!$updated) {
                        $error = 'This reset link is invalid or expired.';
                    } else {
                        if (!saveJsonArray($userDataFile, $userPayload)) {
                            $error = 'Could not update password. Please try again later.';
                        } else {
                            foreach ($resetData['tokens'] as &$tokenRow) {
                                if (!is_array($tokenRow)) {
                                    continue;
                                }

                                if (($tokenRow['username'] ?? '') === $username) {
                                    $tokenRow['used'] = true;
                                    $tokenRow['used_at'] = $now;
                                }
                            }
                            unset($tokenRow);

                            saveJsonArray($resetDataFile, $resetData);
                            $success = 'Password updated successfully. You can now log in.';
                        }
                    }
                }
            }
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
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin-panel/password_managment/login.css">
</head>
<body>
    <div class="login-box">
        <h1>Reset Password</h1>

        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?= e($success) ?></p>
        <?php endif; ?>

        <?php if (!$success && $error !== 'This reset link is invalid or expired.'): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <input type="password" name="new_password" placeholder="New password" autocomplete="new-password" required>
                <input type="password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password" required>
                <button type="submit">Update Password</button>
            </form>
        <?php endif; ?>

        <a class="helper-link" href="../admin-panel/dashboard/admin-panel.php">Back to login</a>
    </div>
</body>
</html>
