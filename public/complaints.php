<?php
// Complaints: handle the submitted form first, then display the HTML below.
$page = "complaints";
$pageTitle = "Complaints";
require_once __DIR__ . "/../src/middleware/init.php";

if ($handleForm) {
    http_response_code(403);
    $error = "This page does not accept that form action.";
}

require __DIR__ . "/partials/header.php";
?>
<section class="card">
<form method="get" class="filters">
<input type="hidden" name="page" value="complaints">
<label>Search title or reference
    <input type="text" name="q" value="<?= htmlspecialchars(is_string($_GET["q"] ?? null) ? $_GET["q"] : "") ?>" maxlength="500">
</label>
<?php

$fieldOptions = ["" => "All statuses"] + STATUSES;
$selectedValue = is_string($_GET["status"] ?? null) ? $_GET["status"] : "";
?>
<label>Status
    <select name="status">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = ["" => "All categories"] + CATEGORIES;
$selectedValue = is_string($_GET["category"] ?? null) ? $_GET["category"] : "";
?>
<label>Category
    <select name="category">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = ["" => "All departments"] + $deps;
$selectedValue = is_string($_GET["department"] ?? null) ? $_GET["department"] : "";
?>
<label>Department
    <select name="department">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php if (isAdmin()): ?>
<label>Submission review
<select name="approval">
  <?php foreach (['' => 'All reviews', 'pending' => 'Awaiting review', 'approved' => 'Approved', 'rejected' => 'Returned for correction'] as $value => $label): ?>
  <option value="<?= e($value) ?>" <?= (string)($_GET['approval'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
  <?php endforeach; ?>
</select></label>
<?php endif; ?>
<button class="primary">Filter</button>
</form>
</section>
<?php
// Keep only the rows matching the selected filters.
$filtered = [];
$search = is_string($_GET["q"] ?? null) ? $_GET["q"] : "";
foreach ($items as $complaint) {
    $matches = true;
    $filters = ["status" => "status", "category" => "category", "department" => "departmentId"];
    foreach ($filters as $inputName => $fieldName) {
        $selected = is_string($_GET[$inputName] ?? null) ? $_GET[$inputName] : "";
        if ($selected !== "" && $selected !== $complaint->$fieldName) {
            $matches = false;
        }
    }
    if (isAdmin() && in_array($_GET['approval'] ?? '', ['pending', 'approved', 'rejected'], true)
        && ($complaint->approvalStatus ?? 'approved') !== $_GET['approval']) {
        $matches = false;
    }
    if ($search !== "" && stripos($complaint->title . " " . $complaint->reference, $search) === false) {
        $matches = false;
    }
    if ($matches) {
        $filtered[] = $complaint;
    }
}
 ?><section class="card">
<div class="section-title">
<h2><?= count( $filtered) ?> complaints</h2>
<?php if (canSubmitComplaint()): ?>
<a href="<?= e(url("report")) ?>">+ New complaint</a>
<?php endif; ?>
</div><?php reportTable(
    $filtered,
); ?></section>

<?php require __DIR__ . "/partials/footer.php"; ?>
