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
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="index.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
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
                                <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <div class="cart-col quantity-col">
                                <div class="quantity-container">
                                    <button type="button" class="quantity-btn minus" data-id="<?php echo $item['id']; ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-id="<?php echo $item['id']; ?>">
                                    <button type="button" class="quantity-btn plus" data-id="<?php echo $item['id']; ?>">+</button>
                                </div>
                            </div>
                            <div class="cart-col subtotal-col">
                                <span class="subtotal">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
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
                        <input type="text" placeholder="Coupon code">
                        <button type="button" class="btn">Apply Coupon</button>
                    </div>
                    <a href="index.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>

                <div class="cart-summary">
                    <h2>Cart Total</h2>
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value cart-total">$<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping:</span>
                        <span class="summary-value">
                            <?php
                            $shipping = $cartTotal > 50 ? 0 : 7.99;
                            echo '$' . number_format($shipping, 2);
                            ?>
                        </span>
                    </div>
                    <?php if ($cartTotal > 50): ?>
                        <div class="free-shipping-message">
                            <i class="fas fa-truck"></i> Free shipping applied!
                        </div>
                    <?php else: ?>
                        <div class="free-shipping-message">
                            <i class="fas fa-info-circle"></i> Spend $<?php echo number_format(50 - $cartTotal, 2); ?> more for free shipping
                        </div>
                    <?php endif; ?>
                    <div class="summary-row total-row">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value">$<?php echo number_format($cartTotal + $shipping, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                        const price = parseFloat(priceElement.textContent.replace('$', ''));
                        const subtotalElement = itemRow.querySelector('.subtotal');
                        subtotalElement.textContent = '$' + (price * quantity).toFixed(2);

                        // Update cart total
                        document.querySelector('.cart-total').textContent = '$' + data.total;

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
                        document.querySelector('.cart-total').textContent = '$' + data.total;

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

        // Update cart summary (shipping and total)
        function updateCartSummary(subtotal) {
            subtotal = parseFloat(subtotal);
            let shipping = 0;

            // Calculate shipping
            if (subtotal <= 50) {
                shipping = 7.99;
            }

            // Update shipping element
            const shippingElement = document.querySelector('.summary-row:nth-child(2) .summary-value');
            shippingElement.textContent = '$' + shipping.toFixed(2);

            // Update free shipping message
            const freeShippingMessage = document.querySelector('.free-shipping-message');
            if (subtotal > 50) {
                freeShippingMessage.innerHTML = '<i class="fas fa-truck"></i> Free shipping applied!';
            } else {
                const remaining = 50 - subtotal;
                freeShippingMessage.innerHTML = `<i class="fas fa-info-circle"></i> Spend $${remaining.toFixed(2)} more for free shipping`;
            }

            // Update total
            const totalElement = document.querySelector('.total-row .summary-value');
            totalElement.textContent = '$' + (subtotal + shipping).toFixed(2);
        }
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>