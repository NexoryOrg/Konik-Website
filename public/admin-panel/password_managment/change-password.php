<?php
require_once __DIR__ . '/../../init.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../admin-panel/dashboard/admin-panel.php');
    exit;
}

$userDataFile = __DIR__ . '/../datenbank/data/user.json';
$users = ['admin' => 'admin'];
if (file_exists($userDataFile)) {
    $userData = json_decode(file_get_contents($userDataFile), true);
    if (json_last_error() === JSON_ERROR_NONE && isset($userData['users']) && is_array($userData['users'])) {
        $users = [];
        foreach ($userData['users'] as $u) {
            if (!empty($u['username']) && isset($u['password'])) {
                $users[$u['username']] = $u['password'];
            }
        }
    }
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = trim($_POST['old_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $currentUser = $_SESSION['admin'];

    if (!$oldPassword || !$newPassword || !$confirmPassword) {
        $error = 'Please fill out all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } elseif (!isset($users[$currentUser]) || $users[$currentUser] !== $oldPassword) {
        $error = 'Old password is incorrect.';
    } else {
        $users[$currentUser] = $newPassword;
        $payload = ['users' => array_map(fn($k, $v) => ['username' => $k, 'password' => $v], array_keys($users), $users)];
        file_put_contents($userDataFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
