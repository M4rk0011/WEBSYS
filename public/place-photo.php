<?php
/**
 * Public request handler or view for the place photo page.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// Download only a photo belonging to a map marker this account may see.
require __DIR__ . '/../src/services/service.php';
$currentUser = isset($_SESSION['user_id']) ? findUser((string)$_SESSION['user_id']) : null;
if (!$currentUser || !$currentUser->isActive) { http_response_code(401); exit; }
$id = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
$place = directory('places')->find($id);
if (!$place || !canSeePlace($place)) { http_response_code(404); exit; }
$name = (string)($place['photo_name'] ?? '');
if (!validPhotoName($name) || !is_file(placePhotoDir() . '/' . $name)) { http_response_code(404); exit; }
$mime = ['jpg'=>'image/jpeg', 'png'=>'image/png', 'webp'=>'image/webp'][pathinfo($name, PATHINFO_EXTENSION)];
session_write_close();
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $name . '"');
header("Content-Security-Policy: default-src 'none'; sandbox");
header('Content-Length: ' . filesize(placePhotoDir() . '/' . $name));
readfile(placePhotoDir() . '/' . $name);
