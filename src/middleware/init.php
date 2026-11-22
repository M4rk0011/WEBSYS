<?php
/**
 * Applies shared initialization or authorization rules (init).
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Every page loads this once: shared functions, login session and page access.
require_once __DIR__ . "/../services/service.php";

$_SESSION["csrf"] = $_SESSION["csrf"] ?? bin2hex(random_bytes(32));
$currentUser = null;
if (isset($_SESSION["user_id"])) {
    $currentUser = findUser($_SESSION["user_id"]);
}
if (isset($_SESSION["user_id"]) && (!$currentUser || !$currentUser->isActive)) {
    unset($_SESSION["user_id"]);
    $currentUser = null;
}

$authPage = in_array($page, ["login", "register"], true);
if (!$currentUser && !$authPage && $page !== "announcements") {
    header("Location: login.php");
    exit;
}
if ($currentUser && $authPage) {
    header("Location: dashboard.php");
    exit;
}
if ($currentUser && !in_array($page, allowedPages(), true)) {
    http_response_code(403);
    exit("Access denied. This page is not available for your role.");
}

$error = "";
$action = post("action");
$handleForm = $_SERVER["REQUEST_METHOD"] === "POST";

// These two small forms appear on many pages, so handle them in one shared place.
if ($handleForm && in_array($action, ["logout", "dismiss-announcement"], true)) {
    $handleForm = false;
    try {
        checkFormToken();
        demand($currentUser !== null);

        if ($action === "logout") {
            $_SESSION = [];
            session_regenerate_id(true);
            header("Location: login.php");
            exit;
        }

        // The X hides this exact version of the announcement for this account only.
        $sql = "SELECT * FROM php_announcements WHERE id = :id AND published = 1";
        $prep = $pdo->prepare($sql);
        $prep->execute([":id" => post("id")]);
        $announcement = $prep->fetch();
        if ($announcement) {
            $version = announcementVersion($announcement);
            if (hash_equals($version, post("version"))) {
                $dismissed = (array) ($currentUser->dismissedAnnouncements ?? []);
                $dismissed[$announcement["id"]] = $version;
                $currentUser->dismissedAnnouncements = (object) $dismissed;
                saveUser($currentUser);
            }
        }

        $returnPage = "dashboard";
        if (in_array($page, allowedPages(), true)) {
            $returnPage = $page;
        }
        $args = [];
        if (is_string($_GET["id"] ?? null) && $_GET["id"] !== "") {
            $args["id"] = $_GET["id"];
        }
        go($returnPage, $args);
    } catch (PDOException $e) {
        $error = "Could not save. Check the database connection.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);
