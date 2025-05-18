<?php
require_once 'includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$pageTitle = "Cart Test Page";

// Include header
include 'includes/header.php';

// Get a sample product from database
$productId = 1; // Change this to an existing product ID in your database

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
?>

<div class="container" style="padding: 50px 0;">
    <h1>Cart Test Page</h1>

    <?php if ($product): ?>
        <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h2><?php echo $product['name']; ?></h2>
            <p>Price: $<?php echo $product['price']; ?></p>

            <div style="margin: 20px 0;">
                <h3>Size:</h3>
                <div class="size-options" style="display: flex; gap: 10px; margin: 10px 0;">
                    <button class="size-btn" data-size="S">S</button>
                    <button class="size-btn" data-size="M">M</button>
                    <button class="size-btn" data-size="L">L</button>
                    <button class="size-btn" data-size="XL">XL</button>
                </div>

                <h3>Quantity:</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin: 10px 0;">
                    <button id="decrease">-</button>
                    <input type="number" id="quantity" value="1" min="1" style="width: 50px; text-align: center;">
                    <button id="increase">+</button>
                </div>

                <button id="addToCart" style="margin-top: 20px; padding: 10px 20px;"
                    data-id="<?php echo $product['id']; ?>">
                    Add to Cart
                </button>
            </div>
        </div>
    <?php else: ?>
        <p>No product found with ID <?php echo $productId; ?>. Please change the product ID in the code.</p>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <h2>Current Cart Contents:</h2>
        <pre id="cartContents" style="background-color: #f8f8f8; padding: 20px; border-radius: 5px;"><?php print_r($_SESSION['cart'] ?? []); ?></pre>
    </div>
</div>

<script>
    // Size selection
    const sizeButtons = document.querySelectorAll('.size-btn');
    let selectedSize = null;

    sizeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            sizeButtons.forEach(btn => btn.style.backgroundColor = '');

            // Add active class to clicked button
            this.style.backgroundColor = '#1D503A';
            this.style.color = 'white';

            // Store selected size
            selectedSize = this.getAttribute('data-size');
            console.log('Selected size:', selectedSize);
        });
    });

    // Quantity control
    const quantityInput = document.getElementById('quantity');

    document.getElementById('decrease').addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    document.getElementById('increase').addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        quantityInput.value = currentValue + 1;
    });

    // Add to cart
    document.getElementById('addToCart').addEventListener('click', function() {
        if (!selectedSize) {
            alert('Please select a size first');
            return;
        }

        const productId = this.getAttribute('data-id');
        const quantity = parseInt(quantityInput.value);

        console.log('Adding to cart:', {
            productId: productId,
            size: selectedSize,
            quantity: quantity
        });

        // Send AJAX request
        fetch('cart-update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${productId}&size=${selectedSize}&quantity=${quantity}&action=add`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);

                if (data.success) {
                    alert(data.message);

                    // Refresh the page to show updated cart
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Could not add to cart'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding to cart. See console for details.');
            });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>