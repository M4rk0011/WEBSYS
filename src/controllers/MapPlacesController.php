<?php
$page = 'map-places';
$pageTitle = 'Manage map locations';
require_once __DIR__ . '/../middleware/init.php';
demand(isAdmin());
$store = directory('places');
if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ['save-place', 'delete-place'], true));
        $id = post('id');
        if ($id !== '' && !preg_match('/\A[a-f0-9]{24}\z/D', $id)) throw new RuntimeException('Invalid location ID.');
        $old = $id !== '' ? $store->find($id) : null;
        if ($id !== '' && !$old) throw new RuntimeException('Location not found.');
        if ($action === 'delete-place') {
            $store->delete($id);
            if ($old['photo_name'] !== '') removePlacePhoto($old['photo_name']);
            $_SESSION['flash'] = 'Location deleted.';
        } else {
            $kind = inputText('kind', 30);
            if (!isset(PLACE_KINDS[$kind])) throw new RuntimeException('Choose a location type.');
            $hazard = inputText('hazard_type', 30);
            if ($kind !== 'hazard') $hazard = '';
            if (!array_key_exists($hazard, HAZARD_TYPES)) throw new RuntimeException('Choose a valid hazard type.');
            $latitude = filter_var(post('latitude'), FILTER_VALIDATE_FLOAT);
            $longitude = filter_var(post('longitude'), FILTER_VALIDATE_FLOAT);
            if ($latitude === false || $longitude === false || !insideBarangay((float)$latitude, (float)$longitude)) {
                throw new RuntimeException('Pick a spot inside the approximate Lower General Luna outline.');
            }
            $review = inputText('review_status', 20);
            demand(in_array($review, ['draft', 'verified'], true));
            $oldImage = (string)($old['photo_name'] ?? '');
            $remove = isset($_POST['remove_photo']);
            $newImage = uploadPlacePhoto($_FILES['photo'] ?? null);
            $photo = $newImage ?: ($remove ? '' : $oldImage);
            try {
                $store->save([
                    'id' => $id,
                    'name' => inputText('name', 160, true),
                    'kind' => $kind,
                    'hazard_type' => $hazard,
                    'description' => inputText('description', 1600),
                    'latitude' => (float)$latitude,
                    'longitude' => (float)$longitude,
                    'review_status' => $review,
                    'photo_name' => $photo,
                ]);
            } catch (Throwable $e) {
                if ($newImage) removePlacePhoto($newImage);
                throw $e;
            }
            if ($oldImage && $oldImage !== $photo) removePlacePhoto($oldImage);
            $_SESSION['flash'] = 'Map location saved.';
        }
        go('map-places');
    } catch (PDOException $e) {
        $error = 'Could not save. Check database tables in database/upgrade.sql.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
$edit = null;
if (is_string($_GET['edit'] ?? null) && $_GET['edit'] !== '') {
    $edit = $store->find($_GET['edit']);
    if (!$edit) { http_response_code(404); exit('Location not found.'); }
}
$records = $store->all();
