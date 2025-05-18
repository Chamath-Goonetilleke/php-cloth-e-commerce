<?php
require_once 'includes/config.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Get action from POST data
$action = $_POST['action'] ?? '';

// Prepare response array for AJAX requests
$response = [
    'success' => false,
    'message' => '',
    'count' => 0,
    'total' => 0
];

// Handle different actions
switch ($action) {
    case 'add':
        addToCart();
        break;
    case 'update':
        updateCartItem();
        break;
    case 'remove':
        removeCartItem();
        break;
    case 'reset':
        resetCart();
        break;
    default:
        $response['message'] = 'Invalid action';
}

// Return JSON response for AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Redirect back to cart page for non-AJAX requests
    header('Location: cart.php');
    exit;
}

/**
 * Add item to cart
 */
function addToCart()
{
    global $response, $conn;

    // Get and sanitize product details from POST
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // Validate product ID
    if (empty($productId)) {
        $response['message'] = 'Invalid product ID';
        return;
    }

    // Validate size
    if (empty($size)) {
        $response['message'] = 'Size is required';
        return;
    }

    // Debug - log the received data
    error_log("Adding to cart: Product ID: $productId, Size: $size, Quantity: $quantity");

    // Fetch product details from database
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'Product not found';
        return;
    }

    $product = $result->fetch_assoc();

    // Use sale price if available, otherwise use regular price
    $price = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];

    // Check if item already exists in cart with the same product ID and size
    $itemExists = false;
    $existingItemKey = null;

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $productId && $item['size'] == $size) {
            $itemExists = true;
            $existingItemKey = $key;
            break;
        }
    }

    if ($itemExists) {
        // Update the existing item by adding the new quantity to the existing quantity
        $_SESSION['cart'][$existingItemKey]['quantity'] += $quantity;
        $response['message'] = 'Quantity updated';
    } else {
        // Add new item to cart
        $cartItemId = uniqid();
        $_SESSION['cart'][] = [
            'id' => $cartItemId,
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'size' => $size,
            'image' => $product['image_path']
        ];
        $response['message'] = 'Product added to cart';
    }

    // Return success response
    $response['success'] = true;
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();

    // Debug - log the cart contents
    error_log("Cart after add: " . print_r($_SESSION['cart'], true));
}

/**
 * Update cart item quantity
 */
function updateCartItem()
{
    global $response;

    $itemId = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if (empty($itemId)) {
        $response['message'] = 'Invalid item ID';
        return;
    }

    // Debug - log update attempt
    error_log("Updating cart item: ID: $itemId, Quantity: $quantity");

    $itemUpdated = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemId) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            $itemUpdated = true;
            break;
        }
    }

    if (!$itemUpdated) {
        $response['message'] = 'Item not found in cart';
        return;
    }

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Cart updated';
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();

    // Debug - log the cart contents after update
    error_log("Cart after update: " . print_r($_SESSION['cart'], true));
}

/**
 * Remove item from cart
 */
function removeCartItem()
{
    global $response;

    $itemId = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';

    if (empty($itemId)) {
        $response['message'] = 'Invalid item ID';
        return;
    }

    // Debug - log removal attempt
    error_log("Removing cart item: ID: $itemId");

    $itemFound = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemId) {
            unset($_SESSION['cart'][$key]);
            $itemFound = true;
            break;
        }
    }

    if (!$itemFound) {
        $response['message'] = 'Item not found in cart';
        return;
    }

    // Reindex array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Item removed from cart';
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();

    // Debug - log the cart contents after removal
    error_log("Cart after remove: " . print_r($_SESSION['cart'], true));
}

/**
 * Reset the entire cart (for debugging purposes)
 */
function resetCart()
{
    global $response;

    // Clear the cart
    $_SESSION['cart'] = [];

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Cart has been reset';
    $response['count'] = 0;
    $response['total'] = 0;

    // Debug log
    error_log("Cart has been reset");
}

/**
 * Get total number of items in cart
 */
function getCartItemCount()
{
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Get total price of all items in cart
 */
function getCartTotal()
{
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return number_format($total, 2);
}
