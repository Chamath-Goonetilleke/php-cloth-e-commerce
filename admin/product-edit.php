<?php
// Check for product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    session_start();
    $_SESSION['admin_message'] = "Invalid product ID";
    $_SESSION['admin_message_type'] = "danger";
    header("Location: products.php");
    exit;
}

require_once 'includes/config.php';
$productId = (int)$_GET['id'];
// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    session_start();
    $_SESSION['admin_message'] = "Product not found";
    $_SESSION['admin_message_type'] = "danger";
    header("Location: products.php");
    exit;
}
$product = $result->fetch_assoc();

// Handle form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
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
    $imagePath = $product['image_path']; // Keep existing image by default
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
                // Delete old image if it exists
                if (!empty($product['image_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $product['image_path'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $product['image_path']);
                }
                $imagePath = '/onefitclothing/uploads/products/' . $filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    // If checkbox is set to remove image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
        if (!empty($product['image_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $product['image_path'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $product['image_path']);
        }
        $imagePath = '';
    }
    // If no errors, update product
    if (empty($errors)) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, stock = ?, category = ?, image_path = ?, is_featured = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddissii", $name, $description, $price, $sale_price, $stock, $category, $imagePath, $is_featured, $productId);
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "Product updated successfully";
            $_SESSION['admin_message_type'] = "success";
            header("Location: products.php");
            exit;
        } else {
            $errors[] = "Error updating product: " . $conn->error;
        }
    }
}

// Set page variables
$pageTitle = "Edit Product";
$contentTitle = "Edit Product";

// Include header
require_once 'includes/header.php';
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

        <form method="POST" action="product-edit.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="name">Product Name*</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($product['name']); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="price">Price (LKR)*</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01"
                            value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : htmlspecialchars($product['price']); ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="sale_price">Sale Price (LKR)</label>
                        <input type="number" id="sale_price" name="sale_price" class="form-control" min="0" step="0.01"
                            value="<?php echo isset($_POST['sale_price']) ? htmlspecialchars($_POST['sale_price']) : htmlspecialchars($product['sale_price']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" min="0"
                            value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : htmlspecialchars($product['stock']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="category">Category*</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="tshirt" <?php echo (isset($_POST['category']) ? $_POST['category'] : $product['category']) == 'tshirt' ? 'selected' : ''; ?>>T-Shirts</option>
                            <option value="hoodie" <?php echo (isset($_POST['category']) ? $_POST['category'] : $product['category']) == 'hoodie' ? 'selected' : ''; ?>>Hoodies</option>
                            <option value="new" <?php echo (isset($_POST['category']) ? $_POST['category'] : $product['category']) == 'new' ? 'selected' : ''; ?>>New Arrivals</option>
                            <option value="sale" <?php echo (isset($_POST['category']) ? $_POST['category'] : $product['category']) == 'sale' ? 'selected' : ''; ?>>Sale Items</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group" style="margin-top:32px;">
                        <div class="form-check">
                            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input" <?php echo (isset($_POST['is_featured']) ? $_POST['is_featured'] : $product['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured" class="form-check-label">Featured</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="6"><?php
                                                                                            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($product['description'] ?? '');
                                                                                            ?></textarea>
            </div>
            <div class="form-group">
                <label>Current Image</label>
                <div class="current-image-container">
                    <?php if (!empty($product['image_path'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 200px; max-height: 200px;">
                            <div class="image-actions">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remove_image" value="1"> Remove image
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No image</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="image">Upload New Image</label>
                <div class="custom-file">
                    <input type="file" id="image" name="image" class="custom-file-input" accept="image/*">
                    <label class="custom-file-label" for="image">Choose file</label>
                </div>
                <small class="form-text text-muted">Recommended size: 800x800px. Max file size: 5MB.</small>
            </div>
            <div class="form-group">
                <label>New Image Preview</label>
                <div class="image-preview">
                    <img id="imagePreview" src="" alt="Preview" style="display: none; max-width: 200px; max-height: 200px;">
                    <div id="noImagePreview" class="no-image">
                        <i class="fas fa-image"></i>
                        <p>No new image selected</p>
                    </div>
                </div>
            </div>
            <div class="form-group mt-20">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Product
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

    .current-image-container {
        margin-bottom: 20px;
    }

    .current-image {
        display: inline-block;
        border: 1px solid var(--border-color);
        padding: 10px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .image-actions {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
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

    .checkbox {
        display: flex;
        align-items: center;
    }

    .checkbox input[type="checkbox"] {
        margin-right: 5px;
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

        // Handle remove image checkbox
        $('input[name="remove_image"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('#image').prop('disabled', true);
                $('.custom-file-label').html('Image will be removed');
                $('.custom-file').addClass('disabled');
            } else {
                $('#image').prop('disabled', false);
                $('.custom-file-label').html('Choose file');
                $('.custom-file').removeClass('disabled');
            }
        });
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>