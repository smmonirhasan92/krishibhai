<?php
/**
 * Krishibhai - Homepage
 * BD-Style layout: Featured Row, Sink Row, Accessories Row
 */
require_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/header.php';

// Fetch Hero Slides
$slides = [];
try {
    $slides = $pdo->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY order_index ASC")->fetchAll();
} catch(Exception $e) {}

// Fetch 8-12 Categories for Grid
$gridCats = [];
try {
    $gridCats = $pdo->query("SELECT * FROM categories ORDER BY id ASC LIMIT 11")->fetchAll();
} catch(Exception $e) {}

// Fetch products by specific categories for "BD Style" rows
$categoryRows = [];
$rowCategories = ['sink', 'kitchen-accessories', 'kitchen-hood', 'gas-stove'];
foreach ($rowCategories as $slug) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.hero_image AS cat_banner FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE c.slug = ? ORDER BY p.created_at DESC LIMIT 4");
        $stmt->execute([$slug]);
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            $categoryRows[$slug] = [
                'name' => $rows[0]['cat_name'],
                'banner' => $rows[0]['cat_banner'],
                'products' => $rows
            ];
        }
    } catch(Exception $e) {}
}
?>

<!-- ===========================
     HERO SECTION — MINIMALIST KRISHI EDITION
=========================== -->
<style>
    .krishi-hero {
        height: 500px;
        background: url('assets/images/hero_minimalist.png') center/cover no-repeat;
        position: relative;
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .krishi-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.6) 50%, transparent 100%);
    }
    .hero-content {
        position: relative;
        z-index: 10;
        max-width: 650px;
    }
    .hero-title {
        color: #144a05;
        font-size: 3.5rem;
        font-weight: 900;
        line-height: 1.1;
        margin-bottom: 1.5rem;
    }
    .hero-subtitle {
        color: #4b5563;
        font-size: 1.25rem;
        line-height: 1.6;
        margin-bottom: 2.5rem;
    }
    .btn-krishi {
        background: #629d25;
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s;
        box-shadow: 0 10px 20px -5px rgba(98, 157, 37, 0.4);
    }
    .btn-krishi:hover {
        background: #4a771c;
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -5px rgba(98, 157, 37, 0.5);
    }
    @media (max-width: 768px) {
        .krishi-hero { height: 450px; text-align: center; justify-content: center; }
        .krishi-hero::before { background: rgba(255,255,255,0.85); }
        .hero-title { font-size: 2.5rem; }
    }
</style>

<section class="krishi-hero">
    <div class="container mx-auto px-4">
        <div class="hero-content">
            <h1 class="hero-title">
                আধুনিক কৃষি<br>
                <span style="color: #629d25;">সফল কৃষক</span>
            </h1>
            <p class="hero-subtitle">
                বাংলাদেশের সেরা মানের বীজ, সার, কীটনাশক এবং আধুনিক কৃষি সরঞ্জাম। 
                সাশ্রয়ী মূল্যে ঘরে বসেই অর্ডার করুন।
            </p>
            <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                <a href="#products" class="btn-krishi">
                    <i class="ph ph-shopping-cart-simple"></i>
                    কেনাকাটা শুরু করুন
                </a>
                <a href="#inquiry" class="px-8 py-4 rounded-xl border-2 border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">
                    পরামর্শ নিন
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===========================
     CATEGORY NAV + PRODUCT GRID — VIBRANT
