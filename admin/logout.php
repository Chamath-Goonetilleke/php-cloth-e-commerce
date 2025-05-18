<?php
// Include config file
require_once 'includes/config.php';

// Unset all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Set logout message (optional)
$_SESSION['admin_message'] = "You have been logged out successfully";
$_SESSION['admin_message_type'] = 'info';

// Redirect to login page
header('Location: login.php');
exit;
