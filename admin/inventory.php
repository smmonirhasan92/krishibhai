<?php
/**
 * Krishibhai - Inventory Management
 * Quick stock tracking and bulk updates.
 */
$adminTitle = 'ইনভেন্টরি কন্ট্রোল';
include_once __DIR__ . '/includes/header.php';

$products = [];
try {
    $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.stock_status DESC, p.name ASC")->fetchAll();
} catch(Exception $e) {}

// Stats
$total = count($products);
$inStock = 0;
$outOfStock = 0;
foreach($products as $p) {
    if($p['stock_qty'] > 0) $inStock++;
    else $outOfStock++;
}
?>

<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">ইনভেন্টরি ম্যানেজমেন্ট</h1>
            <p class="text-sm text-slate-500">পণ্যের প্রাপ্যতা রিয়েল-টাইমে পর্যবেক্ষণ এবং আপডেট করুন।</p>
        </div>
        <div class="flex gap-3">
            <a href="barcode-scanner.php" class="btn btn-primary">
                <i class="ph ph-barcode"></i>
                স্ক্যানার চালু করুন
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="kpi-card">
            <div class="kpi-icon bg-slate-100 text-slate-600"><i class="ph ph-package"></i></div>
            <div class="kpi-value"><?php echo $total; ?></div>
            <div class="kpi-label">মোট পণ্য</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon bg-emerald-100 text-emerald-600"><i class="ph ph-check-circle"></i></div>
            <div class="kpi-value"><?php echo $inStock; ?></div>
            <div class="kpi-label">স্টকে আছে</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon bg-rose-100 text-rose-600"><i class="ph ph-warning-circle"></i></div>
            <div class="kpi-value"><?php echo $outOfStock; ?></div>
            <div class="kpi-label">স্টকে নেই</div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">স্টক প্রাপ্যতা তালিকা</span>
            <div class="flex gap-4">
                <input type="text" id="inventory-search" onkeyup="filterInventory()" placeholder="পণ্য খুঁজুন..." class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:border-green-500">
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table" id="inventory-table">
                <thead>
                    <tr>
                        <th>পণ্য</th>
                        <th>ক্যাটাগরি</th>
                        <th>বিক্রয় মূল্য</th>
                        <th>পরিমাণ</th>
                        <th>অবস্থা</th>
                        <th style="text-align:right;">কুইক অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr class="inventory-row">
                        <td>
                            <div style="font-weight:700; color:#111827;"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div style="font-size:10px; color:#9ca3af; font-family:monospace;">বারকোড: <?php echo $p['barcode'] ?: 'নেই'; ?></div>
                        </td>
                        <td><span style="font-size:11px; font-weight:600; color:#6b7280;"><?php echo htmlspecialchars($p['category_name'] ?: 'সাধারণ'); ?></span></td>
                        <td style="font-weight:700;">৳ <?php echo number_format($p['price']); ?></td>
                        <td><span style="font-weight:700; color:#4b5563;"><?php echo $p['stock_qty']; ?></span></td>
                        <td>
                            <select onchange="updateStock(this, <?php echo $p['id']; ?>)" 
                                    class="text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border-none outline-none cursor-pointer <?php echo ($p['stock_status'] == 'In Stock') ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                <option value="In Stock" <?php echo ($p['stock_status'] == 'In Stock') ? 'selected' : ''; ?>>স্টকে আছে</option>
                                <option value="Out of Stock" <?php echo ($p['stock_status'] == 'Out of Stock') ? 'selected' : ''; ?>>স্টকে নেই</option>
                            </select>
                        </td>
                        <td style="text-align:right;">
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="text-slate-400 hover:text-green-500 transition">
                                <i class="ph ph-pencil-simple" style="font-size:1.25rem;"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterInventory() {
    const input = document.getElementById('inventory-search');
    const filter = input.value.toUpperCase();
    const rows = document.querySelectorAll('.inventory-row');
    
    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function updateStock(select, id) {
    const status = select.value;
    select.disabled = true;
    
    // Use the same API as scanner for consistency
    fetch('api-update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, price: 0, stock_status: status }) // price 0 means keep current
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            select.className = `text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border-none outline-none cursor-pointer ${status === 'In Stock' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}`;
        }
        select.disabled = false;
    })
    .catch(() => { select.disabled = false; alert('আপডেট করতে ব্যর্থ হয়েছে।'); });
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
