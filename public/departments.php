<?php
/**
 * Public request handler or view for the departments page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Page display only. The controller above validates submitted forms and prepares data.
require __DIR__ . "/../src/controllers/DepartmentsController.php";
require __DIR__ . "/partials/header.php";

?>
<section class="card narrow">
<h2>Add department / service desk</h2>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="department">
    <input type="hidden" name="id" value="">

<label>Department name
    <input type="text" name="name" value="" maxlength="500" required>
</label>
<button class="primary">Add department</button>
</form>
</section>
<section class="card">
<h2>Department directory</h2>
<p class="muted">The initial service desks are examples. Rename them to match your barangay organization.</p><?php foreach (
    departments()
    as $d
) {
    ?>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="department">
    <input type="hidden" name="id" value="<?= htmlspecialchars($d["id"]) ?>">
<div class="filters">
<label>Department
    <input type="text" name="name" value="<?= htmlspecialchars($d["name"]) ?>" maxlength="500" required>
</label>
<button>Rename</button>
</div>
</form><?php
} ?></section>

<?php require __DIR__ . "/partials/footer.php"; ?>
