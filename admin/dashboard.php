<?php
$adminTitle = 'ড্যাশবোর্ড';
include_once __DIR__ . '/includes/header.php';

// KPI Stats
$totalRevenue = 0; $todayRevenue = 0; $totalOrders = 0; $pendingCount = 0;
$totalProducts = 0; $totalLeads = 0;

try {
    $totalRevenue   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
    $todayRevenue   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status != 'Cancelled'")->fetchColumn();
    $totalOrders    = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingCount   = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();
    $totalProducts  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalLeads     = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
} catch(Exception $e) {}

// Last 7 days chart data
$chartLabels = []; $chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $chartLabels[] = $label;
    try {
        $r = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=? AND status!='Cancelled'");
        $r->execute([$date]);
        $chartData[] = (int)$r->fetchColumn();
    } catch(Exception $e) { $chartData[] = 0; }
}

// Orders by status for donut
$statusData = ['Pending'=>0,'Processing'=>0,'Delivered'=>0,'Cancelled'=>0];
try {
    $rows = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
    foreach ($rows as $row) {
        if (isset($statusData[$row['status']])) $statusData[$row['status']] = (int)$row['cnt'];
    }
} catch(Exception $e) {}

// Recent orders
$recentOrders = [];
try {
    $recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch(Exception $e) {}

// Top products by order count
$topProducts = [];
try {
    $topProducts = $pdo->query("SELECT p.name, p.image, COUNT(oi.id) as order_count, SUM(oi.price * oi.quantity) as revenue FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY order_count DESC LIMIT 5")->fetchAll();
} catch(Exception $e) {}
?>

<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1.25rem; margin-bottom:1.75rem;">
    <!-- KPI 1 -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(98, 157, 37, 0.1);">🔥</div>
        <div class="kpi-value">৳ <?php echo number_format($totalRevenue); ?></div>
        <div class="kpi-label">মোট আয়</div>
        <div class="kpi-trend neutral">সর্বমোট (বাতিল ব্যতীত)</div>
        <div class="kpi-bg-accent" style="background:#629d25;"></div>
    </div>
    <!-- KPI 2 -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(16,185,129,0.1);">📈</div>
        <div class="kpi-value">৳ <?php echo number_format($todayRevenue); ?></div>
        <div class="kpi-label">আজকের আয়</div>
        <div class="kpi-trend up">↑ আজকের লাইভ তথ্য</div>
        <div class="kpi-bg-accent" style="background:#10b981;"></div>
    </div>
    <!-- KPI 3 -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(59,130,246,0.1);">📦</div>
        <div class="kpi-value"><?php echo number_format($totalOrders); ?></div>
        <div class="kpi-label">মোট অর্ডার</div>
        <div class="kpi-trend neutral"><?php echo $pendingCount; ?> কাজ বাকি</div>
        <div class="kpi-bg-accent" style="background:#3b82f6;"></div>
    </div>
    <!-- KPI 4 -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(168,85,247,0.1);">🏷️</div>
        <div class="kpi-value"><?php echo number_format($totalProducts); ?></div>
        <div class="kpi-label">পণ্যের তালিকা</div>
        <div class="kpi-trend neutral"><?php echo $totalLeads; ?> লিড পাওয়া গেছে</div>
        <div class="kpi-bg-accent" style="background:#a855f7;"></div>
    </div>
</div>

<!-- Charts Row -->
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.25rem; margin-bottom:1.75rem;">
    <!-- Revenue Chart -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">গত ৭ দিনের আয়</span>
            <span style="font-size:0.75rem; color:#9ca3af; font-weight:600;">৳ চার্ট</span>
        </div>
        <div class="admin-card-body chart-wrap" style="height:260px;">
            <canvas id="revenueChart" style="max-height:220px;"></canvas>
        </div>
    </div>
    <!-- Orders Donut -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">অর্ডারের অবস্থা</span>
        </div>
        <div class="admin-card-body chart-wrap" style="height:260px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <canvas id="statusChart" style="max-height:180px; max-width:180px;"></canvas>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-top:1rem; width:100%;">
                <?php
                $statusColors = ['Pending'=>'#f59e0b','Processing'=>'#3b82f6','Delivered'=>'#10b981','Cancelled'=>'#629d25'];
                foreach($statusData as $s => $c):
                ?>
                <div style="display:flex; align-items:center; gap:0.4rem;">
                    <span style="width:8px; height:8px; border-radius:2px; background:<?php echo $statusColors[$s]; ?>; flex-shrink:0;"></span>
                    <span style="font-size:0.6875rem; font-weight:600; color:#6b7280;"><?php 
                        $bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
                        echo $bnStatus[$s] ?? $s; 
                    ?></span>
                    <span style="font-size:0.6875rem; font-weight:800; color:#111827; margin-left:auto;"><?php echo $c; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent Orders + Top Products -->
<div style="display:grid; grid-template-columns: 3fr 2fr; gap:1.25rem;">
    <!-- Recent Orders -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">সাম্প্রতিক অর্ডার</span>
            <a href="orders.php" class="btn btn-ghost" style="font-size:0.75rem; padding:0.375rem 0.875rem;">সব দেখুন →</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>অর্ডার</th>
                        <th>কাস্টমার</th>
                        <th>পরিমাণ</th>
                        <th>অবস্থা</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:2rem; color:#9ca3af;">কোন অর্ডার নেই।</td></tr>
                    <?php endif; ?>
                    <?php foreach($recentOrders as $o):
                        $statusClass = strtolower($o['status']);
                    ?>
                    <tr>
                        <td><span style="font-weight:800; color:#111827;">#<?php echo str_pad($o['id'],4,'0',STR_PAD_LEFT); ?></span></td>
                        <td>
                            <div style="font-weight:700; color:#111827;"><?php echo htmlspecialchars($o['customer_name']); ?></div>
                            <div style="font-size:0.75rem; color:#9ca3af;"><?php echo htmlspecialchars($o['phone']); ?></div>
                        </td>
                        <td style="font-weight:800; color:#111827;">৳ <?php echo number_format($o['total_amount']); ?></td>
                        <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php 
                            $bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
                            echo $bnStatus[$o['status']] ?? $o['status']; 
                        ?></span></td>
                        <td style="color:#9ca3af; font-size:0.8125rem;"><?php echo date('d M', strtotime($o['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Products -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">সেরা পণ্য</span>
            <a href="products.php" class="btn btn-ghost" style="font-size:0.75rem; padding:0.375rem 0.875rem;">পরিচালনা →</a>
        </div>
        <div class="admin-card-body" style="padding:0.75rem 1rem;">
            <?php if (empty($topProducts)): ?>
            <p style="color:#9ca3af; text-align:center; padding:2rem 0; font-size:0.875rem;">কোন তথ্য পাওয়া যায়নি।</p>
            <?php endif; ?>
            <?php foreach($topProducts as $i => $tp): 
                $img = !empty($tp['image']) ? '../' . $tp['image'] : null;
            ?>
            <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 0; border-bottom:1px solid #f3f4f6;">
                <span style="font-size:0.75rem; font-weight:900; color:#d1d5db; width:20px; flex-shrink:0;"><?php echo $i+1; ?></span>
                <?php if ($img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" 
                         style="width:36px; height:36px; border-radius:8px; object-fit: cover; background:#f8fafc;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <div class="thumbnail-fallback" style="display: <?php echo $img ? 'none' : 'flex'; ?>; width:36px; height:36px; border-radius:8px; background:linear-gradient(135deg, #f8fafc, #f1f5f9); border:1px solid #e2e8f0; align-items:center; justify-center; flex-shrink:0;">
                    <i class="ph ph-package text-slate-400 text-lg" style="margin:auto;"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.8125rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($tp['name']); ?></div>
                    <div style="font-size:0.6875rem; color:#9ca3af; margin-top:2px;"><?php echo $tp['order_count']; ?> অর্ডারের সংখ্যা</div>
                </div>
                <div style="font-size:0.8125rem; font-weight:800; color:#111827; white-space:nowrap;">৳ <?php echo number_format($tp['revenue']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const chartDefaults = {
    font: { family: 'Inter', weight: '600' },
    color: '#9ca3af'
};
Chart.defaults.font.family = 'Inter';
Chart.defaults.color = '#9ca3af';

// Revenue Line Chart
const rCtx = document.getElementById('revenueChart').getContext('2d');
const gradient = rCtx.createLinearGradient(0, 0, 0, 220);
gradient.addColorStop(0, 'rgba(98, 157, 37, 0.2)');
gradient.addColorStop(1, 'rgba(98, 157, 37, 0)');

new Chart(rCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'আয় (৳)',
            data: <?php echo json_encode($chartData); ?>,
            borderColor: '#629d25',
            backgroundColor: gradient,
            borderWidth: 2.5,
            pointBackgroundColor: '#629d25',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#f9fafb',
                bodyColor: '#9ca3af',
                padding: 12,
                cornerRadius: 8,
                callbacks: { label: ctx => ' ৳ ' + ctx.raw.toLocaleString() }
            }
        },
        scales: {
            x: { grid: { display: false }, border:{ display:false }, ticks: { font:{weight:'700'} } },
            y: {
                grid: { color: '#f3f4f6', borderDash:[4,4] },
                border: { display:false, dash:[4,4] },
                ticks: { 
                    font: {weight:'700'},
                    callback: v => '৳' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                }
            }
        }
    }
});

// Status Donut Chart
const sCtx = document.getElementById('statusChart').getContext('2d');
new Chart(sCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending','Processing','Delivered','Cancelled'],
        datasets: [{
            data: [
                <?php echo $statusData['Pending']; ?>,
                <?php echo $statusData['Processing']; ?>,
                <?php echo $statusData['Delivered']; ?>,
                <?php echo $statusData['Cancelled']; ?>
            ],
            backgroundColor: ['#f59e0b','#3b82f6','#10b981','#629d25'],
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        cutout: '72%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#f9fafb',
                bodyColor: '#9ca3af',
                padding: 10, cornerRadius: 8
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
