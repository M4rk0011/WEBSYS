<?php
require __DIR__ . "/../src/services/service.php";
$currentUser = isset($_SESSION["user_id"]) ? findUser((string) $_SESSION["user_id"]) : null;
if (!$currentUser || !$currentUser->isActive) {
    http_response_code(401);
    exit();
}
if (!isset($_SESSION["user_id"]) || !findUser((string) $_SESSION["user_id"])) {
    http_response_code(401);
    exit();
}
$report = is_string($_GET["report"] ?? null) ? findReport($_GET["report"]) : null;
$name = $_GET["name"] ?? null;
if (
    !$report ||
    !canView($report) ||
    !validPhotoName($name) ||
    !in_array($name, $report->images, true) ||
    !is_file(photoDirectory() . "/" . $name)
) {
    http_response_code(404);
    exit();
}
session_write_close();
$mime = ["jpg" => "image/jpeg", "png" => "image/png", "webp" => "image/webp"][
    pathinfo($name, PATHINFO_EXTENSION)
];
header("Content-Type: " . $mime);
header('Content-Disposition: inline; filename="' . $name . '"');
header("Content-Security-Policy: default-src 'none'; sandbox");
header("Content-Length: " . filesize(photoDirectory() . "/" . $name));
readfile(photoDirectory() . "/" . $name);
