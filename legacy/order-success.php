<?php
/**
 * Zaman Kitchens - Order Success Page
 */
require_once __DIR__ . '/includes/db.php';

$order = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT o.*, oi.quantity, p.name AS product_name, p.price AS product_price FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id LEFT JOIN products p ON p.id = oi.product_id WHERE o.id = ?");
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch();
    } catch(Exception $e) {}
}

if (!$order) {
    header("Location: " . SITE_URL);
    exit();
}

$pageTitle = "Order Confirmed";
include_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-16">
    <div class="max-w-lg w-full mx-auto px-4 text-center">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-10">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Order Confirmed! 🎉</h1>
            <p class="text-gray-500 mb-6">Thank you, <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>! We've received your order.</p>

            <!-- Order Details Card -->
            <div class="bg-gray-50 rounded-2xl p-5 text-left space-y-3 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Order ID</span>
                    <span class="font-bold text-gray-900">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Product</span>
                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-extrabold text-amber-600">৳ <?php echo number_format($order['total_amount']); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    <span class="bg-yellow-100 text-yellow-700 font-bold text-xs px-2 py-1 rounded-full">⏳ Pending Confirmation</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Delivery To</span>
                    <span class="font-medium text-gray-700 text-right max-w-xs"><?php echo htmlspecialchars($order['address']); ?></span>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-6">Our team will call <strong class="text-gray-900"><?php echo htmlspecialchars($order['phone']); ?></strong> within <strong>2 hours</strong> to confirm delivery details.</p>

            <!-- CTA Buttons -->
            <div class="flex flex-col gap-3">
                <a href="https://wa.me/8801700000000?text=Hi%2C%20my%20Order%20ID%20is%20%23<?php echo $order['id']; ?>" target="_blank"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    💬 Track via WhatsApp
                </a>
                <a href="<?php echo SITE_URL; ?>"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-xl transition">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
