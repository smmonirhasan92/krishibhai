<?php
/**
 * Zaman Kitchens - Product Detail Page
 * Features: Image zoom, description, suggested products
 */
require_once __DIR__ . '/includes/db.php';

$product = null;
$suggested = [];

// Clean URL: /product/slug or ?slug=
$slug = $_GET['slug'] ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($slug) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if ($product) {
            // Suggested products (same category)
            $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
            $stmt->execute([$product['category_id'], $product['id']]);
            $suggested = $stmt->fetchAll();

            // Fetch Price Rules
            $stmt = $pdo->prepare("SELECT * FROM price_rules WHERE product_id = ? AND is_active = 1 ORDER BY min_qty ASC");
            $stmt->execute([$product['id']]);
            $priceRules = $stmt->fetchAll();
        }
    } catch(Exception $e) {}
}

if (!$product) {
    header("Location: " . SITE_URL);
    exit();
}

$pageTitle = htmlspecialchars($product['name']);
$pageDesc  = substr(strip_tags($product['description'] ?? ''), 0, 160);
$gallery   = json_decode($product['gallery_images'] ?? '[]', true) ?: [];
array_unshift($gallery, $product['main_image'] ?? $product['image_url'] ?? '');
$gallery   = array_filter($gallery);

include_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">

    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-400 mb-6 flex gap-2 items-center">
        <a href="<?php echo SITE_URL; ?>" class="hover:text-green-600">হোম</a>
        <span>/</span>
        <a href="<?php echo SITE_URL; ?>/category/<?php echo $product['cat_slug']; ?>" class="hover:text-green-600"><?php echo htmlspecialchars($product['cat_name'] ?? ''); ?></a>
        <span>/</span>
        <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>

    <!-- Product Layout -->
    <div class="grid md:grid-cols-2 gap-10 mb-16">

        <!-- Image Gallery -->
        <div>
            <!-- Main Image with Zoom Effect -->
            <div class="relative bg-gray-50 rounded-2xl overflow-hidden aspect-square mb-3 border border-gray-100" id="mainImgWrap">
                <img id="mainImg"
                    src="<?php echo htmlspecialchars($gallery[0] ?? ''); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="w-full h-full object-contain transition duration-300 hover:scale-110 cursor-zoom-in"
                    onerror="this.src='https://placehold.co/600x600/f5f5f5/aaa?text=No+Image'">
            </div>
            <!-- Thumbnails -->
            <?php if (count($gallery) > 1): ?>
            <div class="flex gap-2 overflow-x-auto">
                <?php foreach($gallery as $i => $img): ?>
                <img src="<?php echo htmlspecialchars($img); ?>"
                    class="w-16 h-16 rounded-xl object-cover cursor-pointer border-2 <?php echo $i === 0 ? 'border-green-500' : 'border-transparent hover:border-green-300'; ?> flex-shrink-0 transition"
                    onclick="document.getElementById('mainImg').src=this.src; document.querySelectorAll('[onclick]').forEach(e=>e.classList.replace('border-green-500','border-transparent')); this.classList.replace('border-transparent','border-green-500');"
                    onerror="this.style.display='none'"
                    alt="">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="flex flex-col">
            <span class="text-xs font-bold text-green-600 uppercase tracking-widest mb-2"><?php echo htmlspecialchars($product['cat_name'] ?? ''); ?></span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-4 leading-tight"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-4xl font-extrabold text-green-600">৳ <?php echo number_format($product['price']); ?></span>
                <span class="text-sm <?php echo ($product['stock_status'] ?? 'In Stock') === 'In Stock' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'; ?> font-bold px-3 py-1 rounded-full">
                    <?php echo ($product['stock_status'] ?? 'In Stock') === 'In Stock' ? 'স্টক আছে' : 'স্টক নেই'; ?>
                </span>
            </div>

            <!-- Key Info Badges -->
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full">🚚 দ্রুত ডেলিভারি</span>
                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full">🛡️ ১০০% অরিজিনাল</span>
                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full">💳 ক্যাশ অন ডেলিভারি</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 mb-8">
                <a href="checkout.php?product=<?php echo $product['id']; ?>"
                    class="bg-green-600 hover:bg-green-700 text-white font-extrabold py-4 rounded-xl text-center transition shadow-lg shadow-green-200 text-base">
                    🛒 এখনই কিনুন — ক্যাশ অন ডেলিভারি
                </a>
                <a href="https://wa.me/<?php echo str_replace(['+', '-', ' '], '', SITE_WHATSAPP); ?>?text=আমি+এই+পণ্যটি+অর্ডার+করতে+চাই:+<?php echo urlencode($product['name']); ?>+দাম:+<?php echo $product['price']; ?>"
                    target="_blank"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl text-center transition flex items-center justify-center gap-2">
                    💬 হোয়াটসঅ্যাপে অর্ডার করুন
                </a>
            </div>

            <!-- Wholesale / Bulk Pricing -->
            <?php if (!empty($priceRules)): ?>
            <div class="mb-8 bg-green-50 border border-green-100 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-green-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-green-500 rounded-full"></span>
                    পাইকারি মূল্য / বাল্ক পারচেজ
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach($priceRules as $rule): ?>
                    <div class="bg-white p-3 rounded-xl border border-green-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500"><?php echo $rule['min_qty']; ?>+ টি</span>
                        <span class="text-sm font-extrabold text-green-600">
                            <?php if($rule['discount_type'] === 'fixed'): ?>
                                ৳ <?php echo number_format($rule['value']); ?> / টি
                            <?php else: ?>
                                <?php echo $rule['value']; ?>% ছাড়
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-[10px] text-green-400 mt-3 font-medium italic">* পণ্যের সংখ্যার ওপর ভিত্তি করে ডিসকাউন্ট স্বয়ংক্রিয়ভাবে যোগ হবে।</p>
            </div>
            <?php endif; ?>

            <!-- Description -->
            <?php if ($product['description']): ?>
            <div class="border-t pt-6">
                <h3 class="font-bold text-gray-900 mb-3">পণ্যের বিবরণ</h3>
                <div class="text-gray-600 text-sm leading-relaxed prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Suggested Products -->
    <?php if (!empty($suggested)): ?>
    <section>
        <h2 class="text-2xl font-extrabold text-gray-900 mb-6">আপনার পছন্দ হতে পারে</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($suggested as $p): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
