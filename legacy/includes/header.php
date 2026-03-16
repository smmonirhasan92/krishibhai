<?php
/**
 * Zaman Kitchens - Header Component
 * Features: Sticky header, Category Dropdown, Search Bar
 */
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
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' - Premium Kitchen Accessories & Sinks'; ?></title>
    <meta name="description" content="<?php echo isset($pageDesc) ? $pageDesc : "Bangladesh's best kitchen accessories, sinks, hoods, and cabinets. Shop online with fast delivery."; ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .group:hover .group-hover\:scale-105 { transform: scale(1.05); }
        /* WhatsApp button pulse */
        @keyframes pulse-ring { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.3); opacity: 0; } }
        .wa-pulse::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; background: #25d366; animation: pulse-ring 1.5s infinite; z-index: -1; }
    </style>

    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="bg-white text-gray-900 antialiased">

<!-- ===== TOP BAR ===== -->
<div class="bg-amber-600 text-white text-xs text-center py-1.5 px-4">
    🚚 Free Delivery in Dhaka &nbsp;|&nbsp; 📞 <a href="tel:01700000000" class="underline hover:no-underline font-bold">01700-000000</a> &nbsp;|&nbsp; 💬 WhatsApp Available 10AM–8PM
</div>

<!-- ===== MAIN HEADER ===== -->
<header class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex items-center h-16 gap-4">

            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="flex-shrink-0 flex items-center gap-2">
                <div class="w-9 h-9 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div class="leading-none">
                    <span class="font-extrabold text-gray-900 text-base tracking-tight">Zaman</span>
                    <span class="font-extrabold text-amber-600 text-base tracking-tight"> Kitchens</span>
                </div>
            </a>

            <!-- Category Dropdown (Desktop) -->
            <div class="hidden md:block relative group ml-4">
                <button class="flex items-center gap-1.5 font-semibold text-sm text-gray-700 hover:text-amber-600 transition px-3 py-2 rounded-lg hover:bg-gray-50 border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    All Categories
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Dropdown Menu -->
                <div class="absolute top-full left-0 mt-1 bg-white rounded-xl shadow-2xl border border-gray-100 p-2 hidden group-hover:block min-w-56 z-50">
                    <?php foreach($categories as $cat): ?>
                    <a href="<?php echo SITE_URL; ?>/category/<?php echo $cat['slug']; ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 font-medium transition">
                        <span class="w-2 h-2 bg-amber-400 rounded-full flex-shrink-0"></span>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Bar -->
            <form action="<?php echo SITE_URL; ?>/search" method="GET" class="flex-1 max-w-xl">
                <div class="relative">
                    <input type="text" name="q" placeholder="Search sinks, hoods, cabinets..."
                        class="w-full pl-4 pr-12 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-amber-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Action Icons -->
            <div class="flex items-center gap-2 ml-auto">
                <a href="tel:01700000000" class="hidden md:flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">
                    📞 <span>Call Now</span>
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

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 bg-white">
        <div class="container mx-auto px-4 py-3 grid grid-cols-2 gap-2">
            <?php foreach($categories as $cat): ?>
            <a href="<?php echo SITE_URL; ?>/category/<?php echo $cat['slug']; ?>" class="text-sm text-gray-700 hover:text-amber-600 font-medium py-1.5 px-2">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</header>
