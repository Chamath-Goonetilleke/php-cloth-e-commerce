<?php
require_once 'includes/config.php';

// Add missing columns to users table
$alterQueries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL"
];

$errors = [];
$success = true;

foreach ($alterQueries as $query) {
    if (!$conn->query($query)) {
        $errors[] = "Error executing query: " . $query . " - " . $conn->error;
        $success = false;
    }
}

// Check for the name/full_name column inconsistency
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
$hasNameColumn = $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
$hasFullNameColumn = $result->num_rows > 0;

// If we have name but not full_name, add full_name
if ($hasNameColumn && !$hasFullNameColumn) {
    if (!$conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) DEFAULT NULL")) {
        $errors[] = "Error adding full_name column: " . $conn->error;
        $success = false;
    }

    // Copy name values to full_name
    if (!$conn->query("UPDATE users SET full_name = name WHERE full_name IS NULL")) {
        $errors[] = "Error copying name to full_name: " . $conn->error;
        $success = false;
    }
}

// If we have full_name but not name, add name
if (!$hasNameColumn && $hasFullNameColumn) {
    if (!$conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(100) DEFAULT NULL")) {
        $errors[] = "Error adding name column: " . $conn->error;
        $success = false;
    }

    // Copy full_name values to name
    if (!$conn->query("UPDATE users SET name = full_name WHERE name IS NULL")) {
        $errors[] = "Error copying full_name to name: " . $conn->error;
        $success = false;
    }
}

// Output results
if ($success) {
    echo "✅ Database updated successfully! The following changes were made:<br>";
    echo "- Added 'phone' column to users table<br>";
    echo "- Added 'address' column to users table<br>";

    if ($hasNameColumn && !$hasFullNameColumn) {
        echo "- Added 'full_name' column and copied data from 'name'<br>";
    }

    if (!$hasNameColumn && $hasFullNameColumn) {
        echo "- Added 'name' column and copied data from 'full_name'<br>";
    }

    echo "<p>You can now return to <a href='myaccount.php'>your account page</a>.</p>";
} else {
    echo "❌ Error updating database:<br>";
    foreach ($errors as $error) {
        echo "- $error<br>";
    }
    echo "<p>Please contact the administrator with these error messages.</p>";
}
