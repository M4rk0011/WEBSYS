<?php
// Location marker images are private and served via place-photo.php.
function placePhotoDir(): string
{
    return dirname(__DIR__, 2) . '/storage/place-photos';
}
function uploadPlacePhoto(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo upload failed. Maximum 2 MB.');
    }
    $tmp = $file['tmp_name'] ?? '';
    if (!is_string($tmp) || !is_uploaded_file($tmp) || filesize($tmp) > 2 * 1024 * 1024) {
        throw new RuntimeException('Invalid image. Upload JPG, PNG or WebP (maximum 2 MB).');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    $info = @getimagesize($tmp);
    if (!$ext || !$info || ($info['mime'] ?? '') !== $mime || $info[0] * $info[1] > 20000000) {
        throw new RuntimeException('Only valid JPG, PNG or WebP images under 20 megapixels are allowed.');
    }
    $dir = placePhotoDir();
    if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
        throw new RuntimeException('Unable to create the photo directory.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new RuntimeException('Could not save image. Check folder permissions.');
    }
    return $name;
}
function removePlacePhoto(string $name): void
{
    if (validPhotoName($name) && is_file(placePhotoDir() . '/' . $name)) {
        @unlink(placePhotoDir() . '/' . $name);
    }
}
