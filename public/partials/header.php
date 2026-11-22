<?php
// Shared page frame: menus, title and notices. Page forms stay in the named PHP files.
$items = [];
if ($currentUser) {
    $items = visibleReports();
}
$stats = metrics($items);
$deps = ["" => "Unassigned"];
foreach (departments() as $department) {
    $deps[$department["id"]] = $department["name"];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> · Lower General Luna</title>
<link rel="stylesheet" href="assets/css/app.css">
<link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
</head>
<body>
<?php if (!$authPage): ?>
<aside class="sidebar">
<a class="brand" href="index.php">
<img src="assets/img/lower-general-luna-logo.png" alt="Lower General Luna barangay logo" class="brand-logo">
<div>Lower General Luna<small>Citizen Services</small>
</div>
</a>
<p class="nav-label">COMMUNITY WORKSPACE</p>
<nav>
<?php
$nav = $currentUser
    ? [
        "dashboard" => "Overview",
        "complaints" => "Complaints",
        "map" => "Location map",
        "announcements" => nextAnnouncement($currentUser) ? "Announcements · New" : "Announcements",
        "profile" => "My profile",
    ]
    : ["announcements" => "Announcements", "login" => "Sign in"];
if (canSubmitComplaint()) {
    $nav["report"] = "+ Submit complaint";
}
if (canViewAnalytics()) {
    $nav += ["monitoring" => "Service monitoring", "analytics" => "Reports & analytics"];
}
if (isAdmin()) {
    $nav += [
        "users" => "Accounts & verification",
        "departments" => "Departments",
        "residents" => "Inhabitants profiling",
        "businesses" => "Business & CCTV records",
        "map-places" => "Map spots & hazards",
    ];
}
foreach ($nav as $p => $label): ?><a class="<?= $page === $p ? "active" : "" ?>" href="<?= e(url($p)) ?>"><?= e($label) ?></a><?php endforeach;
?></nav><?php if ($currentUser): ?><div class="account">
<strong><?= e($currentUser->profile->firstName) ?></strong>
<small><?= e(ROLES[role()] ?? role()) ?></small>
<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
    <input type="hidden" name="action" value="logout">
    <input type="hidden" name="id" value="">
<button type="submit">Sign out</button>
</form>
</div><?php endif; ?></aside>
<main class="workspace">
<header>
<div>
<p class="eyebrow">BARANGAY LOWER GENERAL LUNA</p>
<h1><?= e($pageTitle) ?></h1>
</div>
<span class="date"><?= date("M j, Y") ?></span>
</header>
<?php
if ($flash): ?><div class="notice" role="status"><?= e($flash) ?></div><?php endif;
if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif;

 if (
    $currentUser &&
    role() === "resident" &&
    !($currentUser->verified ?? false)
): ?><div class="notice">Your account is awaiting administrator verification. You can edit your profile and read announcements; complaint submission is available after verification.</div><?php endif; 
 endif; ?>
