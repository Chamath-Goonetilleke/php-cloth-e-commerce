<?php
// Set page variables
$pageTitle = "Users";
$contentTitle = "Users Management";

// Include header
include 'includes/header.php';

// Security: Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Configuration
$perPage = 10;
$maxPerPage = 100;

// Get and validate pagination
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? min($maxPerPage, max(5, (int)$_GET['per_page'])) : $perPage;
$offset = ($currentPage - 1) * $perPage;

// Get and sanitize search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParam = !empty($search) ? "%$search%" : null;

// Status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // Get total users count with filters
    $countSql = "SELECT COUNT(*) as count FROM users WHERE 1=1";
    $countParams = [];
    $countTypes = "";

    if (!empty($search)) {
        $countSql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $countParams = array_merge($countParams, [$searchParam, $searchParam, $searchParam]);
        $countTypes .= "sss";
    }

    if ($statusFilter === 'blocked') {
        $countSql .= " AND blocked = 1";
    } elseif ($statusFilter === 'active') {
        $countSql .= " AND (blocked = 0 OR blocked IS NULL)";
    }

    $stmt = $conn->prepare($countSql);
    if (!empty($countParams)) {
        $stmt->bind_param($countTypes, ...$countParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $totalUsers = $result->fetch_assoc()['count'];
    $totalPages = ceil($totalUsers / $perPage);

    // Handle user actions with CSRF protection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['admin_message'] = "Invalid request. Please try again.";
            $_SESSION['admin_message_type'] = "danger";
            header('Location: users.php');
            exit;
        }

        if (isset($_POST['action'])) {
            $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

            if ($userId > 0) {
                switch ($_POST['action']) {
                    case 'delete':
                        handleUserDeletion($conn, $userId);
                        break;
                    case 'block':
                        handleUserBlock($conn, $userId, true);
                        break;
                    case 'unblock':
                        handleUserBlock($conn, $userId, false);
                        break;
                }
            }
        }
        
    }

    // Get users with optimized query
    $sql = "SELECT u.*, 
            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count
            FROM users u WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= "sss";
    }

    if ($statusFilter === 'blocked') {
        $sql .= " AND u.blocked = 1";
    } elseif ($statusFilter === 'active') {
        $sql .= " AND (u.blocked = 0 OR u.blocked IS NULL)";
    }

    $sql .= " ORDER BY u.created_at DESC LIMIT ?, ?";
    $params = array_merge($params, [$offset, $perPage]);
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    // Get statistics
    $stats = getUserStats($conn);
} catch (Exception $e) {
    error_log("Users page error: " . $e->getMessage());
    $_SESSION['admin_message'] = "An error occurred. Please try again.";
    $_SESSION['admin_message_type'] = "danger";
    $users = [];
    $stats = ['new_users' => 0, 'users_with_orders' => 0, 'avg_orders' => 0];
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper functions
function handleUserDeletion($conn, $userId)
{
    try {
        // Check if user has orders
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderCount = $result->fetch_assoc()['count'];

        if ($orderCount > 0) {
            $_SESSION['admin_message'] = "Cannot delete user with existing orders. Consider blocking instead.";
            $_SESSION['admin_message_type'] = "warning";
        } else {
            // Begin transaction
            $conn->begin_transaction();

            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);

            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['admin_message'] = "User deleted successfully";
                $_SESSION['admin_message_type'] = "success";
            } else {
                $conn->rollback();
                throw new Exception("Failed to delete user");
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['admin_message'] = "Error deleting user: " . $e->getMessage();
        $_SESSION['admin_message_type'] = "danger";
    }
}

function handleUserBlock($conn, $userId, $block = true)
{
    try {
        $blockValue = $block ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET blocked = ? WHERE id = ?");
        $stmt->bind_param("ii", $blockValue, $userId);

        if ($stmt->execute()) {
            $action = $block ? "blocked" : "unblocked";
            $_SESSION['admin_message'] = "User $action successfully";
            $_SESSION['admin_message_type'] = "success";
        } else {
            throw new Exception("Failed to update user status");
        }
    } catch (Exception $e) {
        $action = $block ? "blocking" : "unblocking";
        $_SESSION['admin_message'] = "Error $action user: " . $e->getMessage();
        $_SESSION['admin_message_type'] = "danger";
    }
}

function getUserStats($conn)
{
    $stats = ['new_users' => 0, 'users_with_orders' => 0, 'avg_orders' => 0];

    try {
        // New users in last 30 days
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $stats['new_users'] = $stmt->get_result()->fetch_assoc()['count'];

        // Users with orders
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM orders");
        $stmt->execute();
        $stats['users_with_orders'] = $stmt->get_result()->fetch_assoc()['count'];

        // Average orders per user
        if ($stats['users_with_orders'] > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders");
            $stmt->execute();
            $totalOrders = $stmt->get_result()->fetch_assoc()['count'];
            $stats['avg_orders'] = $totalOrders / $stats['users_with_orders'];
        }
    } catch (Exception $e) {
        error_log("Error getting user stats: " . $e->getMessage());
    }

    return $stats;
}
?>

<!-- Users List -->
<div class="card">
    <div class="card-body">
        <!-- Enhanced Search and Filter Form -->
        <div class="d-flex justify-content-between align-items-center mb-20 flex-wrap">
            <form action="users.php" method="GET" class="d-flex align-items-center flex-wrap gap-10">
                <div class="form-group mb-0">
                    <input type="text" name="search" placeholder="Search users..." class="form-control"
                        value="<?php echo htmlspecialchars($search); ?>" style="min-width: 200px;">
                </div>

                <div class="form-group mb-0">
                    <select name="status" class="form-control">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Users</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active Users</option>
                        <option value="blocked" <?php echo $statusFilter === 'blocked' ? 'selected' : ''; ?>>Blocked Users</option>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <select name="per_page" class="form-control">
                        <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10 per page</option>
                        <option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25 per page</option>
                        <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50 per page</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="users.php" class="btn btn-secondary">Reset</a>
            </form>

            <div class="text-muted">
                Showing <?php echo count($users); ?> of <?php echo $totalUsers; ?> users
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
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Orders</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo !empty($user['blocked']) ? 'table-warning' : ''; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div class="ml-2">
                                            <div><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($user['blocked'])): ?>
                                        <span class="badge badge-danger">Blocked</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="orders.php?user_id=<?php echo $user['id']; ?>"
                                        class="badge badge-<?php echo $user['order_count'] > 0 ? 'info' : 'secondary'; ?>">
                                        <?php echo $user['order_count']; ?> order<?php echo $user['order_count'] != 1 ? 's' : ''; ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if (empty($user['blocked'])): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirmAction('block this user')">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="block">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Block User">
                                                    <i class="fas fa-ban"></i> Block
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirmAction('unblock this user')">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="unblock">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Unblock User">
                                                    <i class="fas fa-unlock"></i> Unblock
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- <form method="POST" class="d-inline ml-1" onsubmit="return confirmAction('permanently delete this user')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-20">
                <div class="text-muted">
                    Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                </div>
                <ul class="pagination">
                    <?php
                    $queryParams = http_build_query(array_filter([
                        'search' => $search,
                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                        'per_page' => $perPage !== 10 ? $perPage : null
                    ]));
                    $queryString = $queryParams ? '&' . $queryParams : '';
                    ?>

                    <?php if ($currentPage > 1): ?>
                        <li><a href="users.php?page=1<?php echo $queryString; ?>">First</a></li>
                        <li><a href="users.php?page=<?php echo $currentPage - 1; ?><?php echo $queryString; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a href="users.php?page=<?php echo $i; ?><?php echo $queryString; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li><a href="users.php?page=<?php echo $currentPage + 1; ?><?php echo $queryString; ?>">Next</a></li>
                        <li><a href="users.php?page=<?php echo $totalPages; ?><?php echo $queryString; ?>">Last</a></li>
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
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['new_users']); ?></h3>
                        <span>New Users (30 days)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['users_with_orders']); ?></h3>
                        <span>Users with Orders</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['avg_orders'], 1); ?></h3>
                        <span>Avg. Orders per User</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .user-avatar {
        background-color: #1D503A;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .gap-10 {
        gap: 10px;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .ml-1 {
        margin-left: 0.25rem;
    }

    .ml-2 {
        margin-left: 0.5rem;
    }

    .table-warning {
        background-color: rgba(255, 193, 7, 0.1);
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

        .d-flex.flex-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }

        .d-flex.flex-wrap>* {
            margin-bottom: 10px;
        }
    }
</style>

<script>
    function confirmAction(action) {
        return confirm('Are you sure you want to ' + action + '?');
    }
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>