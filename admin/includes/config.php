<?php
// Database configuration
require_once $_SERVER['DOCUMENT_ROOT'] . '/onefitclothing/includes/config.php';

// Admin specific constants
define('ADMIN_URL', SITE_URL . '/admin');
define('ADMIN_TITLE', 'OneFit Clothing Admin');

// Check if admin is logged in
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireAdminLogin()
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Get admin user data
function getAdminData($adminId)
{
    global $conn;

    $sql = "SELECT * FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
