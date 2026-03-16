<?php
/**
 * Zaman Kitchens - Admin Orders Management
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['Pending', 'Processing', 'Delivered', 'Cancelled'];
    if (in_array($_POST['status'], $allowed)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['order_id']]);
    }
    header("Location: orders.php");
    exit();
}

// Filter by status
$statusFilter = $_GET['status'] ?? '';
$allowed = ['Pending', 'Processing', 'Delivered', 'Cancelled'];
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
<?php 
$adminTitle = 'Orders Management';
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="px-12 py-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Order Management</h1>
            <p class="text-slate-500 font-medium">Track and fulfill <span class="text-amber-600"><?php echo count($orders); ?></span> active transactions.</p>
        </div>
        <!-- Filter Buttons -->
        <div class="flex gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
            <a href="orders.php" class="<?php echo !$statusFilter ? 'bg-amber-600 text-white shadow-lg shadow-amber-200' : 'text-slate-500 hover:bg-slate-50'; ?> px-6 py-2.5 rounded-xl text-xs font-black transition uppercase tracking-widest">All</a>
            <?php foreach(['Pending','Processing','Delivered','Cancelled'] as $s): ?>
            <a href="orders.php?status=<?php echo $s; ?>" class="<?php echo $statusFilter === $s ? 'bg-amber-600 text-white shadow-lg shadow-amber-200' : 'text-slate-500 hover:bg-slate-50'; ?> px-6 py-2.5 rounded-xl text-xs font-black transition uppercase tracking-widest"><?php echo $s; ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glass-card rounded-[2.5rem] shadow-sm overflow-hidden border border-white/40">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Reference</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Customer Information</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Shipping Details</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Amount</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Status</th>
                        <th class="px-10 py-5 text-right font-bold text-slate-400 uppercase tracking-widest text-[10px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="6" class="px-10 py-20 text-center">
                        <div class="text-5xl mb-4 opacity-10">📦</div>
                        <div class="text-slate-400 font-bold uppercase tracking-widest text-xs">No orders found matching criteria</div>
                    </td></tr>
                    <?php endif; ?>
                    <?php foreach($orders as $order): ?>
                    <?php $statusColor = match($order['status']) {
                        'Pending' => 'bg-amber-100 text-amber-700',
                        'Processing' => 'bg-blue-100 text-blue-700',
                        'Delivered' => 'bg-emerald-100 text-emerald-700',
                        'Cancelled' => 'bg-rose-100 text-rose-700',
                        default => 'bg-slate-100 text-slate-600'
                    }; ?>
                    <tr class="hover:bg-amber-50/20 transition group">
                        <td class="px-10 py-6">
                            <div class="font-black text-slate-900 group-hover:text-amber-600 transition-colors">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></div>
                        </td>
                        <td class="px-10 py-6">
                            <div class="font-black text-slate-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <a href="tel:<?php echo $order['phone']; ?>" class="text-[10px] font-bold text-amber-600 hover:underline uppercase tracking-widest"><?php echo htmlspecialchars($order['phone']); ?></a>
                        </td>
                        <td class="px-10 py-6 text-slate-500 max-w-xs truncate font-medium text-xs"><?php echo htmlspecialchars($order['address']); ?></td>
                        <td class="px-10 py-6 font-black text-slate-900 text-lg">৳ <?php echo number_format($order['total_amount']); ?></td>
                        <td class="px-10 py-6">
                            <span class="<?php echo $statusColor; ?> text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-wider"><?php echo $order['status']; ?></span>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <form method="POST" class="flex justify-end gap-2">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="bg-white border border-slate-100 rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none transition shadow-sm">
                                    <?php foreach(['Pending','Processing','Delivered','Cancelled'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                                    <i class="ph ph-check-circle text-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
</body>
</html>
</body>
</html>
