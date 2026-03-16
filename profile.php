<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href="login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Address Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_address') {
    $label = trim($_POST['address_label'] ?? 'Home');
    $line = trim($_POST['address_line'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip_code'] ?? '');
    
    if ($line && $city) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, address_label, address_line, city, zip_code, is_default) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$user_id, $label, $line, $city, $zip]);
            // Make others non-default
            $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND id != ?")->execute([$user_id, $pdo->lastInsertId()]);
            $success = "New address saved beautifully.";
        } catch(Exception $e) {
            $error = "Failed to save address.";
        }
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>

<div class="bg-gray-50 py-12 min-h-[70vh] relative overflow-hidden">
    <!-- Decorative Blurs -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-amber-400/10 rounded-full blur-3xl -z-10 mix-blend-multiply"></div>

    <div class="container mx-auto px-4 max-w-6xl relative z-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-8">My Account</h1>
        
        <div class="flex flex-col md:flex-row gap-8">
            
            <!-- Sidebar -->
            <div class="md:w-1/3 lg:w-1/4">
                <div class="glass-card bg-white/80 rounded-[2rem] p-6 shadow-xl border border-white sticky top-24">
                    <div class="w-24 h-24 bg-gradient-to-br from-amber-400 to-amber-600 rounded-[1.5rem] flex items-center justify-center text-white text-4xl font-black mb-5 mx-auto shadow-lg shadow-amber-500/30">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <h3 class="text-xl font-black text-center text-slate-900 tracking-tight"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="text-slate-500 text-sm text-center mb-8 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>

                    <div class="space-y-1.5">
                        <a href="profile.php" class="block w-full px-5 py-3.5 bg-amber-50 text-amber-700 font-bold rounded-2xl transition flex items-center gap-3">
                            <span class="text-xl">🧑‍💻</span> Profile Center
                        </a>
                        <?php if($user['role'] === 'admin'): ?>
                        <a href="admin/" class="block w-full px-5 py-3.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-bold rounded-2xl transition flex items-center gap-3 group">
                            <span class="text-xl group-hover:scale-110 transition-transform">⚙️</span> Control Panel
                        </a>
                        <?php endif; ?>
                        <a href="wishlist.php" class="block w-full px-5 py-3.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-bold rounded-2xl transition flex items-center gap-3 group">
                            <span class="text-xl group-hover:scale-110 transition-transform">❤️</span> My Wishlist
                        </a>
                        <a href="logout.php" class="block w-full px-5 py-3.5 text-rose-600 hover:bg-rose-50 font-bold rounded-2xl transition mt-4 flex items-center gap-3 group">
                            <span class="text-xl group-hover:scale-110 transition-transform">🚪</span> Sign Out
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="md:w-2/3 lg:w-3/4 space-y-8">
                
                <?php if($success): ?>
                <div class="bg-emerald-50 text-emerald-600 p-5 rounded-2xl text-sm font-bold border border-emerald-100 flex items-center gap-3">
                    <span class="text-xl p-1 bg-emerald-200 rounded-full">✅</span> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                <?php if($error): ?>
                <div class="bg-rose-50 text-rose-600 p-5 rounded-2xl text-sm font-bold border border-rose-100 flex items-center gap-3">
                    <span class="text-xl p-1 bg-rose-200 rounded-full">❌</span> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <!-- Addresses Area -->
                <div class="glass-card bg-white/80 rounded-[2.5rem] p-8 md:p-10 shadow-xl border border-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl -z-10 translate-x-10 -translate-y-10"></div>
                    
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">Delivery Addresses</h2>
                            <p class="text-slate-500 font-medium text-sm mt-1">Manage where your premium kitchen items are delivered.</p>
                        </div>
                        <button onclick="document.getElementById('address-form').classList.toggle('hidden')" class="bg-slate-900 hover:bg-black text-white text-sm font-bold px-5 py-3 rounded-xl transition flex items-center gap-2 shadow-lg hover:-translate-y-0.5 whitespace-nowrap">
                            <span>+</span> Add New
                        </button>
                    </div>

                    <form id="address-form" method="POST" class="hidden bg-slate-50 p-8 rounded-[2rem] border border-slate-100 mb-8 space-y-5 shadow-sm">
                        <input type="hidden" name="action" value="save_address">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">Address Label</label>
                                <input type="text" name="address_label" placeholder="e.g. Home, HQ" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl outline-none focus:border-amber-400 text-sm font-medium transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">City / Region</label>
                                <input type="text" name="city" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl outline-none focus:border-amber-400 text-sm font-medium transition shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">Full Detailed Address</label>
                            <textarea name="address_line" rows="2" required class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl outline-none focus:border-amber-400 text-sm font-medium transition shadow-sm resize-none" placeholder="House 123, Road 45, Section..."></textarea>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="bg-amber-500 text-slate-900 font-black py-3 px-8 rounded-xl hover:bg-amber-400 transition shadow-lg hover:-translate-y-0.5">Save Address ➔</button>
                        </div>
                    </form>

                    <?php if(empty($addresses)): ?>
                    <div class="bg-slate-50 border border-dashed border-slate-200 rounded-3xl p-10 text-center">
                        <div class="text-5xl opacity-20 mb-4">📍</div>
                        <div class="text-slate-500 font-bold">No addresses saved yet</div>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <?php foreach($addresses as $addr): ?>
                        <div class="p-6 border <?php echo $addr['is_default'] ? 'border-amber-400 bg-amber-50/50 shadow-md' : 'border-slate-100 bg-white hover:border-amber-200 transition'; ?> rounded-[2rem] relative group cursor-default">
                            <?php if($addr['is_default']): ?>
                            <span class="absolute top-5 right-5 text-[9px] bg-amber-500 text-slate-900 font-black px-2.5 py-1 rounded-md uppercase tracking-widest shadow-sm">Default</span>
                            <?php endif; ?>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="bg-slate-100 w-8 h-8 rounded-full flex items-center justify-center text-sm shadow-sm group-hover:bg-amber-100 transition">📍</span>
                                <h4 class="font-black text-slate-900 text-lg tracking-tight"><?php echo htmlspecialchars($addr['address_label']); ?></h4>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed font-medium pl-[44px]">
                                <?php echo htmlspecialchars($addr['address_line']); ?><br>
                                <?php echo htmlspecialchars($addr['city']); ?><?php echo $addr['zip_code'] ? ' - '.$addr['zip_code'] : ''; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Orders Block Placeholder -->
                <div class="glass-card bg-white/80 rounded-[2.5rem] p-8 md:p-10 shadow-xl border border-white">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2.5 bg-slate-100 rounded-full text-xl shadow-inner">📦</div>
                        <h2 class="text-2xl font-black text-slate-900">Recent Box Drops</h2>
                    </div>
                    <div class="bg-slate-50 border border-dashed border-slate-200 rounded-3xl p-12 text-center relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 text-8xl opacity-5 rotate-12 -z-10">🛒</div>
                        <p class="text-slate-500 font-bold mb-6 text-lg tracking-tight">You haven't placed any premium orders yet.</p>
                        <a href="index.php" class="inline-flex items-center gap-2 text-amber-600 bg-amber-50 font-black px-6 py-3 rounded-full hover:bg-amber-100 hover:text-amber-700 transition">
                            Explore Catalog ➔
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
