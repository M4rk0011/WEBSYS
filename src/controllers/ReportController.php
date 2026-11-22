<?php
// Submit a complaint: handle the submitted form first, then display the HTML below.
$page = "report";
$pageTitle = "Submit a complaint";
require_once __DIR__ . "/../middleware/init.php";

if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ["save-report"], true));
        demand($currentUser !== null);
        // This server check blocks admins and staff even if they forge a POST request.
        demand(canSubmitComplaint());
        $complaint = post("id") ? findReport(post("id")) : null;
        if (post("id")) {
            demand(canEditConcernContent($complaint));
        }
        $category = post("category");
        $priority = post("severity");
        demand(isset(CATEGORIES[$category], PRIORITIES[$priority]));
        $lat = filter_var(post("latitude"), FILTER_VALIDATE_FLOAT);
        $lng = filter_var(post("longitude"), FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw new RuntimeException("Choose a valid location on the map.");
        }
        // Validate here too: typed coordinates or a forged request cannot bypass the map.
        if (!insideBarangay((float) $lat, (float) $lng)) {
            throw new RuntimeException("Choose a location inside the red Lower General Luna reporting area.");
        }
        $title = required("title", 200);
        $description = required("description", 10000);
        $address = required("address", 500);
        $new = !$complaint;
        $complaint ??= obj([
            "id" => bin2hex(random_bytes(12)),
            "reference" => "LGL-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(6))),
            "status" => "pending",
            "approvalStatus" => "pending",
            "approvalNote" => "",
            "reviewedBy" => "",
            "reviewedAt" => "",
            "reportedBy" => $currentUser->id,
            "reportedAt" => date(DATE_ATOM),
            "updatedAt" => date(DATE_ATOM),
            "resolvedAt" => "",
            "firstResponseAt" => "",
            "departmentId" => "",
            "deadline" => "",
            "assignedTo" => "",
            "history" => [],
            "feedback" => null,
            "images" => [],
            "witnesses" => [],
            "isAnonymous" => false,
            "additionalNotes" => "",
        ]);
        $complaint->title = $title;
        $complaint->description = $description;
        $complaint->category = $category;
        $complaint->severity = $priority;
        $complaint->location = obj([
            "latitude" => $lat,
            "longitude" => $lng,
            "address" => $address,
            "building" => "",
            "floor" => "",
            "room" => "",
            "landmark" => "",
        ]);
        $complaint->contactInfo = obj([
            "name" => $currentUser->profile->firstName . " " . $currentUser->profile->lastName,
            "email" => $currentUser->email,
            "phone" => $currentUser->profile->phone ?? "",
            "studentId" => "",
            "department" => "",
        ]);
        // Editing a complaint requires a new approval review.
        $complaint->approvalStatus = 'pending';
        $complaint->approvalNote = '';
        $complaint->reviewedBy = '';
        $complaint->reviewedAt = '';
        audit($complaint, $new ? "Complaint submitted; awaiting approval" : "Complaint edited; resubmitted for approval");
        saveReportWithPhotos($complaint);
        $_SESSION["flash"] = "Complaint saved. Reference: " . $complaint->reference;
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
    if (!canEditConcernContent($record)) {
        http_response_code(404);
        exit("Complaint unavailable.");
    }
    $pageTitle = "Edit complaint";
}
