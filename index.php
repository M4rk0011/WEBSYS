<?php
// XAMPP shortcut: open http://localhost/LOWER-GENERAL-LUNA-V3-ORGANIZED/
// The application itself runs from public/.
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: public/index.php' . ($query !== '' ? '?' . $query : ''), true, 302);
exit;
