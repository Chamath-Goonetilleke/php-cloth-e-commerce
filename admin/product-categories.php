<?php
// Set page variables
$pageTitle = "Product Categories";
$contentTitle = "Product Categories";
$headerButtons = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal"><i class="fas fa-plus"></i> Add Category</button>';

// Include header
require_once 'includes/header.php';

// Check if categories table exists
$categoryTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'categories'");
$categoryTableExists = $result && $result->num_rows > 0;

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
        // Insert default category
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $name = "Uncategorized";
        $slug = "uncategorized";
        $desc = "Default category for products";
        $stmt->bind_param("sss", $name, $slug, $desc);
        $stmt->execute();

        // Add category_id column to products table if it doesn't already have it
        $result = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        if (!$result || $result->num_rows === 0) {
            $conn->query("ALTER TABLE products ADD COLUMN category_id INT(11) AFTER description");
        }

        $_SESSION['admin_message'] = "Categories table created successfully";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Error creating categories table: " . $conn->error;
        $_SESSION['admin_message_type'] = "danger";
    }
}

// Check if category_id column exists in products table
$categoryColumnExists = false;
if ($categoryTableExists) {
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
    $categoryColumnExists = $result && $result->num_rows > 0;
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new category
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'active';
        $parentId = empty($_POST['parent_id']) ? null : (int)$_POST['parent_id'];

        // Validate inputs
        $errors = [];

        if (empty($name)) {
            $errors[] = "Category name is required";
        }

        // Generate slug from name
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

        // Check if slug already exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $errors[] = "A category with this name already exists";
        }

        if (empty($errors)) {
            $sql = "INSERT INTO categories (name, slug, description, parent_id, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $slug, $description, $parentId, $status);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Category added successfully";
                $_SESSION['admin_message_type'] = "success";
            } else {
                $_SESSION['admin_message'] = "Error adding category: " . $conn->error;
                $_SESSION['admin_message_type'] = "danger";
            }
        } else {
            $_SESSION['admin_message'] = implode("<br>", $errors);
            $_SESSION['admin_message_type'] = "danger";
        }
    }

    // Edit category
    if (isset($_POST['edit_category'])) {
        $categoryId = (int)$_POST['category_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'active';
        $parentId = empty($_POST['parent_id']) ? null : (int)$_POST['parent_id'];

        // Validate inputs
        $errors = [];

        if (empty($name)) {
            $errors[] = "Category name is required";
        }

        // Generate slug from name
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

        // Check if slug already exists (excluding this category)
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->bind_param("si", $slug, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $errors[] = "A category with this name already exists";
        }

        // Prevent setting parent to itself or its children
        if ($parentId == $categoryId) {
            $errors[] = "A category cannot be its own parent";
        }

        if (empty($errors)) {
            $sql = "UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $slug, $description, $parentId, $status, $categoryId);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Category updated successfully";
                $_SESSION['admin_message_type'] = "success";
            } else {
                $_SESSION['admin_message'] = "Error updating category: " . $conn->error;
                $_SESSION['admin_message_type'] = "danger";
            }
        } else {
            $_SESSION['admin_message'] = implode("<br>", $errors);
            $_SESSION['admin_message_type'] = "danger";
        }
    }

    // Delete category
    if (isset($_POST['delete_category'])) {
        $categoryId = (int)$_POST['category_id'];

        // Check if products use this category
        $productsCount = 0;
        if ($categoryColumnExists) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $productsCount = $row['count'];
            }
        }

        // Check if this is a parent category
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $childrenCount = 0;

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $childrenCount = $row['count'];
        }

        if ($productsCount > 0) {
            $_SESSION['admin_message'] = "Cannot delete category: {$productsCount} products are assigned to this category";
            $_SESSION['admin_message_type'] = "warning";
        } elseif ($childrenCount > 0) {
            $_SESSION['admin_message'] = "Cannot delete category: It has {$childrenCount} subcategories";
            $_SESSION['admin_message_type'] = "warning";
        } else {
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $categoryId);

            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Category deleted successfully";
                $_SESSION['admin_message_type'] = "success";
            } else {
                $_SESSION['admin_message'] = "Error deleting category: " . $conn->error;
                $_SESSION['admin_message_type'] = "danger";
            }
        }
    }

    // Redirect to avoid form resubmission
    header("Location: product-categories.php");
    exit;
}

// Get pagination parameters
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($currentPage - 1) * $perPage;

