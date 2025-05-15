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
    global $response;

    // Get product details from POST
    $productId = $_POST['product_id'] ?? 0;
    $size = $_POST['size'] ?? 'M';
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if (empty($productId)) {
        $response['message'] = 'Invalid product ID';
        return;
    }

    // Check if product exists in database
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'Product not found';
        return;
    }

    $product = $result->fetch_assoc();

    // Generate a unique cart item ID
    $cartItemId = uniqid();

    // Use sale price if available, otherwise use regular price
    $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];

    // Check if item already exists in cart
    $itemExists = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $productId && $item['size'] == $size) {
            // Update quantity instead of adding a new item
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $itemExists = true;
            break;
        }
    }

    // Add new item to cart if it doesn't exist
    if (!$itemExists) {
        $_SESSION['cart'][] = [
            'id' => $cartItemId,
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'size' => $size,
            'image' => $product['image_path']
        ];
    }

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Product added to cart';
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();
}

/**
 * Update cart item quantity
 */
function updateCartItem()
{
    global $response;

    $itemId = $_POST['item_id'] ?? '';
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if (empty($itemId)) {
        $response['message'] = 'Invalid item ID';
        return;
    }

    // Update quantity
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemId) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            break;
        }
    }

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Cart updated';
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();
}

/**
 * Remove item from cart
 */
function removeCartItem()
{
    global $response;

    $itemId = $_POST['item_id'] ?? '';

    if (empty($itemId)) {
        $response['message'] = 'Invalid item ID';
        return;
    }

    // Remove item from cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }

    // Reindex array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    // Return success response
    $response['success'] = true;
    $response['message'] = 'Item removed from cart';
    $response['count'] = getCartItemCount();
    $response['total'] = getCartTotal();
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
