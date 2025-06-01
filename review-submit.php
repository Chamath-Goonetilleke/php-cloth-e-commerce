<?php
require_once 'includes/config.php';
if (session_status() == PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

if (!$productId || !$orderId || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Check if already reviewed
$stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? AND order_id = ?");
$stmt->bind_param("iii", $productId, $userId, $orderId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product for this order.']);
    exit;
}

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiis", $productId, $userId, $orderId, $rating, $feedback);
$success = $stmt->execute();
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not save review.']);
}
