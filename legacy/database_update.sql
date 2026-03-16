-- 1. Create Hero Slides table if not exists
CREATE TABLE IF NOT EXISTS hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT NULL,
    subtitle TEXT DEFAULT NULL,
    image_path VARCHAR(255) NOT NULL,
    button_text VARCHAR(50) DEFAULT 'Shop Now',
    button_link VARCHAR(255) DEFAULT '#',
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Updates specifically for PRO products
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS meta_description TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS variations JSON DEFAULT NULL,
ADD COLUMN IF NOT EXISTS specifications JSON DEFAULT NULL,
ADD COLUMN IF NOT EXISTS purchase_price DECIMAL(10,2) DEFAULT 0.00;

-- 2. Ensure Categories table has dedicated image support (in case it wasn't run earlier)
ALTER TABLE categories ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL;

-- 3. Cleanup: Initial Hero Slides (in case table exists but empty)
INSERT IGNORE INTO hero_slides (image_path, title, subtitle, button_text, button_link) VALUES 
('https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?w=1600', 'Transform Your Kitchen Space', 'Premium Kitchen Solutions trusted by 5,000+ homes.', 'Shop Now', '#featured'),
('https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=1600', 'Premium Sinks & Hoods', 'Modern designs for your dream kitchen.', 'View Collection', 'category/sink');
