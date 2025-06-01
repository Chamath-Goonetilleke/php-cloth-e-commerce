<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$userData = [];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $userData = $result->fetch_assoc();
} else {
    // Handle error - user not found
    logout();
    header('Location: login.php');
    exit;
}

// Get user orders
$orders = [];
$sql = "SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Fetch wishlist products for the user
$wishlistProducts = [];
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT p.* FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $wishlistProducts[] = $row;
    }
}

// Fetch reviewed product IDs for this user/order
$reviewedProductIds = [];
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT product_id, order_id FROM reviews WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reviewedProductIds[$row['order_id']][] = $row['product_id'];
    }
}

// Process form submissions
$updateSuccess = $updateError = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    if (empty($name) || empty($email)) {
        $updateError = "Name and email are required fields";
    } else {
        try {
            // Update user data - try to update all fields, catching errors for missing columns
            // First check if phone and address columns exist
            $hasPhoneColumn = true;
            $hasAddressColumn = true;

            try {
                $result = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
                $hasPhoneColumn = $result && $result->num_rows > 0;
            } catch (Exception $e) {
                $hasPhoneColumn = false;
            }

            try {
                $result = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
                $hasAddressColumn = $result && $result->num_rows > 0;
            } catch (Exception $e) {
                $hasAddressColumn = false;
            }

            // Build the SQL query based on available columns
            $sql = "UPDATE users SET full_name = ?, email = ?";
            $params = array($name, $email);
            $types = "ss";

            if ($hasPhoneColumn) {
                $sql .= ", phone = ?";
                $params[] = $phone;
                $types .= "s";
            }

            if ($hasAddressColumn) {
                $sql .= ", address = ?";
                $params[] = $address;
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $userId;
            $types .= "i";

            $stmt = $conn->prepare($sql);

            // Create a reference array for bind_param
            $bindParams = array($types);
            for ($i = 0; $i < count($params); $i++) {
                $bindParams[] = &$params[$i];
            }

            // Call bind_param with references
            call_user_func_array(array($stmt, 'bind_param'), $bindParams);

            if ($stmt->execute()) {
                $updateSuccess = "Profile updated successfully!";
                // Refresh user data
                $userData['full_name'] = $name;
                $userData['email'] = $email;

                if ($hasPhoneColumn) {
                    $userData['phone'] = $phone;
                }

                if ($hasAddressColumn) {
                    $userData['address'] = $address;
                }
            } else {
                $updateError = "Error updating profile: " . $conn->error;
            }
        } catch (Exception $e) {
            // If there's an error, fall back to the simplest update
            $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $email, $userId);

            if ($stmt->execute()) {
                $updateSuccess = "Profile partially updated. Some fields could not be saved due to database structure.";
                // Refresh user data
                $userData['full_name'] = $name;
                $userData['email'] = $email;
            } else {
                $updateError = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $updateError = "All password fields are required";
    } elseif ($newPassword !== $confirmPassword) {
        $updateError = "New passwords do not match";
    } elseif (strlen($newPassword) < 6) {
        $updateError = "Password must be at least 6 characters long";
    } else {
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashedPassword, $userId);

                if ($stmt->execute()) {
                    $updateSuccess = "Password changed successfully!";
                } else {
                    $updateError = "Error changing password: " . $conn->error;
                }
            } else {
                $updateError = "Current password is incorrect";
            }
        } else {
            $updateError = "User not found";
        }
    }
}

