<?php
/**
 * AJAX - Barcode Product Update API
 */
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit();
}

try {
    if (isset($data['price']) && $data['price'] > 0) {
        $stmt = $pdo->prepare("UPDATE products SET price = ?, stock_status = ? WHERE id = ?");
        $stmt->execute([$data['price'], $data['stock_status'], $data['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE products SET stock_status = ? WHERE id = ?");
        $stmt->execute([$data['stock_status'], $data['id']]);
    }

    // Fetch updated barcode to refresh the UI
    $stmt = $pdo->prepare("SELECT barcode FROM products WHERE id = ?");
    $stmt->execute([$data['id']]);
    $barcode = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'barcode' => $barcode]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
