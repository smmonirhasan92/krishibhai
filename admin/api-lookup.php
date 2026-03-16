<?php
/**
 * AJAX - Barcode Lookup API
 */
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$barcode = $_GET['barcode'] ?? null;

if (!$barcode) {
    echo json_encode(['success' => false, 'message' => 'Barcode required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, name, price, stock_status, image, barcode FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();

    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
