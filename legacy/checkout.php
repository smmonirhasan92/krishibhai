<?php
/**
 * Zaman Kitchens - Checkout Page (Guest Order)
 * Ultra-lean: Name, Phone, Address only
 */
require_once __DIR__ . '/includes/db.php';
session_start();

$product = null;
$error = '';
$success = false;

// Fetch product if direct product buy
if (isset($_GET['product']) && is_numeric($_GET['product'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$_GET['product']]);
        $product = $stmt->fetch();
    } catch(Exception $e) {}
}

// Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $pid     = (int)($_POST['product_id'] ?? 0);
    $qty     = max(1, (int)($_POST['qty'] ?? 1));

    if (empty($name) || empty($phone) || empty($address)) {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
        $error = "Please enter a valid Bangladeshi mobile number (01XXXXXXXXX).";
    } else {
        try {
            if (!$prod) {
                $error = "Product not found. Please try again.";
            } else {
                $unitPrice = $prod['price'];
                
                // Check for Wholesale Price Rules
                $ruleStmt = $pdo->prepare("SELECT * FROM price_rules WHERE product_id = ? AND is_active = 1 AND min_qty <= ? ORDER BY min_qty DESC LIMIT 1");
                $ruleStmt->execute([$pid, $qty]);
                $rule = $ruleStmt->fetch();

                if ($rule) {
                    if ($rule['discount_type'] === 'fixed') {
                        $unitPrice = $rule['value'];
                    } elseif ($rule['discount_type'] === 'percentage') {
                        $unitPrice = $prod['price'] - ($prod['price'] * ($rule['value'] / 100));
                    }
                }

                $total = $unitPrice * $qty;

                // Insert order
                $pdo->prepare("INSERT INTO orders (customer_name, phone, address, total_amount, status) VALUES (?, ?, ?, ?, 'Pending')")
                    ->execute([$name, $phone, $address, $total]);
                $orderId = $pdo->lastInsertId();

                // Insert order item
                $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
                    ->execute([$orderId, $prod['id'], $qty, $unitPrice]);

                // Redirect to success
                header("Location: order-success.php?id=$orderId");
                exit();
            }
        } catch(Exception $e) {
            $error = "Order failed. Please call us directly.";
        }
    }
}

$pageTitle = "Checkout";
include_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-2xl">

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6">
            <a href="<?php echo SITE_URL; ?>" class="hover:text-amber-600">Home</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-medium">Checkout</span>
        </nav>

        <h1 class="text-2xl font-extrabold text-gray-900 mb-8">Place Your Order</h1>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 flex gap-3 items-start">
            <span class="text-xl">⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <!-- Product Summary -->
        <?php if ($product): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6 flex gap-4 items-center shadow-sm">
            <img src="<?php echo htmlspecialchars($product['main_image'] ?? $product['image_url'] ?? ''); ?>" 
                class="w-20 h-20 rounded-xl object-cover bg-gray-100" 
                onerror="this.style.display='none'"
                alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="flex-1">
                <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-amber-600 font-extrabold text-lg mt-1">৳ <?php echo number_format($product['price']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order Form -->
        <form method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <input type="hidden" name="product_id" value="<?php echo $product['id'] ?? ''; ?>">
            
            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-100">
                <label class="font-bold text-gray-700">Quantity</label>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="updateQty(-1)" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center font-bold text-lg hover:border-amber-500">-</button>
                    <input type="number" name="qty" id="qtyInput" value="1" min="1" readonly class="w-12 text-center bg-transparent font-extrabold text-lg outline-none">
                    <button type="button" onclick="updateQty(1)" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center font-bold text-lg hover:border-amber-500">+</button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" required
                    placeholder="e.g. Mohammad Rahim"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number *</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">🇧🇩 +88</span>
                    <input type="tel" name="phone" required
                        placeholder="01700000000"
                        pattern="01[3-9]\d{8}"
                        class="w-full pl-20 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm"
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <p class="text-xs text-gray-400 mt-1">We'll call/WhatsApp this number for delivery confirmation.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Delivery Address *</label>
                <textarea name="address" required rows="3"
                    placeholder="House #, Road #, Area, City..."
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm resize-none"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-400 mt-1">Include landmark if possible for faster delivery.</p>
            </div>

            <!-- Payment Methods -->
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                <p class="text-sm font-bold text-gray-800 mb-2">💳 Payment Method</p>
                <div class="flex flex-wrap gap-3">
                    <span class="bg-white border border-gray-200 text-sm px-3 py-1.5 rounded-lg font-medium text-gray-700">Cash on Delivery</span>
                    <span class="bg-white border border-gray-200 text-sm px-3 py-1.5 rounded-lg font-medium text-gray-700">📱 bKash</span>
                    <span class="bg-white border border-gray-200 text-sm px-3 py-1.5 rounded-lg font-medium text-gray-700">📱 Nagad</span>
                </div>
            </div>

            <!-- Order Total -->
            <?php if ($product): ?>
            <div class="border-t pt-4 flex justify-between items-center">
                <span class="text-gray-600">Total Amount</span>
                <span class="text-2xl font-extrabold text-amber-600">৳ <span id="displayTotal"><?php echo number_format($product['price']); ?></span></span>
            </div>
            <?php endif; ?>

            <button type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-extrabold py-4 rounded-xl transition shadow-lg shadow-amber-200 text-base">
                ✅ Confirm Order — Cash on Delivery
            </button>

            <p class="text-center text-xs text-gray-400">By ordering, you agree to our <a href="#" class="underline">terms</a>. Need help? <a href="https://wa.me/8801700000000" class="text-green-600 font-medium">Chat on WhatsApp</a></p>
        </form>
    </div>
</div>

<script>
    const basePrice = <?php echo $product['price'] ?? 0; ?>;
    const priceRules = <?php 
        $stmt = $pdo->prepare("SELECT min_qty, discount_type, value FROM price_rules WHERE product_id = ? AND is_active = 1 ORDER BY min_qty DESC");
        $stmt->execute([$product['id'] ?? 0]);
        echo json_encode($stmt->fetchAll());
    ?>;

    function updateQty(delta) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
        calculateTotal();
    }

    function calculateTotal() {
        const qty = parseInt(document.getElementById('qtyInput').value || 1);
        let unitPrice = basePrice;

        // Check rules
        for (let rule of priceRules) {
            if (qty >= parseInt(rule.min_qty)) {
                if (rule.discount_type === 'fixed') {
                    unitPrice = parseFloat(rule.value);
                } else if (rule.discount_type === 'percentage') {
                    unitPrice = basePrice - (basePrice * (parseFloat(rule.value) / 100));
                }
                break;
            }
        }

        const total = unitPrice * qty;
        document.getElementById('displayTotal').innerText = total.toLocaleString();
    }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
