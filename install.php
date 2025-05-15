<?php

/**
 * OneFit Clothing Installation Script
 * This script will set up the database and import sample data
 */

// Check if the script is being run from the browser
if (php_sapi_name() !== 'cli') {
    echo '<html><body style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">';
    echo '<h1>OneFit Clothing Installation</h1>';
}

// Include config file
require_once 'includes/config.php';

echo "Starting installation process...\n";

// Step 1: Create database tables
echo "Step 1: Creating database tables...\n";
require_once 'includes/db_setup.php';

// Step 2: Create assets directory if it doesn't exist
echo "Step 2: Setting up asset directories...\n";

// Create directories if they don't exist
$directories = [
    'assets/images/products',
    'assets/images/categories',
    'assets/images/banners',
    'assets/images/users'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created directory: {$dir}\n";
        } else {
            echo "Failed to create directory: {$dir}\n";
        }
    } else {
        echo "Directory already exists: {$dir}\n";
    }
}

// Step 3: Copy product images to assets directory
echo "Step 3: Copying product images...\n";

// Get all PNG files in root directory
$imageFiles = glob('*.png');
$jpgFiles = glob('*.jpg');
$imageFiles = array_merge($imageFiles, $jpgFiles);

$copiedCount = 0;
foreach ($imageFiles as $file) {
    $destination = 'assets/images/products/' . $file;

    // Skip if file already exists in destination
    if (file_exists($destination)) {
        echo "File already exists: {$destination}\n";
        continue;
    }

    if (copy($file, $destination)) {
        $copiedCount++;
        echo "Copied: {$file} to {$destination}\n";
    } else {
        echo "Failed to copy: {$file}\n";
    }
}

echo "Copied {$copiedCount} image files.\n";

// Step 4: Insert sample products into database
echo "Step 4: Importing sample products...\n";

// Associate image filenames with product data
$products = [
    [
        'name' => 'Anime Girl Printed Oversized T-Shirt',
        'description' => 'Stylish anime-inspired t-shirt with oversized fit and high-quality print.',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 50,
        'category' => 'tshirt',
        'image_path' => 'assets/images/products/Anime Girl Printed Oversized T Shirt 01.png',
        'is_featured' => 1
    ],
    [
        'name' => 'Bloom Wild T-Shirt',
        'description' => 'Express your wild side with this beautiful floral print t-shirt.',
        'price' => 27.99,
        'sale_price' => NULL,
        'stock' => 35,
        'category' => 'tshirt',
        'image_path' => 'assets/images/products/Bloom Wild T Shirt 02.png',
        'is_featured' => 0
    ],
    [
        'name' => 'Happiness Navy Blue T-Shirt',
        'description' => 'Comfortable navy blue t-shirt with a positive message.',
        'price' => 25.99,
        'sale_price' => NULL,
        'stock' => 40,
        'category' => 'tshirt',
        'image_path' => 'assets/images/products/Happiness Navy Blue T Shirt 01.png',
        'is_featured' => 0
    ],
    [
        'name' => 'Inferno Edge Tee',
        'description' => 'Bold and fiery design for those who like to stand out.',
        'price' => 32.99,
        'sale_price' => 27.99,
        'stock' => 30,
        'category' => 'tshirt',
        'image_path' => 'assets/images/products/Inferno Edge Tee 01.png',
        'is_featured' => 1
    ],
    [
        'name' => 'Drop-Shoulder Mid-Sleeve T-Shirt',
        'description' => 'Modern drop-shoulder design with comfortable mid-length sleeves.',
        'price' => 29.99,
        'sale_price' => NULL,
        'stock' => 45,
        'category' => 'tshirt',
        'image_path' => 'assets/images/products/Drop-Shoulder Mid-Sleeve T-Shirt (1).png',
        'is_featured' => 0
    ],
    [
        'name' => 'Crop Top Hoodie',
        'description' => 'Trendy crop top hoodie perfect for casual outings.',
        'price' => 39.99,
        'sale_price' => 34.99,
        'stock' => 25,
        'category' => 'hoodie',
        'image_path' => 'assets/images/products/Crop Top Hoodie.png',
        'is_featured' => 1
    ],
    [
        'name' => 'Brown Based Tiny Polkadot Printed Hoodie',
        'description' => 'Stylish brown hoodie with a subtle polkadot pattern.',
        'price' => 45.99,
        'sale_price' => NULL,
        'stock' => 20,
        'category' => 'hoodie',
        'image_path' => 'assets/images/products/Brown Based Tiny Polkadot Printed Hoodie 02.png',
        'is_featured' => 0
    ],
    [
        'name' => 'OneFit Originals Hoodie',
        'description' => 'Our signature hoodie with premium comfort and style.',
        'price' => 49.99,
        'sale_price' => 42.99,
        'stock' => 30,
        'category' => 'hoodie',
        'image_path' => 'assets/images/products/OneFit Originals Hoodie 01.png',
        'is_featured' => 1
    ],
    [
        'name' => 'Gray Marl Cropped Hoodie',
        'description' => 'Modern gray cropped hoodie for a stylish casual look.',
        'price' => 42.99,
        'sale_price' => NULL,
        'stock' => 25,
        'category' => 'hoodie',
        'image_path' => 'assets/images/products/Gray Marl Cropped Hoodie.png',
        'is_featured' => 0
    ],
    [
        'name' => 'Mocha Cozy Hoodie',
        'description' => 'Ultra-soft mocha colored hoodie for maximum comfort.',
        'price' => 47.99,
        'sale_price' => 39.99,
        'stock' => 22,
        'category' => 'hoodie',
        'image_path' => 'assets/images/products/Mocha Cozy Hoodie 01.png',
        'is_featured' => 1
    ]
];

// Insert products into database
foreach ($products as $product) {
    // Check if product already exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
    $stmt->bind_param("s", $product['name']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Product already exists: {$product['name']}\n";
        continue;
    }

    // Insert new product
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, sale_price, stock, category, image_path, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssddissi", $product['name'], $product['description'], $product['price'], $product['sale_price'], $product['stock'], $product['category'], $product['image_path'], $product['is_featured']);

    if ($stmt->execute()) {
        echo "Added product: {$product['name']}\n";
    } else {
        echo "Failed to add product: {$product['name']} - " . $stmt->error . "\n";
    }
}

// Step 5: Create categories
echo "Step 5: Creating product categories...\n";

$categories = [
    ['name' => 'T-Shirts', 'description' => 'Stylish and comfortable t-shirts for all occasions.'],
    ['name' => 'Hoodies', 'description' => 'Stay warm and stylish with our premium hoodies.'],
    ['name' => 'New Arrivals', 'description' => 'Check out our latest products.'],
    ['name' => 'Sale Items', 'description' => 'Great products at discounted prices.']
];

foreach ($categories as $category) {
    // Check if category already exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $category['name']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Category already exists: {$category['name']}\n";
        continue;
    }

    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $category['name'], $category['description']);

    if ($stmt->execute()) {
        echo "Added category: {$category['name']}\n";
    } else {
        echo "Failed to add category: {$category['name']} - " . $stmt->error . "\n";
    }
}

// Installation complete message
echo "\nInstallation completed successfully!\n";
echo "You can now log in with the following credentials:\n";
echo "Username: admin\n";
echo "Password: admin123\n";

if (php_sapi_name() !== 'cli') {
    echo '<p>Installation completed successfully!</p>';
    echo '<p>You can now log in with the following credentials:</p>';
    echo '<p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>';
    echo '<p><a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #1D503A; color: white; text-decoration: none; border-radius: 5px;">Go to Home Page</a></p>';
    echo '</body></html>';
}
