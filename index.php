<?php
/**
 * Entry point for the project; forwards visitors to the public application.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
// XAMPP shortcut: open http://localhost/LOWER-GENERAL-LUNA-V3-ORGANIZED/
// The application itself runs from public/.
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: public/index.php' . ($query !== '' ? '?' . $query : ''), true, 302);
exit;
