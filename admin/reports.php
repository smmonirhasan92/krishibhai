<?php
$adminTitle = 'রিপোর্ট এবং অ্যানালিটিক্স';
include_once __DIR__ . '/includes/header.php';

// Monthly revenue for bar chart (12 months)
$monthlyLabels = []; $monthlyRevenue = []; $monthlyOrders = [];
for ($i = 11; $i >= 0; $i--) {
    $y = date('Y', strtotime("-$i months"));
    $m = date('m', strtotime("-$i months"));
    $monthlyLabels[] = date('M Y', strtotime("-$i months"));
    try {
        $r = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0), COUNT(*) FROM orders WHERE YEAR(created_at)=? AND MONTH(created_at)=? AND status!='Cancelled'");
        $r->execute([$y, $m]);
        $row = $r->fetch(PDO::FETCH_NUM);
        $monthlyRevenue[] = (int)$row[0];
        $monthlyOrders[]  = (int)$row[1];
    } catch(Exception $e) { $monthlyRevenue[] = 0; $monthlyOrders[] = 0; }
}

// Summary stats
$totalRevenue = array_sum($monthlyRevenue);
$totalOrders  = array_sum($monthlyOrders);
$avgOrder = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

// Category-wise revenue
$catRevenue = [];
try {
    $catRevenue = $pdo->query("SELECT c.name, COALESCE(SUM(oi.price * oi.quantity),0) as revenue, COUNT(DISTINCT o.id) as order_count 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id 
        LEFT JOIN order_items oi ON oi.product_id = p.id 
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'Cancelled'
        GROUP BY c.id ORDER BY revenue DESC LIMIT 6")->fetchAll();
} catch(Exception $e) {}

$maxCat = max(array_column($catRevenue, 'revenue') ?: [1]);
?>

<!-- Summary KPIs -->
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; margin-bottom:1.75rem;">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(98,157,37,0.1);">💰</div>
        <div class="kpi-value">৳ <?php echo number_format($totalRevenue); ?></div>
        <div class="kpi-label">গত ১২ মাসের আয়</div>
        <div class="kpi-bg-accent" style="background:#629d25;"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(59,130,246,0.1);">📦</div>
        <div class="kpi-value"><?php echo number_format($totalOrders); ?></div>
        <div class="kpi-label">মোট অর্ডার (১২ মাস)</div>
        <div class="kpi-bg-accent" style="background:#3b82f6;"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(16,185,129,0.1);">📊</div>
        <div class="kpi-value">৳ <?php echo number_format($avgOrder); ?></div>
        <div class="kpi-label">গড় অর্ডার মূল্য</div>
        <div class="kpi-bg-accent" style="background:#10b981;"></div>
    </div>
</div>

<!-- Monthly Revenue Bar Chart -->
<div class="admin-card" style="margin-bottom:1.75rem;">
    <div class="admin-card-header">
        <span class="admin-card-title">মাসিক আয় — গত ১২ মাস</span>
    </div>
    <div class="admin-card-body" style="height:300px; position:relative;">
        <canvas id="monthlyChart" style="max-height:260px;"></canvas>
    </div>
</div>

<!-- Orders Line + Category Revenue -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.75rem;">
    <!-- Orders per month -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">প্রতি মাসে অর্ডার</span>
        </div>
        <div class="admin-card-body" style="height:240px;">
            <canvas id="ordersChart" style="max-height:200px;"></canvas>
        </div>
    </div>

    <!-- Category Revenue -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">ক্যাটাগরি অনুযায়ী আয়</span>
        </div>
        <div class="admin-card-body" style="padding:0.5rem 1rem;">
            <?php foreach($catRevenue as $cat): 
                $pct = $maxCat > 0 ? round(($cat['revenue'] / $maxCat) * 100) : 0;
            ?>
            <div style="margin-bottom:1.1rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                    <span style="font-size:0.8125rem; font-weight:700; color:#374151;"><?php echo htmlspecialchars($cat['name']); ?></span>
                    <span style="font-size:0.8125rem; font-weight:800; color:#111827;">৳ <?php echo number_format($cat['revenue']); ?></span>
                </div>
                <div style="height:6px; border-radius:3px; background:#f3f4f6; overflow:hidden;">
                    <div style="height:100%; width:<?php echo $pct; ?>%; background:linear-gradient(90deg,#629d25,#4a7a1a); border-radius:3px; transition:width 0.8s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
Chart.defaults.font.family = 'Inter';
Chart.defaults.color = '#9ca3af';

// Monthly Revenue Bar
const mCtx = document.getElementById('monthlyChart').getContext('2d');
const mGrad = mCtx.createLinearGradient(0, 0, 0, 260);
mGrad.addColorStop(0, 'rgba(98,157,37,0.9)');
mGrad.addColorStop(1, 'rgba(74,122,26,0.7)');

new Chart(mCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($monthlyLabels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($monthlyRevenue); ?>,
            backgroundColor: mGrad,
            borderRadius: 6, borderSkipped: false
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827', titleColor:'#f9fafb', bodyColor:'#9ca3af',
                padding:12, cornerRadius:8,
                callbacks: { label: ctx => ' ৳ ' + ctx.raw.toLocaleString() }
            }
        },
        scales: {
            x: { grid:{display:false}, border:{display:false}, ticks:{font:{weight:'700'}} },
            y: {
                grid:{color:'#f3f4f6', borderDash:[4,4]}, border:{display:false, dash:[4,4]},
                ticks:{font:{weight:'700'}, callback: v => '৳'+(v>=1000?(v/1000).toFixed(0)+'k':v)}
            }
        }
    }
});

// Orders per month line
const oCtx = document.getElementById('ordersChart').getContext('2d');
const oGrad = oCtx.createLinearGradient(0,0,0,200);
oGrad.addColorStop(0,'rgba(59,130,246,0.2)');
oGrad.addColorStop(1,'rgba(59,130,246,0)');

new Chart(oCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthlyLabels); ?>,
        datasets: [{
            label: 'Orders',
            data: <?php echo json_encode($monthlyOrders); ?>,
            borderColor: '#3b82f6',
            backgroundColor: oGrad,
            borderWidth: 2.5,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4, pointHoverRadius: 6,
            tension: 0.4, fill: true
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor:'#111827', titleColor:'#f9fafb', bodyColor:'#9ca3af', padding:10, cornerRadius:8 }
        },
        scales: {
            x: { grid:{display:false}, border:{display:false}, ticks:{font:{weight:'700'}} },
            y: { grid:{color:'#f3f4f6', borderDash:[4,4]}, border:{display:false}, ticks:{font:{weight:'700'}} }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
