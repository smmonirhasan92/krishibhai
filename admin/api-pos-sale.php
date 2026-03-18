<?php
/**
 * AJAX - Admin POS Sale API
 * Directly creates a Delivered order and reduces stock.
 */
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id']) || !isset($data['qty'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$productId = (int)$data['product_id'];
$qty = (int)$data['qty'];

try {
    $pdo->beginTransaction();

    // 1. Fetch product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) throw new Exception("Product not found");
    if ($product['stock_qty'] < $qty) throw new Exception("Insufficient stock");

    // 2. Create Order (marked as Delivered)
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, total_amount, status, stock_reduced) VALUES ('Offline Sale', '-', 'Counter', ?, 'Delivered', 1)");
    $total = $product['price'] * $qty;
    $stmt->execute([$total]);
    $orderId = $pdo->lastInsertId();

    // 3. Insert Order Item
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$orderId, $productId, $qty, $product['price']]);

    // 4. Reduce Stock
    $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?")->execute([$qty, $productId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Sale recorded successfully!', 'order_id' => $orderId]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
