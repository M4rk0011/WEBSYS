<?php
/**
 * Provides reusable application helper functions.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Shared small PHP functions: escaping, navigation, and the simple CRUD class wrappers.
// Convert an array to an object so pages can use $record->field.
function obj(array $a): object
{
    return json_decode(json_encode($a, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
}
// Escape text before showing it in HTML. This prevents inserted HTML/scripts.
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
// Build the URL for one of our named PHP pages.
function url(string $page, array $args = []): string
{
    $file = $page === "register" ? "signup.php" : $page . ".php";
    return $file . ($args ? "?" . http_build_query($args) : "");
}
// Redirect after saving a form. exit stops the old page from continuing.
function go(string $page, array $args = []): never
{
    header("Location: " . url($page, $args));
    exit();
}
// Procedural helpers delegate database work to simple OOP model classes.
// Fetch all users through the User class. Only admin screens display this list.
function users(): array
{
    $usersTable = new User(db());
    return $usersTable->all();
}
// Find one account using its database ID.
function findUser(string $id): ?object
{
    $usersTable = new User(db());
    return $usersTable->find($id);
}
// Save account data. The User class validates input and hashes new passwords.
function saveUser(object $user, ?string $password = null): void
{
    $usersTable = new User(db());
    $usersTable->save($user, $password);
}
// Get all complaints. Pages must use visibleReports() to apply permissions.
function reports(): array
{
    $complaintsTable = new Complaint(db());
    return $complaintsTable->all();
}
// Supply missing fields and translate old statuses for existing records.
function normalReport(object $complaint): object
{
    foreach (["resolvedAt", "assignedTo", "additionalNotes"] as $k) {
        $complaint->$k ??= "";
    }
    foreach (["building", "floor", "room", "landmark"] as $k) {
        $complaint->location->$k ??= "";
    }
    foreach (["name", "email", "phone", "studentId", "department"] as $k) {
        $complaint->contactInfo->$k ??= "";
    }
    $complaint->witnesses ??= [];
    $complaint->images ??= [];
    $complaint->status =
        [
            "reported" => "pending",
            "investigating" => "under_investigation",
            "closed" => "resolved",
            "false_alarm" => "rejected",
        ][$complaint->status] ?? $complaint->status;
    foreach (["departmentId", "deadline", "firstResponseAt"] as $key) {
        $complaint->$key ??= "";
    }
    $complaint->reference ??= "LGL-" . strtoupper($complaint->id);
    $complaint->history ??= [];
    $complaint->feedback ??= null;
    // Old V3 complaints are treated as already accepted; new V4 reports await staff review.
    $complaint->approvalStatus ??= 'approved';
    $complaint->approvalNote ??= '';
    $complaint->reviewedBy ??= '';
    $complaint->reviewedAt ??= '';
    return $complaint;
}
// Read a complaint by ID. Always check canView() before displaying it.
function findReport(string $id): ?object
{
    $complaintsTable = new Complaint(db());
    return $complaintsTable->find($id);
}
// Store the complaint through the Complaint class and PDO.
function saveReport(object $complaint): void
{
    $complaintsTable = new Complaint(db());
    $complaintsTable->save($complaint);
}
// Put a secret session token in forms so another site cannot submit them.
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf" value="' . e($_SESSION["csrf"]) . '">';
}
function getSeverityColor(string $value): string
{
    return [
        "critical" => "#dc3545",
        "high" => "#fd7e14",
        "medium" => "#ffc107",
        "low" => "#28a745",
    ][$value] ?? "#6c757d";
}
function getSeverityColorFromKey(string $value): string
{
    return getSeverityColor($value);
}
function getStatusColor(string $value): string
{
    return [
        "reported" => "#007bff",
        "investigating" => "#fd7e14",
        "resolved" => "#28a745",
        "closed" => "#6c757d",
        "false_alarm" => "#6f42c1",
    ][$value] ?? "#6c757d";
}
function getCategoryDisplayName(string $value): string
{
    return strtoupper(str_replace("_", " ", $value));
}
function getSeverityDisplayName(string $value): string
{
    return strtoupper($value);
}
function formatStatusLabel(string $value): string
{
    return str_replace("_", " ", $value);
}
function getSeverityInitial(string $value): string
{
    return strtoupper(substr($value, 0, 1));
}
function fmtdate($value): string
{
    if (!$value) {
        return "";
    }
    $timestamp = strtotime((string) $value);
    return $timestamp === false ? "" : date("M j, Y, g:i A", $timestamp);
}
// Read one form value safely as trimmed text.
function post(string $key): string
{
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : "";
}
