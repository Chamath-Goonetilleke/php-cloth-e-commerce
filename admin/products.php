<?php
// Handle deletion before any output
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    session_start();
    require_once 'includes/config.php';
    $productId = (int)$_GET['delete'];
    // Get product image_path first to delete it
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        // Delete the image file if it exists
        if (!empty($product['image_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $product['image_path'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $product['image_path']);
        }
    }
    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Product deleted successfully";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Error deleting product: " . $conn->error;
        $_SESSION['admin_message_type'] = "danger";
    }
    // Redirect to refresh the page
    header('Location: products.php');
    exit;
}

// Set page variables
$pageTitle = "Products";
$contentTitle = "Products Management";
$headerButtons = '<a href="product-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>';

// Include header
require_once 'includes/header.php';

// Check if products table exists and create it if it doesn't
$productsTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'products'");
$productsTableExists = $result && $result->num_rows > 0;

if (!$productsTableExists) {
    // Create products table with basic structure
    $sql = "CREATE TABLE products (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        image VARCHAR(255),
        stock_quantity INT(11) DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['admin_message'] = "Products table created successfully";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Error creating products table: " . $conn->error;
        $_SESSION['admin_message_type'] = "danger";
    }
}

// Get products with pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Get total products count
$totalProducts = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalProducts = $row['count'];
}

$totalPages = ceil($totalProducts / $perPage);

// Get products
$products = array();
$sql = "SELECT * FROM products ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!-- Products List -->
<div class="card">
    <div class="card-body">
        <!-- Search and Filter -->
        <div class="d-flex justify-content-between align-items-center mb-20">
            <form action="products.php" method="GET" class="d-flex align-items-center">
                <div class="form-group mb-0 mr-10">
                    <input type="text" name="search" placeholder="Search products..." class="form-control" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div class="d-flex">
                <a href="products.php" class="btn btn-secondary mr-10">Reset</a>
                <a href="product-categories.php" class="btn btn-outline-primary">Manage Categories</a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Sale Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No products found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo ucfirst($product['category']); ?></td>
                                <td>LKR <?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['sale_price'] !== null ? 'LKR ' . number_format($product['sale_price'], 2) : '-'; ?></td>
                                <td><?php echo isset($product['stock']) ? $product['stock'] : 'N/A'; ?></td>
                                <td>
                                    <?php if (!empty($product['is_featured'])): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger confirm-delete" data-toggle="tooltip" title="Delete">
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
                        <li><a href="products.php?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a href="products.php?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li><a href="products.php?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Template Link -->
<div class="card mt-20">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap">
            <a href="product-add.php" class="btn btn-primary mr-10 mb-10">
                <i class="fas fa-plus"></i> Add New Product
            </a>
            <a href="product-import.php" class="btn btn-secondary mr-10 mb-10">
                <i class="fas fa-file-import"></i> Import Products
            </a>
            <a href="product-export.php" class="btn btn-outline-primary mb-10">
                <i class="fas fa-file-export"></i> Export Products
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>