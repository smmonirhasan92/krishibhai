<?php
/**
 * Zaman Kitchens - Search Results Page
 */
require_once __DIR__ . '/includes/db.php';

$query = $_GET['q'] ?? '';
$products = [];

if (!empty($query)) {
    try {
        $searchTerm = "%$query%";
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC");
        $stmt->execute([$searchTerm, $searchTerm]);
        $products = $stmt->fetchAll();
    } catch(Exception $e) {}
}

$pageTitle = "Search Results for '" . htmlspecialchars($query) . "'";
include_once __DIR__ . '/includes/header.php';
?>

<!-- Search Hero Section -->
<div class="relative overflow-hidden bg-slate-900 border-b border-white/5">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-amber-900 opacity-50"></div>
    <div class="relative z-10 container mx-auto px-4 py-16 text-center">
        <h1 class="text-3xl md:text-5xl font-black text-white mb-4 italic tracking-tight">
            Search Results for <span class="text-amber-500">"<?php echo htmlspecialchars($query); ?>"</span>
        </h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">
            <?php echo count($products); ?> matching items found
        </p>
    </div>
</div>

<!-- Results Grid -->
<section class="py-16 bg-slate-50 min-h-[60vh]">
    <div class="container mx-auto px-4">
        <?php if (empty($products)): ?>
        <div class="text-center py-20 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 max-w-2xl mx-auto">
            <div class="text-8xl mb-8">🔍</div>
            <h2 class="text-2xl font-black text-slate-900 mb-4">No matches found</h2>
            <p class="text-slate-500 mb-10 leading-relaxed px-10">We couldn't find anything matching your search. Try different keywords or browse our categories.</p>
            <a href="<?php echo SITE_URL; ?>" class="inline-block bg-slate-900 text-white font-black px-10 py-4 rounded-2xl hover:bg-slate-800 transition shadow-lg">Back to Home</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
            <?php foreach($products as $p): ?>
            <div class="product-item">
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
