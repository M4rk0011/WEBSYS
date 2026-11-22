<?php
/**
 * Public request handler or view for the monitoring page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Service monitoring: handle the submitted form first, then display the HTML below.
$page = "monitoring";
$pageTitle = "Service monitoring";
require_once __DIR__ . "/../src/middleware/init.php";

if ($handleForm) {
    http_response_code(403);
    $error = "This page does not accept that form action.";
}

require __DIR__ . "/partials/header.php";
?>
<div class="grid cards"><?php foreach (
    [
        "response" => "Average response",
        "resolution" => "Average resolution",
        "unresolved" => "Unresolved",
        "overdue" => "Overdue",
    ]
    as $k => $label
): ?><section class="card">
<span><?= e($label) ?></span>
<strong class="metric"><?= in_array($k, [ "response", "resolution", ]) ? e(numberMetric($stats[$k], " h")) : e($stats[$k]) ?></strong>
</section><?php endforeach; ?></div>
<section class="card">
<h2>Department performance</h2>
<p class="muted">Metrics use the complaints visible to your role. Response begins at submission and ends at the first processing update; assignment alone does not count as a response.</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Department</th>
<th>Total</th>
<th>Unresolved</th>
<th>Resolved</th>
<th>Overdue</th>
<th>Response</th>
<th>Resolution</th>
<th>Satisfaction</th>
</tr>
</thead>
<tbody><?php foreach ($deps as $did => $name):
    $departmentComplaints = [];
    foreach ($items as $complaint) {
        if ($complaint->departmentId === $did) {
            $departmentComplaints[] = $complaint;
        }
    }
    $m = metrics($departmentComplaints); ?><tr>
<td><?= e($name) ?></td>
<td><?= $m["total"] ?></td>
<td><?= $m[ "unresolved" ] ?></td>
<td><?= $m["resolved"] ?></td>
<td><?= $m["overdue"] ?></td>
<td><?= e(numberMetric($m["response"], " h")) ?></td>
<td><?= e(numberMetric($m["resolution"], " h")) ?></td>
<td><?= e(numberMetric($m["satisfaction"], " /5")) ?></td>
</tr><?php
endforeach; ?></tbody>
</table>
</div>
</section>
<section class="card">
<h2>Unresolved complaint monitoring</h2><?php
$unresolved = [];
foreach ($items as $complaint) {
    if (!finished($complaint)) {
        $unresolved[] = $complaint;
    }
}
usort($unresolved, function ($first, $second) {
    return strcmp($first->deadline ?: "9999", $second->deadline ?: "9999");
});
reportTable($unresolved);
?></section>

<?php require __DIR__ . "/partials/footer.php"; ?>
