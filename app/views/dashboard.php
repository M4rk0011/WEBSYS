
<?php
// Community dashboard: handle the submitted form first, then display the HTML below.
$page = "dashboard";
$pageTitle = "Community dashboard";

require_once __DIR__ . "/app/init.php";

if ($handleForm) {
    http_response_code(403);
    $error = "This page does not accept that form action.";
}

require __DIR__ . "/includes/header.php";
?>

<section class="welcome">
    <div>
        <p class="eyebrow">A CONNECTED COMMUNITY</p>

        <h2>Every concern deserves a response.</h2>

        <p>
            <?= role() === "resident"
                ? "Track your submissions and share feedback on resolved concerns."
                : "Review the queue, coordinate departments, and keep residents informed."
            ?>
        </p>

        <a
            class="button primary"
            href="<?= e(url(canSubmitComplaint() ? "report" : "complaints")) ?>"
        >
            <?= canSubmitComplaint()
                ? "Submit a complaint"
                : "Open complaint queue"
            ?>
        </a>
    </div>
</section>

<div class="grid cards">
    <?php
    foreach (
        [
            "total" => "Total complaints",
            "unresolved" => "Unresolved",
            "resolved" => "Resolved",
            "overdue" => "Overdue",
        ] as $key => $label
    ):
    ?>
        <section class="card">
            <span><?= e($label) ?></span>
            <strong class="metric"><?= e($stats[$key]) ?></strong>
        </section>
    <?php endforeach; ?>
</div>

<section class="card">
    <div class="section-title">
        <h2>Recent complaints</h2>
        <a href="<?= e(url("complaints")) ?>">View all →</a>
    </div>

    <?php reportTable(array_slice($items, 0, 5)); ?>
</section>

<section class="card">
    <h2>Community updates</h2>

    <?php
    $news = array_slice(announcements(), 0, 3);

    if (!$news) {
        echo "<p>No announcements yet.</p>";
    }

    foreach ($news as $a):
    ?>
        <article>
            <span class="badge"><?= e($a["type"]) ?></span>

            <h3><?= e($a["title"]) ?></h3>

            <p><?= nl2br(e($a["body"])) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . "/includes/footer.php"; ?>
