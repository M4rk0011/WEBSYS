<?php
// Reports & analytics: handle the submitted form first, then display the HTML below.
$page = "analytics";
$pageTitle = "Reports & analytics";
require_once __DIR__ . "/../src/middleware/init.php";

if ($handleForm) {
    http_response_code(403);
    $error = "This page does not accept that form action.";
}

require __DIR__ . "/partials/header.php";

 $from = is_string($_GET["from"] ?? null) ? $_GET["from"] : "";
$to = is_string($_GET["to"] ?? null) ? $_GET["to"] : "";
// Select complaints within the requested submission dates.
$sample = [];
foreach ($items as $complaint) {
    $submitted = substr($complaint->reportedAt, 0, 10);
    if ($from !== "" && $submitted < $from) {
        continue;
    }
    if ($to !== "" && $submitted > $to) {
        continue;
    }
    $sample[] = $complaint;
}
$m = metrics($sample);
?>
<section class="card">
<form class="filters" method="get">
<input type="hidden" name="page" value="analytics">
<label>Submitted from
    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" maxlength="500">
</label>

<label>Submitted through
    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" maxlength="500">
</label>
<button class="primary">Apply dates</button>
<button type="button" onclick="window.print()">Print report</button>
<a class="button" href="analytics-export.php?<?= e(http_build_query(["from" => $from, "to" => $to])) ?>">Export CSV</a>
</form>
<p class="muted">Dates filter by submission date. Resolution rate = currently resolved complaints ÷ all complaints in the selected group. Rejected complaints remain in the denominator. Durations use elapsed hours, not business hours. Only recorded response/resolution timestamps and submitted feedback contribute to averages.</p>
</section>
<div class="grid cards"><?php foreach (
    [
        "rate" => "Resolution rate",
        "response" => "Average response",
        "resolution" => "Average resolution",
        "satisfaction" => "Citizen satisfaction",
    ]
    as $k => $label
): ?><section class="card">
<span><?= e($label) ?></span>
<strong class="metric"><?= e(numberMetric($m[$k], $k === "rate" ? "%" : ($k === "satisfaction" ? " /5" : " h"))) ?></strong>
</section><?php endforeach; ?></div>
<section class="card">
<p><?= $m[ "total" ] ?> complaints · <?= $m["responses"] ?> recorded responses · <?= $m[ "feedback" ] ?> feedback submissions · Service evaluation: <?= e(numberMetric($m["service"], " /5")) ?></p>
</section>
<div class="grid two"><?php foreach (["category" => "Complaints by category", "departmentId" => "Complaints by department"] as $key => $title): ?><section class="card">
<h2><?= e($title) ?></h2><?php
$groups = [];
foreach ($sample as $complaint) {
    $label =
        $key === "category"
            ? CATEGORIES[$complaint->category] ?? ucfirst($complaint->category)
            : departmentName($complaint->departmentId);
    $groups[$label] = ($groups[$label] ?? 0) + 1;
}
arsort($groups);
if (!$groups) {
    echo "<p>No data in this period.</p>";
}
foreach ($groups as $label => $count): ?><div class="bar-row">
<span><?= e($label) ?></span>
<strong><?= $count ?></strong>
<meter min="0" max="<?= max( 1, count($sample)) ?>" value="<?= $count ?>"><?= $count ?></meter>
</div><?php endforeach;
?></section><?php endforeach; ?></div>
<section class="card">
<h2>Citizen satisfaction summaries</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Reference</th>
<th>Department</th>
<th>Satisfaction</th>
<th>Service</th>
<th>Comments</th>
</tr>
</thead>
<tbody><?php foreach ($sample as $complaint) {
    if ($complaint->feedback): ?><tr>
<td>
<a href="<?= e(url("details", ["id" => $complaint->id])) ?>"><?= e($complaint->reference) ?></a>
</td>
<td><?= e(departmentName($complaint->departmentId)) ?></td>
<td><?= e($complaint->feedback->rating) ?>/5</td>
<td><?= e($complaint->feedback->serviceRating) ?>/5</td>
<td><?= e($complaint->feedback->comment) ?></td>
</tr><?php endif;
} ?></tbody>
</table>
</div>
</section>

<?php require __DIR__ . "/partials/footer.php"; ?>
