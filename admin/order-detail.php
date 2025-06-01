<?php
// Set page variables
$pageTitle = "Order Details";
$contentTitle = "Order Details";

// Include header
require_once 'includes/header.php';

// Get order ID from GET
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    echo '<div class="alert alert-danger">Invalid order ID.</div>';
    require_once 'includes/footer.php';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Order status updated successfully.</div>';
        } else {
            echo '<div class="alert alert-danger">Failed to update order status.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Invalid status selected.</div>';
    }
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderResult = $stmt->get_result();
if (!$orderResult || $orderResult->num_rows === 0) {
    echo '<div class="alert alert-danger">Order not found.</div>';
    require_once 'includes/footer.php';
    exit;
}
$order = $orderResult->fetch_assoc();

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.image_path FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$itemsResult = $stmt->get_result();
$orderItems = [];
if ($itemsResult && $itemsResult->num_rows > 0) {
    while ($row = $itemsResult->fetch_assoc()) {
        $orderItems[] = $row;
    }
}

// Status badge color
function getStatusBadge($status)
{
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<style>
    /* Modern Order Details Styling */
    .order-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .order-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .order-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 300;
    }

    .back-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 12px 24px;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        text-decoration: none;
        color: white;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .info-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
    }

    .info-card h4 {
        color: #333;
        margin-bottom: 20px;
        font-size: 1.3rem;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #f8f9fa;
    }

    .customer-info p {
        margin-bottom: 15px;
        line-height: 1.6;
    }

    .customer-info strong {
        color: #555;
        display: inline-block;
        width: 80px;
    }

    .status-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.badge-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-badge.badge-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #74b9ff;
    }

    .status-badge.badge-primary {
        background: #cce5ff;
        color: #004085;
        border: 1px solid #0984e3;
    }

    .status-badge.badge-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #00b894;
    }

    .status-badge.badge-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #e17055;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }

    .btn-update {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .totals-table {
        background: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
    }

    .totals-table table {
        width: 100%;
        margin: 0;
    }

    .totals-table td {
        padding: 12px 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .totals-table tr:last-child td {
        border-bottom: none;
        background: #667eea;
        color: white;
        font-weight: bold;
    }

    .items-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
    }

    .items-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 25px 30px;
    }

    .items-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead {
        background: #f8f9fa;
    }

    .items-table th {
        padding: 20px 15px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
    }

    .items-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .items-table tr:hover {
        background: #f8f9fa;
    }

    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .no-image {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 1.5rem;
    }

    .product-name {
        font-weight: 600;
        color: #333;
    }

    .price {
        font-weight: 600;
        color: #667eea;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: none;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .order-header {
            padding: 20px;
            text-align: center;
        }

        .order-header h1 {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .items-table {
            font-size: 0.9rem;
        }

        .items-table th,
        .items-table td {
            padding: 12px 8px;
        }
    }
</style>

<div class="order-container">
    <div class="order-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
            <a href="orders.php" class="back-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to Orders
            </a>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h4>👤 Customer Information</h4>
            <div class="customer-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>

            <h4>🚚 Shipping Address</h4>
            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?: $order['address'])); ?></p>

            <h4>💳 Billing Address</h4>
            <p><?php echo nl2br(htmlspecialchars($order['billing_address'] ?: $order['address'])); ?></p>
        </div>

        <div class="info-card">
            <h4>📋 Order Information</h4>

            <div class="status-form">
                <form method="POST" action="">
                    <p style="margin-bottom: 20px;">
                        <strong>Current Status:</strong><br>
                        <span class="status-badge badge-<?php echo getStatusBadge($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>

                    <div style="margin-bottom: 20px;">
                        <label for="status" style="display: block; margin-bottom: 8px; font-weight: 600;">Update Status:</label>
                        <select name="status" id="status" class="form-control" style="max-width: 300px; height:50px;">
                            <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>⏳ Pending</option>
                            <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>⚙️ Processing</option>
                            <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>🚚 Shipped</option>
                            <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>✅ Delivered</option>
                            <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>❌ Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-update">Update Status</button>
                </form>
            </div>

            <div style="margin: 20px 0;">
                <p><strong>📅 Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>💰 Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            </div>

            <h4>💵 Order Totals</h4>
            <div class="totals-table">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;">LKR <?php echo number_format($order['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td style="text-align: right;">LKR <?php echo number_format($order['shipping'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td style="text-align: right;"><strong>LKR <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="items-card">
        <div class="items-header">
            <h3>🛍️ Order Items</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orderItems)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px; color: #999;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
                                No items found for this order.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?php echo $item['image_path']; ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            class="product-image">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21,15 16,10 5,21" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['size']); ?></td>
                                <td><strong><?php echo $item['quantity']; ?></strong></td>
                                <td class="price">LKR <?php echo number_format($item['price'], 2); ?></td>
                                <td class="price"><strong>LKR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>