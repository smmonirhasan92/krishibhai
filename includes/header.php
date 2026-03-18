<?php
/**
 * Zaman Kitchens - Header Component
 * Features: Sticky header, Category Dropdown, Search Bar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// Fetch categories for mega dropdown
$categories = [];
try {
    $categories = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll();
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . get_setting('site_name', 'কৃষিভাই') : get_setting('site_name', 'কৃষিভাই') . ' - ' . get_setting('site_tagline', 'প্রিমিয়াম কৃষি পণ্য ও সরঞ্জাম'); ?></title>
    <meta name="description" content="<?php echo isset($pageDesc) ? $pageDesc : "বাংলাদেশের সেরা কৃষি পণ্য, বীজ, সার এবং সরঞ্জাম। অনলাইনে অর্ডার করুন দ্রুত ডেলিভারি সহ।"; ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/logo.png">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/logo.png">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' | ' . get_setting('site_name', 'কৃষিভাই') : get_setting('site_name', 'কৃষিভাই'); ?>">
    <meta property="og:description" content="<?php echo isset($pageDesc) ? $pageDesc : "বাংলাদেশের সেরা কৃষি পণ্য, বীজ, সার এবং সরঞ্জাম। অনলাইনে অর্ডার করুন দ্রুত ডেলিভারি সহ।"; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/logo.png">

    <!-- Twitter -->
    <meta property="twitter:image" content="<?php echo SITE_URL; ?>/logo.png">

    <!-- App-like Mobile Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#629d25">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Google Fonts: Premium Bengali Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&family=Baloo+Da+2:wght@400..800&family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

    <style>
        :root {
            --font-main: 'Hind Siliguri', sans-serif;
            --font-heading: 'Baloo Da 2', cursive;
            --font-premium: 'Anek Bangla', sans-serif;
        }
        body { font-family: var(--font-main); -webkit-font-smoothing: antialiased; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading) !important; font-weight: 700; }
        .font-premium { font-family: var(--font-premium); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .group:hover .group-hover\:scale-105 { transform: scale(1.05); }
        /* WhatsApp button pulse */
        @keyframes pulse-ring { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.3); opacity: 0; } }
        .wa-pulse::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; background: #25d366; animation: pulse-ring 1.5s infinite; z-index: -1; }
        
        /* Top bar scroll for mobile */
        @keyframes scroll-text { 0% { transform: translateX(10%); } 100% { transform: translateX(-100%); } }
        @media (max-width: 640px) {
            .animate-scroll {
                display: inline-flex;
                animation: scroll-text 15s linear infinite;
                padding-left: 100%;
            }
        }
    </style>

    <?php echo $extraHead ?? ''; ?>
</head>
<body class="bg-white text-gray-900 antialiased">

<!-- ===== TOP BAR ===== -->
<div style="background: linear-gradient(90deg, #144a05, #629d25, #144a05); color: #ffffff;" class="text-[10px] md:text-xs text-center py-1.5 px-2 font-bold tracking-wide overflow-hidden whitespace-nowrap">
    <div class="flex items-center justify-center gap-2 md:gap-4 animate-scroll md:animate-none">
        <span>🚚 সারা বাংলাদেশে হোম ডেলিভারি</span>
        <span class="opacity-30">|</span>
        <span>📞 <a href="tel:<?php echo get_setting('site_phone'); ?>" class="hover:underline" style="color: #ffffff;"><?php echo get_setting('site_phone'); ?></a></span>
        <span class="opacity-30 hidden md:inline">|</span>
        <span class="hidden md:inline">💬 সকাল ১০টা - রাত ৮টা পর্যন্ত খোলা</span>
    </div>
</div>


