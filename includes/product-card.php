<?php
/**
 * Reusable Product Card Component \u2014 Vibrant Premium Edition
 */
$imgSrc = !empty($p['image']) ? $p['image'] : (!empty($p['main_image']) ? $p['main_image'] : 'assets/images/placeholder.jpg');
?>
<?php 
$jsData = json_encode([
    'id' => $p['id'],
    'name' => $p['name'],
    'price' => $p['price'],
    'image' => $imgSrc,
    'description' => $p['description'] ?? 'Premium quality kitchen appliance.'
], JSON_HEX_APOS);
?>
<style>
.zk-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.zk-card:hover { transform: translateY(-8px); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.18), 0 0 0 1px rgba(98,157,37,0.12); }
.zk-card:hover .zk-img { transform: scale(1.08); }
.zk-img { transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.zk-buy-btn {
    background: linear-gradient(135deg, #144a05, #0a2e02);
    transition: all 0.3s ease;
    position: relative; overflow: hidden;
}
.zk-buy-btn::before {
    content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity 0.3s;
    background: linear-gradient(135deg, #629d25, #144a05);
}
.zk-buy-btn:hover::before { opacity: 1; }
.zk-buy-btn span { position: relative; z-index: 1; }
.zk-price-tag {
    background: linear-gradient(135deg, #144a05, #629d25);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.zk-wishlist-btn:hover { background: rgba(98, 157, 37, 0.08); border-color: #629d25; color: #629d25; }
</style>
<div onclick="window.location.href='product.php?slug=<?php echo $p['slug']; ?>'" class="zk-card cursor-pointer bg-white rounded-2xl overflow-hidden border border-gray-100/80 flex flex-col relative shadow-sm hover:shadow-2xl" style="box-shadow: 0 4px 15px -3px rgba(0,0,0,0.06);">
    <div class="overflow-hidden relative bg-slate-50" style="padding-top: 78%; display:flex; align-items:center; justify-content:center;">
        <?php if (!empty($p['image']) || !empty($p['main_image'])): ?>
            <img loading="lazy"
                src="<?php echo htmlspecialchars($imgSrc); ?>"
                alt="<?php echo htmlspecialchars($p['name']); ?>"
                class="zk-img absolute inset-0 w-full h-full object-cover"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <?php endif; ?>
        
        <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-rose-50 to-rose-100/50 border border-rose-100/30" style="display: <?php echo (!empty($p['image']) || !empty($p['main_image'])) ? 'none' : 'flex'; ?>;">
            <svg class="w-12 h-12 text-rose-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-[10px] font-black uppercase tracking-widest text-rose-400">Photo Coming Soon</span>
        </div>
        
        <?php if (!empty($p['is_featured'])): ?>
        <span class="absolute top-3 left-3 text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-md z-10"
              style="background: linear-gradient(135deg, #629d25, #144a05);">⭐ Featured</span>
        <?php endif; ?>
        
        <!-- Quick action buttons overlay -->
        <div class="absolute top-3 right-3 z-20 flex flex-col gap-2">
            <!-- Wishlist -->
            <button onclick="event.stopPropagation(); toggleWishlist(<?php echo htmlspecialchars($jsData); ?>)" 
                class="zk-wishlist-btn w-8 h-8 rounded-xl bg-white/90 backdrop-blur border border-slate-100 flex items-center justify-center text-slate-400 shadow-sm transition wishlist-btn-<?php echo $p['id']; ?>">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
            <!-- Compare -->
            <button onclick="event.stopPropagation(); toggleCompare(<?php echo htmlspecialchars($jsData); ?>)" 
                class="w-8 h-8 rounded-xl bg-white/90 backdrop-blur border border-slate-100 flex items-center justify-center text-slate-400 hover:text-green-600 hover:border-green-200 shadow-sm transition" title="Compare">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </button>
        </div>
    </div>
    <!-- Info -->
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-black text-slate-900 text-sm leading-tight mb-3 line-clamp-2 group-hover:text-green-600 transition" style="letter-spacing: -0.01em;">
            <?php echo htmlspecialchars($p['name']); ?>
        </h3>
        <div class="mt-auto pt-3 flex flex-col sm:flex-row sm:items-center justify-between border-t border-slate-50 gap-2">
            <div class="flex flex-col">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Price</span>
                <span class="text-sm md:text-lg font-black zk-price-tag leading-none">৳ <?php echo number_format($p['price']); ?></span>
            </div>
            
            <div class="flex items-center gap-1 relative z-20">
                <!-- Add to Bag -->
                <button onclick="event.stopPropagation(); addToCart(<?php echo htmlspecialchars(json_encode($p)); ?>);"
                    class="h-9 w-9 flex-shrink-0 flex items-center justify-center rounded-xl bg-slate-50 hover:bg-green-50 text-slate-500 hover:text-green-500 border border-slate-100 hover:border-green-100 transition group/btn shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </button>

                <!-- Buy Now -->
                <button onclick="event.stopPropagation(); buyNow(<?php echo htmlspecialchars(json_encode($p)); ?>);"
                    class="zk-buy-btn text-white text-[9px] md:text-[10px] font-black uppercase tracking-wider px-2 md:px-4 py-2 md:py-2.5 rounded-xl shadow-md">
                    <span>Buy Now</span>
                </button>
            </div>
        </div>
    </div>
</div>


