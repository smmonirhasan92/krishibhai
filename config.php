<?php
/**
 * Global Configuration for Zaman Kitchens
 */

// Site Information
define('SITE_NAME', 'কৃষিভাই');
define('SITE_URL', 'https://krishibhai.com');

// Contact Information
define('SITE_PHONE', '01720-579899');
define('SITE_PHONE_RAW', '01720579899');
define('SITE_WHATSAPP', '8801720579899');
define('SITE_ADDRESS', 'থানা গোপালপুর, টাঙ্গাইল, বাংলাদেশ');

// Social Media Links
define('SITE_FB', 'https://www.facebook.com/krishibhai');
define('SITE_YT', 'https://youtube.com/@krishibhai');
define('SITE_INS', 'https://instagram.com/krishibhai');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'krishibh_db');
define('DB_USER', 'krishibh_admin');
define('DB_PASS', 'mFeU+uXlawV73%{4');

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
