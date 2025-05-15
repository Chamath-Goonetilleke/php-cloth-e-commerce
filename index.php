<?php
require_once 'includes/config.php';

// Set page variables
$pageTitle = "OneFit Clothing - Trendy & Cozy Wear";
$showSaleBanner = true;

// Get featured products
$featuredProducts = [];
$sql = "SELECT * FROM products WHERE is_featured = 1 LIMIT 6";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
}

// Get new arrivals
$newArrivals = [];
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $newArrivals[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Fashion That Feels Like You</h1>
        <p>Discover clothing that combines style, comfort, and quality craftsmanship, designed for the modern lifestyle.</p>
        <a href="tshirts.php" class="btn">Shop Collection</a>
    </div>
</section>

<!-- Categories -->
<section class="categories">
    <h2 class="section-title">Shop By Category</h2>
    <div class="category-container">
        <div class="product-card">
            <div class="product-image">
                <img src="assets/images/products/Anime Girl Printed Oversized T Shirt 01.png" alt="T-Shirts">
                <div class="product-actions">
                    <a href="tshirts.php" class="product-action">View Collection</a>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">T-Shirts</h3>
                <p>Stylish designs for every occasion</p>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <img src="assets/images/products/OneFit Originals Hoodie 01.png" alt="Hoodies">
                <div class="product-actions">
                    <a href="hoodies.php" class="product-action">View Collection</a>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">Hoodies</h3>
                <p>Stay cozy with our premium hoodies</p>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <img src="assets/images/products/Inferno Edge Tee 01.png" alt="New Arrivals">
                <div class="product-actions">
                    <a href="new-arrivals.php" class="product-action">View Collection</a>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">New Arrivals</h3>
                <p>Check out our latest products</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="products">
    <h2 class="section-title">Featured Products</h2>
    <div class="product-container">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-tag">Sale</span>
                    <?php endif; ?>
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-action"><i class="fas fa-eye"></i></a>
                        <a href="#" class="product-action add-to-cart" data-id="<?php echo $product['id']; ?>"><i class="fas fa-shopping-cart"></i></a>
                        <a href="#" class="product-action"><i class="fas fa-heart"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="current-price">$<?php echo $product['sale_price']; ?></span>
                            <span class="original-price">$<?php echo $product['price']; ?></span>
                        <?php else: ?>
                            <span class="current-price">$<?php echo $product['price']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- New Arrivals -->
<section class="products">
    <h2 class="section-title">New Arrivals</h2>
    <div class="product-container">
        <?php foreach ($newArrivals as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-tag">Sale</span>
                    <?php endif; ?>
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-action"><i class="fas fa-eye"></i></a>
                        <a href="#" class="product-action add-to-cart" data-id="<?php echo $product['id']; ?>"><i class="fas fa-shopping-cart"></i></a>
                        <a href="#" class="product-action"><i class="fas fa-heart"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="current-price">$<?php echo $product['sale_price']; ?></span>
                            <span class="original-price">$<?php echo $product['price']; ?></span>
                        <?php else: ?>
                            <span class="current-price">$<?php echo $product['price']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top: 30px;">
        <a href="all-products.php" class="btn">View All Products</a>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter">
    <h2 class="newsletter-title">Subscribe to Our Newsletter</h2>
    <p class="newsletter-text">Stay updated with our latest products, exclusive offers, and fashion tips.</p>
    <form class="newsletter-form" action="subscribe.php" method="POST">
        <input type="email" name="email" placeholder="Your Email Address" required class="newsletter-input">
        <button type="submit" class="newsletter-btn">Subscribe</button>
    </form>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>