<?php
// Set page variables
$pageTitle = "Export Products";
$contentTitle = "Export Products";

// Include header and config
require_once 'includes/config.php';

// Authenticate
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if we're returning a template
if (isset($_GET['template']) && $_GET['template'] == 1) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_template.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM to fix Excel display issues
    fputs($output, "\xEF\xBB\xBF");

    // Add the header row
    fputcsv($output, [
        'name',
        'description',
        'category',
        'price',
        'stock_quantity',
        'status',
        'image'
    ]);

    // Add example data rows
    fputcsv($output, [
        'Sample Product 1',
        'This is a sample product description.',
        'Category 1',
        '1299.99',
        '50',
        'active',
        '/onefitclothing/uploads/products/sample1.jpg'
    ]);

    fputcsv($output, [
        'Sample Product 2',
        'Another sample product description.',
        'Category 2',
        '999.99',
        '25',
        'active',
        '/onefitclothing/uploads/products/sample2.jpg'
    ]);

    // Close the output stream
    fclose($output);
    exit;
}

// Check if we should export data
if (isset($_GET['export']) && $_GET['export'] == 1) {
    // Check if categories table exists
    $categoryTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'categories'");
    $categoryTableExists = $result && $result->num_rows > 0;

    // Check if category_id column exists in products table
    $categoryColumnExists = false;
    if ($categoryTableExists) {
        $result = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        $categoryColumnExists = $result && $result->num_rows > 0;
    }

    // Build query based on table existence
    if ($categoryColumnExists) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";
    } else {
        $sql = "SELECT * FROM products";
    }

    // Apply filters if provided
    $filters = [];
    $filterParams = [];
    $filterTypes = '';

    if (isset($_GET['category_id']) && !empty($_GET['category_id']) && $categoryColumnExists) {
        $filters[] = "p.category_id = ?";
        $filterParams[] = $_GET['category_id'];
        $filterTypes .= 'i';
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters[] = "p.status = ?";
        $filterParams[] = $_GET['status'];
        $filterTypes .= 's';
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $_GET['search'] . '%';
        $filterParams[] = $searchTerm;
        $filterParams[] = $searchTerm;
        $filterTypes .= 'ss';
    }

    if (!empty($filters)) {
        $sql .= " WHERE " . implode(" AND ", $filters);
    }

    // Prepare and execute query
    if (!empty($filterParams)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($filterTypes, ...$filterParams);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    if ($result) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d') . '.csv"');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM to fix Excel display issues
        fputs($output, "\xEF\xBB\xBF");

        // Add the header row
        fputcsv($output, [
            'name',
            'description',
            'category',
            'price',
            'stock_quantity',
            'status',
            'image'
        ]);

        // Add product data rows
        while ($row = $result->fetch_assoc()) {
            $csvRow = [
                $row['name'],
                $row['description'],
                isset($row['category_name']) ? $row['category_name'] : '',
                $row['price'],
                isset($row['stock_quantity']) ? $row['stock_quantity'] : '0',
                isset($row['status']) ? $row['status'] : 'active',
                isset($row['image']) ? $row['image'] : ''
            ];

            fputcsv($output, $csvRow);
        }

        // Close the output stream
        fclose($output);
        exit;
    }
} else {
    // Display the export interface
    // Get categories for filter
    $categories = [];
    $result = $conn->query("SHOW TABLES LIKE 'categories'");
    $categoryTableExists = $result && $result->num_rows > 0;

    if ($categoryTableExists) {
        $result = $conn->query("SELECT id, name FROM categories ORDER BY name");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
    }

    // Include header
    require_once 'includes/header.php';
?>

    <div class="card">
        <div class="card-body">
            <div class="export-container">
                <div class="export-instructions">
                    <h3>Export Products</h3>
                    <p>Generate a CSV file containing all your product data. You can filter the export using the options below.</p>
                </div>

                <form method="GET" action="product-export.php" class="mt-20">
                    <input type="hidden" name="export" value="1">

                    <div class="form-row">
                        <?php if (!empty($categories) && $categoryColumnExists): ?>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="category_id">Filter by Category</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="status">Filter by Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or description">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-20">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Products
                        </button>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>

                <div class="export-options mt-20">
                    <h4>Additional Options</h4>
                    <div class="d-flex flex-wrap mt-10">
                        <a href="product-export.php?template=1" class="btn btn-outline-primary mr-10 mb-10">
                            <i class="fas fa-file"></i> Download CSV Template
                        </a>
                        <a href="product-export.php?export=1" class="btn btn-outline-primary mb-10">
                            <i class="fas fa-download"></i> Export All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .export-container {
            max-width: 100%;
        }

        .export-instructions {
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            margin: 0 -10px;
            flex-wrap: wrap;
        }

        .form-col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }

        .export-options {
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .form-col {
                flex: 100%;
            }
        }
    </style>

<?php
    // Include footer
    require_once 'includes/footer.php';
}
?>