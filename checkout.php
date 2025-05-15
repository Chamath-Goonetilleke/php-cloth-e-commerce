<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Set page variables
$pageTitle = "Checkout - OneFit Clothing";
$showSaleBanner = false;

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Redirect to cart page if cart is empty
    header('Location: cart.php');
    exit;
}

// Initialize variables
$errors = [];
$successMessage = '';
$isLoggedIn = isLoggedIn();
$userData = [];
$db_needs_update = false;

// Check if orders table has all required columns
try {
    // Check if order_number column exists in orders table
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_number'");
    if ($result && $result->num_rows === 0) {
        $db_needs_update = true;
    }

    // Check if name column exists in order_items table
    $result = $conn->query("SHOW COLUMNS FROM order_items LIKE 'name'");
    if ($result && $result->num_rows === 0) {
        $db_needs_update = true;
    }
} catch (Exception $e) {
    // If any error occurs (e.g., table doesn't exist), we need an update
    $db_needs_update = true;
}

// Get user data if logged in
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
}

// Calculate cart totals
$subtotal = 0;
$items = [];

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $items[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'size' => $item['size'] ?? 'N/A',
        'image' => $item['image'] ?? ''
    ];
}

// Calculate shipping
$shipping = $subtotal > 50 ? 0 : 7.99;

// Calculate total
$total = $subtotal + $shipping;

// Process order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && !$db_needs_update) {
    // Validate input
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';

    // Basic validation
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($zipCode)) $errors[] = "ZIP/Postal code is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($paymentMethod)) $errors[] = "Payment method is required";

    // If validation passes, process the order
    if (empty($errors)) {
        try {
            // Start transaction
            $conn->begin_transaction();

            // 1. Create order
            $orderNumber = 'ONF' . time() . rand(100, 999);
            $userId = $isLoggedIn ? $_SESSION['user_id'] : NULL;
            $status = 'pending';

            // Create shipping address from billing address (can be extended for different shipping address later)
            $shippingAddress = $address . "\n" . $city . ", " . $state . " " . $zipCode . "\n" . $country;
            $billingAddress = $shippingAddress; // Same as shipping for now

            $sql = "INSERT INTO orders (order_number, user_id, first_name, last_name, email, phone, 
                    address, city, state, zip_code, country, subtotal, shipping, total_amount, 
                    payment_method, status, shipping_address, billing_address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sissssssssdddsssss",
                $orderNumber,
                $userId,
                $firstName,
                $lastName,
                $email,
                $phone,
                $address,
                $city,
                $state,
                $zipCode,
                $country,
                $subtotal,
                $shipping,
                $total,
                $paymentMethod,
                $status,
                $shippingAddress,
                $billingAddress
            );

            if (!$stmt->execute()) {
                throw new Exception("Error creating order: " . $conn->error);
            }

            $orderId = $conn->insert_id;

            // 2. Add order items
            foreach ($items as $item) {
                // Verify that the product exists
                $productId = null;
                $checkSql = "SELECT id FROM products WHERE id = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("i", $item['id']);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult && $checkResult->num_rows > 0) {
                    $productId = $item['id'];
                }

                $sql = "INSERT INTO order_items (order_id, product_id, name, price, quantity, size) 
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "iisdis",
                    $orderId,
                    $productId,
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $item['size']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error adding order items: " . $conn->error);
                }
            }

            // 3. If user is logged in, update their address if requested
            if ($isLoggedIn && isset($_POST['save_address']) && $_POST['save_address'] == '1') {
                $fullAddress = $address . "\n" . $city . ", " . $state . " " . $zipCode . "\n" . $country;

                $sql = "UPDATE users SET address = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $fullAddress, $_SESSION['user_id']);
                $stmt->execute();
            }

            // Commit transaction
            $conn->commit();

            // Clear the cart
            $_SESSION['cart'] = [];

            // Store order info in session for confirmation page
            $_SESSION['order_complete'] = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total' => $total
            ];

            // Redirect to order confirmation page
            header('Location: order-confirmation.php');
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

