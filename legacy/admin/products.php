<?php
require_once __DIR__ . '/../includes/db.php';

$adminTitle = 'Manage Products';
include_once __DIR__ . '/includes/header.php';

$message = "";
$error = "";

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Product deleted successfully!";
    } catch(Exception $e) { $error = "Error: " . $e->getMessage(); }
}

// Fetch Products with category names
try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
} catch(Exception $e) { $products = []; }

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php if($message): ?> <div class="max-w-7xl mx-auto px-6 mt-6"><div class="bg-green-50 text-green-700 p-4 rounded-2xl font-bold border border-green-100">✅ <?php echo $message; ?></div></div> <?php endif; ?>
<?php if($error): ?> <div class="max-w-7xl mx-auto px-6 mt-6"><div class="bg-red-50 text-red-700 p-4 rounded-2xl font-bold border border-red-100">❌ <?php echo $error; ?></div></div> <?php endif; ?>

<div class="px-12 py-10">
    <div class="flex items-center justify-between mb-12">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Product Catalog</h1>
            <p class="text-slate-500 font-medium">Manage your professional-grade kitchen collection.</p>
        </div>
        <a href="product-edit.php" class="bg-amber-600 hover:bg-amber-700 text-white font-black px-8 py-3.5 rounded-2xl transition shadow-lg shadow-amber-200 flex items-center gap-2">
            <i class="ph ph-plus-circle text-lg"></i>
            Add New Product
        </a>
    </div>

    <?php if($message): ?> <div class="bg-emerald-50 text-emerald-700 p-6 rounded-2xl font-bold border border-emerald-100 mb-8 animate-fade-in">✅ <?php echo $message; ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="bg-rose-50 text-rose-700 p-6 rounded-2xl font-bold border border-rose-100 mb-8 animate-fade-in">❌ <?php echo $error; ?></div> <?php endif; ?>

    <div class="glass-card rounded-[2.5rem] shadow-sm overflow-hidden border border-white/40">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Product Information</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Category</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Sale Price</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Inventory</th>
                        <th class="px-10 py-5 text-right font-bold text-slate-400 uppercase tracking-widest text-[10px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($products)): ?>
                    <tr><td colspan="5" class="px-10 py-20 text-center">
                        <div class="text-5xl mb-4 opacity-10">🏷️</div>
                        <div class="text-slate-400 font-bold uppercase tracking-widest text-xs">No products in catalog</div>
                    </td></tr>
                    <?php endif; ?>
                    <?php foreach ($products as $p): ?>
                    <tr class="hover:bg-amber-50/20 transition group">
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-5">
                                <img src="../<?php echo $p['main_image']; ?>" class="w-16 h-16 rounded-2xl object-cover bg-white shadow-sm group-hover:scale-110 transition-transform duration-500">
                                <div>
                                    <div class="font-black text-slate-900 group-hover:text-amber-600 transition-colors"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><?php echo $p['slug']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6">
                            <span class="px-4 py-2 bg-slate-100 text-slate-600 text-[10px] font-black rounded-xl uppercase tracking-wider"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                        </td>
                        <td class="px-10 py-6 font-black text-slate-900 text-lg">৳ <?php echo number_format($p['price']); ?></td>
                        <td class="px-10 py-6">
                            <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider <?php echo $p['stock_status'] == 'In Stock' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                <?php echo $p['stock_status']; ?>
                            </span>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-blue-500 flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow-sm">
                                    <i class="ph ph-note-pencil text-lg"></i>
                                </a>
                                <a href="products.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Permanent delete?')" class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-rose-500 flex items-center justify-center hover:bg-rose-600 hover:text-white transition shadow-sm">
                                    <i class="ph ph-trash text-lg"></i>
                                </a>
                            </div>
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
