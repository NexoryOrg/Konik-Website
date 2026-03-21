<?php
require_once __DIR__ . '/../../init.php';

$userDataFile = __DIR__ . '/../../database/data/user.json';

function loadUserPayload($file) {
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return [];
    }

    return $data;
}

function isHashedPassword($password) {
    return is_string($password) && preg_match('/^\$(2y|argon2)/', $password) === 1;
}

function parseUsersFromFile($file) {
    $users = [];
    $emails = [];

    $data = loadUserPayload($file);
    if (!isset($data['users']) || !is_array($data['users'])) {
        return ['users' => $users, 'emails' => $emails];
    }

    foreach ($data['users'] as $row) {
        if (!empty($row['username']) && isset($row['password'])) {
            $username = trim((string)$row['username']);
            $users[$username] = (string)$row['password'];

            $email = strtolower(trim((string)($row['email'] ?? '')));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$username] = $email;
            }
        }
    }

    return ['users' => $users, 'emails' => $emails];
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

function verifyUserPassword($storedPassword, $plainPassword) {
    $storedPassword = (string)$storedPassword;
    if (isHashedPassword($storedPassword)) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $plainPassword);
}

function saveUsers($file, $usersArray, $emailsArray = []) {
    $safeUsers = normalizeUsers($usersArray);

    $payload = loadUserPayload($file);
    $existingEmails = [];
    if (isset($payload['users']) && is_array($payload['users'])) {
        foreach ($payload['users'] as $row) {
            if (!is_array($row) || empty($row['username'])) {
                continue;
            }
            $username = trim((string)$row['username']);
            $email = strtolower(trim((string)($row['email'] ?? '')));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $existingEmails[$username] = $email;
            }
        }
    }

    foreach ($emailsArray as $username => $email) {
        $username = trim((string)$username);
        $email = strtolower(trim((string)$email));
        if ($username !== '' && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existingEmails[$username] = $email;
        }
    }

    $serializedUsers = [];
    foreach ($safeUsers as $username => $password) {
        $row = ['username' => $username, 'password' => $password];
        if (isset($existingEmails[$username])) {
            $row['email'] = $existingEmails[$username];
        }
        $serializedUsers[] = $row;
    }

    $payload['users'] = $serializedUsers;
    file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function findUsernameByLogin(string $login, array $users, array $emails): string {
    if ($login !== '' && isset($users[$login])) {
        return $login;
    }

    $lookupEmail = strtolower($login);
    foreach ($emails as $username => $email) {
        if (hash_equals($email, $lookupEmail)) {
            return (string)$username;
        }
    }

    return '';
}

$store = parseUsersFromFile($userDataFile);
$users = $store['users'];
$userEmails = $store['emails'];
if (empty($users)) {
    $users = ['admin' => password_hash('admin', PASSWORD_DEFAULT)];
}

$normalizedUsers = normalizeUsers($users);
if ($normalizedUsers !== $users || !file_exists($userDataFile)) {
    saveUsers($userDataFile, $normalizedUsers, $userEmails);
}
$users = $normalizedUsers;

$csrfToken = csrf_token();
$settingsMessage = '';
$settingsMessageType = '';
$openSettingsModal = false;

