<?php
require_once 'includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Reset cart
$_SESSION['cart'] = [];

// Output debug info if DEBUG is enabled
if (defined('DEBUG') && DEBUG === true) {
    echo "<h2>Cart Reset</h2>";
    echo "<p>The shopping cart has been reset.</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    // Redirect to cart page or homepage
    header("Location: cart.php");
    exit;
}
?>

<p><a href="cart.php">Go to Cart</a></p>
<p><a href="index.php">Go to Homepage</a></p>
<p><a href="test-cart.php">Go to Test Cart Page</a></p>