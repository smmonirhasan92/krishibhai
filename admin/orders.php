<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

$allowed = ['Pending', 'Processing', 'Delivered', 'Cancelled'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    if (in_array($newStatus, $allowed)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Get current order info
            $stmt = $pdo->prepare("SELECT status, stock_reduced FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                // If moving to Cancelled and stock WAS reduced: Restore stock
                if ($newStatus === 'Cancelled' && $order['stock_reduced'] == 1) {
                    $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $items->execute([$orderId]);
                    $orderItems = $items->fetchAll();
                    
                    foreach ($orderItems as $item) {
                        $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?")
                            ->execute([$item['quantity'], $item['product_id']]);
                    }
                    
                    $pdo->prepare("UPDATE orders SET status = ?, stock_reduced = 0 WHERE id = ?")
                        ->execute([$newStatus, $orderId]);
                } 
                // If moving FROM Cancelled to any active status and stock was NOT reduced/restored: Reduce stock again
                else if ($newStatus !== 'Cancelled' && $order['status'] === 'Cancelled' && $order['stock_reduced'] == 0) {
                    $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $items->execute([$orderId]);
                    $orderItems = $items->fetchAll();
                    
                    foreach ($orderItems as $item) {
                        $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?")
                            ->execute([$item['quantity'], $item['product_id']]);
                    }
                    
                    $pdo->prepare("UPDATE orders SET status = ?, stock_reduced = 1 WHERE id = ?")
                        ->execute([$newStatus, $orderId]);
                }
                else {
                    // Simple status update (e.g., Pending -> Processing -> Delivered)
                    // These statuses don't affect stock anymore since it's reduced at placement
                    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
                }
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Order Status Update Error: " . $e->getMessage());
        }
    }
    header("Location: orders.php"); exit();
}

$adminTitle = 'অর্ডার ম্যানেজমেন্ট';
include_once __DIR__ . '/includes/header.php';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM orders";
$params = [];
if ($statusFilter && in_array($statusFilter, $allowed)) {
    $sql .= " WHERE status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";
$orders = $pdo->prepare($sql);
$orders->execute($params);
$orders = $orders->fetchAll();
?>

<!-- Filter Pills -->
<div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <a href="orders.php" style="padding:0.375rem 1rem; border-radius:8px; font-size:0.8125rem; font-weight:700; text-decoration:none;
       <?php echo !$statusFilter ? 'background:linear-gradient(135deg,#629d25,#4a771c);color:white;' : 'background:#f3f4f6;color:#374151;'; ?>">সব</a>
    <?php foreach($allowed as $s): ?>
    <a href="orders.php?status=<?php echo $s; ?>" style="padding:0.375rem 1rem; border-radius:8px; font-size:0.8125rem; font-weight:700; text-decoration:none;
       <?php echo $statusFilter===$s ? 'background:linear-gradient(135deg,#629d25,#4a771c);color:white;' : 'background:#f3f4f6;color:#374151;'; ?>">
        <?php 
            $bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
            echo $bnStatus[$s] ?? $s; 
        ?> <?php if($s==='Pending'): ?>(<?php echo count(array_filter($orders, fn($o)=>$o['status']==='Pending')); ?>)<?php endif; ?>
    </a>
    <?php endforeach; ?>
    <span style="margin-left:auto; font-size:0.8125rem; color:#9ca3af; font-weight:600;"><?php echo count($orders); ?> অর্ডার</span>
</div>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>কাস্টমার</th>
                    <th>ঠিকানা</th>
                    <th>পরিমাণ</th>
                    <th>অবস্থা</th>
                    <th>তারিখ</th>
                    <th style="text-align:right;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:#9ca3af;">
                    <div style="font-size:2rem; margin-bottom:0.5rem;">📭</div>
                    কোন অর্ডার পাওয়া যায়নি।
                </td></tr>
                <?php endif; ?>
                <?php foreach($orders as $o):
                    $sc = strtolower($o['status']);
                ?>
                <tr>
                    <td><span style="font-weight:800; color:#111827;">#<?php echo str_pad($o['id'],4,'0',STR_PAD_LEFT); ?></span></td>
                    <td>
                        <div style="font-weight:700; color:#111827;"><?php echo htmlspecialchars($o['customer_name']); ?></div>
                        <a href="tel:<?php echo $o['phone']; ?>" style="font-size:0.75rem; color:#629d25; font-weight:700; text-decoration:none;"><?php echo htmlspecialchars($o['phone']); ?></a>
                    </td>
                    <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#6b7280;"><?php echo htmlspecialchars($o['address']); ?></td>
                    <td style="font-weight:800; color:#111827;">৳ <?php echo number_format($o['total_amount']); ?></td>
                    <td><span class="status-badge status-<?php echo $sc; ?>"><?php 
                        $bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
                        echo $bnStatus[$o['status']] ?? $o['status']; 
                    ?></span></td>
                    <td style="color:#9ca3af; font-size:0.8125rem;"><?php echo date('d M, Y', strtotime($o['created_at'])); ?></td>
                    <td style="text-align:right;">
                        <a href="invoice.php?id=<?php echo $o['id']; ?>" target="_blank" class="btn btn-ghost" style="padding:0.375rem; margin-right:0.5rem;" title="প্রিন্ট ইনভয়েস">
                            <i class="ph ph-printer" style="font-size:1.1rem;"></i>
                        </a>
                        <form method="POST" style="display:inline-flex; align-items:center; gap:0.5rem;">
                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                            <select name="status" class="admin-input" style="width:auto; padding:0.375rem 0.625rem; font-size:0.8125rem;">
                                <?php foreach($allowed as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['status']===$s ? 'selected' : ''; ?>><?php 
                                    $bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
                                    echo $bnStatus[$s] ?? $s; 
                                ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding:0.375rem 0.875rem;">✓</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
