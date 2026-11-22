<?php
// Only published, not-yet-dismissed messages are selected for this signed-in user.
$announcement = nextAnnouncement($currentUser);

 if ($announcement): ?>
<dialog open id="announcement-popup" class="announcement-popup" aria-labelledby="announcement-heading">
    
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="dismiss-announcement">
    <input type="hidden" name="id" value="<?= htmlspecialchars($announcement['id']) ?>">

        <input type="hidden" name="version" value="<?= e(announcementVersion($announcement)) ?>">
        <button type="submit" class="announcement-close" aria-label="Dismiss announcement" autofocus>×</button>
    </form>
    <p class="eyebrow">BARANGAY ANNOUNCEMENT</p>
    <span class="badge"><?= e($announcement['type']) ?></span>
    <h2 id="announcement-heading"><?= e($announcement['title']) ?></h2>
    <p class="announcement-body"><?= nl2br(e($announcement['body'])) ?></p>
    <small>Published <?= e(fmtdate($announcement['created_at'])) ?></small>
    <p class="muted">You can read this again on the Announcements page.</p>
</dialog>
<?php endif; ?>