// Get total categories count
$totalCategories = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalCategories = $row['count'];
}

$totalPages = ceil($totalCategories / $perPage);

// Get categories for listing with product count based on column existence
$categories = [];
if ($categoryColumnExists) {
    $sql = "SELECT c.*, p.name as parent_name, 
            (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            ORDER BY c.name ASC 
            LIMIT ?, ?";
} else {
    // If category_id column doesn't exist, just set product_count to 0
    $sql = "SELECT c.*, p.name as parent_name, 
            0 as product_count 
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            ORDER BY c.name ASC 
            LIMIT ?, ?";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get all categories for dropdown
$allCategories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allCategories[] = $row;
    }
}
?>

<!-- Categories List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No categories found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php if (!empty($category['description'])): ?>
                                        <div class="description-preview">
                                            <?php echo substr(htmlspecialchars($category['description']), 0, 40); ?>
                                            <?php echo strlen($category['description']) > 40 ? '...' : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo $category['product_count']; ?></span>
                                </td>
                                <td>
                                    <?php echo !empty($category['parent_name']) ? htmlspecialchars($category['parent_name']) : '<span class="text-muted">None</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo (isset($category['status']) && $category['status'] === 'active') ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($category['status'] ?? 'unknown'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#editCategoryModal"
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteCategoryModal"
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                            data-product-count="<?php echo $category['product_count']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                        <li><a href="product-categories.php?page=<?php echo $currentPage - 1; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a href="product-categories.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li><a href="product-categories.php?page=<?php echo $currentPage + 1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal" id="addCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="product-categories.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add-name">Category Name*</label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="add-description">Description</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="add-parent">Parent Category</label>
                        <select class="form-control" id="add-parent" name="parent_id">
                            <option value="">None</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add-status">Status</label>
                        <select class="form-control" id="add-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="product-categories.php">
                <input type="hidden" name="category_id" id="edit-id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-name">Category Name*</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit-parent">Parent Category</label>
                        <select class="form-control" id="edit-parent" name="parent_id">
                            <option value="">None</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit-status">Status</label>
                        <select class="form-control" id="edit-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal" id="deleteCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category "<span id="delete-category-name"></span>"?</p>
                <p id="delete-warning" class="text-danger"></p>
            </div>
            <form method="POST" action="product-categories.php">
                <input type="hidden" name="category_id" id="delete-id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .description-preview {
        font-size: 12px;
        color: #666;
        margin-top: 3px;
    }

    .modal-title {
        color: var(--primary-color);
    }

    .close {
        background: none;
        border: none;
        font-size: 24px;
        color: #999;
        opacity: 0.6;
    }

    .close:hover {
        opacity: 1;
    }

    .modal-content {
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: 1px solid #eee;
        padding: 15px 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid #eee;
        padding: 15px 20px;
    }
</style>

<script>
    $(document).ready(function() {
        // Edit category modal
        $('#editCategoryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var categoryId = button.data('id');
            var categoryName = button.data('name');
            var categoryDescription = button.data('description');
            var categoryParentId = button.data('parent-id');
            var categoryStatus = button.data('status');

            var modal = $(this);
            modal.find('#edit-id').val(categoryId);
            modal.find('#edit-name').val(categoryName);
            modal.find('#edit-description').val(categoryDescription);
            modal.find('#edit-parent').val(categoryParentId);
            modal.find('#edit-status').val(categoryStatus);

            // Remove this category from parent options to prevent circular references
            modal.find('#edit-parent option[value="' + categoryId + '"]').prop('disabled', true);
        });

        // Reset disabled options when modal is closed
        $('#editCategoryModal').on('hidden.bs.modal', function() {
            $(this).find('#edit-parent option').prop('disabled', false);
        });

        // Delete category modal
        $('#deleteCategoryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var categoryId = button.data('id');
            var categoryName = button.data('name');
            var productCount = button.data('product-count');

            var modal = $(this);
            modal.find('#delete-id').val(categoryId);
            modal.find('#delete-category-name').text(categoryName);

            if (productCount > 0) {
                var warning = 'This category has ' + productCount + ' product' + (productCount > 1 ? 's' : '') + ' assigned to it. ';
                warning += 'You cannot delete it until you reassign these products to other categories.';
                modal.find('#delete-warning').text(warning);
                modal.find('button[name="delete_category"]').prop('disabled', true);
            } else {
                modal.find('#delete-warning').text('');
                modal.find('button[name="delete_category"]').prop('disabled', false);
            }
        });
    });
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>