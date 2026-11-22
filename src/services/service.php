<?php
/**
 * Implements reusable business logic for service.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
require_once __DIR__ . "/../bootstrap.php";
const ROLES = [
    "system_admin" => "System Administrator",
    "lgu_admin" => "LGU Administrator",
    "department_head" => "Department Head",
    "personnel" => "Government Personnel",
    "resident" => "Community Resident",
];
const STATUSES = [
    "pending" => "Pending",
    "under_investigation" => "Under investigation",
    "action_taken" => "Action taken",
    "resolved" => "Resolved",
    "rejected" => "Rejected / invalid",
];
const CATEGORIES = [
    "sanitation" => "Sanitation & waste",
    "roads" => "Roads & drainage",
    "lighting" => "Street lighting",
    "noise" => "Noise disturbance",
    "safety" => "Public safety",
    "health" => "Health & welfare",
    "service" => "Government service",
    "other" => "Other",
];
const PRIORITIES = [
    "low" => "Low",
    "medium" => "Medium",
    "high" => "High",
    "critical" => "Critical",
];
function departments(): array
{
    global $pdo;
    $sql = "SELECT * FROM php_departments ORDER BY name";
    $prep = $pdo->prepare($sql);
    $prep->execute();
    return $prep->fetchAll();
}
function departmentName(string $id): string
{
    foreach (departments() as $d) {
        if ($d["id"] === $id) {
            return $d["name"];
        }
    }
    return "Unassigned";
}
function staffName(string $id): string
{
    $user = findUser($id);
    if ($user) {
        return trim($user->profile->firstName . " " . $user->profile->lastName);
    }
    if ($id !== "") {
        return $id;
    }
    return "Unassigned";
}
// Return only complaints the signed-in account is permitted to see.
function visibleReports(): array
{
    $visible = [];
    foreach (reports() as $complaint) {
        if (canView($complaint)) {
            $visible[] = $complaint;
        }
    }
    return $visible;
}
// A new complaint needs administrator approval before personnel can process it.
function isApproved(object $complaint): bool
{
    return ($complaint->approvalStatus ?? 'approved') === 'approved';
}
// Resolved and rejected complaints no longer count as open work.
function finished(object $complaint): bool
{
    return in_array($complaint->status, ["resolved", "rejected"], true);
}
// Mark open complaints past the end of their deadline day as overdue.
function overdue(object $complaint): bool
{
    return !finished($complaint) &&
        $complaint->deadline &&
        strtotime($complaint->deadline . " 23:59:59") < time();
}
// Add the current user, date and action to the complaint activity history.
function audit(object $complaint, string $event, string $note = ""): void
{
    $complaint->history[] = obj([
        "at" => date(DATE_ATOM),
        "by" => $GLOBALS["currentUser"]->id,
        "event" => $event,
        "note" => $note,
    ]);
    $complaint->updatedAt = date(DATE_ATOM);
}
// Reject empty input or text longer than the allowed length.
function required(string $key, int $max = 5000): string
{
    $v = post($key);
    if ($v === "" || strlen($v) > $max) {
        throw new RuntimeException("Please provide a valid " . str_replace("_", " ", $key) . ".");
    }
    return $v;
}
// Check that a submitted department exists in the database.
function departmentValid(string $id): bool
{
    foreach (departments() as $d) {
        if ($d["id"] === $id) {
            return true;
        }
    }
    return false;
}
// Read published announcements; admin screens may request drafts as well.
function announcements(bool $published = true): array
{
    global $pdo;
    $sql = "SELECT * FROM php_announcements";
    if ($published) {
        $sql .= " WHERE published = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    $prep = $pdo->prepare($sql);
    $prep->execute();
    return $prep->fetchAll();
}
function avg(array $values): ?float
{
    return $values ? array_sum($values) / count($values) : null;
}
// Calculate counts and averages from the already permission-filtered complaints.
function metrics(array $items): array
{
    $response = [];
    $resolution = [];
    $ratings = [];
    $service = [];
    $resolved = 0;
    $unresolved = 0;
    $late = 0;
    foreach ($items as $complaint) {
        if ($complaint->firstResponseAt) {
            $response[] = max(
                0,
                (strtotime($complaint->firstResponseAt) - strtotime($complaint->reportedAt)) / 3600,
            );
        }
        if ($complaint->status === "resolved") {
            $resolved++;
            if ($complaint->resolvedAt) {
                $resolution[] = max(
                    0,
                    (strtotime($complaint->resolvedAt) - strtotime($complaint->reportedAt)) / 3600,
                );
            }
        }
        if (!finished($complaint)) {
            $unresolved++;
        }
        if (overdue($complaint)) {
            $late++;
        }
        if ($complaint->feedback) {
            $ratings[] = $complaint->feedback->rating;
            $service[] = $complaint->feedback->serviceRating;
        }
    }
    return [
        "total" => count($items),
        "resolved" => $resolved,
        "unresolved" => $unresolved,
        "overdue" => $late,
        "rate" => count($items) ? (100 * $resolved) / count($items) : 0,
        "response" => avg($response),
        "resolution" => avg($resolution),
        "satisfaction" => avg($ratings),
        "service" => avg($service),
        "responses" => count($response),
        "feedback" => count($ratings),
    ];
}
function numberMetric($n, string $unit = ""): string
{
    return $n === null ? "No data" : number_format((float) $n, 1) . $unit;
}
function badge(object $complaint): void
{
    echo '<span class="badge ' .
        e($complaint->status) .
        '">' .
        e(STATUSES[$complaint->status] ?? $complaint->status) .
        "</span>";
}
// Display a list of complaints passed in by the permission-filtered page.
function reportTable(array $items): void
{
    require __DIR__ . "/../../public/partials/complaint-table.php";
}

// A fingerprint changes when an administrator edits the announcement.
// This lets a corrected announcement appear again after a user closed the old text.
function announcementVersion(array $announcement): string
{
    return hash('sha256', $announcement['title'] . "\n" . $announcement['body'] . "\n" . $announcement['type']);
}

// Each account remembers its own dismissed announcements in its existing profile record.
function nextAnnouncement(object $user): ?array
{
    $dismissed = (array) ($user->dismissedAnnouncements ?? []);
    foreach (announcements() as $announcement) {
        $version = announcementVersion($announcement);
        if (($dismissed[$announcement['id']] ?? '') !== $version) {
            return $announcement;
        }
    }
    return null;
}

// Check the hidden form token before any INSERT, UPDATE or DELETE action.
function checkFormToken()
{
    if (!hash_equals($_SESSION["csrf"], post("csrf"))) {
        throw new RuntimeException("Session expired. Reload and try again.");
    }
}
