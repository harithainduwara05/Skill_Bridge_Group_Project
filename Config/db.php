<?php
require_once __DIR__ . '/env_loader.php';

$host     = 'localhost'; // $_ENV['DB_HOST'] ?? 'localhost';
$user     = 'root'; // $_ENV['DB_USERNAME'] ?? 'root';
$password = ''; // $_ENV['DB_PASSWORD'] ?? '';
$dbname   = 'skillbridge_db'; // $_ENV['DB_DATABASE'] ?? 'skillbridge_db';
$port     = 3306; // !empty($_ENV['DB_PORT']) ? (int)$_ENV['DB_PORT'] : 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $password, $dbname, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
