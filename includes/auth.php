<?php
require_once 'config.php';

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $full_name)
{
    global $conn;

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // User already exists
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);

    if ($stmt->execute()) {
        return $conn->insert_id; // Return the new user ID
    }

    return false;
}

/**
 * Authenticate user login
 */
function login($username, $password)
{
    global $conn;

    // Get user by username or email, including blocked status
    $stmt = $conn->prepare("SELECT id, username, password, role, blocked FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if blocked
        if (!empty($user['blocked'])) {
            return 'blocked';
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            return true;
        }
    }

    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Logout user
 */
function logout()
{
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to home page
    header("Location: " . SITE_URL);
    exit();
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    global $conn;
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, username, email, full_name, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return null;
}
