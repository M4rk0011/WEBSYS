<div class="auth-scene">
  <div class="auth-brand"><span class="brand-mark">LGL</span><div>BARANGAY LOWER GENERAL LUNA<small>CONCERN REPORT SYSTEM</small></div></div>
  <div class="auth-story"><span class="eyebrow">YOUR VOICE. OUR COMMUNITY.</span><h1>A better barangay<br>starts with <em>you.</em></h1><p>Raise a concern. Stay informed. Help make our barangay a safer, more caring place for everyone.</p><div class="auth-pills"><span>Report with confidence</span><span>Track your concerns</span></div></div>
  <section class="login-card" aria-labelledby="signin-title"><span class="eyebrow">WELCOME TO YOUR BARANGAY PORTAL</span><h2 id="signin-title">Good to see you.</h2><p class="auth-intro">Sign in to your account to get started.</p>
      <form class="login-form" method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="login">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" value="<?= e($loginData->username) ?>" name="username" placeholder="Enter your username" autocomplete="username" <?= $errorMessage ? 'aria-describedby="login-error"' : '' ?> required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-input">
            <input type="<?= e(($showPassword ? 'text' : 'password')) ?>" id="password" value="<?= e($loginData->password) ?>" name="password" placeholder="Enter your password" autocomplete="current-password" <?= $errorMessage ? 'aria-describedby="login-error"' : '' ?> required>
            <button type="button" class="toggle-password" data-password="password">
              <?= e(($showPassword ? 'Hide' : 'Show')) ?>
            </button>
          </div>
        </div>

        <?php if ($errorMessage): ?>
        <div class="login-feedback" id="login-error" role="alert" tabindex="-1" autofocus>
          <span class="login-feedback-icon" aria-hidden="true">!</span>
          <div><strong>Unable to sign in</strong><p><?= e($errorMessage) ?></p></div>
        </div>
        <?php endif; ?>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" <?= $isLoading ? 'disabled' : '' ?>>
            <?= e(($isLoading ? 'Logging in...' : 'Sign in →')) ?>
          </button>
        </div>


      </form>

<div class="login-footer"><p>New to the portal? <a href="<?= e(url('register')) ?>">Create an account</a></p></div><div class="auth-note">For residents, administrators &amp; barangay personnel</div></section>
<div class="auth-bottom">Barangay Lower General Luna <span>A connected community. A barangay that cares.</span></div>
</div>