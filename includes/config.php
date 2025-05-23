<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your MySQL username
define('DB_PASS', 'root');  // Change this to your MySQL password
define('DB_NAME', 'onefit_clothing');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 8889);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site URL and constants
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/onefitclothing');
define('SITE_NAME', 'OneFit Clothing');

// Debug mode (set to true to see debug information)
define('DEBUG', true);  // Set to false for production

// Set error reporting based on debug mode
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__FILE__) . '/../error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__FILE__) . '/../error.log');
}
