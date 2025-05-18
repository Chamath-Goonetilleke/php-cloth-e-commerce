<?php
// Load config and check authentication
require_once 'config.php';

// Check if on login page or require login
$isLoginPage = basename($_SERVER['PHP_SELF']) === 'login.php';
if (!$isLoginPage) {
    requireAdminLogin();

    // Get admin user data for display
    $adminData = getAdminData($_SESSION['admin_id']);
}

// Set default page title if not provided
if (!isset($pageTitle)) {
    $pageTitle = ADMIN_TITLE;
} else {
    $pageTitle = $pageTitle . ' - ' . ADMIN_TITLE;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Admin styles -->
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin-styles.css">
    <!-- Page specific styles -->
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>

<body>
    <?php if (!$isLoginPage): ?>
        <div class="admin-container">
            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h1>OneFit Admin</h1>
                    <button id="toggle-sidebar" class="toggle-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <nav class="sidebar-nav">
                    <ul>
                        <li>
                            <a href="<?php echo ADMIN_URL; ?>/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo ADMIN_URL; ?>/products.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo ADMIN_URL; ?>/orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo ADMIN_URL; ?>/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="sidebar-footer">
                    <a href="<?php echo ADMIN_URL; ?>/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Top Header Bar -->
                <header class="top-header">
                    <div class="search-container">
                        <form action="search.php" method="GET">
                            <input type="text" name="query" placeholder="Search...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div class="admin-profile">
                        <a href="<?php echo SITE_URL; ?>" target="_blank" class="view-site">
                            <i class="fas fa-external-link-alt"></i> View Site
                        </a>
                        <div class="profile-dropdown">
                            <button class="profile-btn">
                                <div class="admin-avatar">
                                    <span><?php echo isset($adminData) ? substr($adminData['username'], 0, 1) : 'A'; ?></span>
                                </div>
                                <span class="admin-name"><?php echo isset($adminData) ? $adminData['username'] : 'Admin'; ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="<?php echo ADMIN_URL; ?>/profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="content-wrapper">
                    <div class="page-header">
                        <h1><?php echo isset($contentTitle) ? $contentTitle : $pageTitle; ?></h1>
                        <?php if (isset($headerButtons)): ?>
                            <div class="header-buttons">
                                <?php echo $headerButtons; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['admin_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['admin_message_type']; ?>">
                            <?php
                            echo $_SESSION['admin_message'];
                            unset($_SESSION['admin_message']);
                            unset($_SESSION['admin_message_type']);
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
</body>

</html>