<?php
/**
 * Public request handler or view for the businesses page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
require __DIR__ . '/../src/controllers/BusinessesController.php';
require __DIR__ . '/partials/header.php';
?>
<section class="card">
 <div class="section-title"><div><h2>Business directory &amp; CCTV status</h2><p class="muted">Search establishments, permits, owners and CCTV availability.</p></div><strong><?= count($records) ?> records</strong></div>
 <form method="get" class="filters">
  <label>Search<input type="search" name="q" value="<?= e($search) ?>" placeholder="Business, owner, permit or address" maxlength="100"></label>
  <label>CCTV<select name="cctv"><option value="">All</option>
   <?php foreach (['yes'=>'Has CCTV','no'=>'No CCTV','unknown'=>'Not yet checked'] as $value=>$label): ?>
     <option value="<?= e($value) ?>" <?= $filter === $value ? 'selected' : '' ?>><?= e($label) ?></option>
   <?php endforeach; ?></select></label>
  <button class="primary">Filter</button><a class="button" href="businesses.php">Clear</a><button type="button" onclick="window.print()">Print list</button>
 </form>
 <div class="table-wrap"><table><thead><tr><th>Establishment / owner</th><th>Address</th><th>Permit / renewal</th><th>CCTV</th><th>Actions</th></tr></thead><tbody>
 <?php foreach ($records as $row): ?>
 <tr><td><strong><?= e($row['business_name']) ?></strong><br><small><?= e($row['owner_name']) ?></small></td>
 <td><?= e($row['address']) ?></td>
 <td><?= e($row['permit_no'] ?: 'No permit recorded') ?><br><small class="<?= $row['renewal_date'] && $row['renewal_date'] < date('Y-m-d') ? 'late' : '' ?>"><?= e($row['renewal_date'] ?: 'No renewal date') ?></small></td>
 <td><span class="badge"><?= e(['yes'=>'Has CCTV','no'=>'No CCTV','unknown'=>'Not checked'][$row['cctv_status']] ?? 'Not checked') ?></span>
 <?php if ($row['cctv_status'] === 'yes'): ?><small><?= (int)$row['cctv_count'] ?> camera(s)</small><?php endif; ?></td>
 <td><a href="businesses.php?edit=<?= e($row['id']) ?>#editor">Edit</a></td></tr>
 <?php endforeach; ?></tbody></table></div>
 <?php if (!$records): ?><p class="muted">No businesses match. Add one below.</p><?php endif; ?>
</section>
<section class="card" id="editor">
 <div class="section-title"><h2><?= $edit ? 'Edit establishment' : 'Add establishment' ?></h2><?php if ($edit): ?><a href="businesses.php#editor">+ New record</a><?php endif; ?></div>
 <form method="post" autocomplete="off">
 <?php csrf_field(); ?><input type="hidden" name="action" value="save-business"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
 <div class="grid two">
  <label>Business name *<input name="business_name" required maxlength="180" value="<?= e($edit['business_name'] ?? '') ?>"></label>
  <label>Owner / representative<input name="owner_name" maxlength="160" value="<?= e($edit['owner_name'] ?? '') ?>"></label>
  <label>Business category<input name="business_type" maxlength="120" value="<?= e($edit['business_type'] ?? '') ?>" placeholder="e.g. retail, food service"></label>
  <label>Address *<input name="address" required maxlength="250" value="<?= e($edit['address'] ?? '') ?>"></label>
  <label>Contact<input name="contact" maxlength="100" value="<?= e($edit['contact'] ?? '') ?>"></label>
  <label>Permit / reference number<input name="permit_no" maxlength="100" value="<?= e($edit['permit_no'] ?? '') ?>"></label>
  <label>Next permit renewal<input type="date" name="renewal_date" value="<?= e($edit['renewal_date'] ?? '') ?>"></label>
  <label>CCTV availability<select name="cctv_status" id="business-cctv">
   <?php foreach (['unknown'=>'Not yet checked','yes'=>'Has CCTV','no'=>'No CCTV'] as $value=>$label): ?>
    <option value="<?= e($value) ?>" <?= ($edit['cctv_status'] ?? 'unknown') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
   <?php endforeach; ?></select></label>
  <label>CCTV camera count<input type="number" min="0" max="9999" name="cctv_count" value="<?= e($edit['cctv_count'] ?? '0') ?>"></label>
 </div>
 <label>Notes<textarea name="notes" rows="2" maxlength="1500"><?= e($edit['notes'] ?? '') ?></textarea></label>
 <p class="muted">This tracks whether a business reports CCTV equipment. It does not connect to or monitor live CCTV footage.</p>
 <button class="primary">Save establishment</button>
 </form>
 <?php if ($edit): ?><form method="post" class="danger-form">
  <?php csrf_field(); ?><input type="hidden" name="action" value="delete-business"><input type="hidden" name="id" value="<?= e($edit['id']) ?>">
  <button class="danger" data-confirm="Delete this establishment profile?">Delete establishment</button></form><?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
