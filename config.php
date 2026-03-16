<?php
/**
 * Global Configuration for Krishibhai
 */

// Site Information
define('SITE_NAME', 'কৃষিভাই');
define('SITE_URL', 'https://krishibhai.com');

// Contact Information
define('SITE_PHONE', '01890-190214');
define('SITE_PHONE_RAW', '01890190214');
define('SITE_WHATSAPP', '8801890190214');
define('SITE_ADDRESS', 'শৈলকুপা থানা রোড, শৈলকুপা, ঝিনাইদহ, বাংলাদেশ');

// Social Media Links
define('SITE_FB', 'https://www.facebook.com/krishibhai');
define('SITE_YT', 'https://youtube.com/@krishibhai');
define('SITE_INS', 'https://instagram.com/krishibhai');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'krishibhai_db');
define('DB_USER', 'krishibhai_admin');
define('DB_PASS', 'Sir@@@admin123');

// Paths
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', SITE_URL . '/assets');

// Security Key
define('SECRET_KEY', 'zaman_kitchen_secret_v1');

// Error Reporting (Set to 0 for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Dhaka');
