<?php
require_once 'includes/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default page title if not provided
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME . ' - Trendy & Cozy Wear';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/OneFit Clothing.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="<?php echo SITE_URL; ?>" class="logo">OneFit</a>
        <div class="search-container">
            <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                <input type="text" name="q" placeholder="Search for products...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="nav-links">
            <div class="links">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <a href="<?php echo SITE_URL; ?>/tshirts.php">T-Shirts</a>
                <a href="<?php echo SITE_URL; ?>/hoodies.php">Hoodies</a>
                <a href="<?php echo SITE_URL; ?>/aboutus.php">About Us</a>
                <a href="<?php echo SITE_URL; ?>/contactus.php">Contact</a>
            </div>
            <div class="icons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo SITE_URL; ?>/myaccount.php" class="icon"><i class="fas fa-user"></i></a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="icon"><i class="fas fa-user"></i></a>
                <?php endif; ?>
                <a href="<?php echo SITE_URL; ?>/cart.php" class="icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <?php if (isset($showSaleBanner) && $showSaleBanner): ?>
        <!-- Sale Banner -->
        <div class="sale-banner">
            🔥 SUMMER SALE! Use code SUMMER25 for 25% off all items! Limited time offer! 🔥
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>