<?php
/**
 * Public request handler or view for the analytics export page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
require __DIR__ . "/../src/services/service.php";
$currentUser = isset($_SESSION["user_id"]) ? findUser((string) $_SESSION["user_id"]) : null;
if (!$currentUser || !$currentUser->isActive || !canViewAnalytics()) {
    http_response_code(403);
    exit("Access denied.");
}
$from = is_string($_GET["from"] ?? null) ? $_GET["from"] : "";
$to = is_string($_GET["to"] ?? null) ? $_GET["to"] : "";
header("Content-Type: text/csv; charset=utf-8");
header('Content-Disposition: attachment; filename="barangay-service-report.csv"');
$out = fopen("php://output", "w");
fputcsv($out, [
    "Reference",
    "Category",
    "Department",
    "Priority",
    "Status",
    "Submitted",
    "First response",
    "Resolved",
    "Deadline",
    "Response hours",
    "Resolution hours",
    "Satisfaction",
    "Service evaluation",
]);
foreach (visibleReports() as $r) {
    $day = substr($r->reportedAt, 0, 10);
    if (($from && $day < $from) || ($to && $day > $to)) {
        continue;
    }
    $m = metrics([$r]);
    $row = [
        $r->reference,
        CATEGORIES[$r->category] ?? $r->category,
        departmentName($r->departmentId),
        $r->severity,
        STATUSES[$r->status] ?? $r->status,
        $r->reportedAt,
        $r->firstResponseAt,
        $r->resolvedAt,
        $r->deadline,
        $m["response"] ?? "",
        $m["resolution"] ?? "",
        $r->feedback->rating ?? "",
        $r->feedback->serviceRating ?? "",
    ];
    // Protect spreadsheet users: do not let a cell be interpreted as a formula.
    foreach ($row as $index => $value) {
        if (preg_match('/^[=+@\-\t\r]/', (string) $value)) {
            $row[$index] = "'" . $value;
        }
    }
    fputcsv($out, $row);
}
fclose($out);
