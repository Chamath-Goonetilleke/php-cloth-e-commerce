<?php
require_once 'includes/config.php';

// Set page variables
$pageTitle = "Database Update - OneFit Clothing";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Database Management</h1>

    <div class="card-container">
        <div class="db-card">
            <h2>Update Order Tables</h2>
            <p>Use this option to update the orders and order_items tables with the latest column structure needed for checkout.</p>
            <a href="update_order_tables.php" class="btn">Update Order Tables</a>
        </div>

        <div class="db-card">
            <h2>Update User Table</h2>
            <p>Use this option to update the users table to add missing columns like phone and address.</p>
            <a href="database_fix.php" class="btn">Update User Table</a>
        </div>
    </div>

    <div class="back-link">
        <a href="index.php">&larr; Back to Homepage</a>
    </div>
</div>

<style>
    .container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .page-title {
        font-size: 32px;
        margin-bottom: 30px;
        color: #1D503A;
        text-align: center;
    }

    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .db-card {
        flex: 1 1 300px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        padding: 25px;
    }

    .db-card h2 {
        color: #1D503A;
        margin-top: 0;
        margin-bottom: 15px;
    }

    .db-card p {
        margin-bottom: 20px;
        color: #555;
    }

    .btn {
        display: inline-block;
        background-color: #1D503A;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #163c2c;
    }

    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    .back-link a {
        color: #1D503A;
        text-decoration: none;
        font-weight: 500;
    }

    .back-link a:hover {
        text-decoration: underline;
    }

    @media (max-width: 600px) {
        .card-container {
            flex-direction: column;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>