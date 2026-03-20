<?php
require_once __DIR__ . '/../../init.php';

$userDataFile = __DIR__ . '/../../datenbank/data/user.json';

function isHashedPassword($password) {
    return is_string($password) && preg_match('/^\$(2y|argon2)/', $password) === 1;
}

function parseUsersFromFile($file) {
    $users = [];
    if (!file_exists($file)) {
        return $users;
    }

    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['users']) || !is_array($data['users'])) {
        return $users;
    }

    foreach ($data['users'] as $row) {
        if (!empty($row['username']) && isset($row['password'])) {
            $users[(string)$row['username']] = (string)$row['password'];
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

function verifyUserPassword($storedPassword, $plainPassword) {
    $storedPassword = (string)$storedPassword;
    if (isHashedPassword($storedPassword)) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $plainPassword);
}

function saveUsers($file, $usersArray) {
    $safeUsers = normalizeUsers($usersArray);
    $payload = ['users' => array_map(fn($k, $v) => ['username' => $k, 'password' => $v], array_keys($safeUsers), $safeUsers)];
    file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$users = parseUsersFromFile($userDataFile);
if (empty($users)) {
    $users = ['admin' => password_hash('admin', PASSWORD_DEFAULT)];
}

$normalizedUsers = normalizeUsers($users);
if ($normalizedUsers !== $users || !file_exists($userDataFile)) {
    saveUsers($userDataFile, $normalizedUsers);
}
$users = $normalizedUsers;

$csrfToken = csrf_token();

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

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!isset($error)) {
        if ($username && isset($users[$username]) && verifyUserPassword($users[$username], $password)) {
            if (password_needs_rehash($users[$username], PASSWORD_DEFAULT)) {
                $users[$username] = password_hash($password, PASSWORD_DEFAULT);
                saveUsers($userDataFile, $users);
            }
            session_regenerate_id(true);
            $_SESSION['admin'] = $username;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveSettings']) && isset($_SESSION['admin'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request token';
    } else {
        $newUser = trim($_POST['new_user'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $editUser = trim($_POST['edit_user'] ?? '');
        $editPass = trim($_POST['edit_password'] ?? '');

        if ($newUser && $newPass) {
            $users[$newUser] = $newPass;
        }
        if ($editUser && $editPass && isset($users[$editUser])) {
            $users[$editUser] = $editPass;
        }

        saveUsers($userDataFile, $users);
        $message = 'Settings saved';
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
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

$pendingDir = __DIR__ . '/../../datenbank/bilder/uploads/pending/';
$uploadsDir = __DIR__ . '/../../datenbank/bilder/uploads/';
$rejectedDir = __DIR__ . '/../../datenbank/bilder/uploads/rejected/';
$galleryJsonFile = __DIR__ . '/../../datenbank/json/gallery.json';
$statsFile = __DIR__ . '/../../datenbank/data/stats.json';
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
                'src' => '/datenbank/bilder/uploads/' . $filename,
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
                'img' => '/datenbank/bilder/uploads/' . $filename
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

    $rejectImagePath = '/datenbank/bilder/uploads/rejected/' . $filename;

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
                    'imageUrl' => '/datenbank/bilder/uploads/pending/' . $baseFile
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

$json_file = '' . __DIR__ . '/../../datenbank/json/history.json';
$upload_dir = '' . __DIR__ . '/../../datenbank/json/history/';

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
                <a href="admin-panel.php"><img src="../datenbank/bilder/logo/logo.png" alt="Logo"></a>
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
                                <img src="<?= htmlspecialchars($item['imageUrl']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/datenbank/bilder/logo/logo.png';">
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
                            <div class="pending-item" data-status="approved">
                                <img src="<?= htmlspecialchars($item['img']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/datenbank/bilder/logo/logo.png';">
                                <div class="pending-info">
                                    <div class="pending-header">
                                        <span class="status-badge approved">Approved</span>
                                    </div>
                                    <h3 class="approved-title"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="center-date"><strong>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                                    <p><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</p>
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
                                <img src="<?= htmlspecialchars($item['img']) ?>" alt="Preview" class="pending-img" onerror="this.onerror=null;this.src='/datenbank/bilder/logo/logo.png';">
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

    <div id="password-modal" class="modal hidden">
        <div class="modal-content">
            <h2>Change Password</h2>
            <p>Click here to securely update your password on the external page.</p>
            <a href="/admin-panel/password_managment/change-password.php" target="_blank" rel="noopener noreferrer" class="btn">Change Password</a>
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
            <h2>Delete Entry</h2>
            <p>Are you sure you want to delete this event?</p>
            <div class="modal-actions" style="margin-top: 1rem; text-align: right;">
                <button id="cancel-delete" class="btn secondary">Cancel</button>
                <button id="confirm-delete" class="btn danger">Delete</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.ADMIN_CSRF_TOKEN = <?= json_encode($csrfToken, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="/admin-panel/dashboard/admin-panel.js"></script>

</body>
</html>
