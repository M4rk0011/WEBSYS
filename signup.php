<?php
require_once 'session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
$fullname = input('fullname');
$email = strtolower(input('email'));
$address = input('address');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    if (!validToken()) {
        http_response_code(403);
        $error = 'Please reload the page and try again.';
    } elseif ($fullname === '' || strlen($fullname) > 100 || $address === '' || strlen($address) > 255) {
        $error = 'Enter your full name (up to 100 characters) and address (up to 255 characters).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Use a password between 8 and 72 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        require 'dbconn.php';
        try {
            $stmt = $pdo->prepare('INSERT INTO users (fullname, email, address, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$fullname, $email, $address, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['success'] = 'Account created. You can now log in.';
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $error = (int) ($e->errorInfo[1] ?? 0) === 1062
                ? 'This email is already registered.'
                : 'Unable to create your account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | Barangay Lower General Luna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <p class="brand">BARANGAY LOWER GENERAL LUNA</p>
    <h1>Create an account</h1>
    <p class="muted">Register for the Barangay Concern Report System.</p>
    <?php if ($error): ?><p class="message error" role="alert"><?= escape($error) ?></p><?php endif; ?>
    <form method="post" action="signup.php">
        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
        <label for="fullname">Full name</label>
        <input id="fullname" name="fullname" value="<?= escape($fullname) ?>" maxlength="100" autocomplete="name" required>
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="<?= escape($email) ?>" maxlength="190" autocomplete="email" required>
        <label for="address">Residential address</label>
        <input id="address" name="address" value="<?= escape($address) ?>" maxlength="255" autocomplete="street-address" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <small>Use 8–72 characters.</small>
        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <button type="submit">Sign up</button>
    </form>
    <p class="footer">Already registered? <a href="login.php">Log in</a></p>
</main>
</body>
</html>
