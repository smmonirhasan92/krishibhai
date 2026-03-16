<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: products.php"); exit();
}

$adminTitle = 'পণ্য ব্যবস্থাপনা';
$adminTopbarAction = '<a href="product-edit.php" class="topbar-btn btn-primary"><i class="ph ph-plus-circle"></i> পণ্য যোগ করুন</a>';
include_once __DIR__ . '/includes/header.php';

$products = [];
try {
    $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
} catch(Exception $e) {}
?>

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
                    <th>#</th>
                    <th>পণ্য</th>
                    <th>ক্যাটাগরি</th>
                    <th>মূল্য</th>
                    <th>বারকোড</th>
                    <th>অবস্থা</th>
                    <th style="text-align:right;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:#9ca3af;">কোন পণ্য পাওয়া যায়নি।</td></tr>
                <?php endif; ?>
                <?php foreach($products as $i => $p): 
                    $img = !empty($p['image']) ? '../' . $p['image'] : (!empty($p['main_image']) ? '../' . $p['main_image'] : null);
    // Remove duplicate '../' if already present
    if ($img) $img = str_replace('../..', '..', $img);
                ?>
                <tr>
                    <td style="color:#d1d5db; font-weight:800;"><?php echo $i+1; ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <!-- Improved Thumbnail Preview -->
                            <div style="position:relative; width:64px; height:64px; flex-shrink:0;">
                                <?php if ($img): ?>
                                    <img src="<?php echo htmlspecialchars($img); ?>" 
                                         style="width:100%; height:100%; border-radius:14px; object-fit: cover; background:#f8fafc; border:1.5px solid #f1f5f9; box-shadow:0 4px 12px -2px rgba(0,0,0,0.08); transition:transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="thumbnail-fallback" style="display: <?php echo $img ? 'none' : 'flex'; ?>; width:100%; height:100%; border-radius:14px; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0; align-items:center; justify-content:center; flex-direction:column; gap:1px; box-shadow:inset 0 2px 4px rgba(0,0,0,0.02);">
                                    <i class="ph ph-image-square text-green-400 text-2xl" style="margin-bottom:-2px;"></i>
                                    <span style="font-size:7px; font-weight:900; color:#16a34a; text-transform:uppercase; letter-spacing:0.08em;">ছবি নেই</span>
                                </div>
                            </div>
                            <!-- Product Details -->
                            <div style="min-width:0;">
                                <div style="font-weight:800; color:#111827; font-size:0.9375rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px; line-height:1.2;"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div style="font-size:0.75rem; color:#9ca3af; font-weight:600; margin-top:4px;">ID: #<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-size:0.75rem; font-weight:700; color:#4b5563; background:#f3f4f6; padding:0.25rem 0.625rem; border-radius:20px;"><?php echo htmlspecialchars($p['category_name'] ?: 'ক্যাটাগরি নেই'); ?></span></td>
                    <td style="font-weight:800; color:#111827;">৳ <?php echo number_format($p['price']); ?></td>
                    <td>
                        <?php if(!empty($p['barcode'])): ?>
                            <div style="font-family:'Courier New', monospace; font-size:10px; font-weight:700; background:#f9fafb; border:1px solid #e5e7eb; padding:2px 6px; border-radius:4px; display:inline-block; letter-spacing:1px;"><?php echo htmlspecialchars($p['barcode']); ?></div>
                        <?php else: ?>
                            <span style="color:#9ca3af; font-size:11px; font-style:italic;">বারকোড নেই</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($p['is_featured'])): ?>
                            <span class="status-badge" style="background:rgba(98, 157, 37, 0.1); color:#629d25;">⭐ সেরা পণ্য</span>
                        <?php else: ?>
                            <span class="status-badge" style="background:#f3f4f6; color:#9ca3af;">সাধারণ</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:0.5rem;">
                            <a href="barcode-print.php?id=<?php echo $p['id']; ?>" class="btn btn-ghost" style="padding:0.4rem; width:32px; height:32px; justify-content:center;" title="Print Barcode">
                                <i class="ph ph-barcode" style="font-size:1rem;"></i>
                            </a>
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-ghost" style="padding:0.4rem; width:32px; height:32px; justify-content:center;">
                                <i class="ph ph-note-pencil" style="font-size:1rem;"></i>
                            </a>
                             <button onclick="if(confirm('এই পণ্যটি কি মুছে ফেলতে চান?')) window.location.href='products.php?delete=<?php echo $p['id']; ?>'" class="btn btn-danger" style="padding:0.4rem; width:32px; height:32px; justify-content:center;">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
