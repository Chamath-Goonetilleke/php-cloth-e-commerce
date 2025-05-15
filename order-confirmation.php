<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if order information is available in session
if (!isset($_SESSION['order_complete']) || empty($_SESSION['order_complete'])) {
    // Redirect to home page if no order info
    header('Location: index.php');
    exit;
}

// Get order information from session
$orderInfo = $_SESSION['order_complete'];
$orderId = $orderInfo['order_id'];
$orderNumber = $orderInfo['order_number'];
$orderTotal = $orderInfo['total'];

// Get order details from database
$orderDetails = [];
$orderItems = [];

$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $orderDetails = $result->fetch_assoc();

    // Get order items
    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();

    if ($itemsResult) {
        while ($item = $itemsResult->fetch_assoc()) {
            $orderItems[] = $item;
        }
    }
} else {
    // Order not found in database, use session data only
    $orderDetails = [
        'order_number' => $orderNumber,
        'total_amount' => $orderTotal,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Clear order complete data from session
// Commented out for now to allow refreshing the page
// unset($_SESSION['order_complete']);

// Set page variables
$pageTitle = "Order Confirmation - OneFit Clothing";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<section class="confirmation-section">
    <div class="container">
        <div class="confirmation-wrapper">
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Thank You for Your Order!</h1>
                <p class="confirmation-message">Your order has been successfully placed and is being processed.</p>
            </div>

            <div class="order-info">
                <div class="order-info-row">
                    <div class="order-info-item">
                        <span class="label">Order Number:</span>
                        <span class="value"><?php echo htmlspecialchars($orderNumber); ?></span>
                    </div>
                    <div class="order-info-item">
                        <span class="label">Date:</span>
                        <span class="value"><?php echo date('F j, Y', strtotime($orderDetails['created_at'])); ?></span>
                    </div>
                </div>
                <div class="order-info-row">
                    <div class="order-info-item">
                        <span class="label">Total Amount:</span>
                        <span class="value">$<?php echo number_format($orderTotal, 2); ?></span>
                    </div>
                    <div class="order-info-item">
                        <span class="label">Payment Method:</span>
                        <span class="value"><?php echo htmlspecialchars(ucfirst($orderDetails['payment_method'] ?? 'Unknown')); ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($orderItems)): ?>
                <div class="order-items">
                    <h2>Order Items</h2>
                    <div class="order-items-list">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-meta">
                                        Size: <?php echo htmlspecialchars($item['size']); ?> × <?php echo $item['quantity']; ?>
                                    </p>
                                </div>
                                <div class="item-price">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($orderDetails['subtotal'] ?? ($orderTotal - ($orderDetails['shipping'] ?? 0)), 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>
                        <?php if (isset($orderDetails['shipping']) && $orderDetails['shipping'] > 0): ?>
                            $<?php echo number_format($orderDetails['shipping'], 2); ?>
                        <?php else: ?>
                            Free Shipping
                        <?php endif; ?>
                    </span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span>$<?php echo number_format($orderTotal, 2); ?></span>
                </div>
            </div>

            <div class="shipping-address">
                <h2>Shipping Address</h2>
                <p>
                    <?php
                    $name = htmlspecialchars($orderDetails['first_name'] ?? '') . ' ' . htmlspecialchars($orderDetails['last_name'] ?? '');
                    $address = htmlspecialchars($orderDetails['address'] ?? '');
                    $city = htmlspecialchars($orderDetails['city'] ?? '');
                    $state = htmlspecialchars($orderDetails['state'] ?? '');
                    $zipCode = htmlspecialchars($orderDetails['zip_code'] ?? '');
                    $country = htmlspecialchars($orderDetails['country'] ?? '');

                    echo $name . '<br>';
                    echo $address . '<br>';
                    echo $city . ', ' . $state . ' ' . $zipCode . '<br>';
                    echo $country;
                    ?>
                </p>
            </div>

            <div class="next-steps">
                <p>A confirmation email has been sent to <?php echo htmlspecialchars($orderDetails['email'] ?? 'your email address'); ?>.</p>
                <p>You can view your order status by visiting <a href="myaccount.php">My Account</a>.</p>
                <div class="action-buttons">
                    <a href="index.php" class="btn continue-shopping">Continue Shopping</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="myaccount.php" class="btn view-account">View Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Confirmation Page Styles */
    .confirmation-section {
        padding: 60px 0;
        background-color: #f9f9f9;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .confirmation-wrapper {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        padding: 40px;
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .confirmation-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .success-icon {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
    }

    .confirmation-header h1 {
        color: #1D503A;
        font-size: 32px;
        margin-bottom: 10px;
    }

    .confirmation-message {
        color: #666;
        font-size: 18px;
    }

    .order-info {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .order-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .order-info-row:last-child {
        margin-bottom: 0;
    }

    .order-info-item {
        flex: 1;
    }

    .order-info-item .label {
        font-weight: 600;
        color: #666;
        display: block;
        margin-bottom: 5px;
    }

    .order-info-item .value {
        font-size: 18px;
        color: #333;
    }

    .order-items {
        margin-bottom: 30px;
    }

    .order-items h2 {
        color: #1D503A;
        font-size: 22px;
        margin-bottom: 15px;
    }

    .order-items-list {
        border-top: 1px solid #eee;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .item-name {
        font-size: 16px;
        margin: 0 0 5px 0;
    }

    .item-meta {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .item-price {
        font-weight: 600;
        font-size: 16px;
    }

    .order-summary {
        margin-bottom: 30px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .total-row {
        font-size: 20px;
        font-weight: 600;
        color: #1D503A;
        border-bottom: none;
        padding-top: 15px;
    }

    .shipping-address {
        margin-bottom: 30px;
    }

    .shipping-address h2 {
        color: #1D503A;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .shipping-address p {
        line-height: 1.6;
    }

    .next-steps {
        text-align: center;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }

    .next-steps p {
        margin-bottom: 10px;
    }

    .next-steps a {
        color: #1D503A;
        font-weight: 600;
    }

    .action-buttons {
        margin-top: 25px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .btn {
        display: inline-block;
        padding: 12px 25px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .continue-shopping {
        background-color: #1D503A;
        color: white;
    }

    .continue-shopping:hover {
        color: white;
    }

    .view-account {
        background-color: transparent;
        color: #1D503A;
        border: 1px solid #1D503A;
    }

    .view-account:hover {
        background-color: #1D503A;
        color: white;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .confirmation-section {
            padding: 40px 0;
        }

        .confirmation-wrapper {
            padding: 25px;
        }

        .order-info-row {
            flex-direction: column;
        }

        .order-info-item {
            margin-bottom: 15px;
        }

        .action-buttons {
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>