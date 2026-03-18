<?php
ob_start();
/**
 * Krishibhai - Admin Panel Header & Sidebar
 * Design: Sleek dark sidebar, clean top bar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    ['label' => 'ড্যাশবোর্ড',  'icon' => 'house',         'url' => 'dashboard.php'],
    ['label' => 'অর্ডার',     'icon' => 'package',       'url' => 'orders.php'],
    ['label' => 'পণ্য',       'icon' => 'tag',           'url' => 'products.php'],
    ['label' => 'ক্যাটাগরি',   'icon' => 'folder-simple', 'url' => 'categories.php'],
    ['label' => 'ইনকোয়ারি',  'icon' => 'envelope',      'url' => 'inquiries.php'],
    ['label' => 'স্লাইডার',    'icon' => 'images',        'url' => 'slides.php'],
    ['label' => 'ইনভেন্টরি',   'icon' => 'stack',         'url' => 'inventory.php'],
    ['label' => 'বারকোড স্ক্যান','icon' => 'barcode',       'url' => 'barcode-scanner.php'],
    ['label' => 'রিপোর্ট',     'icon' => 'chart-bar',     'url' => 'reports.php'],
];

// Quick stats for sidebar badge
$pendingOrders = 0;
try {
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($adminTitle ?? 'ড্যাশবোর্ড'); ?> — কৃষিভাই এডমিন</title>
    
    <!-- App-like Mobile Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0f1117">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --sidebar-bg: #0f1117;
            --sidebar-border: rgba(255,255,255,0.06);
            --accent: #629d25;
            --accent-dim: rgba(98, 157, 37, 0.12);
            --text-muted: #6b7280;
            --surface: #ffffff;
            --bg: #f4f6f9;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; min-height: 100vh; }
        
        /* === SIDEBAR === */
        #admin-sidebar {
            width: 260px; flex-shrink: 0;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            height: 100vh; position: sticky; top: 0;
            display: flex; flex-direction: column;
            overflow: hidden;
            transition: transform 0.3s ease;
            z-index: 50;
        }
        .sidebar-logo {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--sidebar-border);
        }
        .sidebar-logo-mark {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #629d25, #4a771c);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 900; color: white; font-size: 14px;
            box-shadow: 0 8px 20px -4px rgba(98, 157, 37, 0.5);
            flex-shrink: 0;
        }
        
        /* Nav links */
        .admin-nav { flex: 1; padding: 1rem 0.75rem; overflow-y: auto; }
        .admin-nav a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.625rem 0.875rem; border-radius: 10px;
            font-size: 0.8125rem; font-weight: 600;
            color: #9ca3af; text-decoration: none;
            transition: all 0.2s; margin-bottom: 2px;
            position: relative;
        }
        .admin-nav a:hover { background: rgba(255,255,255,0.05); color: #e5e7eb; }
        .admin-nav a.active {
            background: var(--accent-dim);
            color: #629d25;
        }
        .admin-nav a.active i { color: #629d25; }
        .admin-nav a i { font-size: 1rem; width: 18px; text-align: center; }
        .nav-badge {
            margin-left: auto; background: #629d25;
            color: white; font-size: 10px; font-weight: 800;
            padding: 1px 7px; border-radius: 20px; min-width: 20px; text-align: center;
        }
        .nav-section-label {
            font-size: 0.625rem; font-weight: 800; letter-spacing: 0.1em;
            text-transform: uppercase; color: #374151; padding: 0.5rem 0.875rem 0.25rem;
        }
        
        /* Sidebar footer */
        .sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid var(--sidebar-border);
        }
        .admin-user-card {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem; border-radius: 10px;
            background: rgba(255,255,255,0.04);
            cursor: pointer;
        }
        .admin-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #629d25, #4a771c);
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; color: white; font-size: 12px; flex-shrink: 0;
        }
        
        /* === MAIN CONTENT === */
        #admin-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        
        /* Top bar */
        #admin-topbar {
            height: 60px; background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center;
            padding: 0 1.5rem; gap: 1rem; flex-shrink: 0;
            position: sticky; top: 0; z-index: 40;
        }
        
        /* Mobile Adjustments */
        @media (max-width: 1024px) {
            #admin-sidebar {
                position: fixed;
                left: 0;
                transform: translateX(-100%);
            }
            #admin-sidebar.active {
                transform: translateX(0);
            }
            #sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 45;
                backdrop-filter: blur(4px);
            }
            #sidebar-overlay.active {
                display: block;
            }
            #admin-topbar { padding: 0 1rem; }
            .admin-page-body { padding: 1rem; }
            .topbar-actions .topbar-btn span { display: none; }
            .kpi-card { padding: 1rem; }
            .kpi-value { font-size: 1.5rem; }
        }
        .topbar-title { font-size: 1rem; font-weight: 800; color: #111827; }
        .topbar-breadcrumb { font-size: 0.75rem; color: #9ca3af; margin-top: 1px; }
        .topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 0.75rem; }
        .topbar-btn {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: 8px;
            font-size: 0.8125rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .topbar-btn-ghost { background: #f3f4f6; color: #374151; }
        .topbar-btn-ghost:hover { background: #e5e7eb; }
        
        /* === CARDS === */
        .admin-card {
            background: white; border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .admin-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between;
        }
        .admin-card-title { font-size: 0.9375rem; font-weight: 800; color: #111827; }
        .admin-card-body { padding: 1.5rem; }
        
        /* KPI Cards */
        .kpi-card {
            background: white; border-radius: 16px;
            border: 1px solid #e5e7eb; padding: 1.5rem;
            position: relative; overflow: hidden;
        }
        .kpi-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; margin-bottom: 1rem;
        }
        .kpi-value { font-size: 1.875rem; font-weight: 900; color: #111827; line-height: 1; }
        .kpi-label { font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.25rem; }
        .kpi-trend { font-size: 0.75rem; font-weight: 700; margin-top: 0.75rem; display: flex; align-items: center; gap: 0.25rem; }
        .kpi-trend.up { color: #10b981; }
        .kpi-trend.neutral { color: #6b7280; }
        .kpi-bg-accent { position: absolute; right: -20px; bottom: -20px; width: 100px; height: 100px; border-radius: 50%; opacity: 0.04; }
        
        /* Tables */
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { 
            padding: 0.75rem 1rem; text-align: left; 
            font-size: 0.6875rem; font-weight: 800; 
            color: #9ca3af; text-transform: uppercase; letter-spacing: 0.08em;
            border-bottom: 1px solid #f3f4f6; 
        }
        .admin-table td { 
            padding: 0.875rem 1rem; font-size: 0.875rem;
            border-bottom: 1px solid #f9fafb; color: #374151;
        }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: #fafafa; }
        
        /* Status badges */
        .status-badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.25rem 0.75rem; border-radius: 6px;
            font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .status-pending  { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* Action buttons */
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.125rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 700; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #629d25, #4a771c); color: white; box-shadow: 0 4px 12px -2px rgba(98, 157, 37, 0.4); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px -4px rgba(98, 157, 37, 0.5); }
        .btn-ghost { background: #f3f4f6; color: #374151; }
        .btn-ghost:hover { background: #e5e7eb; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }
        
        /* Page body */
        .admin-page-body { padding: 2rem; flex: 1; }
        
        /* Form elements */
        .admin-input {
            width: 100%; padding: 0.625rem 0.875rem; border-radius: 8px;
            border: 1.5px solid #e5e7eb; font-size: 0.875rem;
            color: #111827; background: white; transition: border-color 0.2s;
            font-family: 'Inter', sans-serif; outline: none;
        }
        .admin-input:focus { border-color: #629d25; box-shadow: 0 0 0 3px rgba(98, 157, 37, 0.1); }
        .admin-label { font-size: 0.75rem; font-weight: 700; color: #374151; margin-bottom: 0.375rem; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        
        /* Charts */
        .chart-wrap { position: relative; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    </style>
    <script>
        function toggleSidebar() {
            document.getElementById('admin-sidebar').classList.toggle('active');
            document.getElementById('sidebar-overlay').classList.toggle('active');
        }
    </script>
    <?php echo $adminExtraHead ?? ''; ?>
</head>
<body>

<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside id="admin-sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div class="sidebar-logo-mark">KB</div>
            <div>
                <div style="font-weight:800; color:#f9fafb; font-size:0.9375rem; line-height:1.2;">কৃষিভাই</div>
                <div style="font-size:0.625rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.12em;">এডমিন প্যানেল</div>
            </div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="admin-nav">
        <div class="nav-section-label">মেনু নেভিগেশন</div>
        <?php foreach($nav_items as $item):
            $isActive = ($current_page == $item['url']);
        ?>
        <a href="<?php echo $item['url']; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
            <i class="ph ph-<?php echo $item['icon']; ?>"></i>
            <span><?php echo $item['label']; ?></span>
            <?php if ($item['url'] === 'orders.php' && $pendingOrders > 0): ?>
                <span class="nav-badge"><?php echo $pendingOrders; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <div class="nav-section-label" style="margin-top:1rem;">কুইক লিংক</div>
        <a href="../" target="_blank">
            <i class="ph ph-arrow-square-out"></i>
            <span>ওয়েবসাইট দেখুন</span>
        </a>
        <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i class="ph ph-gear"></i>
            <span>সাইট সেটিংস</span>
        </a>
    </nav>

    <!-- User / Logout -->
    <div class="sidebar-footer">
        <div class="admin-user-card">
            <div class="admin-avatar">এ</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.8125rem; font-weight:700; color:#f9fafb; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">এডমিন</div>
                <div style="font-size:0.6875rem; color:#6b7280;">সুপার এডমিন</div>
            </div>
            <a href="logout.php" title="Logout" style="color:#6b7280; text-decoration:none; padding:4px;">
                <i class="ph ph-sign-out" style="font-size:1.1rem;"></i>
            </a>
        </div>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div id="admin-main">
    <!-- Top Bar -->
    <div id="admin-topbar">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="ph ph-list" style="font-size:1.5rem;"></i>
        </button>
        <div>
            <div class="topbar-title"><?php echo htmlspecialchars($adminTitle ?? 'Dashboard'); ?></div>
            <div class="topbar-breadcrumb"><?php echo date('l, F j, Y'); ?></div>
        </div>
        <div class="topbar-actions">
            <?php echo $adminTopbarAction ?? ''; ?>
            <a href="../admin/orders.php" class="topbar-btn topbar-btn-ghost">
                <i class="ph ph-bell"></i>
                <?php if ($pendingOrders > 0): ?>
                    <span style="color:#629d25; font-weight:800;"><?php echo $pendingOrders; ?> পেন্ডিং</span>
                <?php else: ?>
                    <span>কোন অর্ডার নেই</span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Page body injected here -->
    <div class="admin-page-body">

    <!-- Live Notification Sound -->
    <audio id="orderNotificationSound" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" type="audio/mpeg">
    </audio>

    <script>
    let lastPendingCount = <?php echo (int)$pendingOrders; ?>;
    
    function checkNewOrders() {
        fetch('api-orders-check.php?cache=' + Date.now())
            .then(response => response.json())
            .then(data => {
                console.log('Order Poll:', data); // Debug Log
                if (data.success) {
                    const newCount = data.pending_count;
                    
                    if (newCount > lastPendingCount) {
                        // Play Sound
                        const sound = document.getElementById('orderNotificationSound');
                        if (sound) sound.play().catch(e => console.log('Audio play failed:', e));
                        
                        // Show Notification (Custom Toast)
                        showOrderToast(newCount - lastPendingCount);
                        
                        // Update UI Badges
                        updateOrderBadges(newCount);
                    }
                    lastPendingCount = newCount;
                }
            })
            .catch(err => console.error('Notification Error:', err));
    }

    function showOrderToast(newAmount) {
        const toast = document.createElement('div');
        toast.style = "position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: #629d25; color: white; padding: 1.25rem 2rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(98, 157, 37, 0.3); display: flex; align-items: center; gap: 1rem; border: 2px solid rgba(255,255,255,0.2); animation: slideIn 0.5s cubic-bezier(0.1, 0.9, 0.2, 1);";
        toast.innerHTML = `
            <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                <i class="ph ph-package" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <div style="font-weight:900; font-size:1rem; line-height:1.1;">নতুন অর্ডার!</div>
                <div style="font-size:0.75rem; font-weight:700; opacity:0.9;">আপনার \${newAmount} টি নতুন অর্ডার আছে</div>
            </div>
            <button onclick="this.parentElement.remove()" style="margin-left:10px; color:white; opacity:0.6; font-size:1.2rem;">×</button>
        `;
        document.body.appendChild(toast);
        
        // Auto hide after 8 seconds
        setTimeout(() => { if(toast.parentElement) toast.remove(); }, 8000);
    }

    function updateOrderBadges(count) {
        // Update Sidebar Badge
        const sidebarBadge = document.querySelector('.nav-badge');
        if (sidebarBadge) {
            sidebarBadge.innerText = count;
            sidebarBadge.style.display = count > 0 ? 'block' : 'none';
        }
        
        // Update Topbar Bell Text
        const bellText = document.querySelector('.topbar-btn-ghost span');
        if (bellText) {
            bellText.innerHTML = count > 0 ? `<span style="color:#629d25; font-weight:800;">${count} পেন্ডিং</span>` : 'কোন অর্ডার নেই';
        }
    }

    // Add CSS for animation if not present
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.innerHTML = `@keyframes slideIn { from { transform: translateX(100%) scale(0.5); opacity: 0; } to { transform: translateX(0) scale(1); opacity: 1; } }`;
        document.head.appendChild(style);
    }

    // Start Polling every 10 seconds
    setInterval(checkNewOrders, 10000);
    console.log('Live Notifications Active');
    </script>
