<?php
/**
 * Public request handler or view for the profile page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/ProfileController.php";
require __DIR__ . "/partials/header.php";

?>
<section class="card narrow">
<span class="badge"><?= $currentUser->verified ?? false ? "Verified account" : "Awaiting verification" ?></span>
<p>Username: <strong><?= e($currentUser->username) ?></strong> · <?= e(ROLES[role()] ?? role()) ?></p>

<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="profile">
    <input type="hidden" name="id" value="">
<div class="grid two">
<label>First name
    <input type="text" name="firstName" value="<?= htmlspecialchars($currentUser->profile->firstName) ?>" maxlength="500" required>
</label>

<label>Last name
    <input type="text" name="lastName" value="<?= htmlspecialchars($currentUser->profile->lastName) ?>" maxlength="500" required>
</label>

<label>Email
    <input type="email" name="email" value="<?= htmlspecialchars($currentUser->email) ?>" maxlength="500" required>
</label>

<label>Phone
    <input type="tel" name="phone" value="<?= htmlspecialchars($currentUser->profile->phone ?? "") ?>" maxlength="500" required>
</label>
</div>
<label>Residential address
    <input type="text" name="address" value="<?= htmlspecialchars($currentUser->profile->address ?? "") ?>" maxlength="500" required>
</label>
<h3>Change password (optional)</h3>
<label>Current password
    <input type="password" name="currentPassword" value="" maxlength="500">
</label>

<label>New password
    <input type="password" name="password" value="" maxlength="500" minlength="8">
</label>
<button class="primary">Save profile</button>
</form>
</section>

<?php require __DIR__ . "/partials/footer.php"; ?>
