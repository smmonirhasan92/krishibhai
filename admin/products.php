<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

// ===== AJAX: Quick Price Update =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_price') {
    header('Content-Type: application/json');
    try {
        $pdo->prepare("UPDATE products SET price = ? WHERE id = ?")
            ->execute([(float)$_POST['price'], (int)$_POST['id']]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ===== AJAX: Toggle Featured =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_featured') {
    header('Content-Type: application/json');
    try {
        $pdo->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?")
            ->execute([(int)$_POST['id']]);
        $p = $pdo->prepare("SELECT is_featured FROM products WHERE id = ?");
        $p->execute([(int)$_POST['id']]);
        $row = $p->fetch();
        echo json_encode(['success' => true, 'is_featured' => (bool)$row['is_featured']]);
    } catch (Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ===== Bulk Delete =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    if (is_array($ids) && !empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)")->execute($ids);
    }
    header("Location: products.php"); exit();
}

// ===== Single Delete =====
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: products.php"); exit();
}

$adminTitle = 'পণ্য ব্যবস্থাপনা';
$adminTopbarAction = '<a href="product-edit.php" class="topbar-btn btn-primary"><i class="ph ph-plus-circle"></i> পণ্য যোগ করুন</a>';
include_once __DIR__ . '/includes/header.php';

// ===== Fetch Categories for Filter =====
$allCategories = [];
try {
    $allCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch(Exception $e) {}

// ===== Build Query with Filters =====
$search     = trim($_GET['q'] ?? '');
$catFilter  = $_GET['cat'] ?? '';
$stockFilter= $_GET['stock'] ?? '';

$sql    = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catFilter) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catFilter;
}
if ($stockFilter === 'in') {
    $sql .= " AND p.stock_status = 'In Stock'";
} elseif ($stockFilter === 'out') {
    $sql .= " AND p.stock_status != 'In Stock'";
} elseif ($stockFilter === 'featured') {
    $sql .= " AND p.is_featured = 1";
}

$sql .= " ORDER BY p.created_at DESC";

$products = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch(Exception $e) {}
?>

