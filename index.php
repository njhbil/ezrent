<?php
// EZRENT Main Router

// Allow static files to be served by Vercel
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $_SERVER['REQUEST_URI'] ?? '')) {
    // Let Vercel serve static files from public/
    return false;
}

session_start();

// Load configurations
require_once 'php/config/database.php';
require_once 'php/config/midtrans.php';

// Get requested URL
$request = $_SERVER['REQUEST_URI'] ?? '/';

// Remove query string
$request = strtok($request, '?');

// Remove leading/trailing slashes
$request = trim($request, '/');

// Default to 'index' if empty
if (empty($request)) {
    $request = 'index';
}

// Check if file exists in pages folder
$page_file = "pages/{$request}.php";

if (file_exists($page_file)) {
    // Include the page
    include $page_file;
} elseif (is_dir("pages/{$request}")) {
    // If it's a directory (admin/, user/)
    $index_in_dir = "pages/{$request}/index.php";
    if (file_exists($index_in_dir)) {
        include $index_in_dir;
    } else {
        http_response_code(403);
        echo "Access forbidden";
    }
} else {
    // 404 Not Found
    http_response_code(404);
    if (file_exists('pages/404.php')) {
        include 'pages/404.php';
    } else {
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page '{$request}' does not exist.</p>";
        echo "<p><a href='/'>Go to Homepage</a></p>";
    }
}
