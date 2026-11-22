<?php
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/SignupController.php";
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
        <h2>Join your community.</h2>
        <p>Create a resident account. Verification is handled by the barangay administrator.</p>
        <?php if ($flash !== ""): ?>
            <div class="notice"><?= htmlspecialchars($flash) ?></div>
        <?php endif; 
 if ($error !== ""): ?>
            <div class="notice error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="signup.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
            <input type="hidden" name="action" value="register">
            <div class="grid two">
                <label>First name
                    <input type="text" name="firstName" value="<?= htmlspecialchars(post("firstName")) ?>" maxlength="100" required>
                </label>
                <label>Last name
                    <input type="text" name="lastName" value="<?= htmlspecialchars(post("lastName")) ?>" maxlength="100" required>
                </label>
            </div>
            <label>Email
                <input type="email" name="email" value="<?= htmlspecialchars(post("email")) ?>" maxlength="200" required>
            </label>
            <label>Phone
                <input type="tel" name="phone" value="<?= htmlspecialchars(post("phone")) ?>" maxlength="40" required>
            </label>
            <label>Residential address
                <input type="text" name="address" value="<?= htmlspecialchars(post("address")) ?>" maxlength="500" required>
            </label>
            <label>Username
                <input type="text" name="username" value="<?= htmlspecialchars(post("username")) ?>" maxlength="100" required autocomplete="username">
            </label>
            <label>Password (8+ characters)
                <input type="password" name="password" minlength="8" required autocomplete="new-password">
            </label>
            <label>Confirm password
                <input type="password" name="confirmPassword" minlength="8" required autocomplete="new-password">
            </label>
            <button class="primary" type="submit">Create resident account</button>
        </form>
        <p>Already registered? <a href="login.php">Sign in</a></p>
    </section>
</main>
<?php require __DIR__ . "/partials/footer.php"; ?>
