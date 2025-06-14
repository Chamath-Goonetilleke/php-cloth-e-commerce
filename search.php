<?php
require_once 'includes/config.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($query !== '') {
    $like = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$pageTitle = 'Search Results';
include 'includes/header.php';
?>

<section class="hero">
    <h1>Search Results</h1>
    <p>Showing results for: <strong><?php echo htmlspecialchars($query); ?></strong></p>
</section>

<div>
    <section class="product-container">
        <br>
        <?php if ($query === ''): ?>
            <div class="no-products">
                <p>Please enter a search term above.</p>
            </div>
        <?php elseif (empty($products)): ?>
            <div class="no-products">
                <p>No products found matching your search.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <p class="price">LKR <?php echo $product['sale_price'] ? $product['sale_price'] : $product['price']; ?> LKR
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

<?php include 'includes/footer.php'; ?>

<style>
    .hero {
        color: black;
        padding: 3rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
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

    .product-name {
        font-size: 15px;
        margin: 10px 0;
        word-break: break-word;
        white-space: normal;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        text-overflow: ellipsis;
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