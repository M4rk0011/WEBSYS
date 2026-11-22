<?php
/**
 * Public request handler or view for the announcements page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/AnnouncementsController.php";
require __DIR__ . "/partials/header.php";


 if (isAdmin()): ?><section class="card">
<h2>Publish an update</h2>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="announcement">
    <input type="hidden" name="id" value="">

<label>Title
    <input type="text" name="title" value="" maxlength="500" required>
</label>
<?php

$fieldOptions = [
    "Service advisory" => "Service advisory",
    "Public announcement" => "Public announcement",
    "Community update" => "Community update",
];
$selectedValue = "";
?>
<label>Type
    <select name="type">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<label>Message<textarea name="body" rows="4" maxlength="10000" required>
</textarea>
</label>
<label class="check">
<input type="checkbox" name="published" checked> Publish now</label>
<button class="primary">Save announcement</button>
</form>
</section><?php endif; 

$news = announcements(!isAdmin());
if (!$news): ?><section class="card">
<p>No public announcements yet.</p>
</section><?php endif;
foreach ($news as $a): ?><article class="card">
<span class="badge"><?= e($a["type"]) ?></span>
<small><?= e(fmtdate($a["created_at"])) ?> <?= $a["published"] ? "" : " · Draft" ?></small>
<h2><?= e($a["title"]) ?></h2>
<p class="description"><?= nl2br( e($a["body"])) ?></p><?php if (isAdmin()): ?><details>
<summary>Edit announcement</summary>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="announcement">
    <input type="hidden" name="id" value="<?= htmlspecialchars($a["id"]) ?>">

<label>Title
    <input type="text" name="title" value="<?= htmlspecialchars($a["title"]) ?>" maxlength="500" required>
</label>
<?php

$fieldOptions = [
        "Service advisory" => "Service advisory",
        "Public announcement" => "Public announcement",
        "Community update" => "Community update",
    ];
$selectedValue = $a["type"];
?>
<label>Type
    <select name="type">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<label>Message<textarea name="body" rows="4" maxlength="10000" required><?= e($a["body"]) ?></textarea>
</label>
<label class="check">
<input type="checkbox" name="published" <?= $a[ "published" ] ? "checked" : "" ?>> Published</label>
<button class="primary">Save changes</button>
</form>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="delete-announcement">
    <input type="hidden" name="id" value="<?= htmlspecialchars($a["id"]) ?>">
<button class="danger" data-confirm="Delete this announcement?">Delete announcement</button>
</form>
</details><?php endif; ?></article><?php endforeach;
 
 require __DIR__ . "/partials/footer.php"; ?>