// Set page variables
$pageTitle = "OneFit Clothing - My Account";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<div class="myac-container">
    <header class="myac-header">
        <h1 class="myac-title">My Account</h1>
        <button class="myac-logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </header>

    <div class="myac-profile-tabs">
        <div class="myac-tab-buttons">
            <button class="myac-tab-btn myac-active" data-tab="profile">Profile</button>
            <button class="myac-tab-btn" data-tab="orders">Orders</button>
            <button class="myac-tab-btn" data-tab="password">Password</button>
            <button class="myac-tab-btn" data-tab="addresses">Addresses</button>
            <button class="myac-tab-btn" data-tab="wishlist">Wishlist</button>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($updateSuccess)): ?>
            <div class="myac-alert myac-alert-success"><?php echo $updateSuccess; ?></div>
        <?php endif; ?>

        <?php if (!empty($updateError)): ?>
            <div class="myac-alert myac-alert-error"><?php echo $updateError; ?></div>
        <?php endif; ?>

        <!-- Profile Tab -->
        <div id="profile" class="myac-tab-content myac-active">
            <div class="myac-profile-info">
                <div class="myac-profile-header">
                    <div class="myac-avatar">
                        <span><?php
                                // Use available name field (full_name or name)
                                $displayName = !empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : 'U');
                                echo substr($displayName, 0, 1);
                                ?></span>
                    </div>
                    <div class="myac-user-details">
                        <h2><?php
                            // Use available name field (full_name or name)
                            echo htmlspecialchars(!empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : 'User'));
                            ?></h2>
                        <p><?php echo htmlspecialchars($userData['email']); ?></p>
                    </div>
                </div>

                <form class="myac-profile-form" method="POST" action="myaccount.php">
                    <div class="myac-form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php
                                                                        echo htmlspecialchars(!empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : ''));
                                                                        ?>" required>
                    </div>
                    <div class="myac-form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>

                    <?php
                    // Check if the phone column exists
                    $hasPhoneColumn = false;
                    try {
                        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
                        $hasPhoneColumn = $result && $result->num_rows > 0;
                    } catch (Exception $e) {
                        // Column doesn't exist
                    }

                    if ($hasPhoneColumn):
                    ?>
                        <div class="myac-form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                    <?php endif; ?>

                    <?php
                    // Check if the address column exists
                    $hasAddressColumn = false;
                    try {
                        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
                        $hasAddressColumn = $result && $result->num_rows > 0;
                    } catch (Exception $e) {
                        // Column doesn't exist
                    }

                    if ($hasAddressColumn):
                    ?>
                        <div class="myac-form-group">
                            <label for="address">Default Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasPhoneColumn || !$hasAddressColumn): ?>
                        <div class="myac-notification">
                            <p>Some profile fields are not available. <a href="database_fix.php">Click here</a> to update your database.</p>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="update_profile" class="myac-save-btn">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="myac-tab-content">
            <h2>Order History</h2>
            <?php if (empty($orders)): ?>
                <div class="myac-no-orders">
                    <p>You haven't placed any orders yet.</p>
                    <a href="tshirts.php" class="myac-shop-now-btn">Shop Now</a>
                </div>
            <?php else: ?>
                <div class="myac-orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="myac-order-card">
                            <div class="myac-order-header">
                                <div>
                                    <h3>Order #<?php echo $order['order_number']; ?></h3>
                                    <span class="myac-order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="myac-order-status <?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </div>
                            </div>
                            <div class="myac-order-details">
                                <div class="myac-order-info">
                                    <p><strong>Items:</strong> <?php echo $order['item_count']; ?></p>
                                    <p><strong>Total:</strong> RS <?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="myac-view-order-btn">View Details</a>
                            </div>
                            <?php
                            // Fetch order items for this order
                            $orderItems = [];
                            $stmt = $conn->prepare("SELECT oi.*, p.name, p.image_path FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt->bind_param("i", $order['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($item = $result->fetch_assoc()) {
                                $orderItems[] = $item;
                            }
                            ?>
                            <?php if ($order['status'] === 'delivered' && !empty($orderItems)): ?>
                                <div class="myac-order-products" style="padding:15px 20px;">
                                    <strong>Review Your Products:</strong>
                                    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:10px;">
                                        <?php foreach ($orderItems as $item): ?>
                                            <div style="background:#fafafa;padding:10px 15px;border-radius:8px;min-width:220px;display:flex;align-items:center;gap:10px;">
                                                <img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['name']; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;">
                                                <span><?php echo $item['name']; ?></span>
                                                <?php
                                                $alreadyReviewed = isset($reviewedProductIds[$order['id']]) && in_array($item['product_id'], $reviewedProductIds[$order['id']]);
                                                ?>
                                                <?php if (!$alreadyReviewed): ?>
                                                    <button class="myac-review-btn myac-view-order-btn" data-product-id="<?php echo $item['product_id']; ?>" data-order-id="<?php echo $order['id']; ?>" data-product-name="<?php echo htmlspecialchars($item['name']); ?>">Review</button>
                                                <?php else: ?>
                                                    <span style="color:#28a745;font-weight:600;margin-left:10px;">Reviewed</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Password Tab -->
        <div id="password" class="myac-tab-content">
            <h2>Change Password</h2>
            <form class="myac-password-form" method="POST" action="myaccount.php">
                <div class="myac-form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="myac-form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="myac-form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="myac-save-btn">Update Password</button>
            </form>
        </div>

        <!-- Addresses Tab -->
        <div id="addresses" class="myac-tab-content">
            <h2>Saved Addresses</h2>
            <div class="myac-address-wrapper">
                <div class="myac-address-card myac-default">
                    <div class="myac-address-header">
                        <h3>Default Address</h3>
                        <span class="myac-default-badge">Default</span>
                    </div>
                    <div class="myac-address-body">
                        <p><?php echo nl2br(htmlspecialchars($userData['address'] ?? 'No address saved yet.')); ?></p>
                    </div>
                    <div class="myac-address-actions">
                        <button class="myac-edit-btn" onclick="window.location.href='#profile'">Edit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wishlist Tab -->
        <div id="wishlist" class="myac-tab-content">
            <h2>My Wishlist</h2>
            <?php if (empty($wishlistProducts)): ?>
                <div class="myac-no-orders">
                    <p>Your wishlist is empty.</p>
                    <a href="index.php" class="myac-shop-now-btn">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="myac-orders-list">
                    <?php foreach ($wishlistProducts as $product): ?>
                        <div class="myac-order-card myac-wishlist-item" data-id="<?php echo $product['id']; ?>">
                            <div class="myac-order-header">
                                <div style="display:flex;align-items:center;gap:15px;">
                                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                    <div>
                                        <h3 style="margin:0;font-size:18px;"> <?php echo $product['name']; ?> </h3>
                                        <span class="myac-order-date">RS: <?php echo $product['sale_price'] ? $product['sale_price'] : $product['price']; ?> LKR</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="myac-view-order-btn" style="margin-right:10px;">View</a>
                                    <button class="myac-remove-wishlist-btn myac-view-order-btn" data-id="<?php echo $product['id']; ?>" style="background:#fff;color:#e63946;border:1px solid #e63946;">Remove</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="myac-reviewModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:30px 25px;border-radius:10px;max-width:400px;width:90vw;position:relative;">
        <button id="myac-closeReviewModal" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        <h3 id="myac-reviewProductName">Review Product</h3>
        <form id="myac-reviewForm">
            <input type="hidden" name="product_id" id="myac-reviewProductId">
            <input type="hidden" name="order_id" id="myac-reviewOrderId">
            <div style="margin:15px 0;">
                <label>Rating:</label>
                <div id="myac-starRating" style="font-size:28px;cursor:pointer;">
                    <span data-value="1">&#9734;</span>
                    <span data-value="2">&#9734;</span>
                    <span data-value="3">&#9734;</span>
                    <span data-value="4">&#9734;</span>
                    <span data-value="5">&#9734;</span>
                </div>
                <input type="hidden" name="rating" id="myac-reviewRating" required>
            </div>
            <div style="margin-bottom:15px;">
                <label for="myac-reviewFeedback">Feedback:</label>
                <textarea name="feedback" id="myac-reviewFeedback" rows="3" style="width:100%;border-radius:5px;"></textarea>
            </div>
            <button type="submit" class="myac-save-btn">Submit Review</button>
        </form>
        <div id="myac-reviewThankYou" style="display:none;color:#28a745;font-weight:600;text-align:center;margin-top:15px;">Thank you for your review!</div>
    </div>
