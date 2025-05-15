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

<div class="container">
    <header>
        <h1>My Account</h1>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </header>

    <div class="profile-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="profile">Profile</button>
            <button class="tab-btn" data-tab="orders">Orders</button>
            <button class="tab-btn" data-tab="password">Password</button>
            <button class="tab-btn" data-tab="addresses">Addresses</button>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($updateSuccess)): ?>
            <div class="alert alert-success"><?php echo $updateSuccess; ?></div>
        <?php endif; ?>

        <?php if (!empty($updateError)): ?>
            <div class="alert alert-error"><?php echo $updateError; ?></div>
        <?php endif; ?>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content active">
            <div class="profile-info">
                <div class="profile-header">
                    <div class="avatar">
                        <span><?php
                                // Use available name field (full_name or name)
                                $displayName = !empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : 'U');
                                echo substr($displayName, 0, 1);
                                ?></span>
                    </div>
                    <div class="user-details">
                        <h2><?php
                            // Use available name field (full_name or name)
                            echo htmlspecialchars(!empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : 'User'));
                            ?></h2>
                        <p><?php echo htmlspecialchars($userData['email']); ?></p>
                    </div>
                </div>

                <form class="profile-form" method="POST" action="myaccount.php">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php
                                                                        echo htmlspecialchars(!empty($userData['full_name']) ? $userData['full_name'] : (!empty($userData['name']) ? $userData['name'] : ''));
                                                                        ?>" required>
                    </div>
                    <div class="form-group">
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
                        <div class="form-group">
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
                        <div class="form-group">
                            <label for="address">Default Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasPhoneColumn || !$hasAddressColumn): ?>
                        <div class="notification">
                            <p>Some profile fields are not available. <a href="database_fix.php">Click here</a> to update your database.</p>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content">
            <h2>Order History</h2>
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <p>You haven't placed any orders yet.</p>
                    <a href="tshirts.php" class="shop-now-btn">Shop Now</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3>Order #<?php echo $order['order_number']; ?></h3>
                                    <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status <?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </div>
                            </div>
                            <div class="order-details">
                                <div class="order-info">
                                    <p><strong>Items:</strong> <?php echo $order['item_count']; ?></p>
                                    <p><strong>Total:</strong> RS <?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="view-order-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Password Tab -->
        <div id="password" class="tab-content">
            <h2>Change Password</h2>
            <form class="password-form" method="POST" action="myaccount.php">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="save-btn">Update Password</button>
            </form>
        </div>

        <!-- Addresses Tab -->
        <div id="addresses" class="tab-content">
            <h2>Saved Addresses</h2>
            <div class="address-wrapper">
                <div class="address-card default">
                    <div class="address-header">
                        <h3>Default Address</h3>
                        <span class="default-badge">Default</span>
                    </div>
                    <div class="address-body">
                        <p><?php echo nl2br(htmlspecialchars($userData['address'] ?? 'No address saved yet.')); ?></p>
                    </div>
                    <div class="address-actions">
                        <button class="edit-btn" onclick="window.location.href='#profile'">Edit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Account Page Specific Styles */
    :root {
        --primary-color: #1D503A;
        --logout-color: #ff4757;
        --bg-light: #f9f9f9;
        --text-dark: #333;
        --accent: #1D503A;
        --border-color: #e1e1e1;
        --success-color: #28a745;
        --error-color: #dc3545;
    }

    body {
        background: var(--bg-light);
        color: var(--text-dark);
    }

    .container {
        max-width: 960px;
        margin: 40px auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 20px 30px;
        border-bottom: 1px solid var(--border-color);
    }

    header h1 {
        font-size: 26px;
        color: var(--primary-color);
    }

    .logout-btn {
        background: white;
        color: var(--logout-color);
        padding: 8px 16px;
        border: 1px solid var(--logout-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background: var(--logout-color);
        color: white;
    }

    .profile-tabs {
        padding: 20px 30px;
    }

    .tab-buttons {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 30px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .tab-buttons::-webkit-scrollbar {
        display: none;
    }

    .tab-btn {
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

    .tab-btn::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 0;
        height: 3px;
        background-color: var(--primary-color);
        transition: width 0.3s ease;
    }

    .tab-btn.active {
        color: var(--primary-color);
    }

    .tab-btn.active::after {
        width: 100%;
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.4s ease-in-out;
    }

    .tab-content.active {
        display: block;
    }

    /* Profile Section */
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }

    .avatar {
        width: 80px;
        height: 80px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 20px;
    }

    .avatar span {
        color: white;
        font-size: 36px;
        font-weight: 600;
    }

    .user-details h2 {
        margin-bottom: 5px;
        font-size: 24px;
    }

    .user-details p {
        color: #777;
    }

    .profile-form,
    .password-form {
        margin-top: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--primary-color);
        outline: none;
    }

    .save-btn {
        background-color: var(--primary-color);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .save-btn:hover {
        background-color: #174330;
        transform: translateY(-2px);
    }

    /* Orders Section */
    .no-orders {
        text-align: center;
        padding: 40px 0;
    }

    .shop-now-btn {
        display: inline-block;
        background-color: var(--primary-color);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 20px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .shop-now-btn:hover {
        background-color: #174330;
        transform: translateY(-2px);
    }

    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.3s ease, transform 0.2s ease;
    }

    .order-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .order-header {
        padding: 15px 20px;
        background-color: #f9f9f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-date {
        font-size: 14px;
        color: #777;
    }

    .order-status {
        font-size: 14px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
    }

    .order-status.processing {
        background-color: #ffeaa7;
        color: #d35400;
    }

    .order-status.shipped {
        background-color: #81ecec;
        color: #00b894;
    }

    .order-status.delivered {
        background-color: #55efc4;
        color: #00b894;
    }

    .order-details {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .view-order-btn {
        background-color: transparent;
        color: var(--primary-color);
        padding: 8px 16px;
        border: 1px solid var(--primary-color);
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .view-order-btn:hover {
        background-color: var(--primary-color);
        color: white;
    }

    /* Address Section */
    .address-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .address-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        position: relative;
    }

    .address-card.default {
        border-color: var(--primary-color);
    }

    .address-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .default-badge {
        background-color: var(--primary-color);
        color: white;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .address-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .edit-btn {
        background-color: transparent;
        color: var(--primary-color);
        padding: 8px 16px;
        border: 1px solid var(--primary-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .edit-btn:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .delete-btn {
        background-color: transparent;
        color: var(--error-color);
        padding: 8px 16px;
        border: 1px solid var(--error-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .delete-btn:hover {
        background-color: var(--error-color);
        color: white;
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Notification for missing columns */
    .notification {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
        font-size: 14px;
    }

    .notification p {
        margin: 0;
    }

    .notification a {
        color: #1D503A;
        font-weight: 600;
        text-decoration: none;
    }

    .notification a:hover {
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            margin: 20px;
            border-radius: 12px;
        }

        header {
            padding: 15px 20px;
        }

        .profile-tabs {
            padding: 15px 20px;
        }

        .tab-btn {
            padding: 10px 15px;
            font-size: 14px;
        }

        .avatar {
            width: 60px;
            height: 60px;
        }

        .avatar span {
            font-size: 24px;
        }

        .user-details h2 {
            font-size: 20px;
        }

        .order-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .view-order-btn {
            align-self: flex-end;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to current button and content
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        // Check if there's a hash in the URL to activate specific tab
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabBtn = document.querySelector(`.tab-btn[data-tab="${hash}"]`);
            if (tabBtn) {
                tabBtn.click();
            }
        }
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>