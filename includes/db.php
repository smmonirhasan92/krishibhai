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
