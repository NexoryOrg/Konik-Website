<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['admin'])) {
    header('Location: /admin-panel/dashboard/admin-panel.php');
    exit;
}

$userDataFile = __DIR__ . '/../../database/data/user.json';

function isHashedPassword($password) {
    return is_string($password) && preg_match('/^\$(2y|argon2)/', $password) === 1;
}

function parseUsersFromFile($file) {
    $users = [];
    if (!file_exists($file)) {
        return $users;
    }

    $userData = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($userData['users']) || !is_array($userData['users'])) {
        return $users;
    }

    foreach ($userData['users'] as $u) {
        if (!empty($u['username']) && isset($u['password'])) {
            $users[(string)$u['username']] = (string)$u['password'];
        }
    }

    return $users;
}

function normalizeUsers($usersArray) {
    $normalized = [];
    foreach ($usersArray as $username => $password) {
        $username = trim((string)$username);
        if ($username === '') {
            continue;
        }

        $stored = (string)$password;
        if (!isHashedPassword($stored)) {
            $stored = password_hash($stored, PASSWORD_DEFAULT);
        }
        $normalized[$username] = $stored;
    }

    return $normalized;
}

function saveUsers($file, $usersArray) {
    $safeUsers = normalizeUsers($usersArray);
    $payload = ['users' => array_map(fn($k, $v) => ['username' => $k, 'password' => $v], array_keys($safeUsers), $safeUsers)];
    file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function verifyUserPassword($storedPassword, $plainPassword) {
    $storedPassword = (string)$storedPassword;
    if (isHashedPassword($storedPassword)) {
        return password_verify($plainPassword, $storedPassword);
    }
    return hash_equals($storedPassword, $plainPassword);
}

$users = parseUsersFromFile($userDataFile);
if (empty($users)) {
    $users = ['admin' => password_hash('admin', PASSWORD_DEFAULT)];
}
$users = normalizeUsers($users);
saveUsers($userDataFile, $users);

$csrfToken = csrf_token();

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token.';
    }

    $oldPassword = trim($_POST['old_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $currentUser = $_SESSION['admin'];

    if (!$error && (!$oldPassword || !$newPassword || !$confirmPassword)) {
        $error = 'Please fill out all fields.';
    } elseif (!$error && $newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } elseif (!$error && !isset($users[$currentUser])) {
        $error = 'Current account could not be found.';
    } elseif (!$error && !verifyUserPassword($users[$currentUser], $oldPassword)) {
        $error = 'Old password is incorrect.';
    } else {
        $users[$currentUser] = password_hash($newPassword, PASSWORD_DEFAULT);
        saveUsers($userDataFile, $users);
        $message = 'Password changed successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <main class="container">
        <section class="section change-card">
            <h1>Change Password</h1>
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($message): ?>
                <div class="alert success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <label for="old_password">Current Password</label>
                <input type="password" id="old_password" name="old_password" placeholder="Current Password" required>

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="New Password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

                <button type="submit" class="btn">Save Password</button>
            </form>
            <a href="../dashboard/admin-panel.php" class="back-link">Back to Admin Panel</a>
        </section>
    </main>
</body>
</html>
