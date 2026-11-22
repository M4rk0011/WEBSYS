<?php
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/ReportController.php";
require __DIR__ . "/partials/header.php";


 if (
    role() === "resident" &&
    !($currentUser->verified ?? false)
): ?><section class="card">
<p>Please wait for your account to be verified before submitting a complaint.</p>
</section><?php else: ?>
<section class="card">
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="save-report">
    <input type="hidden" name="id" value="<?= htmlspecialchars($record->id ?? "") ?>">
<?php

// Keep the submitted values visible when a validation error occurs.
$form = [
    "title" => $record->title ?? "",
    "description" => $record->description ?? "",
    "category" => $record->category ?? "other",
    "severity" => $record->severity ?? "medium",
    "address" => $record->location->address ?? "",
    "latitude" => $record->location->latitude ?? "",
    "longitude" => $record->location->longitude ?? "",
];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($form as $field => $value) {
        $form[$field] = post($field);
    }
}
?>
<label>Complaint title
    <input type="text" name="title" value="<?= htmlspecialchars($form["title"]) ?>" maxlength="500" required>
</label>

<label>Description of concern<textarea name="description" rows="5" maxlength="10000" required><?= e($form["description"]) ?></textarea>
</label>
<div class="grid two"><?php
$fieldOptions = CATEGORIES;
$selectedValue = $form["category"];
?>
<label>Complaint category
    <select name="category">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = PRIORITIES;
$selectedValue = $form["severity"];
?>
<label>Suggested priority
    <select name="severity">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
</div>

<label>Location details / landmark
    <input type="text" name="address" value="<?= htmlspecialchars($form["address"]) ?>" maxlength="500" required>
</label>

<p class="muted">Click inside the red line to choose the complaint location. You can drag the pin or type coordinates; locations outside the reporting area cannot be saved.</p>
<div id="location-map" data-edit="true" data-lat="<?= e($form["latitude"]) ?>" data-lng="<?= e($form["longitude"]) ?>">
</div>
<?php require __DIR__ . "/partials/map-area.php"; ?>
<div class="grid two">
<label>Latitude
    <input type="number" name="latitude" value="<?= htmlspecialchars($form["latitude"]) ?>" maxlength="500" required step="any">
</label>

<label>Longitude
    <input type="number" name="longitude" value="<?= htmlspecialchars($form["longitude"]) ?>" maxlength="500" required step="any">
</label>
</div>
<label>Photo evidence (up to 5 JPG, PNG or WebP images, 2 MB each)<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
</label>
<div id="photo-preview" class="photos">
</div>
<?php if ($record) {
    foreach ($record->images as $photo): ?><label class="photo-remove">
<img src="<?= e(photoUrl($record->id, $photo)) ?>" alt="Existing complaint evidence">
<span>
<input type="checkbox" name="removePhotos[]" value="<?= e($photo) ?>"> Remove this photo</span>
</label><?php endforeach;
} ?>
<p class="muted">Your contact details are shared only with authorized staff. A submission reference is generated automatically.</p>
<button class="primary">Save complaint</button>
</form>
</section><?php endif; 
 require __DIR__ . "/partials/footer.php"; ?>
