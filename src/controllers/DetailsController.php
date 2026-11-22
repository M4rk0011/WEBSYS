<?php
/**
 * Handles requests and validation for the Details module.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Complaint details: handle the submitted form first, then display the HTML below.
$page = "details";
$pageTitle = "Complaint details";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ["assign", "status", "feedback", "delete-report", "approval"], true));
        demand($currentUser !== null);
        $complaint = findReport(post("id"));
        demand($complaint !== null && canView($complaint));
        if ($action === 'approval') {
            demand(isAdmin());
            // Approval is only available for unprocessed, pending complaints.
            demand($complaint->status === 'pending');
            demand(($complaint->approvalStatus ?? 'approved') !== 'approved');
            $choice = post('approvalStatus');
            demand(in_array($choice, ['approved', 'rejected'], true));
            $note = inputText('approvalNote', 1000, $choice === 'rejected');
            $complaint->approvalStatus = $choice;
            $complaint->approvalNote = $note;
            $complaint->reviewedBy = $currentUser->id;
            $complaint->reviewedAt = date(DATE_ATOM);
            audit($complaint, 'Submission ' . $choice, $note);
            saveReport($complaint);
        } elseif ($action === "assign") {
            demand(isApproved($complaint));
            demand(canAssign($complaint));
            $dept = post("departmentId");
            demand(departmentValid($dept));
            if (role() === "department_head") {
                demand($dept === $currentUser->departmentId);
            }
            $assignee = post("assignedTo");
            if ($assignee) {
                $user = findUser($assignee);
                demand(
                    $user !== null &&
                        $user->isActive &&
                        in_array(role($user), ["personnel", "department_head"], true) &&
                        ($user->departmentId ?? "") === $dept,
                );
            }
            $deadline = post("deadline");
            $date = DateTime::createFromFormat("!Y-m-d", $deadline);
            if (!$date || $date->format("Y-m-d") !== $deadline) {
                throw new RuntimeException("Provide a valid deadline.");
            }
            demand(isset(PRIORITIES[post("severity")]));
            $complaint->departmentId = $dept;
            $complaint->assignedTo = $assignee;
            $complaint->deadline = $deadline;
            $complaint->severity = post("severity");
            audit(
                $complaint,
                "Assignment updated",
                departmentName($dept) . " · " . staffName($assignee) . " · due " . $deadline,
            );
            saveReport($complaint);
        } elseif ($action === "status") {
            demand(isApproved($complaint));
            demand(canProcess($complaint));
            $status = post("status");
            demand(isset(STATUSES[$status]));
            $note = required("note", 5000);
            $allowed = [
                "pending" => ["under_investigation", "rejected"],
                "under_investigation" => ["action_taken", "resolved", "rejected"],
                "action_taken" => ["under_investigation", "resolved", "rejected"],
                "resolved" => ["under_investigation"],
                "rejected" => ["under_investigation"],
            ];
            demand(in_array($status, $allowed[$complaint->status] ?? [], true));
            if (finished($complaint)) {
                demand(isAdmin());
            }
            $complaint->status = $status;
            if (!$complaint->firstResponseAt) {
                $complaint->firstResponseAt = date(DATE_ATOM);
            }
            $complaint->resolvedAt = $status === "resolved" ? date(DATE_ATOM) : "";
            if ($complaint->feedback && $status !== "resolved") {
                $complaint->feedbackArchive ??= [];
                $complaint->feedbackArchive[] = $complaint->feedback;
                $complaint->feedback = null;
            }
            audit($complaint, "Status: " . STATUSES[$status], $note);
            saveReport($complaint);
        } elseif ($action === "feedback") {
            demand(
                role() === "resident" &&
                    owns($complaint) &&
                    $complaint->status === "resolved" &&
                    !$complaint->feedback,
            );
            $rating = filter_var(post("rating"), FILTER_VALIDATE_INT);
            $serviceRating = filter_var(post("serviceRating"), FILTER_VALIDATE_INT);
            demand($rating >= 1 && $rating <= 5 && $serviceRating >= 1 && $serviceRating <= 5);
            $complaint->feedback = obj([
                "rating" => $rating,
                "serviceRating" => $serviceRating,
                "comment" => required("comment", 3000),
                "at" => date(DATE_ATOM),
                "by" => $currentUser->id,
            ]);
            audit($complaint, "Resident feedback submitted");
            saveReport($complaint);
        } elseif ($action === "delete-report") {
            demand(isAdmin());
            $sql = "DELETE FROM php_reports WHERE id = :id";
            $prep = $pdo->prepare($sql);
            $prep->execute([":id" => $complaint->id]);
            foreach ($complaint->images as $photo) {
                deletePhoto($photo);
            }
            $_SESSION["flash"] = "Complaint deleted.";
            go("complaints");
        } else {
            throw new RuntimeException("Unknown action.");
        }
        $_SESSION["flash"] = "Complaint updated.";
        go("details", ["id" => $complaint->id]);
    } catch (PDOException $e) {
        $error = "Could not save. Check for duplicate names or database configuration.";
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

// Fetch one record for the form/detail screen and check ownership before showing it.
$id = is_string($_GET["id"] ?? null) ? $_GET["id"] : "";
if ($id === "" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $id = post("id");
}
$record = null;
if ($id !== "") {
    $record = findReport($id);
    if (!$record || !canView($record)) {
        http_response_code(404);
        exit("Complaint unavailable.");
    }
}
if (!$record) {
    http_response_code(404);
    exit("Complaint unavailable.");
}
