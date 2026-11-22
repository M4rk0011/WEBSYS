<?php
/**
 * Public request handler or view for the details page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/DetailsController.php";
require __DIR__ . "/partials/header.php";

?>
<section class="card">
<div class="section-title">
<div>
<p class="eyebrow"><?= e($record->reference) ?></p>
<h2><?= e($record->title) ?></h2>
</div><?php badge(
    $record,
); ?></div>
<p class="description"><?= nl2br(e($record->description)) ?></p>
<p><strong>Submission review:</strong>
  <span class="badge <?= e($record->approvalStatus) ?>"><?= e(ucfirst($record->approvalStatus)) ?></span>
  <?php if ($record->approvalNote): ?><span class="muted"> — <?= e($record->approvalNote) ?></span><?php endif; ?>
</p>
<?php if ($record->approvalStatus !== 'approved' && role() === 'resident'): ?>
<p class="notice">Your complaint is <?= $record->approvalStatus === 'pending' ? 'awaiting administrator review' : 'not approved yet' ?>. You can edit and resubmit it while the complaint is still pending.</p>
<?php endif; ?>
<div class="grid two">
<div>
<h3>Complaint information</h3>
<p>Category: <?= e(CATEGORIES[$record->category] ?? ucfirst($record->category)) ?><br>Priority: <?= e(ucfirst($record->severity)) ?><br>Submitted: <?= e(fmtdate($record->reportedAt)) ?><br>Location: <?= e($record->location->address) ?><br>Coordinates: <?= e($record->location->latitude . ", " . $record->location->longitude) ?></p>
</div>
<div>
<h3>Service assignment</h3>
<p>Department: <?= e(departmentName($record->departmentId)) ?><br>Responsible personnel: <?= e(staffName($record->assignedTo)) ?><br>Deadline: <span class="<?= overdue($record) ? "late" : "" ?>"><?= e($record->deadline ?: "Not set") ?></span>
<br>First response: <?= e(fmtdate($record->firstResponseAt) ?: "Awaiting response") ?><br>Resolved: <?= e(fmtdate($record->resolvedAt) ?: "Not resolved") ?></p>
</div>
</div>
<p>Resident: <?= e($record->contactInfo->name) ?> · <?= e($record->contactInfo->email) ?> · <?= e($record->contactInfo->phone) ?></p>
<div class="photos"><?php foreach ($record->images as $photo): ?><a href="<?= e(photoUrl($record->id, $photo)) ?>" target="_blank" rel="noopener">
<img src="<?= e(photoUrl($record->id, $photo)) ?>" alt="Complaint photo evidence">
</a><?php endforeach; ?></div>
<div class="actions">
<a class="button" href="export.php?id=<?= e($record->id) ?>">Download PDF</a><?php
if (canEditConcernContent($record)): ?><a class="button" href="<?= e(url("report", ["id" => $record->id])) ?>">Edit complaint</a><?php endif;
if (isAdmin()) {
    ?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="delete-report">
    <input type="hidden" name="id" value="<?= htmlspecialchars($record->id) ?>">
<button class="danger" data-confirm="Delete this complaint and its photos permanently?">Delete</button>
</form><?php
}
?></div>
</section>
<?php if (isAdmin() && $record->status === 'pending' && $record->approvalStatus !== 'approved'): ?>
<section class="card">
<h2>Administrator review</h2>
<p class="muted">Approve a submission before assigning it to personnel. Rejected submissions can be edited and resubmitted by residents.</p>
<form method="post">
<?php csrf_field(); ?><input type="hidden" name="action" value="approval"><input type="hidden" name="id" value="<?= e($record->id) ?>">
<label>Decision<select name="approvalStatus">
<option value="approved">Approve submission</option><option value="rejected">Reject / return for correction</option>
</select></label>
<label>Note (required for rejection)<textarea name="approvalNote" rows="2" maxlength="1000" placeholder="Reason or instructions"></textarea></label>
<button class="primary">Save review decision</button>
</form>
</section>
<?php endif; ?>
<?php if (isApproved($record) && canAssign($record)): ?><section class="card">
<h2>Department assignment</h2>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="assign">
    <input type="hidden" name="id" value="<?= htmlspecialchars($record->id) ?>">
<?php

$assignDeps =
    role() === "department_head"
        ? [$currentUser->departmentId => departmentName($currentUser->departmentId)]
        : $deps;
$staff = ["" => "Unassigned"];
foreach (users() as $user) {
    if (
        $user->isActive &&
        in_array(role($user), ["department_head", "personnel"], true) &&
        (role() !== "department_head" || ($user->departmentId ?? "") === $currentUser->departmentId)
    ) {
        $staff[$user->id] =
            $user->profile->firstName .
            " " .
            $user->profile->lastName .
            " · " .
            departmentName($user->departmentId ?? "");
    }
}
?><div class="grid two"><?php
$fieldOptions = $assignDeps;
$selectedValue = $record->departmentId;
?>
<label>Department
    <select name="departmentId">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = $staff;
$selectedValue = $record->assignedTo;
?>
<label>Responsible personnel (must match department)
    <select name="assignedTo">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = PRIORITIES;
$selectedValue = $record->severity;
?>
<label>Priority
    <select name="severity">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>

<label>Deadline
    <input type="date" name="deadline" value="<?= htmlspecialchars($record->deadline) ?>" maxlength="500" required>
</label>
</div>
<button class="primary">Save assignment</button>
</form>
</section><?php endif; 
 if (
    isApproved($record) && canProcess($record) &&
    (!finished($record) || isAdmin())
): ?><section class="card">
<h2>Process complaint</h2>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="status">
    <input type="hidden" name="id" value="<?= htmlspecialchars($record->id) ?>">
<?php

$next = [
    "pending" => ["under_investigation", "rejected"],
    "under_investigation" => ["action_taken", "resolved", "rejected"],
    "action_taken" => ["under_investigation", "resolved", "rejected"],
    "resolved" => ["under_investigation"],
    "rejected" => ["under_investigation"],
];
$choices = [];
foreach ($next[$record->status] ?? [] as $statement) {
    $choices[$statement] = STATUSES[$statement];
}
$fieldOptions = $choices;
$selectedValue = "";
?>
<label>Next status
    <select name="status">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<label>Action taken / reason<textarea name="note" rows="3" required maxlength="5000">
</textarea>
</label>
<button class="primary">Record status update</button>
</form>
</section><?php endif; ?>
<section class="card">
<h2>Resolution feedback</h2><?php if ($record->feedback): ?><p>Satisfaction: <strong><?= e($record->feedback->rating) ?>/5</strong> · Service evaluation: <strong><?= e($record->feedback->serviceRating) ?>/5</strong>
</p>
<p><?= nl2br(e($record->feedback->comment)) ?></p>
<small><?= e(fmtdate($record->feedback->at)) ?></small><?php elseif (role() === "resident" && owns($record) && $record->status === "resolved"):
    ?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="feedback">
    <input type="hidden" name="id" value="<?= htmlspecialchars($record->id) ?>">
<div class="grid two"><?php
$fieldOptions = [
    5 => "5 — Very satisfied",
    4 => "4 — Satisfied",
    3 => "3 — Neutral",
    2 => "2 — Dissatisfied",
    1 => "1 — Very dissatisfied",
];
$selectedValue = "";
?>
<label>Resolution satisfaction
    <select name="rating">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<?php

$fieldOptions = [
    5 => "5 — Excellent",
    4 => "4 — Good",
    3 => "3 — Average",
    2 => "2 — Poor",
    1 => "1 — Very poor",
];
$selectedValue = "";
?>
<label>Overall service
    <select name="serviceRating">
        <?php foreach ($fieldOptions as $optionValue => $optionText): ?>
            <option value="<?= htmlspecialchars((string) $optionValue) ?>" <?= (string) $optionValue === (string) $selectedValue ? "selected" : "" ?>><?= htmlspecialchars($optionText) ?></option>
        <?php endforeach; ?>
    </select>
</label>
</div>
<label>Feedback comments<textarea name="comment" rows="3" required maxlength="3000">
</textarea>
</label>
<button class="primary">Send feedback</button>
</form><?php
else:
     ?><p class="muted">The resident can submit one evaluation after this complaint is resolved.</p><?php
endif; ?></section>
<section class="card">
<h2>Activity history</h2><?php
if (!$record->history) {
    echo "<p>No recorded history for this imported complaint.</p>";
}
foreach (array_reverse($record->history) as $entry): ?><article class="timeline">
<strong><?= e($entry->event) ?></strong>
<small><?= e(fmtdate($entry->at)) ?> · <?= e(staffName($entry->by)) ?></small>
<p><?= nl2br(e($entry->note)) ?></p>
</article><?php endforeach;
?></section>

<?php require __DIR__ . "/partials/footer.php"; ?>
