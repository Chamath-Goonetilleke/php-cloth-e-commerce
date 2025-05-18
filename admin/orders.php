<?php
// Set page variables
$pageTitle = "Orders";
$contentTitle = "Orders Management";

// Include header
require_once 'includes/header.php';

// Get orders with pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$queryBase = "FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
$countQueryBase = "SELECT COUNT(*) as count $queryBase";
$dataQueryBase = "SELECT o.*, u.email, u.full_name $queryBase";
$params = array();
$types = "";

// Add filters
if (!empty($status)) {
    $queryBase .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($search)) {
    $queryBase .= " AND (o.order_number LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

// Get total orders count
$totalOrders = 0;
$countQuery = $countQueryBase;
$stmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $bindParams = array($types);
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalOrders = $row['count'];
}

$totalPages = ceil($totalOrders / $perPage);

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);

    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Order status updated successfully";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Error updating order status: " . $conn->error;
        $_SESSION['admin_message_type'] = "danger";
    }

    // Redirect to refresh
    header('Location: orders.php');
    exit;
}

// Get orders data
$dataQuery = $dataQueryBase . " ORDER BY o.created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($dataQuery);

// Add limit parameters
$limitParams = array($offset, $perPage);
$limitTypes = "ii";

if (!empty($params)) {
    $allParams = array_merge($params, $limitParams);
    $allTypes = $types . $limitTypes;

    $bindParams = array($allTypes);
    foreach ($allParams as $key => $value) {
        $bindParams[] = &$allParams[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
} else {
    $stmt->bind_param("ii", $offset, $perPage);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Status counts for filter
$statusCounts = array();
$statusQuery = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$result = $conn->query($statusQuery);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statusCounts[$row['status']] = $row['count'];
    }
}
?>

<!-- Orders Filter -->
<div class="card mb-20">
    <div class="card-body">
        <form action="orders.php" method="GET" class="d-flex flex-wrap align-items-center">
            <div class="form-group mb-0 mr-10">
                <input type="text" name="search" placeholder="Search orders..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="form-group mb-0 mr-10">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>
                        Pending (<?php echo isset($statusCounts['pending']) ? $statusCounts['pending'] : 0; ?>)
                    </option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>
                        Processing (<?php echo isset($statusCounts['processing']) ? $statusCounts['processing'] : 0; ?>)
                    </option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>
                        Shipped (<?php echo isset($statusCounts['shipped']) ? $statusCounts['shipped'] : 0; ?>)
                    </option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>
                        Delivered (<?php echo isset($statusCounts['delivered']) ? $statusCounts['delivered'] : 0; ?>)
                    </option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>
                        Cancelled (<?php echo isset($statusCounts['cancelled']) ? $statusCounts['cancelled'] : 0; ?>)
                    </option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mr-10">Filter</button>
            <a href="orders.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Orders List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_number']; ?></td>
                                <td>
                                    <?php echo !empty($order['full_name']) ? $order['full_name'] : 'N/A'; ?><br>
                                    <small><?php echo $order['email']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>LKR <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" action="orders.php" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-control status-select"
                                            data-order-id="<?php echo $order['id']; ?>"
                                            style="background-color: <?php
                                                                        echo $order['status'] == 'pending' ? '#ffeaa7' : ($order['status'] == 'processing' ? '#81ecec' : ($order['status'] == 'shipped' ? '#74b9ff' : ($order['status'] == 'delivered' ? '#55efc4' : ($order['status'] == 'cancelled' ? '#fab1a0' : ''))));
                                                                        ?>; color: <?php
                                                            echo $order['status'] == 'pending' ? '#d35400' : ($order['status'] == 'processing' ? '#0984e3' : ($order['status'] == 'shipped' ? '#0984e3' : ($order['status'] == 'delivered' ? '#00b894' : ($order['status'] == 'cancelled' ? '#d63031' : ''))));
                                                            ?>; border-color: transparent; font-weight: 500;">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="update-status-btn">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="invoice-print.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary" target="_blank" data-toggle="tooltip" title="Print Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-center mt-20">
                <ul class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <li><a href="orders.php?page=<?php echo $currentPage - 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a href="orders.php?page=<?php echo $i; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li><a href="orders.php?page=<?php echo $currentPage + 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .status-form {
        position: relative;
        display: inline-block;
    }

    .status-select {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 13px;
        appearance: none;
        -webkit-appearance: none;
        min-width: 120px;
    }

    .update-status-btn {
        display: none;
        position: absolute;
        top: 0;
        right: -70px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .update-status-btn:hover {
        background-color: var(--primary-dark);
    }
</style>

<script>
    $(document).ready(function() {
        // Handle status change
        $('.status-select').on('change', function() {
            $(this).siblings('.update-status-btn').show();
        });
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>