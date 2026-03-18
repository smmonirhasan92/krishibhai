<?php
require_once __DIR__ . '/../includes/db.php';

echo "--- Orders Table ---\n";
try {
    $stmt = $pdo->query("DESCIBE orders");
} catch(Exception $e) {
    // Try DESCRIBE (typo in my thought, let's just do query)
    try {
        $stmt = $pdo->query("DESCRIBE orders");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch(Exception $e2) {
        echo "Error DESCRIBE orders: " . $e2->getMessage() . "\n";
    }
}

echo "\n--- Products Table ---\n";
try {
    $stmt = $pdo->query("DESCRIBE products");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch(Exception $e) {
    echo "Error DESCRIBE products: " . $e->getMessage() . "\n";
}

echo "\n--- Attempting Fix ---\n";
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS stock_reduced TINYINT(1) DEFAULT 0");
    echo "Success: stock_reduced column checked/added.\n";
} catch(Exception $e) {
    // IF NOT EXISTS might not be supported in some MySQL versions with ALTER TABLE
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN stock_reduced TINYINT(1) DEFAULT 0");
        echo "Success: stock_reduced column added.\n";
    } catch(Exception $e2) {
        echo "Final Error: " . $e2->getMessage() . "\n";
    }
}
?>
