<?php
require_once 'includes/config.php';

// Set default page variables
$pageTitle = "Product Details";
$showSaleBanner = false;

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if product ID is not provided
if ($productId === 0) {
    header("Location: index.php");
    exit();
}

// Get product details
$product = null;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
    // Update page title with product name
    $pageTitle = $product['name'] . " - OneFit Clothing";
} else {
    // Product not found, redirect to home page
    header("Location: index.php");
    exit();
}

// Get related products
$relatedProducts = [];
$sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $product['category'], $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $relatedProducts[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<main class="container">
    <div class="product-section">
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="product-main-image">
            </div>
            <div class="thumbnail-container">
                <div class="thumbnail">
                    <img src="<?php echo $product['image_path']; ?>" alt="Thumbnail 1" class="product-thumbnail active" data-image="<?php echo $product['image_path']; ?>">
                </div>
                <?php
                // Get additional product images if available
                $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($image = $result->fetch_assoc()) {
                        echo '<div class="thumbnail">';
                        echo '<img src="' . $image['image_path'] . '" alt="Thumbnail" class="product-thumbnail" data-image="' . $image['image_path'] . '">';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?php echo $product['name']; ?></h1>
            <div class="product-price">
                <?php if ($product['sale_price']): ?>
                    RS: <?php echo $product['sale_price']; ?> LKR <span class="discount">-<?php
                                                                                            $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                                                                                            echo $discount . '%';
                                                                                            ?></span>
                <?php else: ?>
                    RS: <?php echo $product['price']; ?> LKR
                <?php endif; ?>
            </div>

            <div class="product-rating">
                <div class="stars">★★★★☆</div>
                <span>(24 reviews)</span>
            </div>

            <div class="product-description">
                <p><?php echo $product['description']; ?></p>
            </div>

            <div class="options-container">
                <label class="option-label">Size</label>
                <div class="size-options">
                    <button class="size-btn" data-size="S">S</button>
                    <button class="size-btn" data-size="M">M</button>
                    <button class="size-btn" data-size="L">L</button>
                    <button class="size-btn" data-size="XL">XL</button>
                </div>

                <div class="quantity-selector">
                    <label class="option-label">Quantity</label>
                    <button id="decrease" class="quantity-btn">-</button>
                    <input type="text" id="quantity" class="quantity-input" id="quantity" value="1" max="<?php echo $product['stock']; ?>">
                    <button id="increase" class="quantity-btn">+</button>
                </div>

                <button id="addToCart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
            </div>
        </div>
    </div>

    <div class="product-features">
        <h2 class="features-title">Product Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">☁️</div>
                <h3>Premium Quality</h3>
                <p>Made from high-quality materials for durability and comfort.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧥</div>
                <h3>Perfect Fit</h3>
                <p>Designed for a comfortable fit that flatters your body shape.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3>Stylish Design</h3>
                <p>Modern, trendy design that keeps you looking fashionable.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧼</div>
                <h3>Easy to Care</h3>
                <p>Machine-washable and easy to maintain its quality and appearance.</p>
            </div>
        </div>
    </div><br>

    <div class="product-details">
        <h2 class="details-title">Product Specifications</h2>
        <ul>
            <li><strong>Material:</strong> Cotton, Polyester blend</li>
            <li><strong>Color:</strong> As shown in image</li>
            <li><strong>Fit:</strong> <?php echo ucfirst($product['category']) === 'Hoodie' ? 'Relaxed fit' : 'Regular fit'; ?></li>
            <li><strong>Care:</strong> Cold machine wash, hang dry for best results</li>
            <li><strong>Style:</strong> <?php echo ucfirst($product['category']); ?></li>
            <li><strong>Origin:</strong> Ethically made</li>
        </ul>
    </div>

    <div class="reviews-section">
        <h2 class="reviews-title">Customer Reviews</h2>
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-name">John D.</div>
                <div class="stars">★★★★★</div>
            </div>
            <p>I absolutely love this product. The material is high quality and it fits perfectly. Would definitely buy again!</p>
        </div>
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-name">Sarah M.</div>
                <div class="stars">★★★★☆</div>
            </div>
            <p>The product is good quality and looks nice. However, it runs a bit small so consider ordering a size up.</p>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
        <div class="similar-products">
            <h2 class="similar-title">You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <img src="<?php echo $relatedProduct['image_path']; ?>" alt="<?php echo $relatedProduct['name']; ?>" />
                        </div>
                        <div class="product-card-info">
                            <div class="product-card-title"><?php echo $relatedProduct['name']; ?></div><br><br>
                            <div class="product-card-price">
                                <?php if ($relatedProduct['sale_price']): ?>
                                    RS: <?php echo $relatedProduct['sale_price']; ?> LKR
                                <?php else: ?>
                                    RS: <?php echo $relatedProduct['price']; ?> LKR
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn">View Product</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
    .product-section {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        margin: 50px 0;
    }

    .product-gallery {
        flex: 1;
        min-width: 300px;
    }

    .main-image {
        width: 100%;
        height: 500px;
        background-color: #f0f0f0;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        overflow: hidden;
    }

    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-container {
        display: flex;
        gap: 10px;
    }

    .thumbnail {
        width: 80px;
        height: 80px;
        background-color: #f0f0f0;
        cursor: pointer;
        border-radius: 4px;
        overflow: hidden;
    }

    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-info {
        flex: 1;
        min-width: 300px;
    }

    .product-title {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .product-price {
        font-size: 24px;
        font-weight: bold;
        color: #1D503A;
        margin-bottom: 20px;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .stars {
        color: gold;
    }

    .product-description {
        margin-bottom: 30px;
    }

    .options-container {
        margin-bottom: 30px;
    }

    .option-label {
        font-weight: bold;
        margin-bottom: 10px;
        display: block;
    }

    .size-options {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
    }

    .size-btn {
        border: 1px solid #ccc;
        padding: 8px 15px;
        cursor: pointer;
        transition: all 0.3s;
        color: black;
    }

    .size-btn.active {
        background-color: #333;
        color: white;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
    }

    .quantity-btn {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f0f0;
        border: none;
        cursor: pointer;
    }

    .quantity-input {
        width: 50px;
        height: 30px;
        text-align: center;
        border: 1px solid #ccc;
    }

    #addToCart {
        background-color: #e63946;
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.3s;
    }

    #addToCart:hover {
        background-color: #1D503A;
    }

    .product-features {
        margin-top: 50px;
    }

    .features-title,
    .details-title,
    .reviews-title,
    .similar-title {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
    }

    .feature-card {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .feature-icon {
        font-size: 24px;
        margin-bottom: 15px;
        color: #e63946;
    }

    .reviews-section {
        margin: 70px 0;
    }

    .review-card {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .reviewer-name {
        font-weight: bold;
    }

    .similar-products {
        margin: 70px 0;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
    }

    .product-card {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s;
    }

    .product-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .product-card-image {
        height: 200px;
        background-color: #f0f0f0;
        overflow: hidden;
    }

    .product-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-card-info {
        padding: 15px;
    }

    .product-card-title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .product-card-price {
        color: #1D503A;
        font-weight: bold;
    }

    .product-details ul {
        padding-left: 20px;
        line-height: 1.6;
    }

    .product-details li {
        margin-bottom: 10px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .discount {
        font-size: 14px;
        color: #777;
        margin-left: 5px;
    }

    .btn {
        display: inline-block;
        background-color: white;
        color: #1D503A;
        padding: 0.8rem 1.5rem;
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
    }
</style>

<script>
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

    // Thumbnail image selection
    document.addEventListener('DOMContentLoaded', function() {
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.querySelector('.main-image img');

        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').getAttribute('data-image');
                mainImage.src = imgSrc;

                // Update active class
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>