=========================== -->
<style>
    #products { position: relative; }
    .cat-pill-item.active .cat-pill-inner {
        background: linear-gradient(135deg, #629d25, #144a05);
        color: #edf2f4;
        box-shadow: 0 15px 35px -8px rgba(98, 157, 37, 0.5);
        transform: translateY(-6px) scale(1.05);
        border-color: #629d25 !important;
    }
    .cat-pill-item.active .cat-name {
        color: #144a05 !important;
        font-weight: 900;
    }
    .cat-pill-inner { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .product-section-bg {
        background: linear-gradient(180deg, #edf2f4 0%, #f5f5f7 60%, #edf2f4 100%);
    }
    .section-heading-accent {
        background: linear-gradient(135deg, #144a05 0%, #629d25 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
</style>
<section id="products" class="product-section-bg py-20">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block text-[10px] font-black uppercase tracking-[0.35em] px-6 py-2 rounded-full mb-6"
                   style="background: rgba(98, 157, 37, 0.08); color: #144a05; border: 1px solid rgba(98, 157, 37, 0.25);">🔥 বিশেষ পণ্যসমূহ</span>
            <h2 class="text-4xl md:text-6xl font-black mb-5 tracking-tight" style="color: #2b2d42;">
                পছন্দের <span class="section-heading-accent">ক্যাটাগরি</span>
            </h2>
            <p class="max-w-xl mx-auto text-lg leading-relaxed" style="color: #8d99ae;">সহজেই ক্যাটাগরি অনুযায়ী পণ্য খুঁজে নিন।</p>
        </div>

        <!-- CIRCULAR CATEGORY NAV -->
        <div class="flex md:flex-wrap items-center md:items-start justify-start md:justify-center gap-6 md:gap-12 mb-20 overflow-x-auto pb-4 scrollbar-hide flex-nowrap md:flex-wrap px-4 -mx-4">
            <div onclick="filterCategory('all', this)" class="cat-pill-item active group cursor-pointer text-center flex-shrink-0">
                <div class="cat-pill-inner w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center shadow-lg overflow-hidden mb-3 mx-auto"
                     style="background: #144a05;">
                    <span class="text-3xl">🏠</span>
                </div>
                <span class="cat-name block text-[10px] md:text-xs font-black uppercase tracking-widest text-slate-700">সব পণ্য</span>
            </div>

            <?php foreach($gridCats as $cat): 
                $catImg = !empty($cat['hero_image']) ? $cat['hero_image'] : null;
            ?>
            <div onclick="filterCategory('<?php echo $cat['slug']; ?>', this)" class="cat-pill-item group cursor-pointer text-center flex-shrink-0">
                <div class="cat-pill-inner w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center bg-white border border-slate-100 shadow-sm group-hover:shadow-md overflow-hidden mb-3 mx-auto">
                    <?php if($catImg): ?>
                        <img src="<?php echo htmlspecialchars($catImg); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-115">
                    <?php else: ?>
                        <span class="text-3xl opacity-20">🍽️</span>
                    <?php endif; ?>
                </div>
                <span class="cat-name block text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-500 group-hover:text-green-600 transition-colors"><?php echo htmlspecialchars($cat['name']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- PRODUCT GRID -->
        <?php
        $allProducts = $pdo->query("SELECT p.*, c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
        ?>
        <div id="product-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-8">
            <?php foreach($allProducts as $p): ?>
            <div class="product-item transition-all duration-700 transform opacity-100 scale-100" data-category="<?php echo $p['cat_slug']; ?>">
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    function filterCategory(slug, el) {
        document.querySelectorAll('.cat-pill-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        const items = document.querySelectorAll('.product-item');
        items.forEach(item => {
            if (slug === 'all' || item.getAttribute('data-category') === slug) {
                item.style.display = 'block';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0) scale(1)';
                }, 50);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px) scale(0.95)';
                setTimeout(() => { item.style.display = 'none'; }, 500);
            }
        });
    }
</script>

<!-- Initialize Swiper -->
<script>
    const swiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        effect: 'fade',
        fadeEffect: { crossFade: true }
    });
</script>

<!-- ===========================
     FEATURED PRODUCTS ROW
=========================== -->
<?php if (!empty($featured)): ?>
<section id="featured" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">⭐ বিশেষ পণ্যসমূহ</h2>
                <p class="text-gray-500 mt-1">আমাদের সেরা সংগৃহীত পণ্যসমূহ</p>
            </div>
            <a href="#" class="text-green-600 font-semibold hover:underline hidden md:block">সব দেখুন &rarr;</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($featured as $p): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===========================
     CATEGORY-WISE ROWS
=========================== -->
<?php foreach($categoryRows as $slug => $row): ?>
<?php if (!empty($row['products'])): ?>
<section class="py-12 <?php echo $slug === 'sink' ? 'bg-white' : 'bg-gray-50'; ?>" id="cat-<?php echo $slug; ?>">
    <div class="container mx-auto px-4">
        <!-- Category Banner -->
        <?php if ($row['banner']): ?>
        <div class="relative mb-8 rounded-2xl overflow-hidden h-40" style="background: url('<?php echo $row['banner']; ?>') center/cover">
            <div class="absolute inset-0 bg-black/50 flex items-center px-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-white"><?php echo $row['name']; ?></h2>
                    <a href="category/<?php echo $slug; ?>" class="text-white bg-green-600/20 hover:bg-green-600 px-3 py-1 rounded-lg text-xs mt-2 inline-block transition">সব দেখুন &rarr;</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900"><?php echo $row['name']; ?></h2>
            </div>
            <a href="category/<?php echo $slug; ?>" class="text-green-600 font-semibold hover:underline">সব দেখুন &rarr;</a>
        </div>
        <?php endif; ?>

        <!-- Product Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($row['products'] as $p): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endforeach; ?>

<!-- Quick Inquiry Form: COMPACT & SLIM -->
<section class="py-12 bg-white" id="inquiry">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-slate-900 rounded-[2rem] overflow-hidden flex flex-col md:flex-row shadow-2xl">
            <div class="md:w-2/5 p-8 bg-gradient-to-br from-slate-900 to-slate-800 text-white border-r border-white/5">
                <h2 class="text-2xl font-black mb-4"><span class="text-green-500">বিশেষ</span> পরামর্শ?</h2>
                <p class="text-slate-400 text-xs mb-6 leading-relaxed">আমাদের কৃষি বিশেষজ্ঞরা আপনাকে সঠিক পণ্য নির্বাচনে সহায়তা করবে।</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm font-bold">
                        <span class="text-green-600">📞</span> <?php echo SITE_PHONE; ?>
                    </div>
                    <div class="flex items-center gap-3 text-sm font-bold">
                        <span class="text-green-600">📍</span> গোপালপুর, টাঙ্গাইল
                    </div>
                </div>
            </div>
            <div class="md:w-3/5 p-8">
                <form action="api/submit_lead.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="name" required placeholder="নাম" class="w-full px-5 py-3 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:border-green-500 transition text-sm">
                    <input type="text" name="phone" required placeholder="ফোন নম্বর" class="w-full px-5 py-3 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:border-green-500 transition text-sm">
                    <input type="text" name="message" placeholder="আপনার চাহিদা বা পরামর্শ..." class="md:col-span-2 w-full px-5 py-3 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:border-green-500 transition text-sm">
                    <button type="submit" class="md:col-span-2 bg-green-600 hover:bg-green-700 text-white font-black py-3 rounded-xl transition shadow-lg shadow-green-600/20 text-sm">
                        অনুরোধ পাঠান
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us — Monochrome Red -->
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(145deg, #2b2d42 0%, #1a1b2e 60%, #0f0f1a 100%);">
    <div class="absolute inset-0 hero-grid-bg"></div>
    <div class="floating-blob1 absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full opacity-10"
         style="background: radial-gradient(circle, #ef233c 0%, transparent 70%);"></div>
    <div class="floating-blob2 absolute -bottom-40 -left-20 w-[500px] h-[500px] rounded-full opacity-10"
         style="background: radial-gradient(circle, #d80032 0%, transparent 70%);"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block text-[10px] font-black uppercase tracking-[0.35em] px-6 py-2 rounded-full mb-6"
                  style="border: 1px solid rgba(98, 157, 37, 0.4); color: #629d25; background: rgba(98, 157, 37, 0.1);">কেন আমরা</span>
            <h2 class="text-4xl md:text-5xl font-black tracking-tight" style="color: #edf2f4;">
                কেন <span style="background: linear-gradient(135deg, #629d25, #144a05); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">কৃষিভাই?</span>
            </h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php
            $features = [
                ['🚚', 'দ্রুত ডেলিভারি', 'সারা বাংলাদেশে ২-৩ দিনের মধ্যে ডেলিভারি', 'rgba(98, 157, 37, 0.1)', '#629d25'],
                ['🛡️', '১০০% অরিজিনাল', 'সেরা মানের অরিজিনাল কৃষি পণ্য', 'rgba(20, 74, 5, 0.1)', '#144a05'],
                ['💬', 'বিশেষজ্ঞ পরামর্শ', 'কৃষি বিশেষজ্ঞদের থেকে সঠিক পরামর্শ', 'rgba(141,153,174,0.1)', '#8d99ae'],
                ['💳', 'নির্ভরযোগ্য মূল্য', 'সরাসরি কৃষকবান্ধব সাশ্রয়ী মূল্য', 'rgba(237,242,244,0.05)', '#edf2f4'],
            ];
            foreach($features as $f):
            ?>
            <div class="group p-8 rounded-3xl transition-all duration-300 hover:-translate-y-2 cursor-default"
                 style="background: <?php echo $f[3]; ?>; border: 1px solid rgba(255,255,255,0.06);">
                <div class="text-5xl mb-5"><?php echo $f[0]; ?></div>
                <h3 class="font-black text-lg mb-2" style="color: #edf2f4;"><?php echo $f[1]; ?></h3>
                <p class="text-sm leading-relaxed" style="color: #8d99ae;"><?php echo $f[2]; ?></p>
                <div class="mt-5 w-8 h-1 rounded-full transition-all duration-300 group-hover:w-16"
                     style="background: <?php echo $f[4]; ?>;"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===========================
     COMPARE MODAL (Glassmorphism)
=========================== -->
<div id="compare-modal" class="fixed inset-0 z-[115] hidden items-center justify-center p-4 md:p-10">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCompareModal()"></div>
    <div class="relative bg-slate-50 w-full max-w-6xl h-full max-h-[90vh] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col border border-white/20">
        <div class="p-6 md:p-8 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3"><span class="text-green-600">⇄</span> পণ্য তুলনা করুন</h2>
            <button onclick="closeCompareModal()" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 text-slate-600 transition font-bold">✕</button>
        </div>
        <div class="flex-1 overflow-x-auto p-6 md:p-8">
            <div id="compare-grid" class="flex gap-6 w-max min-w-full">
                <!-- Comparison columns injected here -->
            </div>
        </div>
    </div>
</div>

<!-- ===========================
     QUICK VIEW MODAL (Glassmorphism)
=========================== -->
<div id="quick-view-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 md:p-10">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeQuickView()"></div>
    <div id="qv-content" class="relative bg-white/90 backdrop-blur-xl w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row transition-all duration-500 transform scale-90 opacity-0 border border-white/20">
        <!-- Content will be injected via JS -->
    </div>
</div>

<!-- ===========================
     SIDE CART (Sliding Drawer)
=========================== -->
<div id="side-cart" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-[110] transform translate-x-full transition-transform duration-500 ease-in-out border-l border-slate-100 flex flex-col">
    <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-900 text-white">
        <h2 class="text-xl font-black italic tracking-tight">আপনার কেনাকাটা</h2>
        <button onclick="toggleCart()" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/10 transition">✕</button>
    </div>
    <div id="cart-items" class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Cart items injected here -->
        <div class="text-center py-12 text-slate-400">
            <div class="text-4xl mb-4">🛍️</div>
            <p class="font-bold">আপনার ব্যাগ খালি</p>
            <p class="text-xs mt-2">পণ্য যোগ করুন এবং কেনাকাটা শুরু করুন!</p>
        </div>
    </div>
    <div class="p-6 bg-slate-50 border-t border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <span class="text-slate-500 font-bold uppercase tracking-widest text-xs">মোট</span>
            <span id="cart-total" class="text-2xl font-black text-slate-900">৳ ০</span>
        </div>
        <button onclick="toggleCart(); openCheckout();" class="w-full bg-green-600 hover:bg-green-700 text-white text-center font-black py-4 rounded-2xl transition shadow-xl shadow-green-600/20 block">
            অর্ডার করুন
        </button>
    </div>
</div>

<script>
    // Global State
    let cart = JSON.parse(localStorage.getItem('zk_cart')) || [];
    let wishlist = JSON.parse(localStorage.getItem('zk_wishlist')) || [];
    let compareList = JSON.parse(localStorage.getItem('zk_compare')) || [];

    // Compare Logic
    function toggleCompare(product) {
        event.stopPropagation();
        const index = compareList.findIndex(item => item.id == product.id);
        if (index > -1) {
            compareList.splice(index, 1);
        } else {
            if (compareList.length >= 3) {
                alert('You can only compare up to 3 products at a time.');
                return;
            }
            compareList.push(product);
        }
        localStorage.setItem('zk_compare', JSON.stringify(compareList));
        renderCompareBar();
    }

    function renderCompareBar() {
        let bar = document.getElementById('compare-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'compare-bar';
            bar.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[90] bg-slate-900/90 backdrop-blur-md text-white px-6 py-4 rounded-full shadow-2xl flex items-center gap-6 transform transition-all duration-300 translate-y-24';
            bar.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-sm">
                    <span><span id="compare-count" class="text-green-500">0</span> টি পণ্য তুলনা করার জন্য</span>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="openCompareModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-black transition">তুলনা করুন</button>
                    <button onclick="clearCompare()" class="text-slate-400 hover:text-white transition">✕</button>
                </div>
            `;
            document.body.appendChild(bar);
        }

        const countEl = document.getElementById('compare-count');
        if (countEl) countEl.innerText = compareList.length;

        if (compareList.length > 0) {
            bar.classList.remove('translate-y-24');
        } else {
            bar.classList.add('translate-y-24');
        }
    }

    function clearCompare() {
        compareList = [];
        localStorage.setItem('zk_compare', JSON.stringify(compareList));
        renderCompareBar();
    }

    function openCompareModal() {
        if (compareList.length < 2) {
            alert('Please select at least 2 products to compare.');
            return;
        }
        const modal = document.getElementById('compare-modal');
        const grid = document.getElementById('compare-grid');
        
        let html = '';
        compareList.forEach(p => {
            html += `
                <div class="bg-white p-6 rounded-3xl border border-slate-100 flex-1 relative min-w-[280px]">
                    <button onclick="toggleCompare({id: ${p.id}}); openCompareModal();" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-rose-500 flex items-center justify-center transition">✕</button>
                    <img src="${p.image}" class="w-full h-48 object-cover rounded-2xl mb-6 bg-slate-50">
                    <h3 class="font-black text-slate-900 text-lg mb-2 leading-tight">${p.name}</h3>
                    <div class="text-2xl font-black text-green-600 mb-6">৳ ${parseInt(p.price).toLocaleString()}</div>
                    
                    <div class="space-y-4 text-sm">
                        <div class="border-t border-slate-100 pt-4">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">ব্র্যান্ড</span>
                            <span class="font-bold text-slate-700">কৃষিভাই প্রো</span>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">বিবরণ</span>
                            <span class="text-slate-600 line-clamp-3 leading-relaxed">${p.description}</span>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">স্টক</span>
                            <span class="font-bold text-emerald-600">স্টক আছে</span>
                        </div>
                    </div>
                    
                    <button onclick="closeCompareModal(); buyNow(${JSON.stringify(p).replace(/"/g, '&quot;')})" class="w-full mt-8 bg-slate-900 hover:bg-slate-800 text-white font-black py-3.5 rounded-xl transition shadow-xl hover:-translate-y-0.5">এখনই কিনুন</button>
                </div>
            `;
        });
        
        grid.innerHTML = html;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCompareModal() {
        let modal = document.getElementById('compare-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Wishlist Logic
    function renderWishlistCount() {
        const countEl = document.getElementById('wishlist-count');
        if (countEl) {
            if (wishlist.length > 0) {
                countEl.innerHTML = wishlist.length;
                countEl.classList.remove('hidden');
            } else {
                countEl.classList.add('hidden');
            }
        }
    }

    function toggleWishlist(product) {
        const index = wishlist.findIndex(item => item.id == product.id);
        let action = 'added';
        if (index > -1) {
            wishlist.splice(index, 1);
            action = 'removed';
        } else {
            wishlist.push(product);
        }
        localStorage.setItem('zk_wishlist', JSON.stringify(wishlist));
        renderWishlistCount();

        // Animate Button if exists
        if (typeof window.event !== 'undefined' && window.event) {
            let btn = window.event.target;
            if (btn && btn.closest) btn = btn.closest(`.wishlist-btn-${product.id}`) || btn.closest('button');
            if (btn && btn.classList.contains(`wishlist-btn-${product.id}`)) {
                if (action === 'added') {
                    btn.classList.add('bg-rose-50', 'text-rose-500', 'border-rose-100');
                    btn.classList.remove('bg-white/80', 'text-slate-400', 'border-transparent');
                } else {
                    btn.classList.remove('bg-rose-50', 'text-rose-500', 'border-rose-100');
                    btn.classList.add('bg-white/80', 'text-slate-400', 'border-transparent');
                }
            }
        }
        
        // If on wishlist page, re-render
        if (typeof renderWishlistPage === 'function') {
            renderWishlistPage();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderWishlistCount();
    });

    function toggleCart() {
        const sideCart = document.getElementById('side-cart');
        sideCart.classList.toggle('translate-x-full');
        renderCart();
    }

    function addToCart(product, skipAnimation = false) {
        const exists = cart.find(item => item.id == product.id);
        if (exists) {
            exists.qty++;
        } else {
            cart.push({...product, qty: 1});
        }
        localStorage.setItem('zk_cart', JSON.stringify(cart));
        
        if (!skipAnimation && typeof window.event !== 'undefined' && window.event) {
            let btn = window.event.target;
            if (btn && btn.closest) {
                btn = btn.closest('button');
            }
            if (btn) {
                const originalText = btn.innerHTML;
                btn.classList.add('bg-green-500');
                btn.innerHTML = '✓';
                setTimeout(() => {
                    btn.classList.remove('bg-green-500');
                    btn.innerHTML = originalText;
                }, 1000);
            }
        }

        renderCart();
        if (!skipAnimation && window.innerWidth > 768) {
            const sideCart = document.getElementById('side-cart');
            if (sideCart && sideCart.classList.contains('translate-x-full')) {
                toggleCart();
            }
        }
    }

    function buyNow(product) {
        // Add item to cart SILENTLY (no animation, no sidecart)
        addToCart(product, true);
        
        // Hide side cart if open
        const sideCart = document.getElementById('side-cart');
        if (sideCart && !sideCart.classList.contains('translate-x-full')) {
            sideCart.classList.add('translate-x-full');
        }
        
        // Open the Checkout Modal Immediately
        setTimeout(() => {
            openCheckout();
        }, 50);
    }

    function renderCart() {
        const countEl = document.getElementById('cart-count');
        const container = document.getElementById('cart-items');
        const totalEl = document.getElementById('cart-total');

        if (countEl) countEl.innerHTML = cart.reduce((a, b) => a + b.qty, 0);

        if (!container || !totalEl) return;

        if (cart.length === 0) {
            container.innerHTML = `<div class="text-center py-12 text-slate-400">
                <div class="text-4xl mb-4">🛍️</div>
                <p class="font-bold">Your bag is empty</p>
    ,ReplacementContent:            </div>`;
            totalEl.innerHTML = '৳ 0';
            return;
        }

        let total = 0;
        container.innerHTML = cart.map((item, index) => {
            total += item.price * item.qty;
            return `
            <div class="flex gap-4 items-center group">
                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100">
                    <img src="${item.image}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1">
                    <h4 class="font-black text-slate-900 text-sm leading-tight">${item.name}</h4>
                    <p class="text-amber-600 font-bold text-xs mt-1">৳ ${item.price.toLocaleString()}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <button onclick="updateQty(${index}, -1)" class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold hover:bg-amber-100 transition">-</button>
                        <span class="text-xs font-black">${item.qty}</span>
                        <button onclick="updateQty(${index}, 1)" class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold hover:bg-amber-100 transition">+</button>
                    </div>
                </div>
                <button onclick="removeFromCart(${index})" class="text-slate-300 hover:text-red-500 transition">✕</button>
            </div>`;
        }).join('');
        totalEl.innerHTML = '৳ ' + total.toLocaleString();
    }

    function updateQty(index, delta) {
        cart[index].qty += delta;
        if (cart[index].qty < 1) return removeFromCart(index);
        localStorage.setItem('zk_cart', JSON.stringify(cart));
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        localStorage.setItem('zk_cart', JSON.stringify(cart));
        renderCart();
    }

    // Quick View Logic
    function openQuickView(p) {
        const modal = document.getElementById('quick-view-modal');
        const content = document.getElementById('qv-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        content.innerHTML = `
            <button onclick="closeQuickView()" class="absolute top-6 right-6 z-20 w-12 h-12 rounded-full bg-white/50 backdrop-blur-md flex items-center justify-center hover:bg-white transition text-xl">✕</button>
            <div class="md:w-1/2 h-[400px] md:h-auto overflow-hidden">
                <img src="${p.image}" class="w-full h-full object-cover">
            </div>
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <span class="text-amber-600 text-[10px] font-black uppercase tracking-widest mb-4">Limited Edition</span>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 leading-tight">${p.name}</h2>
                <div class="text-4xl font-black text-green-600 mb-8 italic">৳ ${parseInt(p.price).toLocaleString()}</div>
                <p class="text-slate-500 mb-10 text-lg leading-relaxed">${p.description}</p>
                <div class="flex gap-3">
                    <button onclick='addToCart(${JSON.stringify(p)})' class="flex-1 bg-slate-100 hover:bg-green-100 text-slate-800 font-bold py-4 rounded-2xl transition shadow-sm flex items-center justify-center gap-2 group/btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover/btn:text-green-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>ব্যাগ যোগ করুন</span>
                    </button>
                    
                    <button onclick='buyNow(${JSON.stringify(p).replace(/"/g, '&quot;')}); closeQuickView();' class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-widest text-sm py-4 rounded-2xl transition shadow-xl shadow-slate-200 text-center">
                        কিনুন
                    </button>
                    
                    <button onclick='toggleWishlist(${JSON.stringify(p).replace(/"/g, '&quot;')})' class="w-14 h-14 rounded-2xl border border-slate-200 bg-white flex items-center justify-center text-xl hover:bg-rose-50 hover:border-rose-100 hover:text-rose-500 transition flex-shrink-0 wishlist-btn-${p.id}">
                        ${wishlist.findIndex(item => item.id == p.id) > -1 ? '❤️' : '🤍'}
                    </button>
                </div>
            </div>
        `;

        setTimeout(() => {
            content.classList.remove('scale-90', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeQuickView() {
        const content = document.getElementById('qv-content');
        content.classList.add('scale-90', 'opacity-0');
        setTimeout(() => {
            document.getElementById('quick-view-modal').classList.add('hidden');
            document.getElementById('quick-view-modal').classList.remove('flex');
        }, 500);
    }
</script>

<!-- ===========================
     CHECKOUT MODAL (Zoom-in Animation)
=========================== -->
<div id="checkout-modal" class="fixed inset-0 z-[120] hidden items-center justify-center p-3">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeCheckout()"></div>
    <div id="checkout-content" class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 transform scale-75 opacity-0 border border-slate-100">
        
        <!-- Top Right Close Button -->
        <button onclick="closeCheckout()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/10 flex items-center justify-center hover:bg-black/20 transition z-10 text-slate-800 font-bold">✕</button>

        <div class="bg-green-600 p-6 text-white text-center relative border-b border-green-700/20">
            <h2 class="text-2xl font-black italic tracking-tight">অর্ডার করুন</h2>
            <p class="text-green-100/80 text-[10px] font-bold uppercase tracking-widest mt-1">ক্যাশ অন ডেলিভারি</p>
        </div>
        
        <form id="order-form" onsubmit="submitOrder(event)" class="p-6 space-y-4">
            <div id="order-summary-mini" class="bg-slate-50 p-3 rounded-xl border border-slate-100 mb-2 max-h-32 overflow-y-auto hidden">
                <!-- Summary injected here -->
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 opacity-30 group-focus-within:opacity-100 transition text-sm">👤</span>
                    <input type="text" name="name" required placeholder="আপনার নাম" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-green-500 outline-none transition text-sm font-medium text-slate-800">
                </div>
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 opacity-30 group-focus-within:opacity-100 transition text-sm">📞</span>
                    <input type="tel" name="phone" required placeholder="মোবাইল নম্বর" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-green-500 outline-none transition text-sm font-medium text-slate-800">
                </div>
            </div>
            
            <div class="relative group">
                <span class="absolute left-4 top-3.5 opacity-30 group-focus-within:opacity-100 transition text-sm">📍</span>
                <textarea name="address" required rows="2" placeholder="পুরো ঠিকানা (থানা ও জেলা উল্লেখ করুন)" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-green-500 outline-none transition text-sm font-medium text-slate-800 resize-none"></textarea>
            </div>

            <button type="submit" id="order-btn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-4 rounded-xl transition shadow-xl shadow-slate-200 mt-2 flex justify-center items-center gap-2">
                অর্ডার নিশ্চিত করুন <span class="text-green-400">৳ <span id="checkout-total">0</span></span>
            </button>
            <p class="text-[10px] text-slate-400 text-center font-bold uppercase tracking-widest mt-2">পণ্য বুঝে পেয়ে মূল্য পরিশোধ করুন</p>
        </form>

        <!-- Success Message (Hidden) -->
        <div id="success-screen" class="hidden absolute inset-0 bg-white flex flex-col items-center justify-center text-center p-12">
            <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-5xl mb-8 animate-bounce">✓</div>
            <h2 class="text-3xl font-black text-slate-900 mb-4">অর্ডার সম্পন্ন হয়েছে!</h2>
            <p class="text-slate-500 mb-8 leading-relaxed">কৃষিভাই থেকে কেনাকাটা করার জন্য ধন্যবাদ। আমাদের প্রতিনিধি শীঘ্রই আপনাকে ফোন করবেন।</p>
            <button onclick="window.location.reload()" class="bg-slate-900 text-white font-black px-10 py-4 rounded-2xl hover:bg-slate-800 transition">আরো কেনাকাটা করুন</button>
        </div>
    </div>
</div>

<script>
    function openCheckout() {
        if (cart.length === 0) return alert('আপনার ব্যাগ খালি!');
        
        const modal = document.getElementById('checkout-modal');
        const content = document.getElementById('checkout-content');
        const summary = document.getElementById('order-summary-mini');
        const totalDisplay = document.getElementById('checkout-total');
        
        let total = cart.reduce((a, b) => a + (b.price * b.qty), 0);
        totalDisplay.innerText = total.toLocaleString();
        
        summary.innerHTML = cart.map(item => `
            <div class="flex justify-between items-center text-xs mb-1 last:mb-0">
                <span class="font-bold text-slate-600">${item.qty}x ${item.name}</span>
                <span class="text-slate-400">৳ ${ (item.price * item.qty).toLocaleString() }</span>
            </div>
        `).join('');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-75', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCheckout() {
        const content = document.getElementById('checkout-content');
        content.classList.add('scale-75', 'opacity-0');
        setTimeout(() => {
            document.getElementById('checkout-modal').classList.add('hidden');
        }, 500);
    }

    async function submitOrder(e) {
        e.preventDefault();
        const btn = document.getElementById('order-btn');
        const form = document.getElementById('order-form');
        const formData = new FormData(form);
        formData.append('items', JSON.stringify(cart));

        btn.disabled = true;
        btn.innerHTML = '<span class="animate-pulse">প্রসেসিং...</span>';

        try {
            const response = await fetch('api/place_order.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                localStorage.removeItem('zk_cart');
                document.getElementById('success-screen').classList.remove('hidden');
                document.getElementById('success-screen').classList.add('flex');
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = 'অর্ডার নিশ্চিত করুন';
            }
        } catch (error) {
            alert('Something went wrong. Please try again or call us.');
            btn.disabled = false;
        }
    }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
