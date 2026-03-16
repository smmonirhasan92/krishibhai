<?php
/**
 * Krishibhai - Manage Hero Slides
 * Features: List, Add, Edit, Delete slides with Image Upload
 */
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth Guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$error = "";

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "স্লাইড সফলভাবে ডিলিট করা হয়েছে!";
    } catch(Exception $e) { $error = "স্লাইড ডিলিট করতে ত্রুটি: " . $e->getMessage(); }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $button_text = trim($_POST['button_text'] ?? 'এখনই কিনুন');
    $button_link = trim($_POST['button_link'] ?? '#');
    $order_index = (int)($_POST['order_index'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image_path = $_POST['existing_image'] ?? '';

    // Handle File Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/slides/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'slide-' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'assets/uploads/slides/' . $file_name;
        }
    }

    if ($image_path) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE hero_slides SET title = ?, subtitle = ?, image_path = ?, button_text = ?, button_link = ?, order_index = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $image_path, $button_text, $button_link, $order_index, $is_active, $id]);
                $message = "স্লাইড সফলভাবে আপডেট করা হয়েছে!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, image_path, button_text, button_link, order_index, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $image_path, $button_text, $button_link, $order_index, $is_active]);
                $message = "স্লাইড সফলভাবে যোগ করা হয়েছে!";
            }
        } catch(Exception $e) { $error = "ডেটাবেস ত্রুটি: " . $e->getMessage(); }
    } else {
        $error = "স্লাইডের জন্য একটি ইমেজ প্রয়োজন।";
    }
}

// Fetch Slides
$slides = $pdo->query("SELECT * FROM hero_slides ORDER BY order_index ASC, id DESC")->fetchAll();

// Fetch single slide for editing
$editSlide = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM hero_slides WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editSlide = $stmt->fetch();
}

$adminTitle = 'হিরো স্লাইডার';
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="grid md:grid-cols-12 gap-8 items-start">
    
    <!-- Form Section -->
    <div class="md:col-span-4">
        <div class="admin-card sticky top-24">
            <div class="admin-card-header">
                <span class="admin-card-title"><?php echo $editSlide ? 'স্লাইড এডিট করুন' : 'নতুন স্লাইড তৈরি করুন'; ?></span>
            </div>
            <div class="admin-card-body">
                <?php if($message): ?> <div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-4 text-xs font-bold border border-emerald-100 flex items-center gap-2"><i class="ph ph-check-circle text-lg"></i> <?php echo $message; ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-4 text-xs font-bold border border-rose-100 flex items-center gap-2"><i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?></div> <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id" value="<?php echo $editSlide['id'] ?? ''; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo $editSlide['image_path'] ?? ''; ?>">
                    
                    <div>
                        <label class="admin-label">স্লাইড শিরোনাম</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($editSlide['title'] ?? ''); ?>"
                            placeholder="যেমন: আধুনিক কৃষি সমাধান" class="admin-input">
                    </div>

                    <div>
                        <label class="admin-label">বর্ণনা / সাব-টেক্সট</label>
                        <textarea name="subtitle" rows="3" placeholder="সংক্ষেপে কিছু লিখুন..." class="admin-input"><?php echo htmlspecialchars($editSlide['subtitle'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="admin-label">বাটন টেক্সট</label>
                            <input type="text" name="button_text" value="<?php echo htmlspecialchars($editSlide['button_text'] ?? 'এখনই কিনুন'); ?>" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">ক্রমানুসার</label>
                            <input type="number" name="order_index" value="<?php echo $editSlide['order_index'] ?? 0; ?>" class="admin-input">
                        </div>
                    </div>

                    <div>
                        <label class="admin-label">রিডাইরেক্ট লিঙ্ক</label>
                        <input type="text" name="button_link" value="<?php echo htmlspecialchars($editSlide['button_link'] ?? '#'); ?>" class="admin-input">
                    </div>

                    <div>
                        <label class="admin-label">স্লাইড ইমেজ</label>
                        <div class="relative group mt-1">
                            <?php if(!empty($editSlide['image_path'])): ?>
                                <img src="../<?php echo $editSlide['image_path']; ?>" class="w-full h-32 rounded-xl object-cover border border-slate-100 mb-3 grayscale group-hover:grayscale-0 transition duration-500">
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 py-2">
                        <input type="checkbox" name="is_active" id="is_active" <?php echo ($editSlide['is_active'] ?? 1) ? 'checked' : ''; ?> class="w-5 h-5 accent-emerald-500 rounded cursor-pointer">
                        <label for="is_active" class="text-xs font-black text-slate-600 cursor-pointer">এই স্লাইডটি লাইভ দেখান</label>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit" class="flex-1 btn btn-primary py-3 justify-center text-sm">
                            <i class="ph ph-magic-wand"></i>
                            <?php echo $editSlide ? 'আপডেট করুন' : 'স্লাইড লঞ্চ করুন'; ?>
                        </button>
                        <?php if($editSlide): ?>
                            <a href="slides.php" class="btn btn-ghost py-3 px-4">✕</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- List Section -->
    <div class="md:col-span-8 grid gap-5">
        <?php if(empty($slides)): ?>
            <div class="admin-card p-16 text-center border-2 border-dashed border-slate-200">
                <div class="text-5xl mb-4">🖼️</div>
                <h3 class="font-black text-slate-400">আপনার গ্যালারি খালি। আপনার প্রথম স্লাইড তৈরি করুন!</h3>
            </div>
        <?php else: ?>
            <?php foreach ($slides as $slide): ?>
            <div class="admin-card group hover:border-indigo-200 transition duration-300">
                <div class="flex flex-col md:flex-row h-full">
                    <!-- Artwork Preview -->
                    <div class="md:w-52 relative h-40 md:h-auto overflow-hidden">
                        <img src="../<?php echo $slide['image_path']; ?>" class="w-full h-full object-cover grayscale-[0.5] group-hover:grayscale-0 transition duration-700">
                        <div class="absolute top-2 left-2 px-3 py-1 rounded-full bg-black/70 backdrop-blur text-white text-[9px] font-black uppercase tracking-widest">Index #<?php echo $slide['order_index']; ?></div>
                    </div>
                    <!-- Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-black text-lg text-slate-900 leading-tight"><?php echo htmlspecialchars($slide['title']); ?></h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Link: <?php echo htmlspecialchars($slide['button_link']); ?></p>
                                </div>
                                <?php if($slide['is_active']): ?>
                                    <span class="status-badge" style="background:rgba(16,185,129,0.1); color:#10b981;">● সক্রিয়</span>
                                <?php else: ?>
                                    <span class="status-badge" style="background:#f3f4f6; color:#9ca3af;">○ ড্রাফট</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                        </div>
                        <div class="flex items-center gap-2 mt-6">
                            <a href="slides.php?edit=<?php echo $slide['id']; ?>" class="btn btn-ghost flex-1 justify-center text-xs">
                                <i class="ph ph-pencil-simple"></i> স্লাইড এডিট করুন
                            </a>
                            <a href="slides.php?delete=<?php echo $slide['id']; ?>" 
                               onclick="return confirm('অবিলম্বে এই স্লাইডটি ডিলিট করতে চান?')"
                               class="btn btn-danger px-4 justify-center">
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
