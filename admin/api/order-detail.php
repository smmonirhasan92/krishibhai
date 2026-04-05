<?php
/**
 * Order Detail API — returns order info + items as JSON
 * Usage: GET /admin/api/order-detail.php?id=123
 */
require_once __DIR__ . '/../../includes/db.php';

// Auth check — must be logged in admin
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit();
}

try {
    // Fetch order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit();
    }

    // Fetch order items
    $items = [];
    try {
        $stmt2 = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt2->execute([$id]);
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // order_items table might not exist in all installs
        // Try parsing from order JSON if available
        if (!empty($order['items'])) {
            $decoded = json_decode($order['items'], true);
            if (is_array($decoded)) $items = $decoded;
        }
    }

    echo json_encode([
        'success' => true,
        'order'   => $order,
        'items'   => $items,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
