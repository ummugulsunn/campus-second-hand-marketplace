<?php
declare(strict_types=1);

// PDO connection configuration for campus_marketplace database.
$dbConfig = [
    'host'    => getenv('DB_HOST') ?: 'localhost',
    'name'    => getenv('DB_NAME') ?: 'campus_marketplace',
    'user'    => getenv('DB_USER') ?: 'root',  // Update DB_USER in your environment for production.
    'pass'    => getenv('DB_PASS') ?: '',       // Update DB_PASS in your environment for production.
    'charset' => 'utf8mb4',
];

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['name'],
    $dbConfig['charset']
);

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $pdoOptions);
} catch (PDOException $e) {
    // In production, consider logging instead of echoing raw errors.
    exit('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}


