<?php
// Accounts & verification: handle the submitted form first, then display the HTML below.
$page = "users";
$pageTitle = "Accounts & verification";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ["create-user", "update-user"], true));
        demand($currentUser !== null);
        demand(isAdmin());
        $targetRole = post("role");
        demand(isset(ROLES[$targetRole]) && $targetRole !== 'system_admin');
        demand(
            role() === "system_admin" ||
                in_array($targetRole, ["resident", "personnel", "department_head"], true),
        );
        $user = $action === "update-user" ? findUser(post("id")) : null;
        if ($action === "update-user") {
            demand($user !== null && $user->id !== $currentUser->id);
            demand(role() === "system_admin" || !isAdminUser($user));
            demand(role($user) !== "system_admin");
        }
        $dept = post("departmentId");
        if (in_array($targetRole, ["department_head", "personnel"], true) && !departmentValid($dept)) {
            throw new RuntimeException("Select a department for this staff account.");
        }
        $user ??= obj([
            "id" => bin2hex(random_bytes(12)),
            "createdAt" => date(DATE_ATOM),
            "profile" => [
                "studentId" => "",
                "department" => "",
                "position" => "",
                "address" => "",
                "phone" => "",
            ],
        ]);
        $user->username = required("username", 100);
        $user->email = required("email", 200);
        $user->role = $targetRole;
        $user->departmentId = in_array($targetRole, ["department_head", "personnel"], true)
            ? $dept
            : "";
        $user->verified = isset($_POST["verified"]);
        $user->isActive = isset($_POST["active"]);
        $user->profile->firstName = required("firstName", 100);
        $user->profile->lastName = required("lastName", 100);
        if ($action === "create-user" && strlen(post("password")) < 8) {
            throw new RuntimeException("Password must have at least 8 characters.");
        }
        if (post("password") !== "" && strlen(post("password")) < 8) {
            throw new RuntimeException("Password must have at least 8 characters.");
        }
        saveUser($user, post("password") !== "" ? post("password") : null);
        $_SESSION["flash"] = "Account saved.";
        go("users");
    } catch (PDOException $e) {
        $error = "Could not save. Check for duplicate names or database configuration.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
