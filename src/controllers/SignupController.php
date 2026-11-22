<?php
/**
 * Handles requests and validation for the Signup module.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// SIGNUP: residents enter their details; the administrator verifies them later.
$page = "register";
$pageTitle = "Create an account";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand($action === "register");
        $username = required("username", 100);
        $email = required("email", 200);
        $password = post("password");
        $confirmPassword = post("confirmPassword");
        $firstName = required("firstName", 100);
        $lastName = required("lastName", 100);
        $phone = required("phone", 40);
        $address = required("address", 500);

        if (strlen($password) < 8 || $password !== $confirmPassword) {
            throw new RuntimeException("Use at least 8 characters and matching passwords.");
        }

        // These values are fixed by PHP. A signup form cannot create an admin.
        $user = obj([
            "id" => bin2hex(random_bytes(12)),
            "username" => $username,
            "email" => $email,
            "role" => "resident",
            "verified" => false,
            "departmentId" => "",
            "profile" => [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "phone" => $phone,
                "address" => $address,
                "studentId" => "",
                "department" => "",
                "position" => "",
            ],
            "createdAt" => date(DATE_ATOM),
            "isActive" => true,
        ]);

        // The User class checks duplicates and uses a prepared INSERT query.
        $accounts = new User($pdo);
        $accounts->save($user, $password);
        $_SESSION["flash"] = "Account created. Sign in to view your profile. An administrator must verify your account before you submit complaints.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not save. Check for duplicate names or database configuration.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
