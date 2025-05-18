<?php
require_once 'includes/config.php';

// Set page variables
$pageTitle = "Shopping Cart - OneFit Clothing";
$showSaleBanner = false;

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

// Include header
include 'includes/header.php';
?>

<!-- Cart Section -->
<section class="cart-section">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <p class="empty-cart-suggestion">Check out our latest collection and find something you'll love!</p>
                <div class="empty-cart-actions">
                    <a href="index.php" class="btn primary-btn">Continue Shopping</a>
                    <a href="tshirts.php" class="btn secondary-btn">T-Shirts</a>
                    <a href="hoodies.php" class="btn secondary-btn">Hoodies</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-main-container">
                <div class="cart-container">
                    <div class="cart-header">
                        <div class="cart-row">
                            <div class="cart-col product-col">Product</div>
                            <div class="cart-col price-col">Price</div>
                            <div class="cart-col quantity-col">Quantity</div>
                            <div class="cart-col subtotal-col">Subtotal</div>
                            <div class="cart-col remove-col"></div>
                        </div>
                    </div>

                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item" data-cart-item="<?php echo $item['id']; ?>">
                                <div class="cart-col product-col">
                                    <div class="product-info">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                        <div>
                                            <h3><?php echo $item['name']; ?></h3>
                                            <p>Size: <?php echo $item['size']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="cart-col price-col">
                                    <span class="price">LKR <?php echo number_format($item['price'], 2); ?> </span>
                                </div>
                                <div class="cart-col quantity-col">
                                    <div class="quantity-container">
                                        <button type="button" class="quantity-btn minus" data-id="<?php echo $item['id']; ?>">-</button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-id="<?php echo $item['id']; ?>">
                                        <button type="button" class="quantity-btn plus" data-id="<?php echo $item['id']; ?>">+</button>
                                    </div>
                                </div>
                                <div class="cart-col subtotal-col">
                                    <span class="subtotal">LKR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                                <div class="cart-col remove-col">
                                    <button type="button" class="remove-btn" data-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-actions">
                        <div class="coupon">
                            <input type="text" placeholder="Enter coupon code">
                            <button type="button" class="coupon-btn">Apply Coupon</button>
                        </div>
                        <a href="index.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>

                <div class="cart-summary-container">
                    <div class="cart-summary">
                        <h2>Cart Total</h2>
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value cart-total">LKR <?php echo number_format($cartTotal, 2); ?> </span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Shipping:</span>
                            <span class="summary-value">
                                <?php
                                $shipping = $cartTotal > 5000 ? 0 : 350;
                                echo 'LKR ' . number_format($shipping, 2);
                                ?>
                            </span>
                        </div>
                        <?php if ($cartTotal > 5000): ?>
                            <div class="free-shipping-message">
                                <i class="fas fa-truck"></i> Free shipping applied!
                            </div>
                        <?php else: ?>
                            <div class="free-shipping-message">
                                <i class="fas fa-info-circle"></i> Spend LKR <?php echo number_format(5000 - $cartTotal, 2); ?>more for free shipping
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total-row">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value">LKR <?php echo number_format($cartTotal + $shipping, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Main cart section styles */
    .cart-section {
        padding: 60px 0;
        background-color: #f9f9f9;
        min-height: 70vh;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .cart-main-container {
        display: flex;
        width: 100%;
        flex-direction: column;
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (min-width: 992px) {
        .cart-main-container {
            flex-direction: row;
            gap: 30px;
        }
    }

    .page-title {
        font-size: 32px;
        margin-bottom: 30px;
        color: #1D503A;
        font-weight: 700;
        text-align: center;
        position: relative;
    }

    .page-title:after {
        content: "";
        display: block;
        width: 80px;
        height: 3px;
        background-color: #e63946;
        margin: 15px auto 0;
    }

    /* Empty cart styling */
    .empty-cart {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        padding: 60px 30px;
        text-align: center;
        margin: 30px auto;
        max-width: 700px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .empty-cart-icon {
        font-size: 80px;
        color: #1D503A;
        margin-bottom: 25px;
        background-color: rgba(29, 80, 58, 0.08);
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }

    .empty-cart h2 {
        font-size: 28px;
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .empty-cart p {
        color: #666;
        font-size: 16px;
        margin-bottom: 10px;
        max-width: 450px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-cart-suggestion {
        margin-bottom: 30px;
        color: #777;
    }

    .empty-cart-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-top: 25px;
    }

    /* Button Styles */
    .primary-btn {
        background-color: #1D503A;
        color: white;
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        border: 2px solid #1D503A;
        font-size: 16px;
    }

    .primary-btn:hover {
        background-color: #143726;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(29, 80, 58, 0.2);
    }

    .secondary-btn {
        background-color: transparent;
        color: #1D503A;
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        border: 2px solid #1D503A;
        font-size: 16px;
    }

    .secondary-btn:hover {
        background-color: rgba(29, 80, 58, 0.1);
        transform: translateY(-3px);
    }

    /* Cart with items styling */
    .cart-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 40px;
        width: 100%;
        animation: fadeIn 0.6s ease-out;
        transition: all 0.3s ease;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .cart-header {
        background-color: #f5f5f5;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .cart-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
        align-items: center;
        gap: 10px;
    }

    .cart-col {
        padding: 10px;
        font-weight: 600;
        color: #333;
    }

    .cart-items {
        padding: 0 20px;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
        align-items: center;
        gap: 10px;
        padding: 20px 10px;
        border-bottom: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        animation: slideInRight 0.3s ease-out forwards;
        animation-delay: calc(var(--item-index, 0) * 0.1s);
        opacity: 0;
    }

    .cart-item:hover {
        background-color: #f9f9f9;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .product-info img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }

    .product-info:hover img {
        transform: scale(1.05);
    }

    .product-info h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .product-info p {
        color: #666;
        font-size: 14px;
    }

    .price-col .price {
        font-weight: 600;
        color: #1D503A;
    }

    .quantity-container {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
        max-width: 120px;
    }

    .quantity-btn {
        background-color: #f0f0f0;
        border: none;
        color: #333;
        width: 35px;
        height: 35px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quantity-btn:hover {
        background-color: #e0e0e0;
    }

    .quantity-btn:active {
        transform: scale(0.95);
    }

    .quantity-input {
        width: 40px;
        height: 35px;
        border: none;
        border-left: 1px solid #ddd;
        border-right: 1px solid #ddd;
        text-align: center;
        font-size: 14px;
    }

    .subtotal-col .subtotal {
        font-weight: 600;
        color: #e63946;
    }

    .remove-btn {
        background: none;
        border: none;
        color: #999;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .remove-btn:hover {
        color: #e63946;
        transform: rotate(5deg);
    }

    /* Cart actions and summary */
    .cart-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background-color: #f5f5f5;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .coupon {
        display: flex;
        gap: 10px;
    }

    .coupon input {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .coupon-btn {
        background-color: #1D503A;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
    }

    .coupon-btn:hover {
        background-color: #143726;
        transform: translateY(-2px);
    }

    .coupon-btn:active {
        transform: scale(0.95);
    }

    .continue-shopping {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #1D503A;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .continue-shopping:hover {
        color: #143726;
    }

    /* Cart summary styling */
    .cart-summary-container {
        width: 100%;
        margin-top: 0;
    }

    @media (min-width: 992px) {
        .cart-summary-container {
            width: 350px;
            min-width: 350px;
            margin-top: 0;
        }
    }

    .cart-summary {
        display: flex;
        flex-direction: column;
        width: 100%;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        padding: 25px;
    }

    .cart-summary h2 {
        font-size: 22px;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
        font-weight: 600;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .summary-label {
        color: #555;
        font-weight: 500;
    }

    .summary-value {
        font-weight: 600;
        color: #333;
    }

    .free-shipping-message {
        margin: 15px 0;
        padding: 12px;
        background-color: #f8f8f8;
        border-radius: 6px;
        font-size: 14px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .free-shipping-message i {
        color: #1D503A;
        font-size: 16px;
    }

    .total-row {
        margin-top: 10px;
        padding-top: 15px;
        border-top: 2px solid #eee;
        border-bottom: none;
    }

    .total-row .summary-label {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    .total-row .summary-value {
        font-size: 22px;
        color: #e63946;
        font-weight: 700;
    }

    .checkout-btn {
        display: block;
        width: 100%;
        background-color: #e63946;
        color: white;
        border: none;
        padding: 15px;
        text-align: center;
        border-radius: 6px;
        margin-top: 25px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 8px rgba(230, 57, 70, 0.2);
    }

    .checkout-btn:hover {
        background-color: #d32f3c;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
    }

    .checkout-btn:active {
        transform: scale(0.95);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {

        .cart-row,
        .cart-item {
            grid-template-columns: 2.5fr 1fr 1.5fr 1fr 0.5fr;
        }

        .cart-summary {
            padding: 20px;
            max-width: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .checkout-btn {
            padding: 16px;
            font-size: 18px;
            margin-top: 15px;
        }
    }

    @media (max-width: 768px) {
        .cart-header {
            display: none;
        }

        .cart-row,
        .cart-item {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 20px;
        }

        .cart-item {
            position: relative;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .product-info {
            margin-bottom: 15px;
        }

        .product-info img {
            width: 70px;
            height: 70px;
        }

        .price-col,
        .quantity-col,
        .subtotal-col {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-top: 1px solid #f0f0f0;
            margin-top: 5px;
        }

        .price-col::before {
            content: "Price:";
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .quantity-col::before {
            content: "Quantity:";
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .subtotal-col::before {
            content: "Subtotal:";
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .remove-col {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .remove-btn {
            background-color: #f8f8f8;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-actions {
            flex-direction: column;
            gap: 20px;
        }

        .coupon {
            width: 100%;
        }

        .coupon input {
            flex-grow: 1;
        }

        .cart-summary-container {
            margin-top: 10px;
        }

        .empty-cart {
            padding: 40px 20px;
        }

        .empty-cart-icon {
            font-size: 60px;
            width: 120px;
            height: 120px;
        }

        .empty-cart h2 {
            font-size: 24px;
        }

        .empty-cart-actions {
            flex-direction: column;
            gap: 10px;
        }

        .primary-btn,
        .secondary-btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .page-title {
            font-size: 26px;
        }

        .cart-section {
            padding: 40px 0;
        }

        .product-info {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .product-info img {
            margin-bottom: 10px;
        }

        .quantity-container {
            width: 100%;
            max-width: none;
        }

        .cart-summary {
            padding: 15px;
        }

        .free-shipping-message {
            padding: 10px;
            font-size: 13px;
        }

        .checkout-btn {
            margin-top: 15px;
        }
    }

    /* Add focus styles for inputs */
    input:focus {
        outline: none;
        border-color: #1D503A;
        box-shadow: 0 0 0 2px rgba(29, 80, 58, 0.1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Apply animation delay to cart items for staggered effect
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach((item, index) => {
            item.style.setProperty('--item-index', index);
        });

        // Update quantity buttons
        const minusButtons = document.querySelectorAll('.quantity-btn.minus');
        const plusButtons = document.querySelectorAll('.quantity-btn.plus');
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const removeButtons = document.querySelectorAll('.remove-btn');

        // Minus button functionality
        minusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const input = document.querySelector(`.quantity-input[data-id="${itemId}"]`);
                let value = parseInt(input.value);

                if (value > 1) {
                    value--;
                    input.value = value;
                    updateCartItem(itemId, value);
                }
            });
        });

        // Plus button functionality
        plusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const input = document.querySelector(`.quantity-input[data-id="${itemId}"]`);
                let value = parseInt(input.value);

                value++;
                input.value = value;
                updateCartItem(itemId, value);
            });
        });

        // Direct input change
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const itemId = this.getAttribute('data-id');
                let value = parseInt(this.value);

                if (value < 1) {
                    value = 1;
                    this.value = value;
                }

                updateCartItem(itemId, value);
            });
        });

        // Remove item button
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                removeCartItem(itemId);
            });
        });

        // Update cart item function
        function updateCartItem(itemId, quantity) {
            fetch('cart-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `item_id=${itemId}&quantity=${quantity}&action=update`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update subtotal for this item
                        const itemRow = document.querySelector(`[data-cart-item="${itemId}"]`);
                        const priceElement = itemRow.querySelector('.price');
                        const priceText = priceElement.textContent;
                        const price = parseFloat(priceText.replace('RS: ', '').replace(' LKR', ''));
                        const subtotalElement = itemRow.querySelector('.subtotal');
                        subtotalElement.textContent = `RS: ${(price * quantity).toFixed(2)} LKR`;

                        // Update cart total
                        document.querySelector('.cart-total').textContent = `RS: ${data.total} LKR`;

                        // Update shipping and total
                        updateCartSummary(data.total);
                    }
                })
                .catch(error => {
                    console.error('Error updating cart:', error);
                });
        }

        // Remove cart item function
        function removeCartItem(itemId) {
            fetch('cart-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `item_id=${itemId}&action=remove`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove item from DOM
                        const itemRow = document.querySelector(`[data-cart-item="${itemId}"]`);
                        itemRow.remove();

                        // Update cart total
                        document.querySelector('.cart-total').textContent = `RS: ${data.total} LKR`;

                        // Update shipping and total
                        updateCartSummary(data.total);

                        // If cart is empty, reload the page to show empty cart message
                        if (data.count === 0) {
                            window.location.reload();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                });
        }

        // Update cart summary
        function updateCartSummary(total) {
            total = parseFloat(total);

            // Calculate shipping
            const shipping = total > 5000 ? 0 : 350;

            // Update shipping display
            const shippingElement = document.querySelector('.summary-row:nth-child(2) .summary-value');
            shippingElement.textContent = `RS: ${shipping.toFixed(2)} LKR`;

            // Update free shipping message
            const freeShippingMessage = document.querySelector('.free-shipping-message');
            if (total > 5000) {
                freeShippingMessage.innerHTML = '<i class="fas fa-truck"></i> Free shipping applied!';
            } else {
                const amountNeeded = (5000 - total).toFixed(2);
                freeShippingMessage.innerHTML = `<i class="fas fa-info-circle"></i> Spend RS: ${amountNeeded} LKR more for free shipping`;
            }

            // Update total
            const totalElement = document.querySelector('.total-row .summary-value');
            totalElement.textContent = `RS: ${(total + shipping).toFixed(2)} LKR`;
        }
    });
