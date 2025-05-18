<?php
// Set page variables
$pageTitle = "Import Products";
$contentTitle = "Import Products";

// Include header
require_once 'includes/header.php';

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

// Process file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $errors = [];
    $successCount = 0;
    $errorCount = 0;

    // Validate file
    if ($_FILES['csv_file']['error'] === 0) {
        $fileInfo = pathinfo($_FILES['csv_file']['name']);

        if (strtolower($fileInfo['extension']) !== 'csv') {
            $errors[] = "Only CSV files are allowed";
        } else {
            // Process CSV file
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');

            if ($file) {
                // Get headers from first row
                $headers = fgetcsv($file);

                if ($headers) {
                    // Convert headers to lowercase for case-insensitive matching
                    $headers = array_map('strtolower', $headers);

                    // Required fields
                    $requiredFields = ['name', 'price'];
                    $missingFields = [];

                    foreach ($requiredFields as $field) {
                        if (!in_array($field, $headers)) {
                            $missingFields[] = $field;
                        }
                    }

                    if (!empty($missingFields)) {
                        $errors[] = "Missing required fields in CSV: " . implode(', ', $missingFields);
                    } else {
                        // Map headers to indexes
                        $colMap = [];
                        foreach ($headers as $index => $header) {
                            $colMap[$header] = $index;
                        }

                        // Process data rows
                        $rowNumber = 2; // Start at row 2 (after headers)

                        while (($data = fgetcsv($file)) !== FALSE) {
                            // Skip empty rows
                            if (count($data) <= 1 && empty($data[0])) {
                                $rowNumber++;
                                continue;
                            }

                            $productData = [];
                            $rowValid = true;

                            // Extract product data
                            if (isset($colMap['name']) && isset($data[$colMap['name']])) {
                                $productData['name'] = trim($data[$colMap['name']]);
                                if (empty($productData['name'])) {
                                    $errorCount++;
                                    $rowValid = false;
                                    continue;
                                }
                            } else {
                                $errorCount++;
                                $rowValid = false;
                                continue;
                            }

                            if (isset($colMap['price']) && isset($data[$colMap['price']])) {
                                $price = trim($data[$colMap['price']]);
                                if (is_numeric($price) && $price > 0) {
                                    $productData['price'] = $price;
                                } else {
                                    $errorCount++;
                                    $rowValid = false;
                                    continue;
                                }
                            } else {
                                $errorCount++;
                                $rowValid = false;
                                continue;
                            }

                            // Optional fields
                            if (isset($colMap['description']) && isset($data[$colMap['description']])) {
                                $productData['description'] = trim($data[$colMap['description']]);
                            } else {
                                $productData['description'] = '';
                            }

                            if (isset($colMap['category']) && $categoryColumnExists && isset($data[$colMap['category']])) {
                                $categoryName = trim($data[$colMap['category']]);
                                if (!empty($categoryName)) {
                                    // Find category by name or create new one
                                    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
                                    $stmt->bind_param("s", $categoryName);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result && $result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $productData['category_id'] = $row['id'];
                                    } else {
                                        // Create new category
                                        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $categoryName));
                                        $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                                        $stmt->bind_param("ss", $categoryName, $slug);

                                        if ($stmt->execute()) {
                                            $productData['category_id'] = $stmt->insert_id;
                                        } else {
                                            $productData['category_id'] = 1; // Default to first category
                                        }
                                    }
                                } else {
                                    $productData['category_id'] = 1; // Default to first category
                                }
                            } else {
                                $productData['category_id'] = 1; // Default to first category
                            }

                            if (isset($colMap['stock_quantity']) && isset($data[$colMap['stock_quantity']])) {
                                $stock = trim($data[$colMap['stock_quantity']]);
                                if (is_numeric($stock) && $stock >= 0) {
                                    $productData['stock_quantity'] = $stock;
                                } else {
                                    $productData['stock_quantity'] = 0;
                                }
                            } else {
                                $productData['stock_quantity'] = 0;
                            }

                            if (isset($colMap['status']) && isset($data[$colMap['status']])) {
                                $status = strtolower(trim($data[$colMap['status']]));
                                if ($status === 'active' || $status === 'inactive') {
                                    $productData['status'] = $status;
                                } else {
                                    $productData['status'] = 'active';
                                }
                            } else {
                                $productData['status'] = 'active';
                            }

                            if (isset($colMap['image']) && isset($data[$colMap['image']])) {
                                $productData['image'] = trim($data[$colMap['image']]);
                            } else {
                                $productData['image'] = '';
                            }

                            // Check if product already exists by name
                            $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
                            $stmt->bind_param("s", $productData['name']);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && $result->num_rows > 0) {
                                // Update existing product
                                $row = $result->fetch_assoc();
                                $productId = $row['id'];

                                if ($categoryColumnExists) {
                                    $sql = "UPDATE products SET 
                                            description = ?, 
                                            category_id = ?, 
                                            price = ?, 
                                            stock_quantity = ?, 
                                            status = ?, 
                                            image = ?, 
                                            updated_at = NOW() 
                                            WHERE id = ?";

                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param(
                                        "sidsisi",
                                        $productData['description'],
                                        $productData['category_id'],
                                        $productData['price'],
                                        $productData['stock_quantity'],
                                        $productData['status'],
                                        $productData['image'],
                                        $productId
                                    );
                                } else {
                                    $sql = "UPDATE products SET 
                                            description = ?, 
                                            price = ?, 
                                            stock_quantity = ?, 
                                            status = ?, 
                                            image = ?, 
                                            updated_at = NOW() 
                                            WHERE id = ?";

                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param(
                                        "sdissi",
                                        $productData['description'],
                                        $productData['price'],
                                        $productData['stock_quantity'],
                                        $productData['status'],
                                        $productData['image'],
                                        $productId
                                    );
                                }
                            } else {
                                // Insert new product
                                if ($categoryColumnExists) {
                                    $sql = "INSERT INTO products (
                                            name, 
                                            description, 
                                            category_id, 
                                            price, 
                                            stock_quantity, 
                                            status, 
                                            image
                                        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param(
                                        "ssidiss",
                                        $productData['name'],
                                        $productData['description'],
                                        $productData['category_id'],
                                        $productData['price'],
                                        $productData['stock_quantity'],
                                        $productData['status'],
                                        $productData['image']
                                    );
                                } else {
                                    $sql = "INSERT INTO products (
                                            name, 
                                            description, 
                                            price, 
                                            stock_quantity, 
                                            status, 
                                            image
                                        ) VALUES (?, ?, ?, ?, ?, ?)";

                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param(
                                        "ssdiss",
                                        $productData['name'],
                                        $productData['description'],
                                        $productData['price'],
                                        $productData['stock_quantity'],
                                        $productData['status'],
                                        $productData['image']
                                    );
                                }
                            }

                            if ($stmt->execute()) {
                                $successCount++;
                            } else {
                                $errorCount++;
                            }

                            $rowNumber++;
                        }
                    }
                } else {
                    $errors[] = "CSV file is empty or invalid format";
                }

                fclose($file);
            } else {
                $errors[] = "Failed to open CSV file";
            }
        }
    } else {
        $errors[] = "Error uploading file: " . $_FILES['csv_file']['error'];
    }
}
?>

