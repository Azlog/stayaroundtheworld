<?php
define('shoppingcart', true);
// Determine the base URL
$base_url = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http';
$base_url .= '://' . rtrim($_SERVER['HTTP_HOST'], '/');
$base_url .= $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ? '' : ':' . $_SERVER['SERVER_PORT'];
$base_url .= '/' . ltrim(substr(str_replace('\\', '/', realpath(__DIR__)), strlen($_SERVER['DOCUMENT_ROOT'])), '/');
define('base_url', rtrim($base_url, '/') . '/');
// If somehow the above URL fails to resolve the correct URL, you can simply comment out the below line and manually specifiy the URL to the system.
// define('base_url', 'http://yourdomain.com/shoppingcart/');
// Initialize a new session
session_start();
// Include the configuration file, this contains settings you can change.
include 'config.php';
// Include functions and connect to the database using PDO MySQL
include 'functions.php';
// Connect to MySQL database
$pdo = pdo_connect_mysql();
// Output error variable
$error = '';
// Define all the routes for all pages
$url = routes([
    '/',
    '/home',
    '/product/{id}',
    '/products',
    '/products/{category}/{sort}',
    '/products/{p}/{category}/{sort}',
    '/myaccount',
    '/download/{id}',
    '/cart',
    '/cart/{remove}',
    '/checkout',
    '/placeorder',
    '/search/{query}',
    '/logout'
]);
// Check if route exists
if ($url) {
    include $url;
} else {
    // Page is set to home (home.php) by default, so when the visitor visits that will be the page they see.
    $page = isset($_GET['page']) && file_exists($_GET['page'] . '.php') ? $_GET['page'] : 'home';
    // Include the requested page
    include $page . '.php';
}
?>
