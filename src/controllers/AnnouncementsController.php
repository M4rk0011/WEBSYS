<?php
// Public announcements: handle the submitted form first, then display the HTML below.
$page = "announcements";
$pageTitle = "Public announcements";
require_once __DIR__ . "/../middleware/init.php";

// CREATE, UPDATE or DELETE an announcement. Only administrators can save changes.
if ($handleForm) {
    try {
        checkFormToken();
        demand(isAdmin());
        demand(in_array($action, ["announcement", "delete-announcement"], true));
        $id = post("id");

        if ($action === "delete-announcement") {
            $sql = "DELETE FROM php_announcements WHERE id = :id";
            $prep = $pdo->prepare($sql);
            $prep->execute([":id" => $id]);
        } else {
            $title = required("title", 200);
            $body = required("body", 10000);
            $type = post("type");
            $published = 0;
            if (isset($_POST["published"])) {
                $published = 1;
            }
            demand(in_array($type, ["Service advisory", "Public announcement", "Community update"], true));

            $values = [
                ":title" => $title,
                ":body" => $body,
                ":type" => $type,
                ":published" => $published,
            ];
            if ($id !== "") {
                $sql = "UPDATE php_announcements SET title = :title, body = :body,
                        type = :type, published = :published WHERE id = :id";
                $values[":id"] = $id;
            } else {
                $sql = "INSERT INTO php_announcements (id, title, body, type, published, created_at)
                        VALUES (:id, :title, :body, :type, :published, :created_at)";
                $values[":id"] = bin2hex(random_bytes(8));
                $values[":created_at"] = date(DATE_ATOM);
            }
            $prep = $pdo->prepare($sql);
            $prep->execute($values);
        }
        $_SESSION["flash"] = "Announcement updated.";
        header("Location: announcements.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not save. Check the database connection.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
