<?php
/**
 * AJAX - Place Order API
 */
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$items   = json_decode($_POST['items'] ?? '[]', true);

if (empty($name) || empty($phone) || empty($address) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields and add items to your bag.']);
    exit();
}

// Simple Phone Validation
if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid Bangladeshi mobile number (01XXXXXXXXX).']);
    exit();
}

try {
    $pdo->beginTransaction();

    $totalAmount = 0;
    foreach ($items as $item) {
        $totalAmount += $item['price'] * $item['qty'];
    }

    // Insert Order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, total_amount, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$name, $phone, $address, $totalAmount]);
    $orderId = $pdo->lastInsertId();

    // Insert Order Items and Update Stock immediately
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
    
    foreach ($items as $item) {
        $itemStmt->execute([$orderId, (int)$item['id'], (int)$item['qty'], (float)$item['price']]);
        $stockStmt->execute([(int)$item['qty'], (int)$item['id']]);
    }

    // Update order with stock_reduced = 1
    $pdo->prepare("UPDATE orders SET stock_reduced = 1 WHERE id = ?")->execute([$orderId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Order System Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'অর্ডার সিস্টেমের কিছু সমস্যা হচ্ছে। দয়া করে আমাদের কল করুন: ' . SITE_PHONE]);
}
