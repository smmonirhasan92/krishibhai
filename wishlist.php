<?php
$pageTitle = "My Wishlist";
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 min-h-[70vh] py-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-amber-400/10 rounded-full blur-3xl -z-10"></div>
    
    <div class="container mx-auto px-4 max-w-6xl relative z-10">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">My Wishlist</h1>
                <p class="text-slate-500 font-medium mt-1">Products you love, curated just for you.</p>
            </div>
            <button onclick="clearWishlist()" id="clear-btn" class="hidden text-sm font-bold text-rose-500 hover:text-rose-600 transition px-5 py-2.5 rounded-xl border border-rose-100 hover:bg-rose-50">Clear All</button>
        </div>

        <!-- No Items State -->
        <div id="empty-state" class="hidden text-center py-24">
            <div class="text-7xl opacity-10 mb-6">🤍</div>
            <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Your wishlist is empty</h2>
            <p class="text-slate-500 font-medium mb-8">Browse our premium catalog and tap the heart to save items here.</p>
            <a href="index.php" class="inline-flex items-center gap-2 bg-slate-900 text-white font-bold px-8 py-4 rounded-2xl hover:bg-slate-800 transition shadow-xl hover:-translate-y-0.5">
                Explore Products ➔
            </a>
        </div>

        <!-- Items Grid -->
        <div id="wishlist-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"></div>
    </div>
</div>

<script>
    let wishlist = JSON.parse(localStorage.getItem('zk_wishlist')) || [];

    function renderWishlistPage() {
        wishlist = JSON.parse(localStorage.getItem('zk_wishlist')) || [];
        const grid = document.getElementById('wishlist-grid');
        const empty = document.getElementById('empty-state');
        const clearBtn = document.getElementById('clear-btn');

        if (wishlist.length === 0) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            clearBtn.classList.add('hidden');
            return;
        }

        empty.classList.add('hidden');
        clearBtn.classList.remove('hidden');

        grid.innerHTML = wishlist.map((p, i) => `
            <div class="bg-white rounded-2xl border border-gray-100 hover:border-amber-300 hover:shadow-xl transition duration-300 overflow-hidden flex flex-col group">
                <div class="relative overflow-hidden" style="padding-top: 75%;">
                    <img src="${p.image}" alt="${p.name}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://placehold.co/400x300/f5f5f5/aaa?text=No+Image'">
                    <button onclick="removeFromWishlist(${i})" class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center hover:bg-rose-200 transition shadow-sm text-xs font-black">✕</button>
                </div>
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="font-black text-slate-800 text-sm leading-tight mb-2 line-clamp-2 group-hover:text-amber-600 transition">${p.name}</h3>
                    <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <span class="text-base font-black text-amber-600">৳ ${parseInt(p.price).toLocaleString()}</span>
                        <button onclick="buyNow(${JSON.stringify(p).replace(/"/g, '&quot;')})" class="bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-black uppercase tracking-wider px-3 py-2 rounded-lg transition shadow-md">Buy Now</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function removeFromWishlist(index) {
        wishlist.splice(index, 1);
        localStorage.setItem('zk_wishlist', JSON.stringify(wishlist));
        renderWishlistPage();
    }

    function clearWishlist() {
        if (!confirm('Remove all items from wishlist?')) return;
        localStorage.removeItem('zk_wishlist');
        wishlist = [];
        renderWishlistPage();
    }

    function buyNow(product) {
        let cart = JSON.parse(localStorage.getItem('zk_cart')) || [];
        const exists = cart.find(item => item.id == product.id);
        if (exists) { exists.qty++; } else { cart.push({...product, qty: 1}); }
        localStorage.setItem('zk_cart', JSON.stringify(cart));
        window.location.href = 'index.php#checkout';
    }

    document.addEventListener('DOMContentLoaded', renderWishlistPage);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
