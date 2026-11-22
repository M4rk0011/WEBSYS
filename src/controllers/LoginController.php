<?php
/**
 * Handles requests and validation for the Login module.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// LOGIN: read the form, find the account with PDO, and check its password.
$page = "login";
$pageTitle = "Welcome";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand($action === "login");
        $username = post("username");
        $password = post("password");

        if (($_SESSION["loginBlockedUntil"] ?? 0) > time()) {
            throw new RuntimeException("Too many attempts. Try again in one minute.");
        }

        $sql = "SELECT * FROM php_users WHERE LOWER(username) = LOWER(:username)";
        $prep = $pdo->prepare($sql);
        $prep->execute([":username" => $username]);
        $row = $prep->fetch();

        if (!$row || !password_verify($password, $row["password_hash"])) {
            $_SESSION["loginFailures"] = ($_SESSION["loginFailures"] ?? 0) + 1;
            if ($_SESSION["loginFailures"] >= 5) {
                $_SESSION["loginBlockedUntil"] = time() + 60;
                $_SESSION["loginFailures"] = 0;
            }
            throw new RuntimeException("Invalid username or password.");
        }

        $user = json_decode($row["body"]);
        if (!$user->isActive) {
            throw new RuntimeException("This account is inactive. Contact the administrator.");
        }

        // Give a successful login a new session ID and form token.
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user->id;
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
        unset($_SESSION["loginFailures"], $_SESSION["loginBlockedUntil"]);
        $user->lastLogin = date(DATE_ATOM);
        saveUser($user);

        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not sign in. Check the database connection.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
