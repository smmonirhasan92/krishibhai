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

$pageTitle = "অর্ডার নিশ্চিত হয়েছে";
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

            <h1 class="text-2xl font-extrabold text-gray-900 mb-2">অর্ডার সফলভাবে সম্পন্ন হয়েছে! 🎉</h1>
            <p class="text-gray-500 mb-6">ধন্যবাদ, <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>! আপনার অর্ডারটি আমরা পেয়েছি।</p>

            <!-- Order Details Card -->
            <div class="bg-gray-50 rounded-2xl p-5 text-left space-y-3 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">অর্ডার আইডি</span>
                    <span class="font-bold text-gray-900">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">পণ্য</span>
                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">মোট টাকা</span>
                    <span class="font-extrabold text-green-600">৳ <?php echo number_format($order['total_amount']); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">অবস্থা</span>
                    <span class="bg-yellow-100 text-yellow-700 font-bold text-xs px-2 py-1 rounded-full">⏳ কনফার্মেশনের অপেক্ষায়</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">ডেলিভারি ঠিকানা</span>
                    <span class="font-medium text-gray-700 text-right max-w-xs"><?php echo htmlspecialchars($order['address']); ?></span>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-6">আমাদের প্রতিনিধি আগামী ২ ঘণ্টার মধ্যে আপনার নম্বরে (<strong class="text-gray-900"><?php echo htmlspecialchars($order['phone']); ?></strong>) কল করে অর্ডারটি কনফার্ম করবেন।</p>

            <!-- CTA Buttons -->
            <div class="flex flex-col gap-3">
                <a href="https://wa.me/<?php echo str_replace(['+', '-', ' '], '', SITE_WHATSAPP); ?>?text=হ্যালো%2C%20আমার%20অর্ডার%20আইডি%20হলো%20%23<?php echo $order['id']; ?>" target="_blank"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    💬 হোয়াটসঅ্যাপে ট্র্যাক করুন
                </a>
                <a href="<?php echo SITE_URL; ?>"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-xl transition">
                    আরো কেনাকাটা করুন
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
