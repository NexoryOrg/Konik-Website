<?php
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: 3306;
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]
    );
    echo "✅ DB Verbindung erfolgreich!<br>";
} catch (PDOException $e) {
    echo "❌ DB Verbindung fehlgeschlagen: " . $e->getMessage() . "<br>";
}

// Debug: Zeige geladene ENV-Variablen
echo "DB_HOST: " . getenv('DB_HOST') . "<br>";
echo "DB_USER: " . getenv('DB_USER') . "<br>";
echo "DB_NAME: " . getenv('DB_NAME') . "<br>";
?>