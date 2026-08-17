<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) return false;
$route = trim($path, '/');
$_GET['route'] = $route;
require __DIR__ . '/index.php';