<!-- ===== MAIN HEADER ===== -->
<header class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex items-center h-16 gap-4">

            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="flex-shrink-0 flex items-center gap-2">
                <img src="<?php echo SITE_URL; ?>/logo.png" alt="<?php echo get_setting('site_name', 'কৃষিভাই'); ?>" class="h-10 md:h-12 w-auto object-contain" onerror="this.onerror=null; this.src='https://placehold.co/200x80/629d25/ffffff?text=<?php echo urlencode(get_setting('site_name', 'Krishibhai')); ?>';">
            </a>

            <!-- Category Dropdown (Desktop) -->
            <div class="hidden md:block relative group ml-4">
                <button class="flex items-center gap-1.5 font-semibold text-sm text-gray-700 hover:text-green-600 transition px-3 py-2 rounded-lg hover:bg-gray-50 border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    সকল ক্যাটাগরি
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Dropdown Menu -->
                <div class="absolute top-full left-0 mt-1 bg-white rounded-xl shadow-2xl border border-gray-100 p-2 hidden group-hover:block min-w-56 z-50">
                    <?php foreach($categories as $cat): ?>
                    <a href="<?php echo SITE_URL; ?>#products" 
                       onclick="if(window.location.pathname === '/' || window.location.pathname === '/index.php') { event.preventDefault(); filterCategory('<?php echo $cat['slug']; ?>', document.querySelector('.cat-circle-item[onclick*=\\'<?php echo $cat['slug']; ?>\\']')); }"
                       class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 font-medium transition">
                        <span class="w-2 h-2 bg-green-400 rounded-full flex-shrink-0"></span>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Bar -->
            <form action="<?php echo SITE_URL; ?>/search" method="GET" class="flex-1 max-w-xl">
                <div class="relative">
                    <input type="text" name="q" placeholder="বীজ, সার বা সরঞ্জাম খুঁজুন..."
                        class="w-full pl-4 pr-12 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-green-400 focus:bg-white transition">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Action Icons -->
            <div class="flex items-center gap-2 ml-auto">
                <!-- User Profile -->
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?php echo SITE_URL; ?>/profile.php" class="hidden sm:flex p-2.5 rounded-xl bg-slate-50 hover:bg-green-50 transition border border-slate-100 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
                <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/login.php" class="hidden sm:flex p-2.5 rounded-xl bg-slate-50 hover:bg-green-50 transition border border-slate-100 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </a>
                <?php endif; ?>

                <!-- Wishlist Trigger -->
                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="relative p-2.5 rounded-xl bg-slate-50 hover:bg-green-100 transition border border-slate-100 group hidden sm:flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span id="wishlist-count" class="absolute -top-1 -right-1 bg-green-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white hidden">0</span>
                </a>

                <!-- Cart Trigger -->
                <button onclick="toggleCart()" class="relative p-2.5 rounded-xl bg-slate-50 hover:bg-green-50 transition border border-slate-100 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cart-count" class="absolute -top-1 -right-1 bg-green-600 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white">0</span>
                </button>
 
                <a href="tel:<?php echo get_setting('site_phone'); ?>" class="hidden md:flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">
                    📞 <span>কল করুন</span>
                </a>
                <!-- Mobile Menu Button -->
                <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden p-2 rounded-lg hover:bg-gray-50 border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    </div>
</header>

<!-- ===== BOTTOM NAVIGATION (MOBILE APP FEEL) ===== -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-6 py-2 z-[60] flex justify-between items-center shadow-[0_-5px_15px_rgba(0,0,0,0.05)] pb-safe-area">
    <a href="<?php echo SITE_URL; ?>" class="flex flex-col items-center gap-1 text-green-600">
        <i class="ph ph-house-line text-2xl"></i>
        <span class="text-[10px] font-bold">হোম</span>
    </a>
    <a href="<?php echo SITE_URL; ?>#products" class="flex flex-col items-center gap-1 text-gray-400">
        <i class="ph ph-squares-four text-2xl"></i>
        <span class="text-[10px] font-bold">ক্যাটাগরি</span>
    </a>
    <button onclick="toggleCart()" class="flex flex-col items-center gap-1 text-gray-400 relative">
        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white -mt-8 border-4 border-white shadow-lg">
            <i class="ph ph-shopping-cart text-2xl"></i>
        </div>
        <span class="text-[10px] font-bold mt-1 text-green-600">কার্ট</span>
        <span id="cart-count-mobile" class="absolute top-[-25px] right-0 bg-red-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white">0</span>
    </button>
    <a href="<?php echo SITE_URL; ?>/wishlist.php" class="flex flex-col items-center gap-1 text-gray-400">
        <i class="ph ph-heart text-2xl"></i>
        <span class="text-[10px] font-bold">উইশলিস্ট</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/login.php" class="flex flex-col items-center gap-1 text-gray-400">
        <i class="ph ph-user text-2xl"></i>
        <span class="text-[10px] font-bold">প্রোফাইল</span>
    </a>
</div>

<style>
    .pb-safe-area { padding-bottom: calc(0.5rem + env(safe-area-inset-bottom)); }
</style>

<script>
    // Sync mobile cart count
    function syncMobileCart() {
        const count = document.getElementById('cart-count').innerText;
        const mob = document.getElementById('cart-count-mobile');
        if(mob) mob.innerText = count;
    }
    setInterval(syncMobileCart, 1000);
</script>
