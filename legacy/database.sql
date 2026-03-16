-- Zaman Kitchens Database Schema
-- Optimized for High-Performance & High-Conversion
-- Created for Shared Hosting (PHP 8.x, MySQL)

CREATE DATABASE IF NOT EXISTS zamankitchens_db;
USE zamankitchens_db;

-- 1. Categories Table (Added hero_image)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    hero_image VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Products Table (Added gallery_images, is_featured)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_status ENUM('In Stock', 'Out of Stock') DEFAULT 'In Stock',
    main_image VARCHAR(255),
    gallery_images JSON DEFAULT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Orders Table (Clean 3-field structure as requested)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Processing', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin (admin / admin123)
-- Using INSERT IGNORE to prevent duplicate error
INSERT IGNORE INTO admins (id, username, password) VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Initial Categories with Placeholder Hero Images
INSERT IGNORE INTO categories (id, name, slug) VALUES 
(1, 'Kitchen Cabinet', 'kitchen-cabinet'),
(2, 'Kitchen Power Hood', 'kitchen-hood'),
(3, 'Smart Gas Stove', 'gas-stove'),
(4, 'Premium Kitchen Sink', 'sink'),
(5, 'Kitchen Accessories', 'kitchen-accessories'),
(6, 'Dish Rack', 'dish-rack'),
(7, 'Geyser', 'geyser'),
(8, 'Water Purifier', 'water-purifier'),
(9, 'Bath Appliances', 'bath-appliances'),
(10, 'Kitchen Appliances', 'kitchen-appliances'),
(11, 'Home Appliance', 'home-appliance');
