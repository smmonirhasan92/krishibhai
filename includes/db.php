<?php
/**
 * Database Connection Wrapper using PDO
 */
require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Humanized: Log error instead of displaying raw stack trace to users
    error_log("DB Connection Error: " . $e->getMessage());
    die("আমাদের সার্ভারে সাময়িক সমস্যা হচ্ছে। দয়াকরে কিছুক্ষণ পর চেষ্টা করুন। (Connection error)");
}

/**
 * Global Settings Fetcher (WP-style Theme Engine)
 */
$settings = [];
try {
    // Check if table exists first (Silent fail if missing during first migration)
    $check = $pdo->query("SHOW TABLES LIKE 'settings'")->fetch();
    if ($check) {
        // Auto-migrate: ensure stock_reduced exists in orders table
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS stock_reduced TINYINT(1) DEFAULT 0");
        // Auto-migrate: ensure leads table exists
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch(Exception $e) {}

        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    error_log("Settings Loader Error: " . $e->getMessage());
}

// Fallback to config constants if database setting is missing
function get_setting($key, $fallback = '') {
    global $settings;
    if (isset($settings[$key])) return $settings[$key];
    
    // Fallback to legacy constants if they exist
    $const_map = [
        'site_name'    => 'SITE_NAME',
        'site_phone'   => 'SITE_PHONE',
        'site_address' => 'SITE_ADDRESS',
        'site_whatsapp'=> 'SITE_WHATSAPP'
    ];
    
    if (isset($const_map[$key]) && defined($const_map[$key])) {
        return constant($const_map[$key]);
    }
    
    return $fallback;
}
