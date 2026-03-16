<?php
/**
 * Database Migration Script
 * Run this once by visiting: yourdomain.com/fix_db.php
 */
require_once __DIR__ . '/includes/db.php';

echo "<h2>Starting Database Migration...</h2>";

try {
    // 1. Add hero_image to categories
    $checkCategories = $pdo->query("SHOW COLUMNS FROM categories LIKE 'hero_image'")->fetch();
    if (!$checkCategories) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN hero_image VARCHAR(255) DEFAULT NULL AFTER slug");
        echo "✅ Added 'hero_image' column to 'categories' table.<br>";
    } else {
        echo "ℹ️ 'hero_image' column already exists in 'categories'.<br>";
    }

    // 2. Add barcode to products (Pre-emptive for barcode feature)
    $checkProducts = $pdo->query("SHOW COLUMNS FROM products LIKE 'barcode'")->fetch();
    if (!$checkProducts) {
        $pdo->exec("ALTER TABLE products ADD COLUMN barcode VARCHAR(100) DEFAULT NULL UNIQUE AFTER name");
        echo "✅ Added 'barcode' column to 'products' table.<br>";
    } else {
        echo "ℹ️ 'barcode' column already exists in 'products'.<br>";
    }

    echo "<br><h3 style='color:green;'>Migration Successful!</h3>";
    echo "<p>You can now delete this file (fix_db.php) and refresh your Admin Panel.</p>";

} catch (Exception $e) {
    echo "<br><h3 style='color:red;'>Migration Failed!</h3>";
    echo "Error: " . $e->getMessage();
}
