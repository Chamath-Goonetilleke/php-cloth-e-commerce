<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Get the order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
    // Invalid order ID
    header('Location: myaccount.php');
    exit;
}

// Get current user's ID
$userId = $_SESSION['user_id'];

// Get order details
$orderDetails = [];
$orderItems = [];

// First verify that this order belongs to the current user
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderId, $userId);
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
    // Order not found or doesn't belong to current user
    header('Location: myaccount.php');
    exit;
}

// Set page variables
$pageTitle = "Order Details - OneFit Clothing";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<section class="order-detail-section">
    <div class="container">
        <div class="page-header">
            <h1>Order Details</h1>
            <a href="myaccount.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Account</a>
        </div>

        <div class="order-detail-wrapper">
            <div class="order-header">
                <div class="order-main-info">
                    <h2>Order #<?php echo htmlspecialchars($orderDetails['order_number']); ?></h2>
                    <div class="order-date">Placed on <?php echo date('F j, Y', strtotime($orderDetails['created_at'])); ?></div>
                </div>
                <div class="order-status <?php echo strtolower($orderDetails['status']); ?>">
                    <?php echo ucfirst($orderDetails['status']); ?>
                </div>
            </div>

            <div class="order-sections">
                <div class="order-section">
                    <h3>Items (<?php echo count($orderItems); ?>)</h3>
                    <div class="order-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
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

                <div class="order-section">
                    <h3>Payment Information</h3>
                    <div class="payment-method">
                        <div class="label">Payment Method</div>
                        <div class="value"><?php echo ucfirst(htmlspecialchars($orderDetails['payment_method'])); ?></div>
                    </div>
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($orderDetails['subtotal'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>
                                <?php if ($orderDetails['shipping'] > 0): ?>
                                    $<?php echo number_format($orderDetails['shipping'], 2); ?>
                                <?php else: ?>
                                    Free Shipping
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span>$<?php echo number_format($orderDetails['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-section">
                    <h3>Shipping Information</h3>
                    <div class="address-info">
                        <div class="customer-name">
                            <?php echo htmlspecialchars($orderDetails['first_name'] . ' ' . $orderDetails['last_name']); ?>
                        </div>
                        <div class="address-line"><?php echo htmlspecialchars($orderDetails['address']); ?></div>
                        <div class="address-line">
                            <?php echo htmlspecialchars($orderDetails['city'] . ', ' . $orderDetails['state'] . ' ' . $orderDetails['zip_code']); ?>
                        </div>
                        <div class="address-line"><?php echo htmlspecialchars($orderDetails['country']); ?></div>
                        <div class="contact-info">
                            <div class="email"><?php echo htmlspecialchars($orderDetails['email']); ?></div>
                            <?php if (!empty($orderDetails['phone'])): ?>
                                <div class="phone"><?php echo htmlspecialchars($orderDetails['phone']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-actions">
                <a href="index.php" class="btn action-btn"><i class="fas fa-shopping-cart"></i> Continue Shopping</a>
                <?php if ($orderDetails['status'] === 'pending' || $orderDetails['status'] === 'processing'): ?>
                    <!-- Add cancel order functionality if needed -->
                    <!-- <button class="btn action-btn cancel-btn">Cancel Order</button> -->
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
    /* Order Detail Styles */
    .order-detail-section {
        padding: 60px 0;
        background-color: #f9f9f9;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 30px;
        color: #1D503A;
        margin: 0;
    }

    .back-link {
        color: #1D503A;
        font-weight: 500;
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .back-link i {
        margin-right: 5px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .order-detail-wrapper {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
        margin-bottom: 30px;
    }

    .order-main-info h2 {
        font-size: 22px;
        color: #333;
        margin: 0 0 8px 0;
    }

    .order-date {
        color: #666;
        font-size: 14px;
    }

    .order-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .order-status.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .order-status.processing {
        background-color: #cce5ff;
        color: #004085;
    }

    .order-status.shipped {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .order-status.delivered {
        background-color: #d4edda;
        color: #155724;
    }

    .order-status.cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .order-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .order-section {
        margin-bottom: 30px;
    }

    .order-section:first-child {
        grid-column: 1 / -1;
        /* Make the items section span full width */
    }

    .order-section h3 {
        font-size: 18px;
        color: #1D503A;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .order-items {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
    }

    .item-name {
        font-size: 16px;
        margin: 0 0 8px 0;
    }

    .item-meta {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .item-price {
        font-weight: 600;
        font-size: 16px;
    }

    .payment-method {
        margin-bottom: 20px;
    }

    .payment-method .label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #666;
    }

    .payment-method .value {
        font-size: 16px;
    }

    .order-summary {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .total-row {
        font-weight: 600;
        font-size: 18px;
        color: #1D503A;
        padding-top: 12px;
    }

    .address-info {
        line-height: 1.6;
    }

    .customer-name {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .contact-info {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    .order-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .action-btn i {
        margin-right: 5px;
    }

    .btn {
        background-color: #1D503A;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background-color: #163c2c;
    }

    .cancel-btn {
        background-color: #dc3545;
        margin-left: 10px;
    }

    .cancel-btn:hover {
        background-color: #c82333;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .order-detail-section {
            padding: 40px 0;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .order-header {
            flex-direction: column;
            gap: 15px;
        }

        .order-status {
            align-self: flex-start;
        }

        .order-sections {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .order-actions {
            flex-direction: column;
            gap: 10px;
        }

        .action-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>