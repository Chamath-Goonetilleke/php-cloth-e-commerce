<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Log the user out
logout();

// Redirect to home page
header('Location: index.php');
exit;
