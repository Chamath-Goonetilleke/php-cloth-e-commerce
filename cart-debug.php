<?php
require_once 'includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Page title
$pageTitle = "Cart Debug";

// Include header (optional)
// include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1,
        h2 {
            color: #333;
        }

        .card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .action-buttons {
            margin: 20px 0;
        }

        .action-buttons a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background: #1D503A;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .action-buttons a.danger {
            background: #e63946;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cart Debug Information</h1>

        <div class="action-buttons">
            <a href="cart.php">Go to Cart</a>
            <a href="test-cart.php">Go to Test Cart</a>
            <a href="reset-cart.php" class="danger">Reset Cart</a>
        </div>

        <div class="card">
            <h2>Session ID</h2>
            <pre><?php echo session_id(); ?></pre>
        </div>

        <div class="card">
            <h2>Cart Contents</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Cart is empty</p>
            <?php else: ?>
                <pre><?php print_r($_SESSION['cart']); ?></pre>

                <h3>Cart Summary</h3>
                <ul>
                    <li>Total Items: <?php
                                        $totalItems = 0;
                                        foreach ($_SESSION['cart'] as $item) {
                                            $totalItems += $item['quantity'];
                                        }
                                        echo $totalItems;
                                        ?></li>
                    <li>Total Products: <?php echo count($_SESSION['cart']); ?></li>
                    <li>Total Value: <?php
                                        $totalValue = 0;
                                        foreach ($_SESSION['cart'] as $item) {
                                            $totalValue += $item['price'] * $item['quantity'];
                                        }
                                        echo 'RS: ' . number_format($totalValue, 2) . ' LKR';
                                        ?></li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Complete Session Data</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
    </div>
</body>

</html>