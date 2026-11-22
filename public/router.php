<?php
// Optional for PHP's built-in server: php -S localhost:8000 -t public public/router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($path === '/router.php') { http_response_code(404); exit('Not found.'); }
if (str_starts_with($path, '/partials/')) { http_response_code(403); exit('Forbidden.'); }
if ($path !== '/' && is_file(__DIR__ . $path)) return false;
if ($path === '/' || $path === '/index.php') { require __DIR__ . '/index.php'; return; }
if (preg_match('~^/(concerns|incidents)/([a-zA-Z0-9_-]+)/?$~', $path, $m)) {
    $_GET['page'] = 'details'; $_GET['id'] = $m[2];
    require __DIR__ . '/index.php'; return;
}
$routes = [
 'register'=>'signup', 'incidents'=>'complaints', 'concerns'=>'complaints',
 'login'=>'login', 'dashboard'=>'dashboard', 'profile'=>'profile',
 'users'=>'users', 'departments'=>'departments', 'complaints'=>'complaints',
 'report'=>'report', 'details'=>'details', 'map'=>'map',
 'announcements'=>'announcements', 'monitoring'=>'monitoring', 'analytics'=>'analytics',
 'residents'=>'residents', 'businesses'=>'businesses', 'map-places'=>'map-places',
];
$key = trim($path,'/');
if (isset($routes[$key])) {
    header('Location: '. $routes[$key] . '.php', true, 302); exit;
}
http_response_code(404);echo 'Not found.';
