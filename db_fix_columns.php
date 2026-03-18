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
    
    if (in_array('main_image', $existing) && !in_array('image', $existing)) {
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
        'is_featured' => "TINYINT(1) DEFAULT 0",
        'image' => "VARCHAR(255) DEFAULT NULL" // Backup check for image column
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

    // 3. Ensure other tables exist (price_rules)
    echo "Checking <b>price_rules</b> table... ";
    $tblStmt = $pdo->query("SHOW TABLES LIKE 'price_rules'");
    if ($tblStmt->rowCount() == 0) {
        echo "Creating... ";
        $pdo->exec("CREATE TABLE price_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            min_qty INT NOT NULL,
            discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
            value DECIMAL(10,2) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<span style='color:green'>Success</span><br>";
    } else {
        echo "Already exists.<br>";
    }

    echo "<hr><b>✅ Database is now fully compatible with the new code!</b><br>";
    echo "<p style='color:blue'>Important: If images were not showing, the 'main_image' column has been renamed to 'image'.</p>";
    echo "<a href='admin/product-edit.php' style='display:inline-block; padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Go to Admin Panel</a>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Error updating database:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
