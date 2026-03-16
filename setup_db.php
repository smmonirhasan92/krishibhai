<?php
require_once __DIR__ . '/includes/db.php';

echo "<pre>Starting Database Refinement Setup...\n";

try {
    // 1. Refactor Tables (Add columns if they don't exist)
    // Add hero_image to categories
    $pdo->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS hero_image VARCHAR(255) DEFAULT NULL");
    
    // Add gallery_images and is_featured to products
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS gallery_images JSON DEFAULT NULL");
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE");
    // Ensure image_url is renamed or handled as main_image (Schema update)
    $pdo->exec("ALTER TABLE products CHANGE COLUMN IF NOT EXISTS image_url main_image VARCHAR(255)");

    // 2. Load Core Schema
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    echo "Schema updated successfully.\n";

    // 3. Insert Dummy Products with "Featured" Status
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() < 5) {
        $products = [
            ['Luxury Kitchen Cabinet', 'lux-cabinet-01', 'Handcrafted premium kitchen cabinet', 85000, 'https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?w=800', 1, 1],
            ['Nano Diamond Black Sink', 'nano-sink-black', 'Scratch resistant nano technology sink', 15500, 'https://images.unsplash.com/photo-1584622781564-1d9876a13d00?w=800', 4, 1],
            ['Double Burner Glass Stove', 'glass-stove-db', 'Elegant double burner glass stove', 7500, 'https://images.unsplash.com/photo-1590333247377-50a3dec42441?w=800', 3, 1],
            ['Smart Suction Hood', 'smart-hood-v2', 'Touch control smart kitchen hood', 22000, 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800', 2, 0],
            ['Stainless Steel Dish Rack', 'ss-dish-rack', 'Rust-proof 304 SS dish rack', 3200, 'https://images.unsplash.com/photo-1583847268964-b28dc2f51ac9?w=800', 6, 0]
        ];
        
        $insert = $pdo->prepare("INSERT IGNORE INTO products (name, slug, description, price, main_image, category_id, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $p) {
            $insert->execute($p);
        }
        echo "High-end dummy products and featured flags added.\n";
    }

    echo "Total categories in DB: " . $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() . "\n";
    echo "Total products in DB: " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
    echo "Featured products count: " . $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn() . "\n";

    echo "Setup Complete!</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