if (isset($_GET['logout'])) {
    if (!csrf_validate($_GET['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid request token');
    }
    session_destroy();
    header('Location: admin-panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token';
    }

    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!isset($error)) {
        $username = findUsernameByLogin($login, $users, $userEmails);
        if ($username !== '' && isset($users[$username]) && verifyUserPassword($users[$username], $password)) {
            if (password_needs_rehash($users[$username], PASSWORD_DEFAULT)) {
                $users[$username] = password_hash($password, PASSWORD_DEFAULT);
                saveUsers($userDataFile, $users, $userEmails);
            }
            session_regenerate_id(true);
            $_SESSION['admin'] = $username;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveSettings']) && isset($_SESSION['admin'])) {
    $openSettingsModal = true;

    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $settingsMessage = 'Invalid request token';
        $settingsMessageType = 'error';
    } else {
        $newUser = trim($_POST['new_user'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $newEmail = strtolower(trim($_POST['new_email'] ?? ''));
        $editUser = trim($_POST['edit_user'] ?? '');
        $editPass = trim($_POST['edit_password'] ?? '');
        $editEmail = strtolower(trim($_POST['edit_email'] ?? ''));

        $hasChanges = false;

        if ($newUser !== '' || $newPass !== '' || $newEmail !== '') {
            if ($newUser === '' || $newPass === '' || $newEmail === '') {
                $settingsMessage = 'New user requires username, email and password.';
                $settingsMessageType = 'error';
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $settingsMessage = 'New user email is invalid.';
                $settingsMessageType = 'error';
            } else {
                $users[$newUser] = $newPass;
                $userEmails[$newUser] = $newEmail;
                $hasChanges = true;
            }
        }

        if (($editUser !== '' || $editPass !== '' || $editEmail !== '') && $settingsMessage === '') {
            if ($editUser === '' || !isset($users[$editUser])) {
                $settingsMessage = 'Edit user does not exist.';
                $settingsMessageType = 'error';
            } else {
                if ($editPass !== '') {
                    $users[$editUser] = $editPass;
                    $hasChanges = true;
                }

                if ($editEmail !== '') {
                    if (!filter_var($editEmail, FILTER_VALIDATE_EMAIL)) {
                        $settingsMessage = 'Edit email is invalid.';
                        $settingsMessageType = 'error';
                    } else {
                        $userEmails[$editUser] = $editEmail;
                        $hasChanges = true;
                    }
                }
            }
        }

        if ($settingsMessage === '' && $hasChanges) {
            saveUsers($userDataFile, $users, $userEmails);
            $settingsMessage = 'Settings saved successfully.';
            $settingsMessageType = 'success';
        } elseif ($settingsMessage === '') {
            $settingsMessage = 'No changes to save.';
            $settingsMessageType = 'error';
        }
    }
}

if (!isset($_SESSION['admin'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <base href="/">
            <title>Admin Login</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="../admin-panel/password_managment/login.css">
        </head>
        <body>
            <div class="login-box">
                <h1>Admin Login</h1>
                <?php if (isset($error)) echo "<p class='error'>" . e($error) . "</p>"; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="text" name="username" placeholder="Username or email" autocomplete="username" required>
                    <div class="password-field">
                        <input id="admin-login-password" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                        <button type="button" class="password-eye" id="password-eye" aria-label="Show password while pressed" title="Hold to show password">👁</button>
                    </div>
                    <button type="submit" name="login">Login</button>
                </form>
                <a class="helper-link" href="../admin-panel/password_managment/forgot-password.php">Forgot password?</a>
            </div>

            <script>
                (function () {
                    const passwordInput = document.getElementById('admin-login-password');
                    const eyeButton = document.getElementById('password-eye');
                    if (!passwordInput || !eyeButton) {
                        return;
                    }

                    const reveal = () => {
                        passwordInput.type = 'text';
                    };

                    const hide = () => {
                        passwordInput.type = 'password';
                    };

                    eyeButton.addEventListener('pointerdown', reveal);
                    eyeButton.addEventListener('pointerup', hide);
                    eyeButton.addEventListener('pointerleave', hide);
                    eyeButton.addEventListener('pointercancel', hide);
                    eyeButton.addEventListener('blur', hide);
                })();
            </script>
        </body>
        </html>
        <?php
        exit;
    }

$pendingDir = __DIR__ . '/../../database/images/uploads/pending/';
$uploadsDir = __DIR__ . '/../../database/images/uploads/';
$rejectedDir = __DIR__ . '/../../database/images/uploads/rejected/';
$galleryJsonFile = __DIR__ . '/../../database/json/gallery.json';
$statsFile = __DIR__ . '/../../database/data/stats.json';
$stats = [
    'approved' => 0,
    'rejected' => 0,
    'approved_items' => [],
    'rejected_items' => []
];

if (!is_dir($rejectedDir)) {
    mkdir($rejectedDir, 0755, true);
}

if (file_exists($statsFile)) {
    $statsData = json_decode(file_get_contents($statsFile), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($statsData)) {
        $stats = array_merge($stats, array_intersect_key($statsData, ['approved' => 0, 'rejected' => 0, 'approved_items' => [], 'rejected_items' => []]));
    }
}

function saveStats($file, $statsArray) {
    file_put_contents($file, json_encode($statsArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if (isset($_GET['approve']) && isset($_SESSION['admin']) && csrf_validate($_GET['csrf'] ?? '')) {
    $filename = basename($_GET['approve']);
    $jsonFile = $pendingDir . $filename . '.json';
    
    if (file_exists($jsonFile)) {
        $metadata = json_decode(file_get_contents($jsonFile), true);
        $source = $pendingDir . $filename;
        $destination = $uploadsDir . $filename;
        
        if (rename($source, $destination)) {
            $year = substr($metadata['date'], -4);
            $galleryData = json_decode(file_get_contents($galleryJsonFile), true) ?: [];
            
            if (!isset($galleryData[$year])) {
                $galleryData[$year] = [];
            }
            
            $entry = [
                'src' => '/database/images/uploads/' . $filename,
                'alt' => $metadata['title'],
                'des' => $metadata['description']
            ];
            
            $galleryData[$year][] = $entry;
            
            file_put_contents($galleryJsonFile, json_encode($galleryData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $stats['approved'] = ($stats['approved'] ?? 0) + 1;
            $stats['approved_items'][] = [
                'filename' => $filename,
                'title' => $metadata['title'] ?? '',
                'date' => $metadata['date'] ?? '',
                'description' => $metadata['description'] ?? '',
                'email' => $metadata['email'] ?? '',
                'img' => '/database/images/uploads/' . $filename
            ];
            saveStats($statsFile, $stats);

            unlink($jsonFile);
            
            header('Location: admin-panel.php');
            exit;
        }
    }
}

if (isset($_GET['reject']) && isset($_SESSION['admin']) && csrf_validate($_GET['csrf'] ?? '')) {
    $filename = basename($_GET['reject']);
    $jsonFile = $pendingDir . $filename . '.json';
    $imageFile = $pendingDir . $filename;
    $metadata = [];

    if (file_exists($jsonFile)) {
        $metadata = json_decode(file_get_contents($jsonFile), true) ?? [];
    }

    $rejectImagePath = '/database/images/uploads/rejected/' . $filename;

    if (file_exists($imageFile)) {
        rename($imageFile, $rejectedDir . $filename);
    }
    if (file_exists($jsonFile)) unlink($jsonFile);

    $stats['rejected'] = ($stats['rejected'] ?? 0) + 1;
    $stats['rejected_items'][] = [
        'filename' => $filename,
        'title' => $metadata['title'] ?? '',
        'date' => $metadata['date'] ?? '',
        'description' => $metadata['description'] ?? '',
        'email' => $metadata['email'] ?? '',
        'img' => $rejectImagePath
    ];
    saveStats($statsFile, $stats);
    
    header('Location: admin-panel.php');
    exit;
}

$pending = [];
if (is_dir($pendingDir)) {
    $files = scandir($pendingDir);
    foreach ($files as $file) {
        if (substr($file, -5) === '.json') {
            $baseFile = substr($file, 0, -5);
            if (file_exists($pendingDir . $baseFile)) {
                $metadata = json_decode(file_get_contents($pendingDir . $file), true);
                $pending[] = [
                    'filename' => $baseFile,
                    'metadata' => $metadata,
                    'imageUrl' => '/database/images/uploads/pending/' . $baseFile
                ];
            }
        }
    }
}

usort($pending, function($a, $b) {
    return $b['metadata']['timestamp'] - $a['metadata']['timestamp'];
});

$approvedItems = $stats['approved_items'] ?? [];
$rejectedItems = $stats['rejected_items'] ?? [];

$json_file = '' . __DIR__ . '/../../database/json/history.json';
$upload_dir = '' . __DIR__ . '/../../database/json/history/';

if (!file_exists($json_file)) file_put_contents($json_file, json_encode([]));
$events = json_decode(file_get_contents($json_file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action !== '' && !csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid request token']);
        exit;
    }

    if ($action === 'add') {
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '.' . $ext;
            $image_path = $upload_dir . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }

        $new_event = [
            'id' => time(),
            'date' => $_POST['date'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'src' => $image_path,
            'alt' => $_POST['title']
        ];

        $events[] = $new_event;
        file_put_contents($json_file, json_encode($events, JSON_PRETTY_PRINT));
        echo json_encode($new_event);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        foreach ($events as $key => $e) {
            if ($e['id'] === $id) {
                if (!empty($e['src']) && file_exists($e['src'])) unlink($e['src']);
                unset($events[$key]);
            }
        }
        $events = array_values($events);
        file_put_contents($json_file, json_encode($events, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>Admin Managmant</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/admin-panel/dashboard/admin-panel.css">
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="admin-panel.php"><img src="../database/images/logo/logo.png" alt="Logo"></a>
            </div>
            <ul class="navbar-menu" id="navbar-menu">
                <li><a href="/admin-panel/dashboard/admin-panel.php#infos">Dashboard</a></li>
                <li><a href="/admin-panel/dashboard/admin-panel.php#gallery">Gallery</a></li>
                <li><a href="/admin-panel/dashboard/admin-panel.php#timeline">Timeline</a></li>
                <li><a href="#" id="settings-open">Settings</a></li>
                <li><a href="<?= '/admin-panel/dashboard/admin-panel.php?' . http_build_query(['logout' => 1, 'csrf' => $csrfToken]) ?>">Logout</a></li>
            </ul>
            <div class="navbar-toggle" id="navbar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <main>
        <section class="section" id="infos">
            <div class="container">
                <h1>Welcome to the Admin Dashboard</h1>
                <p class="centered-text">Use this panel to manage gallery uploads, timeline events, and user settings.</p>
            </div>

            <div class="dashboard-trends">
                <div class="trend-card">
                    <h3>Uptime checks (24 hours)</h3>
                    <img src="/admin-panel/graph/graph_uptime.php" alt="Uptime chart" style="width:100%; height:auto; max-height:320px;">
                </div>
                <div class="trend-card">
                    <h3>Visitors (24 hours)</h3>
                    <img src="/admin-panel/graph/graph_visitors.php" alt="Visitors chart" style="width:100%; height:auto; max-height:320px;">
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Approved Uploads</h3>
                    <div class="stat"><?= $stats['approved'] ?? 0 ?></div>
                    <div><span class="increase">+<?= $stats['approved'] ?? 0 ?> </span> done</div>
                </div>
                <div class="dashboard-card">
                    <h3>Rejected Uploads</h3>
                    <div class="stat"><?= $stats['rejected'] ?? 0 ?></div>
                    <div><span class="decrease">-<?= $stats['rejected'] ?? 0 ?> </span> blocked</div>
                </div>
            </div>
        </section>

        <section class="section" id="gallery">
            <div class="container">
                <h2>Gallery Management</h2>

                <div class="status-filter">
                    <button class="filter-btn active" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="approved">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>

                <div class="pending-grid status-view pending-view">
                    <?php if (empty($pending)): ?>
                        <div class="empty">
                            <p>No pending requests.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pending as $item): ?>
                            <div class="pending-item" data-status="pending">
                                <img src="<?= htmlspecialchars($item['imageUrl']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/database/images/logo/logo.png';">
                                <div class="pending-info">
                                    <div class="pending-header">
                                        <span class="status-badge pending">Pending</span>
                                    </div>
                                    <h3 class="approved-title"><?= htmlspecialchars($item['metadata']['title']) ?></h3>
                                    <p class="center-date"><strong>Date:</strong> <?= htmlspecialchars($item['metadata']['date']) ?></p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars(substr($item['metadata']['description'], 0, 50)) ?>...</p>
                                    <p><strong>Uploaded:</strong> <?= date('d.m.Y H:i', $item['metadata']['timestamp']) ?></p>
                                    <p><strong>Uploader Email:</strong> <?= htmlspecialchars($item['metadata']['email']) ?></p>
                                    <div class="pending-actions">
                                        <a href="<?= '/admin-panel/dashboard/admin-panel.php?' . http_build_query(['approve' => $item['filename'], 'csrf' => $csrfToken]) ?>" class="approve">Approve</a>
                                        <a href="<?= '/admin-panel/dashboard/admin-panel.php?' . http_build_query(['reject' => $item['filename'], 'csrf' => $csrfToken]) ?>" class="reject">Reject</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="pending-grid status-view approved-view hidden">
                    <?php if (empty($approvedItems)): ?>
                        <div class="empty"><p>No approved requests.</p></div>
                    <?php else: ?>
                        <?php foreach ($approvedItems as $item): ?>
                            <?php
                                $approvedFilename = basename((string)($item['filename'] ?? ''));
                                if ($approvedFilename === '') {
                                    $approvedFilename = basename((string)($item['img'] ?? ''));
                                }
                            ?>
                            <div class="pending-item" data-status="approved">
                                <img src="<?= htmlspecialchars($item['img']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/database/images/logo/logo.png';">
                                <div class="pending-info">
                                    <div class="pending-header">
                                        <span class="status-badge approved">Approved</span>
                                        <?php if ($approvedFilename !== ''): ?>
                                            <div class="approved-image-menu"
                                                 data-filename="<?= htmlspecialchars($approvedFilename) ?>"
                                                 data-title="<?= htmlspecialchars((string)($item['title'] ?? '')) ?>"
                                                 data-date="<?= htmlspecialchars((string)($item['date'] ?? '')) ?>"
                                                 data-description="<?= htmlspecialchars((string)($item['description'] ?? '')) ?>">
                                                <button type="button" class="approved-menu-toggle" aria-label="Image actions" title="Image actions" onclick="return window.adminToggleApprovedMenu(this)">...</button>
                                                <div class="approved-menu-dropdown" role="menu">
                                                    <button type="button" class="approved-menu-action" data-action="edit" onclick="return window.adminApprovedMenuEdit(this)">Edit</button>
                                                    <button type="button" class="approved-menu-action danger" data-action="delete" onclick="return window.adminApprovedMenuDelete(this)">Delete</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="approved-title"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="center-date"><strong>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                                    <p><?= htmlspecialchars(substr((string)$item['description'], 0, 50)) ?>...</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="pending-grid status-view rejected-view hidden">
                    <?php if (empty($rejectedItems)): ?>
                        <div class="empty"><p>No rejected requests.</p></div>
                    <?php else: ?>
                        <?php foreach ($rejectedItems as $item): ?>
                            <div class="pending-item" data-status="rejected">
                                <img src="<?= htmlspecialchars($item['img']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/database/images/logo/logo.png';">
                                <div class="pending-info">
                                    <div class="pending-header">
                                        <span class="status-badge rejected">Rejected</span>
                                    </div>
                                    <h3 class="approved-title"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="center-date"><strong>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                                    <p><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <section class="section" id="timeline">
            <div id="history-admin">
                <div class="info-msg" id="info-msg" hidden>
                    <h3 id="succes-h1">Success</h3>
                    <p id="succes-text">Your events have been updated.</p>
                </div>

                <div class="error-msg" id="error-msg" hidden>
                    <h3>Error!</h3>
                    <p id="error-text">Something went wrong!</p>
                </div>

                <h2>History Management</h2>

                <button id="add-event" type="button">+ Add Event</button>

                <ul id="history-list"></ul>
            </div>
        </section>

    </main>

    <div id="password-modal" class="modal<?= $openSettingsModal ? '' : ' hidden' ?>">
        <div class="modal-content">
            <h2>Security Settings</h2>
            <p>Manage admin password and account emails for secure password reset.</p>

            <?php if ($settingsMessage !== ''): ?>
                <p class="settings-feedback <?= e($settingsMessageType ?: 'success') ?>"><?= e($settingsMessage) ?></p>
            <?php endif; ?>

            <a href="/admin-panel/password_managment/change-password.php" target="_blank" rel="noopener noreferrer" class="btn">Change Own Password</a>

            <form method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <h3>Create User</h3>
                <input type="text" name="new_user" placeholder="New username">
                <input type="email" name="new_email" placeholder="New user email">
                <input type="password" name="new_password" placeholder="New user password">

                <h3>Edit User</h3>
                <input type="text" name="edit_user" placeholder="Existing username">
                <input type="email" name="edit_email" placeholder="New email (optional)">
                <input type="password" name="edit_password" placeholder="New password (optional)">

                <button type="submit" name="saveSettings" class="btn">Save User Settings</button>
            </form>

            <button id="close-modal" class="btn secondary">Close</button>
        </div>
    </div>

    <div id="add-event-modal" class="modal hidden">
        <div class="modal-content">
            <h2>Add Event</h2>
            <form id="add-event-form">
                <input id="event-title" name="title" type="text" placeholder="Event title" required>
                <input id="event-date" name="date" type="date" required>
                <textarea id="event-description" name="description" placeholder="Description"></textarea>
                <input id="event-image" name="image" type="file" accept="image/*" required>
                <button type="submit" class="btn">Add Event</button>
            </form>
        </div>
    </div>

    <div id="confirm-delete-modal" class="modal hidden">
        <div class="modal-content">
            <h2 id="confirm-delete-title">Delete Entry</h2>
            <p id="confirm-delete-text">Are you sure you want to delete this event?</p>
            <div class="modal-actions" style="margin-top: 1rem; text-align: right;">
                <button id="cancel-delete" class="btn secondary">Cancel</button>
                <button id="confirm-delete" class="btn danger">Delete</button>
            </div>
        </div>
    </div>

    <div id="approved-edit-modal" class="modal hidden">
        <div class="modal-content approved-edit-content">
            <h2>Edit Approved Image</h2>
            <form id="approved-edit-form" class="approved-edit-form">
                <input type="hidden" id="approved-edit-filename" name="filename">
                <input type="text" id="approved-edit-title" name="title" placeholder="Title" maxlength="150" required>
                <input type="text" id="approved-edit-date" name="date" pattern="\d{2}\.\d{2}\.\d{4}" placeholder="Date (TT.MM.JJJJ)" required>
                <textarea id="approved-edit-description" name="description" placeholder="Description" maxlength="3000" required></textarea>
                <div class="modal-actions" style="margin-top: 1rem; text-align: right;">
                    <button type="button" id="approved-edit-cancel" class="btn secondary">Cancel</button>
                    <button type="submit" id="approved-edit-save" class="btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.ADMIN_CSRF_TOKEN = <?= json_encode($csrfToken, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="/admin-panel/dashboard/admin-panel.js"></script>

</body>
</html>
