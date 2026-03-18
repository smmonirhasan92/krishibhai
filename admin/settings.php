<?php
/**
 * Site Settings & Theme Options - Enterprise Edition
 */
$adminTitle = 'সাইট সেটিংস';
require_once __DIR__ . '/includes/header.php';

// --- DATABASE AUTO-SETUP (WordPress Style) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        group_name VARCHAR(50) DEFAULT 'general',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert Default Settings if not exist
    $defaults = [
        ['site_name', 'কৃষিভাই', 'general'],
        ['site_tagline', 'আধুনিক কৃষি, সফল কৃষক', 'general'],
        ['site_phone', '01890-190214', 'contact'],
        ['site_whatsapp', '8801890190214', 'contact'],
        ['site_address', 'শৈলকুপা থানা রোড, শৈলকুপা, ঝিনাইদহ, বাংলাদেশ', 'contact'],
        ['theme_show_hero', '1', 'theme'],
        ['theme_show_categories', '1', 'theme'],
        ['theme_show_features', '1', 'theme'],
        ['theme_show_inquiry', '1', 'theme'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, group_name) VALUES (?, ?, ?)");
    foreach ($defaults as $row) { $stmt->execute($row); }
} catch (Exception $e) {}

// --- HANDLE SAVE ---
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $message = '<div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold flex items-center gap-2"><i class="ph ph-check-circle"></i> সফলভাবে সেভ হয়েছে!</div>';
        // Refresh global settings
        header("Location: settings.php?saved=1");
        exit();
    } catch (Exception $e) {
        $message = '<div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6">Error: ' . $e->getMessage() . '</div>';
    }
}

if (isset($_GET['saved'])) {
    $message = '<div class="bg-green-500/10 text-green-600 p-4 rounded-xl mb-6 font-black flex items-center gap-2 border border-green-500/20"><i class="ph ph-check-circle-bold"></i> সেটিংস সফলভাবে আপডেট করা হয়েছে!</div>';
}

// Fetch current values
$db_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while($row = $stmt->fetch()) { $db_settings[$row['setting_key']] = $row['setting_value']; }

function val($key, $default = '') {
    global $db_settings;
    return $db_settings[$key] ?? $default;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 italic">সাইট সেটিংস ও থিম অপশন</h1>
            <p class="text-slate-500 text-sm mt-1">আপনার ওয়েবসাইটের আইডেন্টিটি এবং হোমপেজের সেকশনগুলো এখান থেকে নিয়ন্ত্রণ করুন।</p>
        </div>
        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-100 px-4 py-2 rounded-full border border-slate-200">
            Enterprise Theme Engine v1.0
        </div>
    </div>

    <?php echo $message; ?>

    <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- General Settings -->
            <div class="space-y-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title flex items-center gap-2"><i class="ph ph-identification-card text-green-600"></i> সাইট আইডেন্টিটি</h3>
                    </div>
                    <div class="admin-card-body space-y-4">
                        <div>
                            <label class="admin-label">সাইটের নাম</label>
                            <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars(val('site_name')); ?>" class="admin-input" placeholder="e.g. কৃষিভাই">
                        </div>
                        <div>
                            <label class="admin-label">ট্যাগলাইন</label>
                            <input type="text" name="settings[site_tagline]" value="<?php echo htmlspecialchars(val('site_tagline')); ?>" class="admin-input" placeholder="e.g. আধুনিক কৃষি, সফল কৃষক">
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title flex items-center gap-2"><i class="ph ph-phone text-green-600"></i> কন্টাক্ট ইনফরমেশন</h3>
                    </div>
                    <div class="admin-card-body space-y-4">
                        <div>
                            <label class="admin-label">ফোন নম্বর</label>
                            <input type="text" name="settings[site_phone]" value="<?php echo htmlspecialchars(val('site_phone')); ?>" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">হোয়াটসঅ্যাপ নম্বর (International Format)</label>
                            <input type="text" name="settings[site_whatsapp]" value="<?php echo htmlspecialchars(val('site_whatsapp')); ?>" class="admin-input" placeholder="e.g. 88018XXXXXXXX">
                        </div>
                        <div>
                            <label class="admin-label">ঠিকানা</label>
                            <textarea name="settings[site_address]" class="admin-input h-20"><?php echo htmlspecialchars(val('site_address')); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme / Visibility Settings -->
            <div class="space-y-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title flex items-center gap-2"><i class="ph ph-paint-brush text-green-600"></i> হোমপেজ সেকশন কন্ট্রোল</h3>
                    </div>
                    <div class="admin-card-body space-y-4">
                        <!-- Hero Section -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div>
                                <span class="block font-bold text-sm text-slate-700">হিরো সেকশন (Banner)</span>
                                <span class="text-[10px] text-slate-400">হোমপেজের উপরের স্লাইডারটি দেখান</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[theme_show_hero]" value="0">
                                <input type="checkbox" name="settings[theme_show_hero]" value="1" <?php echo val('theme_show_hero') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <!-- Categories Section -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div>
                                <span class="block font-bold text-sm text-slate-700">ক্যাটাগরি গ্রিড</span>
                                <span class="text-[10px] text-slate-400">পণ্য ক্যাটাগরিগুলো দেখান</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[theme_show_categories]" value="0">
                                <input type="checkbox" name="settings[theme_show_categories]" value="1" <?php echo val('theme_show_categories') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <!-- Features Section -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div>
                                <span class="block font-bold text-sm text-slate-700">কেন আমরা (Features)</span>
                                <span class="text-[10px] text-slate-400">ব্যবসায়ের বিশেষত্বগুলো দেখান</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[theme_show_features]" value="0">
                                <input type="checkbox" name="settings[theme_show_features]" value="1" <?php echo val('theme_show_features') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <!-- Inquiry Section -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div>
                                <span class="block font-bold text-sm text-slate-700">পরামর্শ ফরম (Inquiry)</span>
                                <span class="text-[10px] text-slate-400">পরামর্শ নেওয়ার ফরমটি দেখান</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[theme_show_inquiry]" value="0">
                                <input type="checkbox" name="settings[theme_show_inquiry]" value="1" <?php echo val('theme_show_inquiry') == '1' ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" name="save_settings" class="btn btn-primary flex-1 py-4 justify-center text-base">
                        <i class="ph ph-floppy-disk"></i> সেটিংস সেভ করুন
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
