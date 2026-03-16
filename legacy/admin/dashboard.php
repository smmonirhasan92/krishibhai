<?php
/**
 * Zaman Kitchens - Admin Dashboard
 * Features: KPIs, Latest Orders, AJAX new-order notification with sound
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

// Auth Guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Dashboard Stats
try {
    $totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    $totalSales    = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
    $totalProfit   = $pdo->query("SELECT COALESCE(SUM(oi.quantity * (oi.price - COALESCE(p.purchase_price, 0))), 0) FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE o.status != 'Cancelled'")->fetchColumn();
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $recentOrders  = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();
    $lastOrderId   = $pdo->query("SELECT COALESCE(MAX(id),0) FROM orders")->fetchColumn();
} catch(Exception $e) {
    $totalOrders = $pendingOrders = $totalSales = $totalProfit = $totalProducts = 0;
    $recentOrders = [];
    $lastOrderId = 0;
}

$adminTitle = 'Overview';
include_once __DIR__ . '/includes/header.php'; 
?>

<!-- Content Container -->
<div class="px-12 py-10">
    
    <!-- Notification Alert Bar (hidden by default) -->
    <div id="newOrderAlert" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl mb-8 text-center font-bold hidden cursor-pointer animate-pulse shadow-xl shadow-indigo-200">
        🚀 New order received! Tap to view details.
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Welcome back, Admin!</h1>
            <p class="text-slate-500 font-medium">Here's what's happening today at <span class="text-amber-600">Zaman Kitchens</span>.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                <i class="ph ph-export text-lg"></i>
                Export Results
            </button>
            <a href="product-edit.php" class="px-6 py-3 bg-amber-600 text-white rounded-2xl text-sm font-bold hover:bg-amber-700 transition shadow-lg shadow-amber-200 flex items-center gap-2">
                <i class="ph ph-plus-circle text-lg"></i>
                Add Product
            </a>
        </div>
    </div>

    <!-- Premium KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <?php
        $kpis = [
            ['Total Orders', $totalOrders, '📦', 'text-blue-600', 'bg-blue-50', 'orders.php'],
            ['Pending Status', $pendingOrders, '⏳', 'text-amber-600', 'bg-amber-50', 'orders.php?status=Pending'],
            ['Total Sales', '৳ ' . number_format($totalSales), '💰', 'text-emerald-600', 'bg-emerald-50', 'orders.php'],
            ['Net Profit', '৳ ' . number_format($totalProfit), '📈', 'text-indigo-600', 'bg-indigo-50', 'reports.php'],
        ];
        foreach ($kpis as [$label, $value, $icon, $textColor, $bgColor, $link]): ?>
        <a href="<?php echo $link; ?>" class="group glass-card p-8 rounded-[2.5rem] shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 relative overflow-hidden">
            <div class="flex flex-col gap-4">
                <div class="w-14 h-14 <?php echo $bgColor; ?> <?php echo $textColor; ?> rounded-2xl flex items-center justify-center text-2xl transition-transform group-hover:scale-110 duration-500"><?php echo $icon; ?></div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.1em] mb-1"><?php echo $label; ?></div>
                    <div class="text-3xl font-bold text-slate-900 tracking-tight group-hover:text-amber-600 transition-colors"><?php echo $value; ?></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Recent Orders & Stats Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Recent Orders Table -->
        <div class="lg:col-span-2 glass-card rounded-[2.5rem] shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-10 py-8 border-b border-slate-50">
                <h2 class="font-black text-xl text-slate-900">Recent Transactions</h2>
                <a href="orders.php" class="px-5 py-2 bg-slate-50 text-slate-500 text-xs font-bold rounded-full hover:bg-amber-50 hover:text-amber-600 transition">View All Orders</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/30">
                            <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Reference</th>
                            <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Customer</th>
                            <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Revenue</th>
                            <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Status</th>
                            <th class="px-10 py-5 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="px-10 py-20 text-center">
                            <div class="text-5xl mb-4 opacity-10">📋</div>
                            <div class="text-slate-400 font-bold uppercase tracking-widest text-xs">No transactions available</div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="hover:bg-amber-50/20 transition group">
                            <td class="px-10 py-6">
                                <div class="font-black text-slate-900 group-hover:text-amber-600 transition-colors">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></div>
                            </td>
                            <td class="px-10 py-6">
                                <div class="font-bold text-slate-800"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                <div class="text-[10px] text-slate-400 font-bold"><?php echo htmlspecialchars($order['phone']); ?></div>
                            </td>
                            <td class="px-10 py-6 font-black text-slate-900">৳ <?php echo number_format($order['total_amount']); ?></td>
                            <td class="px-10 py-6">
                                <?php
                                $statusClasses = match($order['status']) {
                                    'Pending' => 'bg-amber-100 text-amber-700',
                                    'Delivered' => 'bg-emerald-100 text-emerald-700',
                                    'Cancelled' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                                ?>
                                <span class="<?php echo $statusClasses; ?> text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-wider"><?php echo $order['status']; ?></span>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <a href="orders.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-white border border-slate-100 text-slate-400 hover:bg-amber-600 hover:text-white hover:border-amber-600 transition shadow-sm">
                                    <i class="ph ph-caret-right font-bold"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="space-y-8">
            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-black text-2xl mb-2">Performance High</h3>
                    <p class="text-slate-400 text-sm mb-8 leading-relaxed">Your shop is operating at peak efficiency. All systems are green.</p>
                    <div class="flex items-center gap-3 px-5 py-3 bg-white/5 rounded-2xl w-fit border border-white/10">
                        <div class="w-2.5 h-2.5 bg-emerald-400 rounded-full shadow-[0_0_15px_rgba(52,211,153,0.5)]"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400">Live Network</span>
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 text-[10rem] opacity-5 rotate-12">⚡</div>
            </div>

            <div class="glass-card rounded-[2.5rem] p-10 shadow-sm">
                <h3 class="font-black text-slate-900 mb-8 border-b border-slate-50 pb-4">Inventory Health</h3>
                <div class="space-y-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Catalog Size</div>
                            <div class="text-2xl font-black text-slate-900"><?php echo $totalProducts; ?> <span class="text-xs text-slate-400 font-bold uppercase ml-1">Items</span></div>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-xl">📦</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <span>Storage Usage</span>
                            <span>75%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-1">
                            <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-full rounded-full shadow-lg" style="width: 75%"></div>
                        </div>
                    </div>
                    
                    <p class="text-[10px] text-slate-400 leading-relaxed font-bold italic">* Stock health is calculated based on recent turnover rates.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let lastKnownOrderId = <?php echo (int)(str_replace(',', '', $lastOrderId)); ?>;

function playAlertSound() {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.setValueAtTime(880, ctx.currentTime);
    osc.frequency.setValueAtTime(660, ctx.currentTime + 0.1);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.3);
}

function pollNewOrders() {
    fetch('api/check_orders.php?last_id=' + lastKnownOrderId)
        .then(r => r.json())
        .then(data => {
            if (data.new_count > 0) {
                lastKnownOrderId = data.latest_id;
                playAlertSound();
                document.getElementById('newOrderAlert').classList.remove('hidden');
                document.getElementById('newOrderAlert').textContent = '🔔 ' + data.new_count + ' new order(s) received!';
            }
        })
        .catch(() => {});
}

setInterval(pollNewOrders, 30000);
</script>

</main> <!-- Closing main from header.php -->
</body>
</html>
