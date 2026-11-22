<?php
// Home page and compatibility for old index.php?page=... bookmarks.
// Normal links go directly to login.php, report.php, users.php, etc.
$page = "dashboard";
if (is_string($_GET["page"] ?? null)) {
    $page = $_GET["page"];
}

// Old forms submitted through index.php are forwarded to the correct named page.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = is_string($_POST["action"] ?? null) ? $_POST["action"] : "";
    $formPages = [
        "login" => "login", "register" => "register", "profile" => "profile",
        "create-user" => "users", "update-user" => "users",
        "department" => "departments", "announcement" => "announcements",
        "delete-announcement" => "announcements", "save-report" => "report",
        "assign" => "details", "status" => "details", "feedback" => "details",
        "delete-report" => "details", "approval" => "details",
        "save-resident" => "residents", "delete-resident" => "residents",
        "save-business" => "businesses", "delete-business" => "businesses",
        "save-place" => "map-places", "delete-place" => "map-places",
    ];
    if (isset($formPages[$action])) {
        $page = $formPages[$action];
    }
}

// Only these fixed filenames may be included. Never include a user-supplied path.
$pages = ["login", "register", "dashboard", "profile", "users", "departments",
    "complaints", "report", "details", "map", "announcements", "monitoring", "analytics", "residents", "businesses", "map-places"];
if (!in_array($page, $pages, true)) {
    $page = "dashboard";
}
$file = $page . ".php";
if ($page === "register") {
    $file = "signup.php";
}
require __DIR__ . "/" . $file;
