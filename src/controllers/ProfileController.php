<?php
// Citizen profile: handle the submitted form first, then display the HTML below.
$page = "profile";
$pageTitle = "Citizen profile";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ["profile"], true));
        demand($currentUser !== null);
        $currentUser->profile->firstName = required("firstName", 100);
        $currentUser->profile->lastName = required("lastName", 100);
        $currentUser->profile->phone = required("phone", 40);
        $currentUser->profile->address = required("address", 500);
        $currentUser->email = required("email", 200);
        $pass = null;
        if (post("password") !== "") {
            $sql = "SELECT password_hash FROM php_users WHERE id = :id";
            $prep = $pdo->prepare($sql);
            $prep->execute([":id" => $currentUser->id]);
            if (
                !password_verify(post("currentPassword"), $prep->fetchColumn()) ||
                strlen(post("password")) < 8
            ) {
                throw new RuntimeException(
                    "Current password is incorrect or new password is too short.",
                );
            }
            $pass = post("password");
        }
        saveUser($currentUser, $pass);
        $_SESSION["flash"] = "Profile saved.";
        go("profile");
    } catch (PDOException $e) {
        $error = "Could not save. Check for duplicate names or database configuration.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
