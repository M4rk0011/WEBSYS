<?php
require_once __DIR__ . '/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/dbconn.php';
try {
    $stmt = $pdo->prepare('SELECT fullname, email, address, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(503);
    exit('Unable to load your account. Check that database.sql has been imported.');
}
if (!$user) {
    unset($_SESSION['user_id']);
    header('Location: login.php');
    exit;
}
$fullName = trim((string) $user['fullname']);
$firstName = explode(' ', $fullName)[0] ?: 'Resident';
$joinedTimestamp = strtotime((string) $user['created_at']);
$joinedDate = $joinedTimestamp ? date('M j, Y', $joinedTimestamp) : (string) $user['created_at'];
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Barangay Lower General Luna</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-design.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" aria-label="Resident portal sidebar">
            <a class="sidebar-brand" href="dashboard.php">
                <span class="sidebar-symbol" aria-hidden="true">LG</span>
                <span><strong>Lower General Luna</strong><small>RESIDENT PORTAL</small></span>
            </a>
            <div class="sidebar-group">
                <span class="sidebar-label">WORKSPACE</span>
                <nav aria-label="Resident portal navigation">
                    <a href="dashboard.php" class="sidebar-link active" aria-current="page"><span class="sidebar-icon" aria-hidden="true">⌂</span> Overview</a>
                    <div class="sidebar-placeholder"><span class="sidebar-icon" aria-hidden="true">✎</span> Submit a concern <small>Soon</small></div>
                    <div class="sidebar-placeholder"><span class="sidebar-icon" aria-hidden="true">◎</span> Track a concern <small>Soon</small></div>
                    <div class="sidebar-placeholder"><span class="sidebar-icon" aria-hidden="true">✧</span> Announcements <small>Soon</small></div>
                </nav>
            </div>
            <div class="sidebar-bottom">
                <span class="sidebar-bottom-icon" aria-hidden="true">✦</span>
                <strong>Growing together</strong>
                <p>Building a simpler way to connect our community.</p>
                <span class="sidebar-bottom-tag">Community first</span>
            </div>
        </aside>

        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="dashboard-breadcrumb"><span>Resident portal</span><span aria-hidden="true">/</span><strong>Overview</strong></div>
                <div class="dashboard-topbar-right">
                    <span class="topbar-account"><span class="account-avatar" aria-hidden="true"><?= escape($initial) ?></span><span><?= escape($fullName) ?></span></span>
                    <form method="post" action="logout.php" class="dashboard-logout">
                        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                        <button type="submit" class="dashboard-logout-button">Log out</button>
                    </form>
                </div>
            </header>

            <main class="dashboard-content">
                <div class="dashboard-page-title"><div><span class="section-eyebrow">YOUR SPACE</span><h1>Dashboard overview</h1><p>Welcome to your community portal.</p></div><span class="dashboard-date"><?= escape(date('l, F j, Y')) ?></span></div>

                <section class="dashboard-welcome" aria-labelledby="welcome-title">
                    <div class="dashboard-welcome-copy">
                        <span class="dashboard-welcome-tag"><span class="status-dot" aria-hidden="true"></span> WE'RE GLAD YOU'RE HERE</span>
                        <h2 id="welcome-title">Good day, <?= escape($firstName) ?>!</h2>
                        <p>One community, many voices. Thank you for being part of Barangay Lower General Luna.</p>
                        <a class="dashboard-hero-link" href="#resident-profile">View your profile <span aria-hidden="true">↗</span></a>
                    </div>
                    <img src="assets/community-hero.svg" alt="" class="dashboard-welcome-art" width="860" height="560">
                </section>

                <section class="dashboard-status-grid" aria-label="Your account at a glance">
                    <div class="status-card"><span class="status-icon" aria-hidden="true">✓</span><div><span class="status-label">SESSION STATUS</span><strong>Signed in</strong><small>Your account is accessible</small></div></div>
                    <div class="status-card"><span class="status-icon" aria-hidden="true">⌂</span><div><span class="status-label">ACCOUNT TYPE</span><strong>Resident</strong><small>Community member</small></div></div>
                    <div class="status-card"><span class="status-icon" aria-hidden="true">▦</span><div><span class="status-label">MEMBER SINCE</span><strong><?= escape($joinedDate) ?></strong><small>Account registration date</small></div></div>
                </section>

                <div class="dashboard-two-column">
                    <section class="dashboard-panel" aria-labelledby="services-heading">
                        <div class="dashboard-panel-head"><div><span class="section-eyebrow">WHAT'S NEXT</span><h2 id="services-heading">Resident services</h2></div><span class="panel-note">In development</span></div>
                        <p class="panel-intro">We're preparing features to make communication with the barangay more convenient.</p>
                        <div class="service-preview"><span class="service-bubble" aria-hidden="true">✎</span><div><strong>Concern submission</strong><p>Send concerns with supporting details.</p></div><span class="service-state">Coming soon</span></div>
                        <div class="service-preview"><span class="service-bubble" aria-hidden="true">◎</span><div><strong>Concern tracking</strong><p>Follow updates and progress in one place.</p></div><span class="service-state">Coming soon</span></div>
                        <div class="service-preview"><span class="service-bubble" aria-hidden="true">✧</span><div><strong>Community announcements</strong><p>Read barangay news and important notices.</p></div><span class="service-state">Coming soon</span></div>
                    </section>

                    <section class="dashboard-panel profile-panel" id="resident-profile" aria-labelledby="profile-heading">
                        <div class="dashboard-panel-head"><div><span class="section-eyebrow">YOUR DETAILS</span><h2 id="profile-heading">Resident profile</h2></div></div>
                        <div class="profile-heading-row"><span class="profile-large-avatar" aria-hidden="true"><?= escape($initial) ?></span><div><strong><?= escape($fullName) ?></strong><span>Registered resident</span></div></div>
                        <dl class="profile-details">
                            <div><dt>Full name</dt><dd><?= escape($fullName) ?></dd></div>
                            <div><dt>Email address</dt><dd><?= escape($user['email']) ?></dd></div>
                            <div><dt>Residential address</dt><dd><?= escape($user['address']) ?></dd></div>
                            <div><dt>Registered on</dt><dd><?= escape($user['created_at']) ?></dd></div>
                        </dl>
                        <p class="profile-footnote">Your profile details come from the account you registered.</p>
                    </section>
                </div>
                <footer class="dashboard-footer">Barangay Lower General Luna · Resident Portal · Academic project</footer>
            </main>
        </div>
    </div>
</body>
</html>