<!-- ===== TOOLBAR ===== -->
<div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
    <form method="GET" style="display:contents;">
        <!-- Search -->
        <div style="position:relative; flex:1; min-width:200px;">
            <i class="ph ph-magnifying-glass" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:1rem;"></i>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="পণ্যের নাম বা বারকোড খুঁজুন..."
                   class="admin-input" style="padding-left:2.25rem; margin:0;">
        </div>
        <!-- Category Filter -->
        <select name="cat" class="admin-input" style="width:auto; min-width:160px; margin:0;">
            <option value="">সব ক্যাটাগরি</option>
            <?php foreach($allCategories as $ac): ?>
            <option value="<?php echo $ac['id']; ?>" <?php echo $catFilter == $ac['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($ac['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <!-- Stock Filter -->
        <select name="stock" class="admin-input" style="width:auto; min-width:150px; margin:0;">
            <option value="">সব অবস্থা</option>
            <option value="in"       <?php echo $stockFilter==='in'       ? 'selected' : ''; ?>>স্টক আছে</option>
            <option value="out"      <?php echo $stockFilter==='out'      ? 'selected' : ''; ?>>স্টক নেই</option>
            <option value="featured" <?php echo $stockFilter==='featured' ? 'selected' : ''; ?>>⭐ সেরা পণ্য</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem; white-space:nowrap;">🔍 ফিল্টার</button>
        <?php if ($search || $catFilter || $stockFilter): ?>
        <a href="products.php" class="btn btn-ghost" style="padding:0.5rem 0.875rem; white-space:nowrap;">✕ রিসেট</a>
        <?php endif; ?>
    </form>
</div>

<!-- ===== BULK ACTION BAR (hidden by default) ===== -->
<div id="bulk-bar" style="display:none; align-items:center; gap:1rem; background:#fffbeb; border:1.5px solid #fcd34d; border-radius:12px; padding:0.75rem 1.25rem; margin-bottom:1rem;">
    <span id="bulk-count" style="font-weight:800; color:#92400e;"></span>
    <span style="font-size:0.875rem; color:#78350f;">পণ্য নির্বাচিত</span>
    <button onclick="bulkDelete()" class="btn btn-danger" style="padding:0.4rem 1rem; font-size:0.8125rem;">
        <i class="ph ph-trash"></i> নির্বাচিত মুছুন
    </button>
    <button onclick="clearSelection()" class="btn btn-ghost" style="padding:0.4rem 0.875rem; font-size:0.8125rem;">বাতিল</button>
</div>

<!-- ===== PRODUCTS TABLE ===== -->
<div class="admin-card">
    <div class="admin-card-header">
        <span class="admin-card-title">সকল পণ্য</span>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:0.75rem; color:#9ca3af; font-weight:700;"><?php echo count($products); ?> টি পণ্য</span>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="select-all" style="width:16px; height:16px; cursor:pointer;" title="সব নির্বাচন">
                    </th>
                    <th>পণ্য</th>
                    <th>ক্যাটাগরি</th>
                    <th>মূল্য / MRP</th>
                    <th>স্টক</th>
                    <th>অবস্থা</th>
                    <th style="text-align:right;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:#9ca3af;">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;">📦</div>
                    <?php echo $search || $catFilter || $stockFilter ? 'কোন পণ্য পাওয়া যায়নি। ফিল্টার পরিবর্তন করুন।' : 'কোন পণ্য নেই। প্রথম পণ্যটি যোগ করুন!'; ?>
                </td></tr>
                <?php endif; ?>
                <?php foreach($products as $i => $p):
                    $img = !empty($p['image']) ? '../' . $p['image'] : (!empty($p['main_image']) ? '../' . $p['main_image'] : null);
                    if ($img) $img = str_replace('../..', '..', $img);
                    $stockBg = ($p['stock_status'] ?? 'In Stock') === 'In Stock' ? 'rgba(98,157,37,0.1)' : 'rgba(239,68,68,0.1)';
                    $stockColor = ($p['stock_status'] ?? 'In Stock') === 'In Stock' ? '#629d25' : '#dc2626';
                ?>
                <tr data-id="<?php echo $p['id']; ?>">
                    <td>
                        <input type="checkbox" class="row-check" value="<?php echo $p['id']; ?>" style="width:16px; height:16px; cursor:pointer;">
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <div style="position:relative; width:60px; height:60px; flex-shrink:0;">
                                <?php if ($img): ?>
                                    <img src="<?php echo htmlspecialchars($img); ?>"
                                         style="width:100%; height:100%; border-radius:12px; object-fit:cover; border:1.5px solid #f1f5f9; box-shadow:0 2px 8px rgba(0,0,0,0.06);"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div style="display:<?php echo $img ? 'none' : 'flex'; ?>; width:100%; height:100%; border-radius:12px; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0; align-items:center; justify-content:center;">
                                    <i class="ph ph-image-square" style="font-size:1.5rem; color:#86efac;"></i>
                                </div>
                            </div>
                            <div>
                                <div style="font-weight:800; color:#111827; font-size:0.9rem; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </div>
                                <div style="font-size:0.7rem; color:#9ca3af; font-weight:600; margin-top:2px;">
                                    ID #<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?>
                                    <?php if (!empty($p['barcode'])): ?>
                                    · <span style="font-family:monospace;"><?php echo htmlspecialchars($p['barcode']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:0.75rem; font-weight:700; color:#4b5563; background:#f3f4f6; padding:0.25rem 0.625rem; border-radius:20px;">
                            <?php echo htmlspecialchars($p['category_name'] ?: 'ক্যাটাগরি নেই'); ?>
                        </span>
                    </td>
                    <td>
                        <!-- Quick Price Edit -->
                        <div style="display:flex; align-items:center; gap:0.25rem;">
                            <span style="color:#9ca3af; font-size:0.75rem;">৳</span>
                            <span class="quick-price"
                                  data-id="<?php echo $p['id']; ?>"
                                  contenteditable="true"
                                  title="ক্লিক করে মূল্য পরিবর্তন করুন"
                                  style="font-weight:800; color:#111827; min-width:50px; border-bottom:1.5px dashed #d1d5db; outline:none; cursor:text; padding:0 2px;">
                                <?php echo number_format($p['price']); ?>
                            </span>
                        </div>
                        <?php if (!empty($p['old_price']) && $p['old_price'] > $p['price']): ?>
                        <div style="font-size:0.7rem; color:#ef4444; text-decoration:line-through; font-weight:600;">৳<?php echo number_format($p['old_price']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:0.75rem; font-weight:700; color:<?php echo $stockColor; ?>; background:<?php echo $stockBg; ?>; padding:0.25rem 0.625rem; border-radius:20px;">
                            <?php echo ($p['stock_status'] ?? 'In Stock') === 'In Stock' ? '✓ আছে' : '✗ নেই'; ?>
                        </span>
                        <?php if (!empty($p['stock_qty'])): ?>
                        <div style="font-size:0.65rem; color:#9ca3af; font-weight:600; margin-top:2px;"><?php echo $p['stock_qty']; ?> টি</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Toggle Featured -->
                        <button onclick="toggleFeatured(<?php echo $p['id']; ?>, this)"
                                class="toggle-featured-btn"
                                data-featured="<?php echo !empty($p['is_featured']) ? '1' : '0'; ?>"
                                style="border:none; background:none; cursor:pointer; font-size:1.3rem; line-height:1; padding:0.25rem; border-radius:8px; transition:transform 0.2s;"
                                title="সেরা পণ্য হিসেবে নির্বাচন">
                            <?php echo !empty($p['is_featured']) ? '⭐' : '☆'; ?>
                        </button>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                            <a href="barcode-print.php?id=<?php echo $p['id']; ?>" class="btn btn-ghost" style="padding:0.4rem; width:32px; height:32px; justify-content:center;" title="বারকোড প্রিন্ট">
                                <i class="ph ph-barcode" style="font-size:1rem;"></i>
                            </a>
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-ghost" style="padding:0.4rem; width:32px; height:32px; justify-content:center;" title="এডিট">
                                <i class="ph ph-note-pencil" style="font-size:1rem;"></i>
                            </a>
                            <button onclick="if(confirm('এই পণ্যটি কি মুছে ফেলতে চান?')) window.location.href='products.php?delete=<?php echo $p['id']; ?>'"
                                    class="btn btn-danger" style="padding:0.4rem; width:32px; height:32px; justify-content:center;" title="মুছুন">
                                <i class="ph ph-trash" style="font-size:1rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Hidden Bulk Delete Form -->
<form id="bulk-delete-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="bulk_delete">
    <input type="hidden" name="ids" id="bulk-ids">
</form>

<!-- Toast -->
<div id="toast" style="display:none; position:fixed; bottom:1.5rem; right:1.5rem; background:#111827; color:#f0fdf4; font-size:0.8rem; font-weight:700; padding:0.65rem 1.25rem; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); z-index:9999; transition:opacity 0.3s;"></div>

<script>
// ===== BULK SELECT =====
const selectAll = document.getElementById('select-all');
const bulkBar   = document.getElementById('bulk-bar');
const bulkCount = document.getElementById('bulk-count');

function getChecked() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
}

function updateBulkBar() {
    const checked = getChecked();
    if (checked.length > 0) {
        bulkBar.style.display = 'flex';
        bulkCount.textContent = checked.length + ' টি';
    } else {
        bulkBar.style.display = 'none';
    }
}

selectAll.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
    selectAll.checked = false;
    updateBulkBar();
}

function bulkDelete() {
    const ids = getChecked();
    if (ids.length === 0) return;
    if (!confirm(ids.length + ' টি পণ্য মুছে ফেলতে চান? এটি পূর্বাবস্থায় ফেরানো যাবে না।')) return;
    document.getElementById('bulk-ids').value = JSON.stringify(ids);
    document.getElementById('bulk-delete-form').submit();
}

// ===== QUICK PRICE EDIT =====
let priceTimer;
document.querySelectorAll('.quick-price').forEach(el => {
    const originalValue = el.textContent.replace(/,/g,'').trim();

    el.addEventListener('focus', function() {
        this.textContent = this.textContent.replace(/,/g,'').trim();
    });

    el.addEventListener('blur', function() {
        const newPrice = parseFloat(this.textContent.replace(/,/g,'').trim());
        const id = this.dataset.id;
        if (isNaN(newPrice) || newPrice <= 0) {
            this.textContent = originalValue;
            return;
        }
        fetch('products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=quick_price&id=' + id + '&price=' + newPrice
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('✅ মূল্য আপডেট হয়েছে!');
                this.textContent = parseInt(newPrice).toLocaleString('en');
            }
        });
    });

    el.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); this.blur(); }
        if (e.key === 'Escape') { this.textContent = originalValue; this.blur(); }
    });
});

// ===== TOGGLE FEATURED =====
function toggleFeatured(id, btn) {
    fetch('products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle_featured&id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = data.is_featured ? '⭐' : '☆';
            btn.dataset.featured = data.is_featured ? '1' : '0';
            btn.style.transform = 'scale(1.4)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
            showToast(data.is_featured ? '⭐ সেরা পণ্য করা হয়েছে!' : '☆ সেরা পণ্য থেকে সরানো হয়েছে');
        }
    });
}

// ===== TOAST =====
let toastTimer;
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display = 'block';
    t.style.opacity = '1';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        t.style.opacity = '0';
        setTimeout(() => t.style.display = 'none', 300);
    }, 2500);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
