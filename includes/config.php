<?php

// Include constants first
require_once __DIR__ . '/constants.php';

// Base URL - will be set by Railway
$base_url = getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : '';

// Database configuration - Railway provides these via MySQL plugin
$db_host = getenv('MYSQL_HOST') ?: 'localhost';
$db_user = getenv('MYSQL_USER') ?: 'root';
$db_pass = getenv('MYSQL_PASSWORD') ?: '';
$db_name = getenv('MYSQL_DATABASE') ?: 'society_management';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    // Don't show detailed errors in production
    if (getenv('ENVIRONMENT') === 'production') {
        die("Database connection failed. Please try again later.");
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}

$conn->set_charset("utf8mb4");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
if (getenv('ENVIRONMENT') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Include functions if exists
if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}
?>