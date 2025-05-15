<?php
// Set page title
$pageTitle = "Database Migration";

// Check if we're submitting the form
if (isset($_POST['run_migration'])) {
    require_once 'includes/config.php';

    // Step 1: Check which columns exist
    $columnsToAdd = [];

    // Check for phone column
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($result->num_rows == 0) {
        $columnsToAdd[] = "phone";
    }

    // Check for address column
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
    if ($result->num_rows == 0) {
        $columnsToAdd[] = "address";
    }

    // Step 2: Add missing columns
    $messages = [];
    $errors = [];
    $success = true;

    if (!empty($columnsToAdd)) {
        $alterSql = "ALTER TABLE users ";
        $alterParts = [];

        foreach ($columnsToAdd as $column) {
            if ($column == 'phone') {
                $alterParts[] = "ADD COLUMN phone VARCHAR(20) DEFAULT NULL";
            } elseif ($column == 'address') {
                $alterParts[] = "ADD COLUMN address TEXT DEFAULT NULL";
            }
        }

        $alterSql .= implode(", ", $alterParts);

        if ($conn->query($alterSql)) {
            $messages[] = "Successfully added columns: " . implode(", ", $columnsToAdd);
        } else {
            $errors[] = "Error adding columns: " . $conn->error;
            $success = false;
        }
    } else {
        $messages[] = "All required columns already exist.";
    }

    // Step 3: Check for name/full_name inconsistency
    $nameResult = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
    $hasNameColumn = $nameResult->num_rows > 0;

    $fullNameResult = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
    $hasFullNameColumn = $fullNameResult->num_rows > 0;

    // If only one exists, create the other and sync data
    if ($hasNameColumn && !$hasFullNameColumn) {
        if ($conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) DEFAULT NULL")) {
            $messages[] = "Added 'full_name' column.";

            if ($conn->query("UPDATE users SET full_name = name WHERE name IS NOT NULL")) {
                $messages[] = "Synchronized data from 'name' to 'full_name'.";
            } else {
                $errors[] = "Error syncing data: " . $conn->error;
                $success = false;
            }
        } else {
            $errors[] = "Error adding 'full_name' column: " . $conn->error;
            $success = false;
        }
    } elseif (!$hasNameColumn && $hasFullNameColumn) {
        if ($conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(100) DEFAULT NULL")) {
            $messages[] = "Added 'name' column.";

            if ($conn->query("UPDATE users SET name = full_name WHERE full_name IS NOT NULL")) {
                $messages[] = "Synchronized data from 'full_name' to 'name'.";
            } else {
                $errors[] = "Error syncing data: " . $conn->error;
                $success = false;
            }
        } else {
            $errors[] = "Error adding 'name' column: " . $conn->error;
            $success = false;
        }
    } else {
        $messages[] = "Name columns are properly set up.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #1D503A;
            border-bottom: 2px solid #1D503A;
            padding-bottom: 10px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        button {
            background-color: #1D503A;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #163c2c;
        }

        ul {
            padding-left: 20px;
        }

        .links {
            margin-top: 30px;
        }

        .links a {
            color: #1D503A;
            text-decoration: none;
            margin-right: 15px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>OneFit Clothing - Database Maintenance</h1>

    <div class="card">
        <h2>Database Migration</h2>

        <?php if (isset($_POST['run_migration'])): ?>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <h3>Errors Occurred</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($messages)): ?>
                <div class="success">
                    <h3>Migration Results</h3>
                    <ul>
                        <?php foreach ($messages as $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <p>The database schema has been updated successfully. You can now continue using the website.</p>
                <div class="links">
                    <a href="index.php">Return to Home Page</a>
                    <a href="myaccount.php">Go to My Account</a>
                </div>
            <?php else: ?>
                <p>There were errors during the migration. Please contact the administrator or try again.</p>
                <form method="POST">
                    <button type="submit" name="run_migration">Try Again</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <div class="warning">
                <p><strong>Warning:</strong> This page will modify your database schema to fix issues with the user profile system.
                    Please make sure you have a backup of your database before proceeding.</p>
            </div>

            <p>This migration will:</p>
            <ul>
                <li>Add missing 'phone' column to users table</li>
                <li>Add missing 'address' column to users table</li>
                <li>Fix any inconsistencies between 'name' and 'full_name' columns</li>
            </ul>

            <form method="POST">
                <button type="submit" name="run_migration">Run Migration</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>