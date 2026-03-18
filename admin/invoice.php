<?php
/**
 * Krishibhai - Standard Invoice Generator
 * Generates a professional, printable invoice for orders.
 */
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized");
}

$orderId = $_GET['id'] ?? null;
if (!$orderId) die("Order ID required");

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) die("Order not found");

// Fetch Items
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$siteName = get_setting('site_name', SITE_NAME);
$sitePhone = get_setting('site_phone', SITE_PHONE);
$siteAddress = get_setting('site_address', SITE_ADDRESS);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ইনভয়েস #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?> - <?php echo $siteName; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 40px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; background: #fff; border-radius: 10px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #629d25; padding-bottom: 20px; margin-bottom: 20px; }
        .brand h1 { color: #629d25; margin: 0; font-size: 28px; font-weight: 800; }
        .brand p { margin: 5px 0 0; font-size: 12px; color: #666; font-weight: 600; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { margin: 0; color: #333; font-size: 20px; text-transform: uppercase; }
        .invoice-details p { margin: 5px 0; font-size: 13px; font-weight: 700; color: #444; }
        .customer-info { margin-bottom: 30px; }
        .customer-info h4 { margin: 0 0 10px; color: #629d25; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        .customer-info p { margin: 2px 0; font-size: 14px; font-weight: 600; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table th { background: #f9fafb; color: #4b5563; font-weight: 700; padding: 12px; text-transform: uppercase; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; font-weight: 600; }
        .text-right { text-align: right; }
        .totals { margin-top: 30px; width: 40%; margin-left: auto; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .total-row.grand { border-bottom: none; color: #629d25; font-size: 18px; font-weight: 900; }
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #eee; padding-top: 20px; }
        @media print {
            body { padding: 0; background: #fff; }
            .invoice-box { box-shadow: none; border: none; }
            .no-print { display: none; }
        }
        .no-print { margin-bottom: 20px; text-align: center; }
        .btn { background: #629d25; color: #fff; padding: 10px 25px; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 14px; transition: 0.3s; cursor: pointer; border: none; display: inline-block; }
        .btn:hover { background: #4a771c; }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn">🖨️ প্রিন্ট ইনভয়েস / PDF</button>
    <a href="orders.php" class="btn" style="background:#4b5563; margin-left:10px;">অর্ডার লিস্ট</a>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="brand">
            <h1><?php echo $siteName; ?></h1>
            <p><?php echo $siteAddress; ?></p>
            <p>ফোন: <?php echo $sitePhone; ?></p>
        </div>
        <div class="invoice-details">
            <h2>ইনভয়েস</h2>
            <p>নম্বর: #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></p>
            <p>তারিখ: <?php echo date('d M, Y', strtotime($order['created_at'])); ?></p>
            <p>অবস্থা: <?php 
                $st = ['Pending'=>'পেন্ডিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
                echo $st[$order['status']] ?? $order['status']; 
            ?></p>
        </div>
    </div>

    <div class="customer-info">
        <h4>বিলিং ডিটেইলস</h4>
        <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
        <?php if($order['phone'] !== '-'): ?><p>ফোন: <?php echo htmlspecialchars($order['phone']); ?></p><?php endif; ?>
        <?php if($order['address'] !== 'Counter'): ?><p>ঠিকানা: <?php echo htmlspecialchars($order['address']); ?></p><?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>বিবরণ</th>
                <th class="text-right">মূল্য</th>
                <th class="text-right">পরিমাণ</th>
                <th class="text-right">মোট</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-right">৳ <?php echo number_format($item['price']); ?></td>
                <td class="text-right"><?php echo $item['quantity']; ?></td>
                <td class="text-right">৳ <?php echo number_format($item['price'] * $item['quantity']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span>সাবটোটাল</span>
            <span>৳ <?php echo number_format($order['total_amount']); ?></span>
        </div>
        <div class="total-row">
            <span>ডিসকাউন্ট</span>
            <span>৳ 0</span>
        </div>
        <div class="total-row grand">
            <span>সর্বমোট</span>
            <span>৳ <?php echo number_format($order['total_amount']); ?></span>
        </div>
    </div>

    <div class="footer">
        <p>আমাদের থেকে কেনাকাটা করার জন্য ধন্যবাদ!</p>
        <p>© <?php echo date('Y'); ?> <?php echo $siteName; ?>. All Rights Reserved.</p>
    </div>
</div>

</body>
</html>
