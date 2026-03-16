<?php
/**
 * Zaman Kitchens - Database Column Fix Script
 * Safely adds missing columns to the products table.
 */
require_once __DIR__ . '/includes/db.php';

echo "<h2>Zaman Kitchens Schema Fix</h2>";

try {
    // Columns to add
    $columns = [
        'meta_title' => "VARCHAR(255) DEFAULT NULL",
        'meta_description' => "TEXT DEFAULT NULL",
        'variations' => "LONGTEXT DEFAULT NULL",
        'specifications' => "LONGTEXT DEFAULT NULL",
        'purchase_price' => "DECIMAL(10,2) DEFAULT 0.00",
        'is_featured' => "TINYINT(1) DEFAULT 0"
    ];

    // Get existing columns
    $stmt = $pdo->query("DESCRIBE products");
    $existing = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

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