<div class="card">
    <div class="card-body">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($successCount) && $successCount > 0): ?>
            <div class="alert alert-success">
                <p>Import completed successfully!</p>
                <ul>
                    <li><?php echo $successCount; ?> products imported/updated successfully</li>
                    <?php if ($errorCount > 0): ?>
                        <li><?php echo $errorCount; ?> products failed to import</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="import-container">
            <div class="import-instructions">
                <h3>Instructions</h3>
                <p>Upload a CSV file with product data. The file should have the following columns:</p>
                <div class="code-block">
                    <pre>name,description,category,price,stock_quantity,status,image</pre>
                </div>

                <ul class="import-requirements">
                    <li><strong>Required columns:</strong> name, price</li>
                    <li><strong>Optional columns:</strong> description, category, stock_quantity, status, image</li>
                    <li>First row must be the column headers</li>
                    <li>Status can be 'active' or 'inactive'</li>
                    <li>If a product with the same name already exists, it will be updated</li>
                    <li>If a category does not exist, it will be created</li>
                </ul>

                <h3>Example Format</h3>
                <div class="code-block">
                    <pre>name,description,category,price,stock_quantity,status,image
Men's T-shirt,High quality cotton t-shirt,T-shirts,1299.99,50,active,/onefitclothing/uploads/products/tshirt.jpg
Women's Leggings,Premium workout leggings,Sportswear,1599.99,30,active,/onefitclothing/uploads/products/leggings.jpg</pre>
                </div>

                <div class="download-template">
                    <a href="product-export.php?template=1" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </div>
            </div>

            <form method="POST" action="product-import.php" enctype="multipart/form-data" class="mt-20">
                <div class="form-group">
                    <label for="csv_file">Upload CSV File</label>
                    <div class="custom-file">
                        <input type="file" id="csv_file" name="csv_file" class="custom-file-input" accept=".csv" required>
                        <label class="custom-file-label" for="csv_file">Choose file</label>
                    </div>
                    <small class="form-text text-muted">Maximum file size: 5MB</small>
                </div>

                <div class="form-group mt-20">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import Products
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .import-container {
        max-width: 100%;
    }

    .import-instructions {
        margin-bottom: 30px;
    }

    .import-requirements {
        margin: 15px 0;
        padding-left: 20px;
    }

    .import-requirements li {
        margin-bottom: 5px;
    }

    .code-block {
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
        overflow-x: auto;
    }

    .code-block pre {
        margin: 0;
        white-space: pre-wrap;
        font-family: monospace;
    }

    .download-template {
        margin-top: 20px;
    }

    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        height: 42px;
        margin-bottom: 0;
    }

    .custom-file-input {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 42px;
        margin: 0;
        opacity: 0;
    }

    .custom-file-label {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1;
        height: 42px;
        padding: 10px 15px;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        display: flex;
        align-items: center;
    }

    .custom-file-label::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        padding: 0 15px;
        color: white;
        content: "Browse";
        background-color: var(--primary-color);
        border-left: 1px solid var(--border-color);
        border-radius: 0 4px 4px 0;
    }
</style>

<script>
    $(document).ready(function() {
        // Custom file input label
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $(this).next('.custom-file-label').html(fileName);
            } else {
                $(this).next('.custom-file-label').html('Choose file');
            }
        });
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>