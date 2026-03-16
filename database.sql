-- Krishibhai Database Schema
-- Optimized for High-Performance & High-Conversion
-- Created for Shared Hosting (PHP 8.x, MySQL)

CREATE DATABASE IF NOT EXISTS krishibh_db;
USE krishibh_db;

-- 1. Categories Table (Added hero_image)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    hero_image VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    purchase_price DECIMAL(10,2) DEFAULT 0.00,
    stock_qty INT DEFAULT 0,
    stock_status ENUM('In Stock', 'Out of Stock') DEFAULT 'In Stock',
    image VARCHAR(255) DEFAULT NULL,
    gallery_images JSON DEFAULT NULL,
    variations LONGTEXT DEFAULT NULL,
    specifications LONGTEXT DEFAULT NULL,
    barcode VARCHAR(100) DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
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

-- 6. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin (admin / admin123)
-- Using INSERT IGNORE to prevent duplicate error
INSERT IGNORE INTO admins (id, username, password) VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Initial Categories with Placeholder Hero Images
INSERT IGNORE INTO categories (id, name, slug) VALUES 
(1, 'সার', 'fertilizer'),
(2, 'বীজ', 'seed'),
(3, 'কীটনাশক', 'pesticide'),
(4, 'কৃষি সরঞ্জাম', 'agri-machinery'),
(5, 'সেচ পাম্প', 'irrigation-pump'),
(6, 'জৈব সার', 'organic-fertilizer'),
(7, 'চারা গাছ', 'sapling'),
(8, 'মাছের খাবার', 'fish-feed'),
(9, 'পশুখাদ্য', 'animal-feed'),
(10, 'বাগান সরঞ্জাম', 'gardening-tools'),
(11, 'অন্যান্য সরঞ্জাম', 'others');
