<?php
require_once 'session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
$email = strtolower(input('email'));
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (!validToken()) {
        http_response_code(403);
        $error = 'Please reload the page and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter your email and password.';
    } else {
        require 'dbconn.php';
        try {
            $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Incorrect email or password.';
        } catch (PDOException $e) {
            $error = 'Unable to log in. Check that database.sql has been imported.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Barangay Lower General Luna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <p class="brand">BARANGAY LOWER GENERAL LUNA</p>
    <h1>Welcome back</h1>
    <p class="muted">Log in to the Barangay Concern Report System.</p>
    <?php if ($success): ?><p class="message success" role="status"><?= escape($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="message error" role="alert"><?= escape($error) ?></p><?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="<?= escape($email) ?>" maxlength="190" autocomplete="username" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">Log in</button>
    </form>
    <p class="footer">New here? <a href="signup.php">Create an account</a></p>
</main>
</body>
</html>
