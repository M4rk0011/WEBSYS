<?php
// DATABASE CONNECTION: change these values if your XAMPP settings are different.
$host = "localhost";
$user = "root";
$pass = "";
$db = "barangay_service_system";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$attributes = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Optional local overrides: copy local.example.php to local.php first.
if (is_file(__DIR__ . "/local.php")) {
    $config = require __DIR__ . "/local.php";
    $dsn = $config["dsn"] ?? $dsn;
    $user = $config["user"] ?? $user;
    $pass = $config["password"] ?? $pass;
}

// Optional hosting/test settings. You do not need these for normal XAMPP use.
if (getenv("APP_SQLITE_PATH")) {
    $dsn = "sqlite:" . getenv("APP_SQLITE_PATH");
}
$dsn = getenv("DB_DSN") ?: $dsn;
$user = getenv("DB_USER") ?: $user;
$pass = getenv("DB_PASSWORD") ?: $pass;

try {
    $pdo = new PDO($dsn, $user, $pass, $attributes);
} catch (PDOException $e) {
    // Do not display database passwords or internal connection errors to visitors.
    http_response_code(503);
    exit("Database connection failed. Start MySQL in XAMPP, import database.sql for a new installation, and check config/db.php or config/local.php. Keep your existing database when updating.");
}

// Older shared helpers use db(). It returns the same $pdo connection above.
function db()
{
    global $pdo;
    return $pdo;
}
