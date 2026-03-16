<?php
/**
 * Zaman Kitchens - Database Column Fix Script
 * Safely adds missing columns to the products table.
 */
require_once __DIR__ . '/includes/db.php';

echo "<h2>Krishibhai Schema Fix</h2>";

try {
    // 1. Rename 'main_image' to 'image' if needed
    $stmt = $pdo->query("DESCRIBE products");
    $existing = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    if (in_array('main_image', existing) && !in_array('image', $existing)) {
        echo "Renaming <b>main_image</b> to <b>image</b>... ";
        $pdo->exec("ALTER TABLE products CHANGE main_image image VARCHAR(255) DEFAULT NULL");
        echo "<span style='color:green'>Success</span><br>";
        $existing = array_diff($existing, ['main_image']);
        $existing[] = 'image';
    }

    // 2. Columns to add
    $columns = [
        'meta_title' => "VARCHAR(255) DEFAULT NULL",
        'meta_description' => "TEXT DEFAULT NULL",
        'variations' => "LONGTEXT DEFAULT NULL",
        'specifications' => "LONGTEXT DEFAULT NULL",
        'purchase_price' => "DECIMAL(10,2) DEFAULT 0.00",
        'stock_qty' => "INT DEFAULT 0",
        'barcode' => "VARCHAR(100) DEFAULT NULL",
        'is_featured' => "TINYINT(1) DEFAULT 0"
    ];

    foreach ($columns as $name => $definition) {
        if (!in_array($name, $existing)) {
            echo "Adding column <b>$name</b>... ";
            $pdo->exec("ALTER TABLE products ADD $name $definition");
            echo "<span style='color:green'>Success</span><br>";
        } else {
            echo "Column <b>$name</b> already exists.<br>";
        }
    }

    echo "<hr><b>Database is now up to date!</b><br>";
    echo "<a href='admin/product-edit.php?id=1'>Click here to return to Product Edit</a>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Error updating database:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
