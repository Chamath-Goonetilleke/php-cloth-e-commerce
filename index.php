<?php
require_once 'includes/config.php';

// Set page variables
$pageTitle = "OneFit Clothing - Trendy & Cozy Wear";
$showSaleBanner = true;

// Get featured products
$featuredProducts = [];
$sql = "SELECT * FROM products WHERE is_featured = 1 LIMIT 4";
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
    <div class="sale-banner">
        🔥 SUMMER SALE! Use code SUMMER25 for 25% off all items! Limited time offer! 🔥
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
</section>

<section class="stats">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">5000+</div>
            <div class="stat-text">Happy Customers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">300+</div>
            <div class="stat-text">Unique Designs</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">98%</div>
            <div class="stat-text">Satisfaction Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-text">Customer Support</div>
        </div>
    </div>
</section>

<!-- Customer Feedback Section -->
<div class="feedback">
    <div class="feedback-title">
        <h2 class="section-title">What Our Customers Say</h2>
    </div>
    <div class="testimonials">
        <div class="testimonial-card">
            <div class="testimonial-header">
                <img src="team1.jpg" alt="Customer 1" class="testimonial-image">
                <div>
                    <div class="testimonial-name">Ushana Bandara</div>
                    <div class="testimonial-date">March 15, 2025</div>
                </div>
            </div>
            <div class="testimonial-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <p class="testimonial-text">The quality of the printed t-shirts is amazing! The designs stay vibrant even after multiple washes. Definitely my go-to store for unique clothing!</p>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-header">
                <img src="team2.jpg" alt="Customer 2" class="testimonial-image">
                <div>
                    <div class="testimonial-name">Emily Rodriguez</div>
                    <div class="testimonial-date">April 01, 2025</div>
                </div>
            </div>
            <div class="testimonial-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <p class="testimonial-text">I love my new hoodie! The fabric is soft and comfortable, perfect for chilly evenings. The fit is exactly as described. Will definitely order more!</p>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-header">
                <img src="team3.jpg" alt="Customer 3" class="testimonial-image">
                <div>
                    <div class="testimonial-name">Jessica Kim</div>
                    <div class="testimonial-date">May 15, 2024</div>
                </div>
            </div>
            <div class="testimonial-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <p class="testimonial-text">Great quality and amazing design! Totally worth the price.</p>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-header">
                <img src="team4.jpg" alt="Customer 3" class="testimonial-image">
                <div>
                    <div class="testimonial-name">KPM Perera</div>
                    <div class="testimonial-date">September 09, 2024</div>
                </div>
            </div>
            <div class="testimonial-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <p class="testimonial-text">OneFit has the best quality printed t-shirts! The fabric feels premium, and the designs are vibrant even after multiple washes. Highly recommended!</p>
        </div>
    </div>
</div>

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