</div>

<style>
    /* Account Page Specific Styles */
    :root {
        --myac-primary-color: #1D503A;
        --myac-logout-color: #ff4757;
        --myac-bg-light: #f9f9f9;
        --myac-text-dark: #333;
        --myac-accent: #1D503A;
        --myac-border-color: #e1e1e1;
        --myac-success-color: #28a745;
        --myac-error-color: #dc3545;
    }

    .myac-container {
        max-width: 960px;
        margin: 40px auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: myac-fadeIn 0.6s ease-in-out;
    }

    @keyframes myac-fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .myac-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 20px 30px;
        border-bottom: 1px solid var(--myac-border-color);
    }

    .myac-header h1 {
        font-size: 26px;
        color: var(--myac-primary-color);
    }

    .myac-logout-btn {
        background: white;
        color: var(--myac-logout-color);
        padding: 8px 16px;
        border: 1px solid var(--myac-logout-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .myac-logout-btn:hover {
        background: var(--myac-logout-color);
        color: white;
    }

    .myac-profile-tabs {
        padding: 20px 30px;
    }

    .myac-tab-buttons {
        display: flex;
        border-bottom: 1px solid var(--myac-border-color);
        margin-bottom: 30px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .myac-tab-buttons::-webkit-scrollbar {
        display: none;
    }

    .myac-tab-btn {
        background: none;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 500;
        color: #777;
        cursor: pointer;
        position: relative;
        white-space: nowrap;
    }

    .myac-tab-btn::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 0;
        height: 3px;
        background-color: var(--myac-primary-color);
        transition: width 0.3s ease;
    }

    .myac-tab-btn.myac-active {
        color: var(--myac-primary-color);
    }

    .myac-tab-btn.myac-active::after {
        width: 100%;
    }

    .myac-tab-content {
        display: none;
        animation: myac-fadeIn 0.4s ease-in-out;
    }

    .myac-tab-content.myac-active {
        display: block;
    }

    /* Profile Section */
    .myac-profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }

    .myac-avatar {
        width: 80px;
        height: 80px;
        background-color: var(--myac-primary-color);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 20px;
    }

    .myac-avatar span {
        color: white;
        font-size: 36px;
        font-weight: 600;
    }

    .myac-user-details h2 {
        margin-bottom: 5px;
        font-size: 24px;
    }

    .myac-user-details p {
        color: #777;
    }

    .myac-profile-form,
    .myac-password-form {
        margin-top: 20px;
    }

    .myac-form-group {
        margin-bottom: 20px;
    }

    .myac-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .myac-form-group input,
    .myac-form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--myac-border-color);
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .myac-form-group input:focus,
    .myac-form-group textarea:focus {
        border-color: var(--myac-primary-color);
        outline: none;
    }

    .myac-save-btn {
        background-color: var(--myac-primary-color);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .myac-save-btn:hover {
        background-color: #174330;
        transform: translateY(-2px);
    }

    /* Orders Section */
    .myac-no-orders {
        text-align: center;
        padding: 40px 0;
    }

    .myac-shop-now-btn {
        display: inline-block;
        background-color: var(--myac-primary-color);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 20px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .myac-shop-now-btn:hover {
        background-color: #174330;
        transform: translateY(-2px);
    }

    .myac-orders-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .myac-order-card {
        border: 1px solid var(--myac-border-color);
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.3s ease, transform 0.2s ease;
    }

    .myac-order-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .myac-order-header {
        padding: 15px 20px;
        background-color: #f9f9f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .myac-order-date {
        font-size: 14px;
        color: #777;
    }

    .myac-order-status {
        font-size: 14px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
    }

    .myac-order-status.processing {
        background-color: #ffeaa7;
        color: #d35400;
    }

    .myac-order-status.shipped {
        background-color: #81ecec;
        color: #00b894;
    }

    .myac-order-status.delivered {
        background-color: #55efc4;
        color: #00b894;
    }

    .myac-order-details {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .myac-view-order-btn {
        background-color: transparent;
        color: var(--myac-primary-color);
        padding: 8px 16px;
        border: 1px solid var(--myac-primary-color);
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .myac-view-order-btn:hover {
        background-color: var(--myac-primary-color);
        color: white;
    }

    /* Address Section */
    .myac-address-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .myac-address-card {
        border: 1px solid var(--myac-border-color);
        border-radius: 12px;
        padding: 20px;
        position: relative;
    }

    .myac-address-card.myac-default {
        border-color: var(--myac-primary-color);
    }

    .myac-address-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .myac-default-badge {
        background-color: var(--myac-primary-color);
        color: white;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .myac-address-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .myac-edit-btn {
        background-color: transparent;
        color: var(--myac-primary-color);
        padding: 8px 16px;
        border: 1px solid var(--myac-primary-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .myac-edit-btn:hover {
        background-color: var(--myac-primary-color);
        color: white;
    }

    .myac-delete-btn {
        background-color: transparent;
        color: var(--myac-error-color);
        padding: 8px 16px;
        border: 1px solid var(--myac-error-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .myac-delete-btn:hover {
        background-color: var(--myac-error-color);
        color: white;
    }

    /* Alert Messages */
    .myac-alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .myac-alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .myac-alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Notification for missing columns */
    .myac-notification {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
        font-size: 14px;
    }

    .myac-notification p {
        margin: 0;
    }

    .myac-notification a {
        color: #1D503A;
        font-weight: 600;
        text-decoration: none;
    }

    .myac-notification a:hover {
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .myac-container {
            margin: 20px;
            border-radius: 12px;
        }

        .myac-header {
            padding: 15px 20px;
        }

        .myac-profile-tabs {
            padding: 15px 20px;
        }

        .myac-tab-btn {
            padding: 10px 15px;
            font-size: 14px;
        }

        .myac-avatar {
            width: 60px;
            height: 60px;
        }

        .myac-avatar span {
            font-size: 24px;
        }

        .myac-user-details h2 {
            font-size: 20px;
        }

        .myac-order-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .myac-view-order-btn {
            align-self: flex-end;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('.myac-tab-btn');
        const tabContents = document.querySelectorAll('.myac-tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('myac-active'));
                tabContents.forEach(content => content.classList.remove('myac-active'));

                // Add active class to current button and content
                this.classList.add('myac-active');
                document.getElementById(tabName).classList.add('myac-active');
            });
        });

        // Check if there's a hash in the URL to activate specific tab
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabBtn = document.querySelector(`.myac-tab-btn[data-tab="${hash}"]`);
            if (tabBtn) {
                tabBtn.click();
            }
        }

        // Wishlist remove button
        document.querySelectorAll('.myac-remove-wishlist-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var productId = btn.getAttribute('data-id');
                fetch('wishlist-update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `product_id=${productId}&action=remove`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the wishlist item from DOM
                            var card = btn.closest('.myac-wishlist-item');
                            if (card) card.remove();
                        } else {
                            alert(data.message);
                        }
                    });
            });
        });

        // Review modal logic
        let currentProductId = null;
        let currentOrderId = null;
        document.querySelectorAll('.myac-review-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                currentProductId = btn.getAttribute('data-product-id');
                currentOrderId = btn.getAttribute('data-order-id');
                document.getElementById('myac-reviewProductId').value = currentProductId;
                document.getElementById('myac-reviewOrderId').value = currentOrderId;
                document.getElementById('myac-reviewProductName').innerText = 'Review: ' + btn.getAttribute('data-product-name');
                document.getElementById('myac-reviewModal').style.display = 'flex';
                document.getElementById('myac-reviewThankYou').style.display = 'none';
                document.getElementById('myac-reviewForm').style.display = 'block';
                // Reset stars
                setStarRating(0);
                document.getElementById('myac-reviewFeedback').value = '';
            });
        });
        document.getElementById('myac-closeReviewModal').onclick = function() {
            document.getElementById('myac-reviewModal').style.display = 'none';
        };
        // Star rating logic
        function setStarRating(rating) {
            document.getElementById('myac-reviewRating').value = rating;
            document.querySelectorAll('#myac-starRating span').forEach(function(star, idx) {
                star.innerHTML = idx < rating ? '&#9733;' : '&#9734;';
            });
        }
        document.querySelectorAll('#myac-starRating span').forEach(function(star) {
            star.addEventListener('mouseover', function() {
                setStarRating(parseInt(star.getAttribute('data-value')));
            });
            star.addEventListener('click', function() {
                setStarRating(parseInt(star.getAttribute('data-value')));
            });
        });
        // Review form submit
        document.getElementById('myac-reviewForm').onsubmit = function(e) {
            e.preventDefault();
            var form = e.target;
            var data = new FormData(form);
            fetch('review-submit.php', {
                    method: 'POST',
                    body: new URLSearchParams(data)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('myac-reviewForm').style.display = 'none';
                        document.getElementById('myac-reviewThankYou').style.display = 'block';
                        // Optionally, update the UI to show 'Reviewed' for this product
                        setTimeout(() => {
                            document.getElementById('myac-reviewModal').style.display = 'none';
                            location.reload();
                        }, 1200);
                    } else {
                        alert(data.message);
                    }
                });
        };
    });
</script>
<?php
// Include footer
include 'includes/footer.php';
?>