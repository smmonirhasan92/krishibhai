<?php
require 'includes/db.php';

echo "🚀 Starting Krishibhai Migration...\n";

function safe_exec($pdo, $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Success: $sql\n";
    } catch (Exception $e) {
        echo "⚠️  Note: " . $e->getMessage() . "\n";
    }
}

// 1. Ensure categories table has new columns
safe_exec($pdo, "ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL");
safe_exec($pdo, "ALTER TABLE categories ADD COLUMN sort_order INT DEFAULT 0");

// 2. Ensure products table has new columns
safe_exec($pdo, "ALTER TABLE products ADD COLUMN old_price DECIMAL(10,2) DEFAULT NULL");

// 3. Ensure settings table exists (it was missing from SHOW TABLES)
safe_exec($pdo, "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 4. Insert default settings if empty
try {
    $check = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check == 0) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['site_name', 'Krishibhai']);
        $stmt->execute(['site_phone', '01712345678']);
        $stmt->execute(['site_address', 'Dhaka, Bangladesh']);
        echo "✅ Default settings inserted.\n";
    }
} catch (Exception $e) {}

echo "🏁 Migration Complete!\n";
