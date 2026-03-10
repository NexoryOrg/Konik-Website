<?php
session_start();

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: upload-admin.php');
    exit;
}

// Einfache Authentifizierung (sollte später verbessert werden)
if (!isset($_SESSION['admin']) && !isset($_GET['token'])) {
    // Zeige Login-Seite (vereinfacht)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
        // Verwende eine Umgebungsvariable oder .env Datei für das Passwort
        $adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin'; // ÄNDERE DIES!
        if ($_POST['password'] === $adminPassword) {
            $_SESSION['admin'] = true;
        } else {
            $error = 'Falsches Passwort';
        }
    }
    
    if (!isset($_SESSION['admin'])) {
        ?>
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Poppins', sans-serif; background: #f7f9f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
                .login-box h1 { color: #2e7d32; }
                .login-box input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #2e7d32; border-radius: 5px; font-family: 'Poppins', sans-serif; }
                .login-box button { width: 100%; padding: 10px; background: #2e7d32; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
                .login-box button:hover { background: #1b5e20; }
                .error { color: #c62828; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h1>Admin Panel</h1>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Admin Passwort" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Admin ist authentifiziert
$pendingDir = __DIR__ . '/datenbank/bilder/uploads/pending/';
$uploadsDir = __DIR__ . '/datenbank/bilder/uploads/';
$galleryJsonFile = __DIR__ . '/datenbank/json/gallery.json';

// Handle Approve
if (isset($_GET['approve']) && isset($_SESSION['admin'])) {
    $filename = basename($_GET['approve']);
    $jsonFile = $pendingDir . $filename . '.json';
    
    if (file_exists($jsonFile)) {
        $metadata = json_decode(file_get_contents($jsonFile), true);
        $source = $pendingDir . $filename;
        $destination = $uploadsDir . $filename;
        
        // Verschiebe Bild
        if (rename($source, $destination)) {
            // Nur in gallery.json hinzufügen (NICHT in history.json)
            $year = substr($metadata['date'], -4);
            $galleryData = json_decode(file_get_contents($galleryJsonFile), true) ?: [];
            
            if (!isset($galleryData[$year])) {
                $galleryData[$year] = [];
            }
            
            $entry = [
                'src' => 'datenbank/bilder/uploads/' . $filename,
                'alt' => $metadata['title'],
                'des' => $metadata['description']
            ];
            
            $galleryData[$year][] = $entry;
            
            file_put_contents($galleryJsonFile, json_encode($galleryData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Lösche JSON Metadata
            unlink($jsonFile);
            
            header('Location: upload-admin.php');
            exit;
        }
    }
}

// Handle Reject
if (isset($_GET['reject']) && isset($_SESSION['admin'])) {
    $filename = basename($_GET['reject']);
    $jsonFile = $pendingDir . $filename . '.json';
    $imageFile = $pendingDir . $filename;
    
    if (file_exists($imageFile)) unlink($imageFile);
    if (file_exists($jsonFile)) unlink($jsonFile);
    
    header('Location: upload-admin.php');
    exit;
}

// Hole pending uploads
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
                    'imageUrl' => 'datenbank/bilder/uploads/pending/' . $baseFile
                ];
            }
        }
    }
}

// Sortiere nach Timestamp (neueste zuerst)
usort($pending, function($a, $b) {
    return $b['metadata']['timestamp'] - $a['metadata']['timestamp'];
});
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Verwaltung</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f7f9f7; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2e7d32; margin-bottom: 20px; }
        .logout { text-align: right; margin-bottom: 20px; }
        .logout a { color: #2e7d32; text-decoration: none; font-weight: 600; }
        .logout a:hover { text-decoration: underline; }
        
        .pending-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .pending-item { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .pending-img { width: 100%; height: 200px; object-fit: cover; }
        .pending-info { padding: 15px; }
        .pending-info h3 { color: #2e7d32; margin-bottom: 5px; }
        .pending-info p { font-size: 0.9rem; margin: 5px 0; color: #666; }
        .pending-actions { display: flex; gap: 10px; margin-top: 10px; }
        .pending-actions a { flex: 1; padding: 8px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600; }
        .approve { background: #4caf50; color: white; }
        .approve:hover { background: #45a049; }
        .reject { background: #f44336; color: white; }
        .reject:hover { background: #da190b; }
        
        .empty { text-align: center; color: #999; padding: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout">
            <a href="<?php echo '?' . http_build_query(['logout' => 1]); ?>">Logout</a>
        </div>
        
        <h1>📸 Überprüfung von hochgeladenen Bildern</h1>
        
        <?php if (empty($pending)): ?>
            <div class="empty">
                <p>Keine ausstehenden Uploads zur Überprüfung</p>
            </div>
        <?php else: ?>
            <div class="pending-grid">
                <?php foreach ($pending as $item): ?>
                    <div class="pending-item">
                        <img src="<?= htmlspecialchars($item['imageUrl']) ?>" alt="Preview" class="pending-img">
                        <div class="pending-info">
                            <h3><?= htmlspecialchars($item['metadata']['title']) ?></h3>
                            <p><strong>Datum:</strong> <?= htmlspecialchars($item['metadata']['date']) ?></p>
                            <p><strong>Beschreibung:</strong> <?= htmlspecialchars(substr($item['metadata']['description'], 0, 50)) ?>...</p>
                            <p><strong>Hochgeladen:</strong> <?= date('d.m.Y H:i', $item['metadata']['timestamp']) ?></p>
                            <div class="pending-actions">
                                <a href="<?= '?' . http_build_query(['approve' => $item['filename']]) ?>" class="approve">✓ Akzeptieren</a>
                                <a href="<?= '?' . http_build_query(['reject' => $item['filename']]) ?>" class="reject">✕ Ablehnen</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