// Include header
include 'includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="page-title">Checkout</h1>

        <?php if ($db_needs_update): ?>
            <div class="alert alert-warning">
                <p>Your database needs to be updated to process orders correctly. Please <a href="database_update.php">click here</a> to update your database.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <form method="POST" action="checkout.php" id="checkout-form" <?php echo $db_needs_update ? 'onsubmit="return false;"' : ''; ?>>
                <div class="checkout-content">
                    <div class="billing-details">
                        <h2>Billing Details</h2>

                        <?php if (!$isLoggedIn): ?>
                            <div class="account-prompt">
                                <p>Already have an account? <a href="login.php?redirect=checkout.php">Login</a></p>
                            </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required
                                    value="<?php echo isset($userData['first_name']) ? htmlspecialchars($userData['first_name']) : (isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required
                                    value="<?php echo isset($userData['last_name']) ? htmlspecialchars($userData['last_name']) : (isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo isset($userData['phone']) ? htmlspecialchars($userData['phone']) : (isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" required
                                value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required
                                    value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state"
                                    value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="zip_code">ZIP/Postal Code *</label>
                                <input type="text" id="zip_code" name="zip_code" required
                                    value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="country">Country *</label>
                                <select id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="US" <?php echo (isset($_POST['country']) && $_POST['country'] == 'US') ? 'selected' : ''; ?>>United States</option>
                                    <option value="CA" <?php echo (isset($_POST['country']) && $_POST['country'] == 'CA') ? 'selected' : ''; ?>>Canada</option>
                                    <option value="UK" <?php echo (isset($_POST['country']) && $_POST['country'] == 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="LK" <?php echo (isset($_POST['country']) && $_POST['country'] == 'LK') ? 'selected' : ''; ?>>Sri Lanka</option>
                                    <option value="AU" <?php echo (isset($_POST['country']) && $_POST['country'] == 'AU') ? 'selected' : ''; ?>>Australia</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="save_address" name="save_address" value="1">
                                <label for="save_address">Save this address to my account</label>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="order_notes">Order Notes (optional)</label>
                            <textarea id="order_notes" name="order_notes" rows="4"><?php echo isset($_POST['order_notes']) ? htmlspecialchars($_POST['order_notes']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="order-summary">
                        <h2>Your Order</h2>
                        <div class="summary-table">
                            <div class="summary-header">
                                <span>Product</span>
                                <span>Subtotal</span>
                            </div>

                            <?php foreach ($items as $item): ?>
                                <div class="summary-item">
                                    <div class="item-details">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-meta">
                                            Size: <?php echo htmlspecialchars($item['size']); ?> × <?php echo $item['quantity']; ?>
                                        </span>
                                    </div>
                                    <span class="item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>

                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>

                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>
                                    <?php if ($shipping > 0): ?>
                                        $<?php echo number_format($shipping, 2); ?>
                                    <?php else: ?>
                                        Free Shipping
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="summary-row total-row">
                                <span>Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <div class="payment-methods">
                            <h3>Payment Method</h3>

                            <div class="payment-option">
                                <input type="radio" id="payment_cod" name="payment_method" value="cod" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'cod') ? 'checked' : ''; ?>>
                                <label for="payment_cod">Cash on Delivery</label>
                                <div class="payment-description">
                                    Pay with cash upon delivery.
                                </div>
                            </div>

                            <div class="payment-option">
                                <input type="radio" id="payment_bank" name="payment_method" value="bank" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank') ? 'checked' : ''; ?>>
                                <label for="payment_bank">Direct Bank Transfer</label>
                                <div class="payment-description">
                                    Make your payment directly into our bank account. Please use your Order ID as the payment reference.
                                </div>
                            </div>

                            <div class="payment-option">
                                <input type="radio" id="payment_card" name="payment_method" value="card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'card') ? 'checked' : ''; ?>>
                                <label for="payment_card">Credit Card</label>
                                <div class="payment-description payment-card-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                </div>
                                <div class="card-fields" id="card-fields">
                                    <div class="form-group">
                                        <label for="card_number">Card Number</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                        </div>
                                        <div class="form-group">
                                            <label for="cvv">CVV</label>
                                            <input type="text" id="cvv" name="cvv" placeholder="123">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-actions">
                            <button type="submit" name="place_order" class="btn place-order-btn" <?php echo $db_needs_update ? 'disabled' : ''; ?>>Place Order</button>
                            <p class="terms-agreement">
                                By placing your order, you agree to our <a href="terms.html">Terms & Conditions</a> and <a href="privacy-policy.html">Privacy Policy</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    /* Checkout Styles */
    .checkout-section {
        padding: 60px 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .page-title {
        font-size: 32px;
        margin-bottom: 30px;
        color: #1D503A;
        text-align: center;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-warning a {
        color: #634b03;
        font-weight: 600;
        text-decoration: underline;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }

    .checkout-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .checkout-content {
        display: flex;
        flex-wrap: wrap;
    }

    .billing-details,
    .order-summary {
        padding: 30px;
    }

    .billing-details {
        flex: 1 1 60%;
        border-right: 1px solid #eee;
    }

    .order-summary {
        flex: 1 1 40%;
        background-color: #f9f9f9;
    }

    h2 {
        font-size: 24px;
        margin-bottom: 25px;
        color: #1D503A;
    }

    .account-prompt {
        background-color: #f1f1f1;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .account-prompt a {
        color: #1D503A;
        font-weight: 600;
    }

    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 15px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #1D503A;
        outline: none;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-group input {
        width: auto;
    }

    .checkbox-group label {
        margin-bottom: 0;
    }

    /* Order Summary Styles */
    .summary-table {
        margin-bottom: 30px;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        padding-bottom: 10px;
        border-bottom: 2px solid #ddd;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .item-details {
        flex: 1;
    }

    .item-name {
        display: block;
        font-weight: 500;
    }

    .item-meta {
        display: block;
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }

    .item-price {
        font-weight: 500;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .total-row {
        font-size: 18px;
        font-weight: 600;
        color: #1D503A;
        border-bottom: none;
        margin-top: 10px;
    }

    /* Payment Methods */
    .payment-methods {
        margin-bottom: 30px;
    }

    .payment-methods h3 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .payment-option {
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .payment-option input {
        margin-right: 10px;
    }

    .payment-option label {
        font-weight: 500;
    }

    .payment-description {
        margin-top: 10px;
        font-size: 14px;
        color: #666;
        margin-left: 25px;
    }

    .payment-card-icons {
        font-size: 24px;
        display: flex;
        gap: 10px;
    }

    .card-fields {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        display: none;
    }

    /* Checkout Actions */
    .checkout-actions {
        margin-top: 30px;
    }

    .place-order-btn {
        width: 100%;
        padding: 15px;
        background-color: #1D503A;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .place-order-btn:hover {
        background-color: #163c2c;
    }

    .place-order-btn[disabled] {
        background-color: #ccc;
        cursor: not-allowed;
    }

    .place-order-btn[disabled]:hover {
        background-color: #ccc;
    }

    .terms-agreement {
        font-size: 14px;
        margin-top: 15px;
        text-align: center;
        color: #666;
    }

    .terms-agreement a {
        color: #1D503A;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .checkout-content {
            flex-direction: column;
        }

        .billing-details {
            border-right: none;
            border-bottom: 1px solid #eee;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide credit card fields based on payment method selection
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const cardFields = document.getElementById('card-fields');

        function toggleCardFields() {
            if (document.getElementById('payment_card').checked) {
                cardFields.style.display = 'block';
            } else {
                cardFields.style.display = 'none';
            }
        }

        // Initial check
        toggleCardFields();

        // Add event listeners
        paymentMethods.forEach(method => {
            method.addEventListener('change', toggleCardFields);
        });

        // Form validation
        const checkoutForm = document.getElementById('checkout-form');

        checkoutForm.addEventListener('submit', function(e) {
            let hasError = false;

            // Validate required fields
            const requiredFields = checkoutForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    hasError = true;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            // Validate card fields if credit card is selected
            if (document.getElementById('payment_card').checked) {
                const cardNumber = document.getElementById('card_number');
                const expiryDate = document.getElementById('expiry_date');
                const cvv = document.getElementById('cvv');

                if (!cardNumber.value.trim() || !expiryDate.value.trim() || !cvv.value.trim()) {
                    hasError = true;

                    if (!cardNumber.value.trim()) cardNumber.style.borderColor = '#dc3545';
                    if (!expiryDate.value.trim()) expiryDate.style.borderColor = '#dc3545';
                    if (!cvv.value.trim()) cvv.style.borderColor = '#dc3545';
                }
            }

            if (hasError) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>