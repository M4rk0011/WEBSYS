<?php
/**
 * Public request handler or view for the residents page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// A private administrative resident registry; it is not the public account list.
require __DIR__ . '/../src/controllers/ResidentsController.php';
require __DIR__ . '/partials/header.php';
?>
<section class="card">
  <div class="section-title"><div><h2>Resident directory</h2><p class="muted">Secretary-only records. Search by resident name, address or household number.</p></div><strong><?= count($records) ?> records</strong></div>
  <form method="get" class="filters">
    <label>Search residents<input type="search" name="q" value="<?= e($search) ?>" placeholder="Name, address or household" maxlength="100"></label>
    <button class="primary">Search</button><a class="button" href="residents.php">Clear</a>
    <button type="button" onclick="window.print()">Print list</button>
  </form>
  <div class="table-wrap"><table><thead><tr><th>Resident</th><th>Household</th><th>Address</th><th>Contact</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($records as $row): ?>
    <tr><td><strong><?= e($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']) ?></strong></td>
    <td><?= e($row['household_no'] ?: '—') ?></td><td><?= e($row['address']) ?></td><td><?= e($row['phone'] ?: '—') ?></td>
    <td><a href="residents.php?edit=<?= e($row['id']) ?>">Edit</a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php if (!$records): ?><p class="muted">No residents found. Enter the first record below.</p><?php endif; ?>
</section>
<section class="card" id="editor">
  <div class="section-title"><h2><?= $edit ? 'Edit resident' : 'Add resident' ?></h2><?php if ($edit): ?><a href="residents.php#editor">+ New record</a><?php endif; ?></div>
  <p class="muted">Only authorized barangay administrators can view and edit this personal information. Record details only with an appropriate lawful purpose and barangay approval.</p>
  <form method="post" autocomplete="off">
    <?php csrf_field(); ?><input type="hidden" name="action" value="save-resident"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
    <div class="grid two">
    <label>First name *<input name="first_name" required maxlength="100" value="<?= e($edit['first_name'] ?? '') ?>"></label>
    <label>Last name *<input name="last_name" required maxlength="100" value="<?= e($edit['last_name'] ?? '') ?>"></label>
    <label>Middle name<input name="middle_name" maxlength="100" value="<?= e($edit['middle_name'] ?? '') ?>"></label>
    <label>Household number<input name="household_no" maxlength="80" value="<?= e($edit['household_no'] ?? '') ?>"></label>
    <label>Birth date (optional)<input type="date" name="birth_date" value="<?= e($edit['birth_date'] ?? '') ?>" max="<?= date('Y-m-d') ?>"></label>
    <label>Sex (optional)<select name="sex">
       <?php foreach ([''=>'Not recorded','female'=>'Female','male'=>'Male','not_specified'=>'Prefer not to say'] as $value=>$label): ?>
        <option value="<?= e($value) ?>" <?= ($edit['sex'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
       <?php endforeach; ?>
    </select></label>
    <label>Phone<input name="phone" maxlength="40" value="<?= e($edit['phone'] ?? '') ?>"></label>
    <label>Address in the barangay *<input name="address" required maxlength="250" value="<?= e($edit['address'] ?? '') ?>"></label>
    </div>
    <label>Notes (optional)<textarea name="notes" maxlength="1500" rows="2"><?= e($edit['notes'] ?? '') ?></textarea></label>
    <button class="primary">Save resident</button>
  </form>
  <?php if ($edit): ?><form method="post" class="danger-form" data-danger-form>
    <?php csrf_field(); ?><input type="hidden" name="action" value="delete-resident"><input type="hidden" name="id" value="<?= e($edit['id']) ?>">
    <button class="danger" data-confirm="Delete this resident profile permanently?">Delete resident</button></form><?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
