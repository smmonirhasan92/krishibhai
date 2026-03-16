<?php
/**
 * OCE Professional Data Foundation Setup
 */
require_once __DIR__ . '/legacy/includes/db.php';

echo "Starting Professional Data Foundation Setup...\n";

try {
    // 1. Create leads table
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        status ENUM('New', 'Contacted', 'Closed') DEFAULT 'New',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Leads table created.\n";

    // 2. Ensure product columns exist
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS purchase_price DECIMAL(10,2) DEFAULT 0.00");
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'assets/images/placeholder.jpg'");
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0");
    echo "✅ Products table schema standardized.\n";

    // 3. Ensure price_rules table exists (for Wholesale)
    $pdo->exec("CREATE TABLE IF NOT EXISTS price_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        min_qty INT NOT NULL,
        discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        value DECIMAL(10,2) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Price rules table ready.\n";

    // 4. Hero Slides table
    $pdo->exec("CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle VARCHAR(255) DEFAULT NULL,
        image VARCHAR(255) NOT NULL,
        button_text VARCHAR(50) DEFAULT 'Shop Now',
        button_link VARCHAR(255) DEFAULT '#products',
        order_index INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Hero slides table ready.\n";

    // 5. Users and Addresses
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('customer', 'admin') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        address_label VARCHAR(50) DEFAULT 'Home',
        address_line TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        zip_code VARCHAR(20) DEFAULT NULL,
        is_default TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ User authentication tables ready.\n";

    // 6. Category Image column fix
    $cols = $pdo->query("SHOW COLUMNS FROM categories LIKE 'image'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN image VARCHAR(255) DEFAULT NULL");
        echo "✅ Categories table updated with image column.\n";
    }

    // 7. Comprehensive Seeding
    // Clear and re-populate categories to ensure images are present
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE categories");
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image) VALUES (?, ?, ?)");
    $cats = [
        ['Kitchen Cabinet', 'kitchen-cabinet', 'assets/images/cat-cabinet.jpg'],
        ['Kitchen Hood', 'kitchen-hood', 'assets/images/cat-hood.jpg'],
        ['Gas Stove', 'gas-stove', 'assets/images/cat-stove.jpg'],
        ['Premium Sinks', 'sinks', 'assets/images/cat-sink.jpg'],
        ['Bath Appliances', 'bath', 'assets/images/cat-bath.jpg']
    ];
    foreach ($cats as $cat) $stmt->execute($cat);
    echo "✅ Professional categories seeded.\n";

    $prodCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($prodCount < 10) {
        $pdo->exec("TRUNCATE TABLE products");
        $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, purchase_price, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($categories as $cat) {
            for ($i = 1; $i <= 3; $i++) {
                $name = $cat['name'] . " Pro " . $i;
                $slug = strtolower(str_replace(' ', '-', $name)) . '-' . rand(100, 999);
                $stmt->execute([
                    $cat['id'],
                    $name,
                    $slug,
                    "High-quality professional " . strtolower($cat['name']) . " for your dream kitchen. Durable and elegant design.",
                    rand(5000, 50000),
                    rand(3000, 25000),
                    'assets/images/placeholder.jpg',
                    rand(10, 50)
                ]);
            }
        }
        echo "✅ 15 Professional products seeded.\n";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n🚀 Master Setup Complete! Your store is now ready for professional use.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
