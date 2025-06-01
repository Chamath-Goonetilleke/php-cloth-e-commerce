<?php
require_once 'includes/config.php';

// Set page variables
$pageTitle = "OneFit Clothing - Hoodies";
$showSaleBanner = false;

// Get all hoodies
$products = [];
$sql = "SELECT * FROM products WHERE category = 'hoodie'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<section class="hero">
    <h1>Hoodies Collection</h1>
    <p>Discover our premium quality hoodies that offer both comfort and style. Made from sustainable materials, our hoodies are perfect for any occasion.</p>
    <a href="hoodies.php" class="btn">Shop Now</a><br>
</section>

<div>
    <section class="product-container">
        <br>
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>No products found in this category.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
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
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3><br><br>
                    <div class="stars">
                        <?php
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        for ($i = 0; $i < $fullStars; $i++) echo '★';
                        if ($halfStar) echo '½';
                        for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '☆';
                        ?>
                        <span style="margin-left:8px;font-size:13px;color:#888;">(<?php echo $reviewCount; ?>)</span>
                    </div>
                    <p class="price">RS: <?php echo $product['price']; ?> LKR
                        <?php if ($product['sale_price']): ?>
                            <span class="discount">-<?php
                                                    $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                                                    echo $discount;
                                                    ?>%</span>
                        <?php endif; ?>
                    </p>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Product</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <br><br><br>
    </section>
</div><br><br>

<?php
// Include footer
include 'includes/footer.php';
?>

<!-- Add this CSS to match the hoodies.html styling -->
<style>
    .hero {
        background-color: #F9E4DA;
        color: white;
        padding: 3rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
        background-image: linear-gradient(45deg, #3a0ca3, #F9E4DA);
        height: auto;
    }

    .hero h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .hero p {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        max-width: 600px;
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

    .product-container {
        display: flex;
        gap: 60px;
        overflow-x: auto;
        padding: 40px;
        white-space: nowrap;
    }

    .product-card {
        width: 200px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease-in-out;
    }

    .product-card:hover {
        transform: scale(1.05);
    }

    .product-card img {
        width: 100%;
        height: auto;
        border-radius: 5px;
    }

    .product-card h3 {
        font-size: 15px;
        margin: 10px 0;
    }

    .price {
        font-size: 18px;
        color: #1D503A;
        font-weight: bold;
    }

    .discount {
        font-size: 14px;
        color: #777;
        margin-left: 5px;
    }

    .stars {
        color: gold;
        font-size: 18px;
        margin: 5px 0;
    }

    .product-card .btn {
        display: inline-block;
        background-color: white;
        color: rgb(0, 0, 0);
        padding: 0.8rem 1.5rem;
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }

    .product-card .btn:hover {
        background-color: lightgray;
        transform: translateY(-2px);
    }
</style>