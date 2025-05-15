<?php

/**
 * OneFit Clothing - Database Migration Script
 * This script updates the orders and order_items tables to add missing columns
 */

require_once 'includes/config.php';

echo "<h1>Updating Order Tables</h1>";
echo "<pre>";

echo "Beginning database migration...\n";

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if orders table exists
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    $orderTableExists = $result && $result->num_rows > 0;

    if (!$orderTableExists) {
        echo "Orders table doesn't exist yet. Creating new table...\n";

        // Create orders table from scratch
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) NOT NULL,
            user_id INT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(50),
            address TEXT NOT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100),
            zip_code VARCHAR(20) NOT NULL,
            country VARCHAR(50) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            shipping DECIMAL(10,2) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            shipping_address TEXT,
            billing_address TEXT,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($conn->query($sql)) {
            echo "Successfully created orders table.\n";
        } else {
            throw new Exception("Error creating orders table: " . $conn->error);
        }
    } else {
        echo "Orders table exists. Checking for missing columns...\n";

        // Get existing columns
        $result = $conn->query("SHOW COLUMNS FROM orders");
        $existingColumns = [];

        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }

        // Define required columns with their definitions
        $requiredColumns = [
            'order_number' => 'VARCHAR(50) NOT NULL',
            'first_name' => 'VARCHAR(100) NOT NULL',
            'last_name' => 'VARCHAR(100) NOT NULL',
            'email' => 'VARCHAR(100) NOT NULL',
            'phone' => 'VARCHAR(50)',
            'address' => 'TEXT NOT NULL',
            'city' => 'VARCHAR(100) NOT NULL',
            'state' => 'VARCHAR(100)',
            'zip_code' => 'VARCHAR(20) NOT NULL',
            'country' => 'VARCHAR(50) NOT NULL',
            'subtotal' => 'DECIMAL(10,2) NOT NULL',
            'shipping' => 'DECIMAL(10,2) NOT NULL',
            'payment_method' => 'VARCHAR(50) NOT NULL',
            'shipping_address' => 'TEXT',
            'billing_address' => 'TEXT'
        ];

        // Check and add missing columns
        foreach ($requiredColumns as $column => $definition) {
            if (!in_array($column, $existingColumns)) {
                $sql = "ALTER TABLE orders ADD COLUMN $column $definition";

                if ($conn->query($sql)) {
                    echo "Added missing column '$column' to orders table.\n";
                } else {
                    throw new Exception("Error adding column '$column': " . $conn->error);
                }
            }
        }

        // Update user_id to allow NULL for guest checkouts
        if (in_array('user_id', $existingColumns)) {
            $result = $conn->query("SHOW CREATE TABLE orders");
            $tableDefinition = $result->fetch_assoc()['Create Table'];

            // Check if user_id is NOT NULL
            if (strpos($tableDefinition, 'user_id` int(11) NOT NULL') !== false) {
                $sql = "ALTER TABLE orders MODIFY COLUMN user_id INT NULL";

                if ($conn->query($sql)) {
                    echo "Modified user_id column to allow NULL values.\n";
                } else {
                    throw new Exception("Error modifying user_id column: " . $conn->error);
                }
            }
        }

        // Check and update the foreign key constraint
        $result = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                               WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                               AND TABLE_NAME = 'orders' 
                               AND COLUMN_NAME = 'user_id' 
                               AND REFERENCED_TABLE_NAME = 'users'");

        if ($result && $result->num_rows > 0) {
            $constraintRow = $result->fetch_assoc();
            $constraintName = $constraintRow['CONSTRAINT_NAME'];

            // Drop the existing foreign key
            $sql = "ALTER TABLE orders DROP FOREIGN KEY " . $constraintName;
            if ($conn->query($sql)) {
                echo "Dropped existing foreign key constraint on user_id.\n";

                // Modify the user_id column to allow NULL values
                $sql = "ALTER TABLE orders MODIFY COLUMN user_id INT NULL";
                if ($conn->query($sql)) {
                    echo "Modified user_id column to allow NULL values.\n";

                    // Add the updated foreign key with ON DELETE SET NULL
                    $sql = "ALTER TABLE orders ADD CONSTRAINT " . $constraintName . " 
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";

                    if ($conn->query($sql)) {
                        echo "Added updated foreign key constraint with ON DELETE SET NULL.\n";
                    } else {
                        throw new Exception("Error adding updated foreign key constraint: " . $conn->error);
                    }
                } else {
                    throw new Exception("Error modifying user_id column: " . $conn->error);
                }
            } else {
                throw new Exception("Error dropping foreign key constraint: " . $conn->error);
            }
        }
    }

    // Check if order_items table exists
    $result = $conn->query("SHOW TABLES LIKE 'order_items'");
    $orderItemsTableExists = $result && $result->num_rows > 0;

    if (!$orderItemsTableExists) {
        echo "Order items table doesn't exist yet. Creating new table...\n";

        // Create order_items table from scratch
        $sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NULL,
            name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            size VARCHAR(10),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($conn->query($sql)) {
            echo "Successfully created order_items table.\n";
        } else {
            throw new Exception("Error creating order_items table: " . $conn->error);
        }
    } else {
        echo "Order items table exists. Checking for missing columns...\n";

        // Get existing columns
        $result = $conn->query("SHOW COLUMNS FROM order_items");
        $existingColumns = [];

        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }

        // Check for missing 'name' column
        if (!in_array('name', $existingColumns)) {
            $sql = "ALTER TABLE order_items ADD COLUMN name VARCHAR(255) NOT NULL";

            if ($conn->query($sql)) {
                echo "Added missing column 'name' to order_items table.\n";
            } else {
                throw new Exception("Error adding column 'name': " . $conn->error);
            }
        }

        // Get foreign key constraints for product_id column
        $result = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                               WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                               AND TABLE_NAME = 'order_items' 
                               AND COLUMN_NAME = 'product_id' 
                               AND REFERENCED_TABLE_NAME = 'products'");

        if ($result && $result->num_rows > 0) {
            $constraintRow = $result->fetch_assoc();
            $constraintName = $constraintRow['CONSTRAINT_NAME'];

            try {
                // Try to drop the foreign key constraint
                $sql = "ALTER TABLE order_items DROP FOREIGN KEY " . $constraintName;
                if ($conn->query($sql)) {
                    echo "Dropped existing foreign key constraint on product_id.\n";
                }
            } catch (Exception $e) {
                echo "Could not drop foreign key, continuing: " . $e->getMessage() . "\n";
            }

            try {
                // Try to modify the column to allow NULL values
                $sql = "ALTER TABLE order_items MODIFY COLUMN product_id INT NULL";
                if ($conn->query($sql)) {
                    echo "Modified product_id column to allow NULL values.\n";
                }
            } catch (Exception $e) {
                echo "Could not modify product_id column, continuing: " . $e->getMessage() . "\n";
            }

            try {
                // Re-add the foreign key with ON DELETE SET NULL
                $sql = "ALTER TABLE order_items ADD CONSTRAINT FK_order_items_product_id
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL";

                if ($conn->query($sql)) {
                    echo "Added updated foreign key constraint with ON DELETE SET NULL.\n";
                }
            } catch (Exception $e) {
                echo "Could not add new foreign key constraint, continuing: " . $e->getMessage() . "\n";
            }
        }
    }

    // Commit transaction
    $conn->commit();
    echo "Database migration completed successfully!\n";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='index.php'>Return to Homepage</a></p>";
