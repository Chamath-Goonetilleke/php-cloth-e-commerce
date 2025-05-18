<?php
// Set page variables
$pageTitle = "Users";
$contentTitle = "Users Management";
$headerButtons = '<a href="user-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New User</a>';

// Include header
require_once 'includes/header.php';

// Get users with pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get total users count
$totalUsers = 0;
$countSql = "SELECT COUNT(*) as count FROM users";

if (!empty($search)) {
    $countSql .= " WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
}

$stmt = $conn->prepare($countSql);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalUsers = $row['count'];
}

$totalPages = ceil($totalUsers / $perPage);

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = (int)$_GET['delete'];

    // Check if user has orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $_SESSION['admin_message'] = "Cannot delete user with existing orders. Consider deactivating instead.";
        $_SESSION['admin_message_type'] = "warning";
    } else {
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "User deleted successfully";
            $_SESSION['admin_message_type'] = "success";
        } else {
            $_SESSION['admin_message'] = "Error deleting user: " . $conn->error;
            $_SESSION['admin_message_type'] = "danger";
        }
    }

    // Redirect to refresh
    header('Location: users.php');
    exit;
}

// Get users
$users = array();
$sql = "SELECT * FROM users";

if (!empty($search)) {
    $sql .= " WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
}

$sql .= " ORDER BY created_at DESC LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $offset, $perPage);
} else {
    $stmt->bind_param("ii", $offset, $perPage);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!-- Users List -->
<div class="card">
    <div class="card-body">
        <!-- Search Form -->
        <div class="d-flex justify-content-between align-items-center mb-20">
            <form action="users.php" method="GET" class="d-flex align-items-center">
                <div class="form-group mb-0 mr-10">
                    <input type="text" name="search" placeholder="Search users..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div>
                <a href="users.php" class="btn btn-secondary mr-10">Reset</a>
                <a href="user-add.php" class="btn btn-primary">Add New User</a>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Orders</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar" style="background-color: #1D503A; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <?php echo $user['full_name'] ?? 'N/A'; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['phone'] ?? 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php
                                    // Get order count for this user
                                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                                    $stmt->bind_param("i", $user['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $orderCount = 0;

                                    if ($result && $result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $orderCount = $row['count'];
                                    }
                                    ?>
                                    <a href="orders.php?user_id=<?php echo $user['id']; ?>" class="badge badge-<?php echo $orderCount > 0 ? 'info' : 'secondary'; ?>">
                                        <?php echo $orderCount; ?> order<?php echo $orderCount != 1 ? 's' : ''; ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="user-view.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger confirm-delete" data-toggle="tooltip" title="Delete" data-confirm-message="Are you sure you want to delete this user? This cannot be undone.">
                                            <i class="fas fa-trash"></i>
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
                        <li><a href="users.php?page=<?php echo $currentPage - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a href="users.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li><a href="users.php?page=<?php echo $currentPage + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Statistics -->
<div class="card mt-20">
    <div class="card-header">
        <h3 class="card-title">User Statistics</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?php
                // Get new users in last 30 days
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stmt->execute();
                $result = $stmt->get_result();
                $newUsers = 0;

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $newUsers = $row['count'];
                }
                ?>
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $newUsers; ?></h3>
                        <span>New Users (30 days)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php
                // Get users with orders
                $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM orders");
                $stmt->execute();
                $result = $stmt->get_result();
                $usersWithOrders = 0;

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $usersWithOrders = $row['count'];
                }
                ?>
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $usersWithOrders; ?></h3>
                        <span>Users with Orders</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php
                // Calculate average orders per user
                $avgOrders = 0;
                if ($usersWithOrders > 0) {
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $totalOrders = $row['count'];
                        $avgOrders = $totalOrders / $usersWithOrders;
                    }
                }
                ?>
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($avgOrders, 1); ?></h3>
                        <span>Avg. Orders per User</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .user-avatar {
        font-weight: 600;
        font-size: 14px;
    }

    .col-md-4 {
        width: 33.33%;
        float: left;
        padding: 0 15px;
        box-sizing: border-box;
    }

    .row {
        margin: 0 -15px;
        display: flex;
        flex-wrap: wrap;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    @media (max-width: 768px) {
        .col-md-4 {
            width: 100%;
            margin-bottom: 20px;
        }
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>