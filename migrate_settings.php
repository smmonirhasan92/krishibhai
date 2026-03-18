<?php
/**
 * Migration Script: Create Settings Table
 * This script sets up the dynamic configuration system.
 */
require_once __DIR__ . '/includes/db.php';

try {
    // 1. Create Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        group_name VARCHAR(50) DEFAULT 'general',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Insert Default Settings if not exist
    $defaults = [
        ['site_name', 'কৃষিভাই', 'general'],
        ['site_tagline', 'আধুনিক কৃষি, সফল কৃষক', 'general'],
        ['site_phone', '01890-190214', 'contact'],
        ['site_email', 'info@krishibhai.com', 'contact'],
        ['site_address', 'শৈলকুপা থানা রোড, শৈলকুপা, ঝিনাইদহ, বাংলাদেশ', 'contact'],
        ['site_whatsapp', '8801890190214', 'contact'],
        ['site_facebook', 'https://www.facebook.com/krishibhai', 'social'],
        ['site_youtube', 'https://youtube.com/@krishibhai', 'social'],
        ['site_instagram', 'https://instagram.com/krishibhai', 'social'],
        ['theme_primary_color', '#629d25', 'theme'],
        ['theme_show_hero', '1', 'theme'],
        ['theme_show_categories', '1', 'theme'],
        ['theme_show_featured', '1', 'theme'],
        ['theme_show_inquiry', '1', 'theme'],
        ['theme_hero_height', '500', 'theme'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, group_name) VALUES (?, ?, ?)");
    foreach ($defaults as $row) {
        $stmt->execute($row);
    }

    echo "Migration Success: Settings table created and seeded.\n";
} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
