<?php
// Set page variables
$pageTitle = "Dashboard";
$contentTitle = "Dashboard";

// Include header
require_once 'includes/header.php';

// Get database statistics
$statsData = array(
    'products' => 0,
    'orders' => 0,
    'users' => 0,
    'revenue' => 0
);

// Count products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $statsData['products'] = $row['count'];
}

// Count orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $statsData['orders'] = $row['count'];
}

// Count users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $statsData['users'] = $row['count'];
}

// Calculate total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $statsData['revenue'] = $row['total'] ? $row['total'] : 0;
}

// Get recent orders (limited to 5)
$recentOrders = array();
$sql = "SELECT o.*, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Get recent users (limited to 5)
$recentUsers = array();
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}

// Get sales by month for the chart
$monthlySales = array();
$sql = "SELECT 
            MONTH(created_at) as month, 
            YEAR(created_at) as year, 
            COUNT(*) as order_count, 
            SUM(total_amount) as total_sales 
        FROM orders 
        WHERE status != 'cancelled' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
        GROUP BY YEAR(created_at), MONTH(created_at) 
        ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthName = date('M', mktime(0, 0, 0, $row['month'], 1));
        $monthlySales[] = array(
            'month' => $monthName . ' ' . $row['year'],
            'count' => $row['order_count'],
            'total' => $row['total_sales']
        );
    }
}
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon products">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $statsData['products']; ?></h3>
            <span>Total Products</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orders">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $statsData['orders']; ?></h3>
            <span>Total Orders</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $statsData['users']; ?></h3>
            <span>Registered Users</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon revenue">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>LKR <?php echo number_format($statsData['revenue'], 2); ?></h3>
            <span>Total Revenue</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mt-20">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales Overview</h3>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card mt-20">
    <div class="card-header">
        <h3 class="card-title">Recent Orders</h3>
        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_number']; ?></td>
                                <td><?php echo $order['email']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>LKR <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo $order['status'] == 'completed' ? 'success' : ($order['status'] == 'processing' ? 'info' : ($order['status'] == 'cancelled' ? 'danger' : 'warning'));
                                                                ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="card mt-20">
    <div class="card-header">
        <h3 class="card-title">Recent Users</h3>
        <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo isset($user['full_name']) ? $user['full_name'] : 'N/A'; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Initialize charts when document is ready
    $(document).ready(function() {
        // Sales Chart
        var salesChartEl = document.getElementById('salesChart').getContext('2d');

        // Prepare data for chart
        var months = [];
        var sales = [];
        var orderCounts = [];

        <?php foreach ($monthlySales as $data): ?>
            months.push('<?php echo $data['month']; ?>');
            sales.push(<?php echo $data['total']; ?>);
            orderCounts.push(<?php echo $data['count']; ?>);
        <?php endforeach; ?>

        // Create chart
        var salesChart = new Chart(salesChartEl, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'Sales (LKR)',
                        data: sales,
                        backgroundColor: 'rgba(29, 80, 58, 0.7)',
                        borderColor: 'rgba(29, 80, 58, 1)',
                        borderWidth: 1,
                        yAxisID: 'y-axis-1'
                    },
                    {
                        label: 'Orders',
                        data: orderCounts,
                        type: 'line',
                        borderColor: 'rgba(230, 57, 70, 1)',
                        backgroundColor: 'rgba(230, 57, 70, 0.2)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(230, 57, 70, 1)',
                        pointRadius: 4,
                        fill: true,
                        yAxisID: 'y-axis-2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                            id: 'y-axis-1',
                            type: 'linear',
                            position: 'left',
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return 'LKR ' + value.toLocaleString();
                                }
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Revenue (LKR)'
                            }
                        },
                        {
                            id: 'y-axis-2',
                            type: 'linear',
                            position: 'right',
                            ticks: {
                                beginAtZero: true
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Orders'
                            },
                            gridLines: {
                                display: false
                            }
                        }
                    ]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var value = dataset.data[tooltipItem.index];

                            if (dataset.label.includes('Sales')) {
                                return dataset.label + ': LKR ' + value.toLocaleString();
                            }

                            return dataset.label + ': ' + value;
                        }
                    }
                }
            }
        });
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>