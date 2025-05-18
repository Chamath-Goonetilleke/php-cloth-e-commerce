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

<div class="card mb-20">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
        <a href="orders.php" class="btn btn-secondary">&larr; Back to Orders</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Customer Info</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?><br>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                </p>
                <h4>Shipping Address</h4>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?: $order['address'])); ?></p>
                <h4>Billing Address</h4>
                <p><?php echo nl2br(htmlspecialchars($order['billing_address'] ?: $order['address'])); ?></p>
            </div>
            <div class="col-md-6">
                <h4>Order Info</h4>
                <form method="POST" action="">
                    <p>
                        <strong>Status:</strong>
                        <span class="badge badge-<?php echo getStatusBadge($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                    </p>
                    <div class="form-group" style="max-width: 250px;">
                        <label for="status">Change Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>Processing (Approve)</option>
                            <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </form>
                <p class="mt-10"><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?><br>
                    <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                </p>
                <h4>Totals</h4>
                <table class="table table-sm">
                    <tr>
                        <td>Subtotal:</td>
                        <td>LKR <?php echo number_format($order['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td>LKR <?php echo number_format($order['shipping'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total:</strong></td>
                        <td><strong>LKR <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Order Items</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
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
                            <td colspan="6" class="text-center">No items found for this order.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['size']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>LKR <?php echo number_format($item['price'], 2); ?></td>
                                <td>LKR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
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
