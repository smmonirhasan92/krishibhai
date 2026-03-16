<?php
/**
 * Zaman Kitchens - Category Page
 * Features: Category hero banner, product grid with pagination
 */
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$category = null;
$products = [];

if ($slug) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $category = $stmt->fetch();

        if ($category) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY is_featured DESC, created_at DESC");
            $stmt->execute([$category['id']]);
            $products = $stmt->fetchAll();
        }
    } catch(Exception $e) {}
}

if (!$category) {
    header("Location: " . SITE_URL);
    exit();
}

$pageTitle = htmlspecialchars($category['name']);
include_once __DIR__ . '/includes/header.php';
?>

<!-- Category Hero Banner -->
<div class="relative overflow-hidden" style="min-height: 220px; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
    <?php if (!empty($category['hero_image'])): ?>
    <img src="<?php echo htmlspecialchars($category['hero_image']); ?>" class="absolute inset-0 w-full h-full object-cover opacity-30" alt="">
    <?php endif; ?>
    <div class="relative z-10 container mx-auto px-4 py-16 flex items-center">
        <div>
            <nav class="text-gray-400 text-sm mb-2 flex items-center gap-2">
                <a href="<?php echo SITE_URL; ?>" class="hover:text-amber-400">Home</a>
                <span>/</span>
                <span class="text-white font-medium"><?php echo htmlspecialchars($category['name']); ?></span>
            </nav>
            <h1 class="text-4xl font-extrabold text-white mb-2"><?php echo htmlspecialchars($category['name']); ?></h1>
            <p class="text-gray-300"><?php echo count($products); ?> products available</p>
        </div>
    </div>
</div>

<!-- Products Grid -->
<section class="py-12">
    <div class="container mx-auto px-4">

        <?php if (empty($products)): ?>
        <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">No Products Yet</h2>
            <p class="text-gray-500">We're stocking up this category. Check back soon!</p>
            <a href="<?php echo SITE_URL; ?>" class="mt-6 inline-block bg-amber-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-amber-700 transition">Back to Home</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($products as $p): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
