<?php
// Handle form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/config.php'; // Ensure DB connection is available
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? floatval($_POST['sale_price']) : null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }

    // Image upload handling
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = "Invalid image format. Allowed formats: JPG, PNG, GIF, WEBP";
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = "Image size too large. Maximum size: 5MB";
        } else {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/onefitclothing/uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . strtolower(str_replace(' ', '_', $_FILES['image']['name']));
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = '/onefitclothing/uploads/products/' . $filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    // If no errors, insert new product
    if (empty($errors)) {
        $sql = "INSERT INTO products (name, description, price, sale_price, stock, category, image_path, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddisss", $name, $description, $price, $sale_price, $stock, $category, $imagePath, $is_featured);
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "Product added successfully";
            $_SESSION['admin_message_type'] = "success";
            header("Location: products.php");
            exit;
        } else {
            $errors[] = "Error adding product: " . $conn->error;
        }
    }
}

// Set page variables
$pageTitle = "Add Product";
$contentTitle = "Add New Product";

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

// Create categories table if it doesn't exist
if (!$categoryTableExists) {
    $sql = "CREATE TABLE categories (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        description TEXT,
        parent_id INT(11) DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY slug (slug)
    )";

    if ($conn->query($sql)) {
        // Add category_id column to products table if it doesn't already have it
        $result = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        if (!$result || $result->num_rows === 0) {
            $conn->query("ALTER TABLE products ADD COLUMN category_id INT(11) AFTER description");
        }

        // Insert default category
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $name = "Uncategorized";
        $slug = "uncategorized";
        $desc = "Default category for products";
        $stmt->bind_param("sss", $name, $slug, $desc);
        $stmt->execute();

        $categoryTableExists = true;
        $categoryColumnExists = true;
    }
}

// Get categories for dropdown
$categories = [];
if ($categoryTableExists) {
    $result = $conn->query("SELECT id, name FROM categories ORDER BY name");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
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

        <form method="POST" action="product-add.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="name">Product Name*</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="price">Price (LKR)*</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="sale_price">Sale Price (LKR)</label>
                        <input type="number" id="sale_price" name="sale_price" class="form-control" min="0" step="0.01" value="<?php echo isset($_POST['sale_price']) ? htmlspecialchars($_POST['sale_price']) : ''; ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0'; ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="category">Category*</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="tshirt" <?php echo (isset($_POST['category']) && $_POST['category'] == 'tshirt') ? 'selected' : ''; ?>>T-Shirts</option>
                            <option value="hoodie" <?php echo (isset($_POST['category']) && $_POST['category'] == 'hoodie') ? 'selected' : ''; ?>>Hoodies</option>
                            <option value="new" <?php echo (isset($_POST['category']) && $_POST['category'] == 'new') ? 'selected' : ''; ?>>New Arrivals</option>
                            <option value="sale" <?php echo (isset($_POST['category']) && $_POST['category'] == 'sale') ? 'selected' : ''; ?>>Sale Items</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group" style="margin-top:32px;">
                        <div class="form-check">
                            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured" class="form-check-label">Featured</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="6"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <div class="custom-file">
                    <input type="file" id="image" name="image" class="custom-file-input" accept="image/*">
                    <label class="custom-file-label" for="image">Choose file</label>
                </div>
                <small class="form-text text-muted">Recommended size: 800x800px. Max file size: 5MB.</small>
            </div>
            <div class="form-group">
                <label>Image Preview</label>
                <div class="image-preview">
                    <img id="imagePreview" src="" alt="Preview" style="display: none; max-width: 200px; max-height: 200px;">
                    <div id="noImagePreview" class="no-image">
                        <i class="fas fa-image"></i>
                        <p>No image selected</p>
                    </div>
                </div>
            </div>
            <div class="form-group mt-20">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
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

    .image-preview {
        margin-top: 10px;
        width: 200px;
        height: 200px;
        border: 1px dashed var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background-color: #f8f9fa;
    }

    .no-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ccc;
    }

    .no-image i {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .no-image p {
        margin: 0;
    }

    @media (max-width: 768px) {
        .form-col {
            flex: 100%;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // Image preview
        $('#image').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                    $('#noImagePreview').hide();
                }
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').hide();
                $('#noImagePreview').show();
            }
        });

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