<?php
require_once __DIR__ . '/session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A simpler way for residents of Barangay Lower General Luna to stay connected to their community.">
    <title>Welcome | Barangay Lower General Luna</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-design.css">
</head>
<body class="landing-page">
    <div class="landing-container">
        <header class="landing-header">
            <a class="site-identity" href="index.php" aria-label="Barangay Lower General Luna home">
                <span class="site-symbol" aria-hidden="true">LG</span>
                <span class="site-identity-text"><strong>Lower General Luna</strong><small>BARANGAY RESIDENT PORTAL</small></span>
            </a>
            <nav class="landing-nav" aria-label="Account navigation">
                <a class="landing-signin" href="login.php">Log in</a>
                <a class="landing-register" href="signup.php">Create an account <span aria-hidden="true">↗</span></a>
            </nav>
        </header>

        <main>
            <section class="landing-hero" aria-labelledby="landing-title">
                <div class="landing-hero-copy">
                    <span class="eyebrow"><span class="status-dot" aria-hidden="true"></span> YOUR COMMUNITY, CONNECTED</span>
                    <h1 id="landing-title">A better barangay begins with <em>every voice.</em></h1>
                    <p>Welcome to the Barangay Lower General Luna Resident Portal — a friendly space for residents and their community.</p>
                    <div class="landing-actions">
                        <a class="primary-action" href="signup.php">Get started <span aria-hidden="true">↗</span></a>
                        <a class="text-action" href="login.php">I already have an account</a>
                    </div>
                    <div class="landing-trust"><span class="trust-mark" aria-hidden="true">✓</span> Built for residents of Lower General Luna, Baguio City</div>
                </div>
                <div class="landing-hero-art">
                    <img src="assets/community-hero.svg" alt="Illustration of a barangay hall and homes against the mountains" width="860" height="560">
                    <div class="art-caption"><span class="art-caption-dot" aria-hidden="true"></span> Stronger communities start here</div>
                </div>
            </section>

            <section class="landing-features" aria-labelledby="feature-title">
                <div class="features-heading"><div><span class="section-eyebrow">LOOKING AHEAD</span><h2 id="feature-title">Made for our community</h2></div><p>More resident services are planned as the project grows.</p></div>
                <div class="landing-feature-grid">
                    <article class="landing-feature">
                        <span class="feature-glyph" aria-hidden="true">✎</span>
                        <h3>Share your concerns</h3>
                        <p>A planned space to raise community issues and provide details.</p>
                        <span class="coming-soon">Coming soon</span>
                    </article>
                    <article class="landing-feature">
                        <span class="feature-glyph" aria-hidden="true">◎</span>
                        <h3>Stay informed</h3>
                        <p>Future updates will help residents follow the status of concerns.</p>
                        <span class="coming-soon">Coming soon</span>
                    </article>
                    <article class="landing-feature">
                        <span class="feature-glyph" aria-hidden="true">✦</span>
                        <h3>Connect with your barangay</h3>
                        <p>A central place for community announcements is on the roadmap.</p>
                        <span class="coming-soon">Coming soon</span>
                    </article>
                </div>
            </section>
        </main>
        <footer class="landing-footer"><span>© <?= date('Y') ?> Barangay Lower General Luna · Academic project</span><span>Baguio City, Philippines</span></footer>
    </div>
</body>
</html>
