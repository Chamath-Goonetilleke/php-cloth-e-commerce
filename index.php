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

// Before rendering products, get wishlist product IDs for logged-in user
$wishlistProductIds = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $wishlistProductIds[] = $row['product_id'];
    }
}

// Fetch latest 4 reviews for homepage feedback section
$latestReviews = [];
$sql = "SELECT r.*, u.full_name, p.name AS product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC LIMIT 4";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $latestReviews[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Custom Wishlist Styles -->
<style>
    .product-actions {
        position: absolute;
        bottom: 0px;
        right: 10px;
        display: flex;
        flex-direction: row;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-actions {
        opacity: 0.9;
    }

    .product-action {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        font-size: 16px;
    }

    .product-action:hover {
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .wishlist-btn {
        position: relative;
    }

    .wishlist-btn i {
        font-size: 18px !important;
        transition: all 0.3s ease;
    }

    .wishlist-btn.active i {
        color: #e63946 !important;
        transform: scale(1.1);
    }

    .wishlist-btn:not(.active) i {
        color: #666 !important;
    }

    .wishlist-btn:hover i {
        transform: scale(1.2);
    }

    /* Fallback for when Font Awesome doesn't load */
    .wishlist-btn .heart-icon {
        width: 18px;
        height: 18px;
        position: relative;
        display: inline-block;
    }

    .wishlist-btn .heart-icon::before,
    .wishlist-btn .heart-icon::after {
        content: '';
        width: 10px;
        height: 16px;
        position: absolute;
        left: 4px;
        transform: rotate(-45deg);
        background: currentColor;
        border-radius: 10px 10px 0 0;
        transform-origin: 0 100%;
    }

    .wishlist-btn .heart-icon::after {
        left: 0;
        transform: rotate(45deg);
        transform-origin: 100% 100%;
    }

    /* Enhanced product card styling */
    .product-card {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-image {
        position: relative;
        overflow: hidden;
    }

    /* Always show wishlist on mobile */
    @media (max-width: 768px) {
        .product-actions {
            opacity: 1;
        }

        .product-action {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
    }
</style>

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
                    <a style="color: white;" href="tshirts.php">View Collection</a>
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
                    <a href="hoodies.php" style="color: white;">View Collection</a>
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
            <?php
            // Fetch average rating and review count for this product
            $avgRating = 0;
            $reviewCount = 0;
            $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?");
            $stmt->bind_param("i", $product['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $avgRating = $row['avg_rating'] !== null ? round($row['avg_rating'], 1) : 0.0;
                $reviewCount = (int)$row['review_count'];
            }
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-tag">Sale</span>
                    <?php endif; ?>
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-action" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="product-action add-to-cart" data-id="<?php echo $product['id']; ?>" title="Add to Cart">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                        <a href="#" class="product-action wishlist-btn <?php echo in_array($product['id'], $wishlistProductIds) ? 'active' : ''; ?>"
                            data-id="<?php echo $product['id']; ?>"
                            title="<?php echo in_array($product['id'], $wishlistProductIds) ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                            <?php if (in_array($product['id'], $wishlistProductIds)): ?>
                                <i class="fas fa-heart"></i>
                            <?php else: ?>
                                <i class="far fa-heart"></i>
                                <!-- Fallback for when Font Awesome doesn't load -->
                                <span class="heart-icon" style="display: none;"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="product-info">
                    <div style="height: 50px;">
                        <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    </div>
                    <div class="product-rating">
                        <?php
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i>';
                        if ($halfStar) echo '<i class="fas fa-star-half-alt"></i>';
                        for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '<i class="far fa-star"></i>';
                        ?>
                        <span style="margin-left:8px;font-size:13px;color:#888;">(<?php echo $reviewCount; ?>)</span>
                    </div>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="current-price">LKR <?php echo $product['sale_price']; ?></span>
                            <span class="original-price">LKR <?php echo $product['price']; ?></span>
                        <?php else: ?>
                            <span class="current-price">LKR <?php echo $product['price']; ?></span>
                        <?php endif; ?>
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
            <?php
            // Fetch average rating and review count for this product
            $avgRating = 0;
            $reviewCount = 0;
            $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?");
            $stmt->bind_param("i", $product['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $avgRating = $row['avg_rating'] !== null ? round($row['avg_rating'], 1) : 0.0;
                $reviewCount = (int)$row['review_count'];
            }
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-tag">Sale</span>
                    <?php endif; ?>
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-action" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="product-action add-to-cart" data-id="<?php echo $product['id']; ?>" title="Add to Cart">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                        <a href="#" class="product-action wishlist-btn <?php echo in_array($product['id'], $wishlistProductIds) ? 'active' : ''; ?>"
                            data-id="<?php echo $product['id']; ?>"
                            title="<?php echo in_array($product['id'], $wishlistProductIds) ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                            <?php if (in_array($product['id'], $wishlistProductIds)): ?>
                                <i class="fas fa-heart"></i>
                            <?php else: ?>
                                <i class="far fa-heart"></i>
                                <!-- Fallback for when Font Awesome doesn't load -->
                                <span class="heart-icon" style="display: none;"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="product-info">
                    <div style="height: 50px;">
                        <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    </div>
                    <div class="product-rating">
                        <?php
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i>';
                        if ($halfStar) echo '<i class="fas fa-star-half-alt"></i>';
                        for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '<i class="far fa-star"></i>';
                        ?>
                        <span style="margin-left:8px;font-size:13px;color:#888;">(<?php echo $reviewCount; ?>)</span>
                    </div>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="current-price">LKR <?php echo $product['sale_price']; ?></span>
                            <span class="original-price">LKR <?php echo $product['price']; ?></span>
                        <?php else: ?>
                            <span class="current-price">LKR <?php echo $product['price']; ?></span>
                        <?php endif; ?>
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
        <?php if (empty($latestReviews)): ?>
            <div class="testimonial-card">
                <p>No feedback yet. Be the first to review a product!</p>
            </div>
        <?php else: ?>
            <?php foreach ($latestReviews as $review): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div>
                            <div class="testimonial-name"><?php echo htmlspecialchars($review['full_name']); ?></div>
                            <div class="testimonial-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <?php
                        for ($i = 0; $i < $review['rating']; $i++) echo '<i class="fas fa-star"></i>';
                        for ($i = $review['rating']; $i < 5; $i++) echo '<i class="far fa-star"></i>';
                        ?>
                        <span style="margin-left:10px;font-size:13px;color:#888;">on <?php echo htmlspecialchars($review['product_name']); ?></span>
                    </div>
                    <p class="testimonial-text"><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Check if Font Awesome is loaded, if not show fallback hearts
    document.addEventListener('DOMContentLoaded', function() {
        // Test if Font Awesome is loaded
        const testIcon = document.createElement('i');
        testIcon.className = 'far fa-heart';
        testIcon.style.position = 'absolute';
        testIcon.style.left = '-9999px';
        document.body.appendChild(testIcon);

        const computedStyle = window.getComputedStyle(testIcon, ':before');
        const isFontAwesome = computedStyle.content !== 'none' && computedStyle.content !== '';

        document.body.removeChild(testIcon);

        // If Font Awesome is not loaded, show fallback hearts
        if (!isFontAwesome) {
            document.querySelectorAll('.wishlist-btn i').forEach(function(icon) {
                icon.style.display = 'none';
            });
            document.querySelectorAll('.wishlist-btn .heart-icon').forEach(function(heart) {
                heart.style.display = 'inline-block';
            });
        }

        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                <?php if (!isset($_SESSION['user_id'])): ?>
                    alert('Please login to add items to wishlist');
                    return;
                <?php endif; ?>

                const productId = btn.getAttribute('data-id');
                const isActive = btn.classList.contains('active');
                const action = isActive ? 'remove' : 'add';

                // Show loading state
                btn.style.opacity = '0.6';
                btn.style.pointerEvents = 'none';

                fetch('wishlist-update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `product_id=${productId}&action=${action}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.style.opacity = '1';
                        btn.style.pointerEvents = 'auto';

                        if (data.success) {
                            const icon = btn.querySelector('i');

                            if (action === 'add') {
                                btn.classList.add('active');
                                btn.title = 'Remove from Wishlist';
                                if (icon) {
                                    icon.className = 'fas fa-heart';
                                }
                            } else {
                                btn.classList.remove('active');
                                btn.title = 'Add to Wishlist';
                                if (icon) {
                                    icon.className = 'far fa-heart';
                                }
                            }

                            // Show success feedback
                            const originalTitle = btn.title;
                            btn.title = action === 'add' ? 'Added to wishlist!' : 'Removed from wishlist!';
                            setTimeout(() => {
                                btn.title = originalTitle;
                            }, 2000);

                        } else {
                            alert(data.message || 'Something went wrong. Please try again.');
                        }
                    })
                    .catch(error => {
                        btn.style.opacity = '1';
                        btn.style.pointerEvents = 'auto';
                        console.error('Error:', error);
                        alert('Something went wrong. Please try again.');
                    });
            });
        });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>

<!-- Add to Cart Modal -->
<div id="addToCartModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:30px 25px;border-radius:10px;max-width:350px;width:90vw;position:relative;">
        <button id="closeAddToCartModal" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        <h3 id="addToCartProductName">Add to Cart</h3>
        <form id="addToCartForm">
            <input type="hidden" name="product_id" id="addToCartProductId">
            <div style="margin:15px 0;">
                <label>Size:</label>
                <div id="addToCartSizes" style="display:flex;gap:10px;margin-top:8px;"></div>
                <input type="hidden" name="size" id="addToCartSize" required>
            </div>
            <div style="margin-bottom:15px;">
                <label>Quantity:</label>
                <input type="number" name="quantity" id="addToCartQuantity" value="1" min="1" style="width:60px;border-radius:5px;padding:5px 10px;">
            </div>
            <button type="submit" class="btn" style="width:100%;background:#1D503A;color:#fff;">Add to Cart</button>
        </form>
        <div id="addToCartFeedback" style="display:none;color:#28a745;font-weight:600;text-align:center;margin-top:15px;">Added to cart!</div>
    </div>
</div>

<script>
    // Add to Cart Modal Logic
    let addToCartModal = document.getElementById('addToCartModal');
    let closeAddToCartModal = document.getElementById('closeAddToCartModal');
    let addToCartForm = document.getElementById('addToCartForm');
    let addToCartSizes = document.getElementById('addToCartSizes');
    let addToCartSize = document.getElementById('addToCartSize');
    let addToCartProductId = document.getElementById('addToCartProductId');
    let addToCartProductName = document.getElementById('addToCartProductName');
    let addToCartQuantity = document.getElementById('addToCartQuantity');
    let addToCartFeedback = document.getElementById('addToCartFeedback');

    // Sizes available (customize if needed)
    const availableSizes = ['S', 'M', 'L', 'XL'];

    // Open modal on add-to-cart click
    Array.from(document.querySelectorAll('.add-to-cart')).forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = btn.getAttribute('data-id');
            const productCard = btn.closest('.product-card');
            const productName = productCard ? productCard.querySelector('.product-name').textContent : '';
            addToCartProductId.value = productId;
            addToCartProductName.textContent = 'Add to Cart: ' + productName;
            addToCartQuantity.value = 1;
            addToCartSize.value = '';
            addToCartFeedback.style.display = 'none';
            addToCartForm.style.display = 'block';
            // Render size buttons
            addToCartSizes.innerHTML = '';
            availableSizes.forEach(function(size) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = size;
                btn.className = 'btn';
                btn.style.margin = '0';
                btn.style.background = '#f0f0f0';
                btn.style.color = '#333';
                btn.style.border = '1px solid #ccc';
                btn.style.borderRadius = '5px';
                btn.style.padding = '6px 14px';
                btn.style.fontWeight = '600';
                btn.onclick = function() {
                    addToCartSize.value = size;
                    Array.from(addToCartSizes.children).forEach(b => b.style.background = '#f0f0f0');
                    btn.style.background = '#1D503A';
                    btn.style.color = '#fff';
                };
                addToCartSizes.appendChild(btn);
            });
            addToCartModal.style.display = 'flex';
        });
    });

    closeAddToCartModal.onclick = function() {
        addToCartModal.style.display = 'none';
    };

    // Handle form submit
    addToCartForm.onsubmit = function(e) {
        e.preventDefault();
        const productId = addToCartProductId.value;
        const size = addToCartSize.value;
        const quantity = addToCartQuantity.value;
        if (!size) {
            alert('Please select a size.');
            return;
        }
        // AJAX request
        fetch('cart-update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}&size=${encodeURIComponent(size)}&quantity=${encodeURIComponent(quantity)}&action=add`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addToCartForm.style.display = 'none';
                    addToCartFeedback.textContent = data.message || 'Added to cart!';
                    addToCartFeedback.style.display = 'block';
                    setTimeout(() => {
                        addToCartModal.style.display = 'none';
                        window.location.reload();
                    }, 1200);
                } else {
                    alert(data.message || 'Could not add to cart.');
                }
            })
            .catch(error => {
                alert('Error adding to cart.');
                console.error(error);
            });
    };
</script>