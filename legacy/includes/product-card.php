<?php
/**
 * Reusable Product Card Component
 * Expects: $p array with keys: id, name, main_image, price, slug
 */
$imgSrc = !empty($p['main_image']) ? $p['image_url'] ?? $p['main_image'] : 'https://placehold.co/400x400/f5f5f5/aaa?text=No+Image';
?>
<div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-amber-300 hover:shadow-xl transition duration-300 flex flex-col">
    <!-- Image -->
    <a href="product/<?php echo htmlspecialchars($p['slug'] ?? ''); ?>" class="block overflow-hidden relative" style="padding-top: 75%;">
        <img loading="lazy"
            src="<?php echo htmlspecialchars($imgSrc); ?>"
            alt="<?php echo htmlspecialchars($p['name']); ?>"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500"
            onerror="this.src='https://placehold.co/400x300/f5f5f5/aaa?text=No+Image'">
        <?php if (!empty($p['is_featured'])): ?>
        <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">Featured</span>
        <?php endif; ?>
    </a>
    <!-- Info -->
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-bold text-gray-800 text-sm md:text-base mb-1 line-clamp-2 flex-1">
            <a href="product/<?php echo htmlspecialchars($p['slug'] ?? ''); ?>" class="hover:text-amber-600 transition">
                <?php echo htmlspecialchars($p['name']); ?>
            </a>
        </h3>
        <div class="flex items-center justify-between mt-3">
            <span class="text-xl font-extrabold text-amber-600">৳ <?php echo number_format($p['price']); ?></span>
            <a href="checkout.php?product=<?php echo $p['id']; ?>" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition shadow hover:shadow-amber-200">
                Buy Now
            </a>
        </div>
    </div>
</div>
