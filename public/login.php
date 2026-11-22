<?php
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/LoginController.php";
require __DIR__ . "/partials/header.php";

?>
<main class="auth">
    <section class="auth-story">
        <a class="brand" href="index.php">
            <img src="assets/img/lower-general-luna-logo.png" alt="Lower General Luna barangay logo" class="brand-logo">
            <div>BARANGAY LOWER GENERAL LUNA<small>Citizen Services</small></div>
        </a>
        <p class="eyebrow">YOUR VOICE. OUR COMMUNITY.</p>
        <h1>Better services<br>start with <em>you.</em></h1>
        <p>Report a concern, follow its progress, and help improve our community.</p>
        <a class="light-link" href="announcements.php">Read public announcements →</a>
    </section>
    <section class="auth-card">
        <p class="eyebrow">COMMUNITY PORTAL</p>
        <h2>Welcome back.</h2>
        <p>Sign in to track complaints and services.</p>
        <?php if ($flash !== ""): ?>
            <div class="notice"><?= htmlspecialchars($flash) ?></div>
        <?php endif; 
 if ($error !== ""): ?>
            <div class="notice error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <!-- CSRF token proves this form came from this browser session. -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
            <input type="hidden" name="action" value="login">
            <label>Username
                <input type="text" name="username" value="<?= htmlspecialchars(post("username")) ?>" maxlength="100" required autocomplete="username">
            </label>
            <label>Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="primary" type="submit">Sign in →</button>
        </form>
        <p>New here? <a href="signup.php">Create an account</a></p>
    </section>
</main>
<?php require __DIR__ . "/partials/footer.php"; ?>
