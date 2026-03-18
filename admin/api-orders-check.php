<?php
/**
 * AJAX - Pending Orders Count API
 * Extremely lightweight and safe for live sites.
 */
require_once __DIR__ . '/../includes/db.php';
session_start();

header('Content-Type: application/json');

// Only allow logged in admins
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Count pending orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'pending_count' => (int)$count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
