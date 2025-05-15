<?php
require_once 'includes/config.php';

// SQL statement to add the missing columns
$sql = "ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL";

if ($conn->query($sql)) {
    echo "Database updated successfully! The columns 'phone' and 'address' have been added.";
} else {
    echo "Error updating database: " . $conn->error;
}
