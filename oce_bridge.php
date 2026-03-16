<?php

/**
 * OCE (Omni-Commerce Engine) - Professional Bridge for Shared Hosting
 * This file should be placed in your 'public_html' folder.
 * It connects the public web directory to the core engine located outside.
 */

define('LARAVEL_START', microtime(true));

// 1. Path to your OCE Core directory (relative or absolute)
// Modify this if you move the core to a different folder
$oceCorePath = __DIR__ . '/../repositories/zamankitchens'; 

// 2. Check if the application is in maintenance mode
if (file_exists($maintenance = $oceCorePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 3. Register the Composer autoloader
if (file_exists($oceCorePath . '/vendor/autoload.php')) {
    require $oceCorePath . '/vendor/autoload.php';
} else {
    die("OCE Error: Vendor directory not found. Please run 'composer install' in the core directory.");
}

// 4. Bootstrap Laravel and handle the request
$app = require_once $oceCorePath . '/bootstrap/app.php';

// 5. Run the application
use Illuminate\Http\Request;
$app->handleRequest(Request::capture());
