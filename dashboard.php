<?php
require_once 'session.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'dbconn.php';
try {
    $stmt = $pdo->prepare('SELECT fullname, email, address, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(503);
    exit('Unable to load your account. Check that database.sql has been imported.');
}
if (!$user) {
    unset($_SESSION['user_id']);
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Barangay Lower General Luna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card dashboard">
    <p class="brand">BARANGAY LOWER GENERAL LUNA</p>
    <h1>Welcome, <?= escape($user['fullname']) ?>!</h1>
    <p class="muted">You are logged in to your resident account.</p>
    <h2>Your profile</h2>
    <dl>
        <dt>Full name</dt><dd><?= escape($user['fullname']) ?></dd>
        <dt>Email address</dt><dd><?= escape($user['email']) ?></dd>
        <dt>Address</dt><dd><?= escape($user['address']) ?></dd>
        <dt>Registered on</dt><dd><?= escape($user['created_at']) ?></dd>
    </dl>
    <p class="notice">Complaint submission, tracking and feedback are planned for a future update.</p>
    <form method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
        <button type="submit">Log out</button>
    </form>
</main>
</body>
</html>
