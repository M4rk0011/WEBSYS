<?php
require __DIR__ . '/../src/controllers/MapPlacesController.php';
require __DIR__ . '/partials/header.php';
?>
<section class="card">
 <div class="section-title"><div><h2>Barangay map markers</h2><p class="muted">Record hazard spots, evacuation centers, roads, clinics and landmarks.</p></div><a class="button" href="map.php">View map</a></div>
 <p class="notice">The red outline is approximate, not an official boundary. Confirm exact hazard zones, evacuation sites and routes with the barangay before selecting “Verified.” No unconfirmed hazard is displayed as official.</p>
 <div class="table-wrap"><table><thead><tr><th>Location</th><th>Kind</th><th>Validation</th><th>Actions</th></tr></thead><tbody>
 <?php foreach ($records as $row): ?>
  <tr><td><strong><?= e($row['name']) ?></strong><small class="location-summary"><?= e($row['description']) ?></small></td>
  <td><?= e(PLACE_KINDS[$row['kind']] ?? $row['kind']) ?></td>
  <td><span class="badge <?= $row['review_status'] === 'verified' ? 'resolved' : 'pending' ?>"><?= e($row['review_status'] === 'verified' ? 'Verified by staff' : 'Draft — staff only') ?></span></td>
  <td><a href="map-places.php?edit=<?= e($row['id']) ?>#editor">Edit</a></td></tr>
 <?php endforeach; ?></tbody></table></div>
 <?php if (!$records): ?><p class="muted">No map locations yet. Add the first verified spot after consulting the barangay.</p><?php endif; ?>
</section>
<section class="card" id="editor">
 <div class="section-title"><h2><?= $edit ? 'Edit map marker' : 'Add map marker' ?></h2><?php if ($edit): ?><a href="map-places.php#editor">+ New marker</a><?php endif; ?></div>
 <form method="post" enctype="multipart/form-data">
  <?php csrf_field(); ?><input type="hidden" name="action" value="save-place"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
  <div class="grid two">
  <label>Place name *<input required name="name" maxlength="160" value="<?= e($edit['name'] ?? '') ?>"></label>
  <label>Marker type<select name="kind">
    <?php foreach (PLACE_KINDS as $kind=>$label): ?><option value="<?= e($kind) ?>" <?= ($edit['kind'] ?? '') === $kind ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
  </select></label>
  <label>Hazard type (only for hazard markers)<select name="hazard_type">
    <?php foreach (HAZARD_TYPES as $kind=>$label): ?><option value="<?= e($kind) ?>" <?= ($edit['hazard_type'] ?? '') === $kind ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
  </select></label>
  <label>Review status<select name="review_status">
    <option value="draft" <?= ($edit['review_status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft — admin only</option>
    <option value="verified" <?= ($edit['review_status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified with barangay</option>
  </select></label>
  </div>
  <label>Details / directions<textarea name="description" rows="3" maxlength="1600"><?= e($edit['description'] ?? '') ?></textarea></label>
  <?php require __DIR__ . '/partials/map-area.php'; ?>
  <p class="muted">Click or drag a pin inside the red boundary. Exact coordinates are checked by the server.</p>
  <div id="location-map" data-edit="true" class="marker-editor-map" aria-label="Click map to choose marker location"></div>
  <div class="grid two">
  <label>Latitude *<input name="latitude" type="number" step="any" required value="<?= e($edit['latitude'] ?? '') ?>" placeholder="Click map"></label>
  <label>Longitude *<input name="longitude" type="number" step="any" required value="<?= e($edit['longitude'] ?? '') ?>" placeholder="Click map"></label>
  </div>
  <label>Place photo (optional, JPG/PNG/WebP, max 2MB)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
  <?php if ($edit && $edit['photo_name']): ?>
   <p><a href="<?= e(photoPlaceUrl($edit['id'])) ?>" target="_blank" rel="noopener"><img class="spot-preview" src="<?= e(photoPlaceUrl($edit['id'])) ?>" alt="Current location photo"></a></p>
   <label class="check"><input type="checkbox" name="remove_photo"> Remove current photo</label>
  <?php endif; ?>
  <button class="primary">Save map marker</button>
 </form>
 <?php if ($edit): ?><form method="post" class="danger-form">
   <?php csrf_field(); ?><input type="hidden" name="action" value="delete-place"><input type="hidden" name="id" value="<?= e($edit['id']) ?>">
   <button class="danger" data-confirm="Permanently delete this map marker?">Delete marker</button>
 </form><?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
