<?php
// Update these with your actual settings
$host = 'mysql-first';          // Your k8s Service name!
$db   = 'local';
$user = 'testuser';
$pass = 'testpassword';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Try a query
    $stmt = $pdo->query('SELECT * FROM company');
    $rows = $stmt->fetchAll();

    echo "Rows in company table:\n";
    print_r($rows);
} catch (\PDOException $e) {
    echo "PDO connection failed: " . $e->getMessage();
}