<?php
require_once __DIR__ . '/includes/db.php';

echo "<h1>Krishibhai DB Fixer</h1>";

try {
    // Check if stock_reduced exists
    $cols = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('stock_reduced', $cols)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN stock_reduced TINYINT(1) DEFAULT 0");
        echo "<p style='color:green;'>✅ Column 'stock_reduced' added to 'orders' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ Column 'stock_reduced' already exists.</p>";
    }
    
    // Check if items can be reduced
    echo "<h3>System Ready.</h3>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
