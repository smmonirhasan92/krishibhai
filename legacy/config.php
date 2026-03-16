<?php
/**
 * Global Configuration for Zaman Kitchens
 */

// Site Information
define('SITE_NAME', 'Zaman Kitchens');
define('SITE_URL', 'https://www.zamankitchens.com');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'zamankitchens_db');
define('DB_USER', 'zamankitchens_admin');
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
