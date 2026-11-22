<?php
// Complaint map: handle the submitted form first, then display the HTML below.
$page = "map";
$pageTitle = "Complaint map";
require_once __DIR__ . "/../src/middleware/init.php";

if ($handleForm) {
    http_response_code(403);
    $error = "This page does not accept that form action.";
}

require __DIR__ . "/partials/header.php";

// $items already passed the role/ownership checks in visibleReports().
// The map adds an area filter; it never grants additional access to a record.
$mapItems = [];
foreach ($items as $complaint) {
    if (complaintInBarangay($complaint)) {
        $mapItems[] = $complaint;
    }
}
$outsideCount = count($items) - count($mapItems);
$mapData = [];
// The landmark/hazard list is permission filtered separately from private complaints.
$places = visiblePlaces();
$placeData = [];
foreach ($places as $p) {
    $placeData[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'kind' => $p['kind'],
        'kindLabel' => PLACE_KINDS[$p['kind']] ?? $p['kind'],
        'hazardLabel' => HAZARD_TYPES[$p['hazard_type']] ?? '',
        'description' => $p['description'],
        'lat' => (float)$p['latitude'],
        'lng' => (float)$p['longitude'],
        'review' => $p['review_status'],
        'photo' => $p['photo_name'] ? photoPlaceUrl($p['id']) : '',
    ];
}
foreach ($mapItems as $complaint) {
    $photoUrls = [];
    foreach ($complaint->images as $name) {
        $photoUrls[] = photoUrl($complaint->id, $name);
    }
    $mapData[] = [
        'id' => $complaint->id,
        'title' => $complaint->title,
        'reference' => $complaint->reference,
        'lat' => (float) $complaint->location->latitude,
        'lng' => (float) $complaint->location->longitude,
        'address' => $complaint->location->address,
        'status' => STATUSES[$complaint->status] ?? $complaint->status,
        'category' => CATEGORIES[$complaint->category] ?? $complaint->category,
        'resident' => $complaint->contactInfo->name,
        // Use the protected photo endpoint, never a direct storage/uploads URL.
        'photos' => $photoUrls,
    ];
}
?>
<section class="card">
<div class="section-title">
<div><h2>Complaints in Lower General Luna</h2>
<p class="muted"><?php if (isAdmin()): ?>All complaints inside the reporting area appear here.<?php else: ?>Only complaints your account can access appear here.<?php endif; ?> Select a pin or a complaint to view its photos.</p></div>
<span class="map-total"><?= count($mapItems) ?> complaint<?= count($mapItems) === 1 ? '' : 's' ?></span>
</div>
<?php if (isAdmin()): ?><a class="button" href="map-places.php">+ Manage locations &amp; hazards</a><?php endif; ?>
<div class="map-layer-switches" aria-label="Show map layers">
 <label><input type="checkbox" id="layer-complaints" checked> Complaints</label>
 <label><input type="checkbox" id="layer-places" checked> Hazards, evacuation sites &amp; landmarks</label>
</div>
<?php require __DIR__ . '/partials/map-area.php'; 
 if ($outsideCount): ?>
<p class="notice"><?= $outsideCount ?> existing complaint(s) have a location outside this area or missing coordinates. They remain available in <a href="complaints.php">Complaints</a>.</p>
<?php endif; ?>
<div class="complaint-map-layout">
<div class="map-column"><div id="location-map"></div></div>
<div class="map-complaint-list" aria-label="Complaints in the reporting area">
<?php if (!$mapItems): ?><p class="muted">No complaints in this area yet.</p><?php endif; 
 foreach ($mapItems as $complaint): ?>
<article class="map-complaint-card">
<?php if ($complaint->images): ?>
<a class="map-card-photo" href="<?= e(photoUrl($complaint->id, $complaint->images[0])) ?>" target="_blank" rel="noopener">
<img src="<?= e(photoUrl($complaint->id, $complaint->images[0])) ?>" alt="Photo evidence for <?= e($complaint->reference) ?>" loading="lazy">
<span><?= count($complaint->images) ?> photo<?= count($complaint->images) === 1 ? '' : 's' ?></span>
</a>
<?php else: ?><div class="map-no-photo">No photo attached</div><?php endif; ?>
<p class="eyebrow"><?= e($complaint->reference) ?></p>
<h3><?= e($complaint->title) ?></h3>
<?php badge($complaint); ?>
<p class="muted"><?= e($complaint->location->address) ?></p>
<div class="actions">
<button type="button" class="show-map-pin" data-report="<?= e($complaint->id) ?>" hidden>View pin &amp; photos</button>
<a href="<?= e(url('details', ['id' => $complaint->id])) ?>">Open complaint</a>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
<section class="map-spot-list"><div class="section-title"><h3>Community map locations</h3><strong><?= count($places) ?> spot<?= count($places) === 1 ? '' : 's' ?></strong></div>
<p class="muted">Only staff-verified spots are visible to residents. The project boundary is approximate. Confirm hazard information with barangay officials before relying on it.</p>
<?php if (!$places): ?><p class="muted">No staff-confirmed hazards, evacuation sites, or landmarks have been entered yet.</p><?php endif; ?>
<div class="spot-grid">
<?php foreach ($places as $p): ?>
<article class="spot-card"><div class="spot-row"><strong><?= e($p['name']) ?></strong><span class="badge <?= e($p['review_status']) ?>"><?= e(PLACE_KINDS[$p['kind']] ?? $p['kind']) ?></span></div>
<?php if ($p['photo_name']): ?><img class="spot-preview" loading="lazy" alt="<?= e($p['name']) ?> photo" src="<?= e(photoPlaceUrl($p['id'])) ?>"><?php endif; ?>
<?php if ($p['kind'] === 'hazard' && $p['hazard_type']): ?><p class="muted"><?= e(HAZARD_TYPES[$p['hazard_type']] ?? $p['hazard_type']) ?></p><?php endif; ?>
<p><?= nl2br(e($p['description'])) ?></p>
<?php if ($p['review_status'] === 'draft'): ?><small class="muted">Draft — not verified</small><?php endif; ?>
<button type="button" class="show-place-pin" data-place="<?= e($p['id']) ?>" hidden>Show on map</button>
</article>
<?php endforeach; ?></div></section>
<script type="application/json" id="map-places-data"><?= json_encode($placeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type="application/json" id="map-reports"><?= json_encode($mapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<noscript><p>Enable JavaScript to use the map. Complaint photos and links above remain available.</p></noscript>
</section>

<?php require __DIR__ . "/partials/footer.php"; ?>
