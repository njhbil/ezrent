<?php
// EZRENT Main Router

// Start session
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
} else {
    // Check if it's a directory (like admin/, user/)
    if (is_dir("pages/{$request}")) {
        $index_in_dir = "pages/{$request}/index.php";
        if (file_exists($index_in_dir)) {
            include $index_in_dir;
        } else {
            // Directory exists but no index.php
            http_response_code(403);
            echo "Directory access forbidden";
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
}
