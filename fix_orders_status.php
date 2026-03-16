<?php
require_once __DIR__ . '/includes/db.php';

echo "<pre>Fixing orders table status column...\n";

try {
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'");
    echo "Successfully updated orders.status to VARCHAR(50).\n";
} catch (Exception $e) {
    echo "Error updating orders.status: " . $e->getMessage() . "\n";
}

echo "Done!</pre>";
