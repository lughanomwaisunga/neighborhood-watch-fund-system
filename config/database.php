<?php
/**
 * Database Configuration
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPassword = $_ENV['DB_PASSWORD'] ?? '';
$dbName = $_ENV['DB_NAME'] ?? 'neighborhood_watch';
$dbPort = $_ENV['DB_PORT'] ?? 3306;

try {
    $connection = new mysqli(
        $dbHost,
        $dbUser,
        $dbPassword,
        $dbName,
        (int)$dbPort
    );

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    // Set charset to utf8mb4
    $connection->set_charset('utf8mb4');

    return $connection;
} catch (Exception $e) {
    die('Error connecting to database: ' . $e->getMessage());
}
