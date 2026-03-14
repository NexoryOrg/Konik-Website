<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: admin-panel.php');
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
        $error = 'Bitte fülle alle Felder aus.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Das neue Passwort und die Bestätigung stimmen nicht überein.';
    } elseif (!isset($users[$currentUser]) || $users[$currentUser] !== $oldPassword) {
        $error = 'Altes Passwort ist falsch.';
    } else {
        $users[$currentUser] = $newPassword;
        $payload = ['users' => array_map(fn($k, $v) => ['username' => $k, 'password' => $v], array_keys($users), $users)];
        file_put_contents($userDataFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = 'Passwort erfolgreich geändert.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort ändern</title>
    <link rel="stylesheet" href="admin-panel.css">
    <style>
        body { padding-top: 90px; }
        .change-card { max-width: 480px; margin: 0 auto; background: #fff; border: 1px solid #dfece1; border-radius: 14px; box-shadow: 0 12px 26px rgba(0,0,0,0.12); padding: 26px; }
        .change-card h1 { color: #2e7d32; margin-bottom: 16px; }
        .alert { margin-bottom: 14px; padding: 12px; border-radius: 8px; }
        .alert.error { background: #ffe6e6; color: #9f1f1f; border: 1px solid #f2a2a2; }
        .alert.success { background: #e6f8e6; color: #1d6a1d; border: 1px solid #8fce8f; }
        .back-link { display: inline-block; margin-top: 16px; color: #2e7d32; font-weight: 600; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="container">
        <section class="section change-card">
            <h1>Passwort ändern</h1>
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($message): ?>
                <div class="alert success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" class="settings-form">
                <input type="password" name="old_password" placeholder="Altes Passwort" required>
                <input type="password" name="new_password" placeholder="Neues Passwort" required>
                <input type="password" name="confirm_password" placeholder="Neues Passwort bestätigen" required>
                <button type="submit" class="btn">Passwort speichern</button>
            </form>
            <a href="admin-panel.php" class="back-link">Zurück zum Admin-Panel</a>
        </section>
    </main>
</body>
</html>