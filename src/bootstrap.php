<?php
declare(strict_types=1);
date_default_timezone_set("Asia/Manila");
if (PHP_SAPI !== "cli") {
    ini_set("session.use_strict_mode", "1");
    session_set_cookie_params([
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    ]);
    session_start();
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    // Send only this site's origin to third-party map tile servers, not full page URLs.
    // OpenStreetMap requires an identifying Referer; same-origin suppresses it.
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Cache-Control: no-store");
}
// Load one database connection, two small OOP models, and shared features.
// All private PHP code stays OUTSIDE public/, as in the example project.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Complaint.php';
require_once __DIR__ . '/models/BarangayDirectory.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/services/photos.php';
require_once __DIR__ . '/services/boundary.php';
require_once __DIR__ . '/services/place-photos.php';
require_once __DIR__ . '/services/community.php';
require_once __DIR__ . '/middleware/permissions.php';
