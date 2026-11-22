<?php
/**
 * Handles requests and validation for the Departments module.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Departments: handle the submitted form first, then display the HTML below.
$page = "departments";
$pageTitle = "Departments";
require_once __DIR__ . "/../middleware/init.php";

// CREATE or UPDATE a department when its form is submitted.
if ($handleForm) {
    try {
        checkFormToken();
        demand(isAdmin() && $action === "department");
        $name = required("name", 150);
        $id = post("id");

        if ($id !== "") {
            demand(departmentValid($id));
            $sql = "UPDATE php_departments SET name = :name WHERE id = :id";
        } else {
            $id = bin2hex(random_bytes(8));
            $sql = "INSERT INTO php_departments (id, name) VALUES (:id, :name)";
        }
        $prep = $pdo->prepare($sql);
        $prep->execute([
            ":id" => $id,
            ":name" => $name,
        ]);
        $_SESSION["flash"] = "Department saved.";
        header("Location: departments.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not save. Check for a duplicate department name.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