</script>

<?php if (defined('DEBUG') && DEBUG): ?>
    <!-- <div class="debug-section">
        <div class="debug-toggle" onclick="toggleDebug()">
            <i class="fas fa-bug"></i> Toggle Debug Info
        </div>
        <div class="debug-content" id="debugContent" style="display: none;">
            <h3>Cart Debug Information</h3>
            <pre><?php print_r($_SESSION['cart']); ?></pre>
            <h3>Session Information</h3>
            <pre>Session ID: <?php echo session_id(); ?></pre>
        </div>
    </div> -->

    <style>
        .debug-section {
            margin: 50px auto;
            max-width: 1200px;
            padding: 0 20px;
        }

        .debug-toggle {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #333;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .debug-toggle:hover {
            background-color: #e9ecef;
        }

        .debug-toggle i {
            color: #e63946;
        }

        .debug-content {
            margin-top: 15px;
            padding: 20px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .debug-content h3 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .debug-content pre {
            background-color: #fff;
            padding: 15px;
            border-radius: 4px;
            overflow: auto;
            max-height: 300px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.4;
            border: 1px solid #eee;
        }
    </style>

    <script>
        function toggleDebug() {
            const debugContent = document.getElementById('debugContent');
            if (debugContent.style.display === 'none') {
                debugContent.style.display = 'block';
            } else {
                debugContent.style.display = 'none';
            }
        }
    </script>
<?php endif; ?>

<?php
// Include footer
include 'includes/footer.php';
?>