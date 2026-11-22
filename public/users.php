<?php
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/UsersController.php";
require __DIR__ . "/partials/header.php";

?>
<section class="card">
<h2>Create an account</h2>
<p class="muted">Residents register themselves. Administrators verify them after checking residency through barangay procedures.</p>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="create-user">
    <input type="hidden" name="id" value="">
<?php

$roleOptions = ROLES;
if (role() !== "system_admin") {
    unset($roleOptions["system_admin"], $roleOptions["lgu_admin"]);
} else {
    unset($roleOptions["system_admin"]);
}
?><div class="grid two">
<label>Username
    <input type="text" name="username" value="" maxlength="500" required>
</label>

<label>Email
    <input type="email" name="email" value="" maxlength="500" required>
</label>

<label>First name
    <input type="text" name="firstName" value="" maxlength="500" required>
</label>

<label>Last name
    <input type="text" name="lastName" value="" maxlength="500" required>
</label>

<label>Temporary password
    <input type="password" name="password" value="" maxlength="500" required minlength="8">
</label>
<?php

$fieldOptions = $roleOptions;
$selectedValue = "resident";
?>
<label>Role
    <select name="role">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = $deps;
$selectedValue = "";
?>
<label>Staff department
    <select name="departmentId">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
</div>
<label class="check">
<input type="checkbox" name="active" checked> Active account</label>
<label class="check">
<input type="checkbox" name="verified"> Resident verified</label>
<button class="primary">Create account</button>
</form>
</section>
<section class="card">
<h2>Accounts & verification</h2><?php foreach (
    users()
    as $user
): ?><details class="user">
<summary>
<strong><?= e($user->profile->firstName . " " . $user->profile->lastName) ?></strong> · <?= e(ROLES[role($user)] ?? role($user)) ?> · <?= $user->verified ?? false ? "Verified" : "Unverified" ?> · <?= $user->isActive ? "Active" : "Inactive" ?></summary>
<p><?= e($user->email) ?> · <?= e($user->profile->phone ?? "") ?><br><?= e($user->profile->address ?? "No address supplied") ?></p>
<?php if (
    $user->id !== $currentUser->id &&
    role($user) !== "system_admin" &&
    (role() === "system_admin" || !isAdminUser($user))
) {
    ?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="update-user">
    <input type="hidden" name="id" value="<?= htmlspecialchars($user->id) ?>">
<div class="grid two">
<label>Username
    <input type="text" name="username" value="<?= htmlspecialchars($user->username) ?>" maxlength="500" required>
</label>

<label>Email
    <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" maxlength="500" required>
</label>

<label>First name
    <input type="text" name="firstName" value="<?= htmlspecialchars($user->profile->firstName) ?>" maxlength="500" required>
</label>

<label>Last name
    <input type="text" name="lastName" value="<?= htmlspecialchars($user->profile->lastName) ?>" maxlength="500" required>
</label>

<label>Reset password (optional)
    <input type="password" name="password" value="" maxlength="500" minlength="8">
</label>
<?php

$fieldOptions = $roleOptions;
$selectedValue = role($user);
?>
<label>Role
    <select name="role">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = $deps;
$selectedValue = $user->departmentId ?? "";
?>
<label>Staff department
    <select name="departmentId">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
</div>
<label class="check">
<input type="checkbox" name="active" <?= $user->isActive ? "checked" : "" ?>> Active</label>
<label class="check">
<input type="checkbox" name="verified" <?= $user->verified ?? false ? "checked" : "" ?>> Residency verified</label>
<button class="primary">Save account</button>
</form><?php
} else {
     ?><p class="muted">Protected account. Edit your own contact details in My profile.</p><?php
} ?></details><?php endforeach; ?></section>

<?php require __DIR__ . "/partials/footer.php"; ?>
