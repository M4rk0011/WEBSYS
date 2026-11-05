<?php
// Default XAMPP MySQL settings. Change these if your setup is different.
$host = '127.0.0.1';
$port = '3306';
$dbname = 'lower_general_luna';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    exit('Database connection failed. Start MySQL in XAMPP, import database.sql in phpMyAdmin, and check dbconn.php.');
}
