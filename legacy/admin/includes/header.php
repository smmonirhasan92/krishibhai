<?php
/**
 * Zaman Kitchens - Professional Admin Header & Sidebar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';

// Auth Guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Sidebar Links
$nav_items = [
    ['label' => 'Dashboard', 'icon' => '🏠', 'url' => 'dashboard.php'],
    ['label' => 'Orders', 'icon' => '📦', 'url' => 'orders.php'],
    ['label' => 'Products', 'icon' => '🏷️', 'url' => 'products.php'],
    ['label' => 'Inquiries', 'icon' => '📩', 'url' => 'inquiries.php'],
    ['label' => 'Categories', 'icon' => '📁', 'url' => 'categories.php'],
    ['label' => 'Hero Slides', 'icon' => '🖼️', 'url' => 'slides.php'],
    ['label' => 'Reports', 'icon' => '📊', 'url' => 'reports.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $adminTitle ?? 'Admin Dashboard'; ?> | Zaman Kitchens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border-right: 1px solid rgba(0, 0, 0, 0.05); }
        .glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .sidebar-link { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { background: rgba(245, 158, 11, 0.05); color: #f59e0b; padding-left: 2rem; }
        .sidebar-link-active { background: #f59e0b; color: white; box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3); }
        .sidebar-link-active:hover { color: white; padding-left: 1.5rem; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen flex">

<!-- Professional Vertical Sidebar -->
<aside class="w-72 sidebar h-screen sticky top-0 flex flex-col z-50">
    <div class="p-8 flex items-center gap-4 mb-8">
        <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center font-black text-white text-xl shadow-xl shadow-amber-500/20">ZK</div>
        <div>
            <h1 class="font-black text-lg tracking-tight">Zaman Kitchen</h1>
            <div class="text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase">Control Center</div>
        </div>
    </div>

    <nav class="flex-1 px-6 space-y-2">
        <?php foreach($nav_items as $item): 
            $isActive = ($current_page == $item['url']);
        ?>
        <a href="<?php echo $item['url']; ?>" 
           class="sidebar-link flex items-center gap-4 px-6 py-4 rounded-2xl font-bold text-sm <?php echo $isActive ? 'sidebar-link-active' : 'text-slate-500'; ?>">
            <span class="text-xl"><?php echo $item['icon']; ?></span>
            <span><?php echo $item['label']; ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="p-6 mt-auto">
        <div class="bg-slate-900 rounded-3xl p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">System Health</div>
                <div class="text-lg font-black mb-4">Pro Plan</div>
                <a href="logout.php" class="block text-center py-3 bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold transition">Logout</a>
            </div>
            <div class="absolute -right-4 -bottom-4 text-6xl opacity-10 group-hover:rotate-12 transition-transform">💎</div>
        </div>
    </div>
</aside>

<!-- Main Content Area -->
<main class="flex-1">
    <!-- Main Header -->
    <header class="h-20 flex items-center justify-between px-12 sticky top-0 z-40 bg-[#F8FAFC]/80 backdrop-blur-md">
        <div>
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]"><?php echo date('l, F j, Y'); ?></h2>
            <div class="text-xl font-black text-slate-900"><?php echo $adminTitle ?? 'Overview'; ?></div>
        </div>
        
        <div class="flex items-center gap-6">
            <a href="../" target="_blank" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-amber-600 transition">
                <span>View Site</span>
                <i class="ph ph-arrow-square-out text-lg"></i>
            </a>
            <div class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden">
                <img src="https://ui-avatars.com/api/?name=Admin&background=f59e0b&color=fff" alt="Admin">
            </div>
        </div>
    </header>
