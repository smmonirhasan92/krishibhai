<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

// Handle Add/Edit/Delete Category
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_name'])) {
    $name = trim($_POST['cat_name']);
    $cat_id = $_POST['cat_id'] ?? null;
    $hero_image = $_POST['existing_image'] ?? null;

    if (empty($name)) {
        $error = "❌ ক্যাটাগরির নাম দেওয়া আবশ্যক!";
    } else {
        try {
            // Robust Slug generation and uniqueness check
            $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if (!$base_slug) $base_slug = 'category';

            $slug = $base_slug;
            $counter = 1;
            while (true) {
                $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $check->execute([$slug, (int)$cat_id]);
                if (!$check->fetch()) break;
                $slug = $base_slug . '-' . $counter++;
            }

            if (!empty($_FILES['cat_image']['name'])) {
                $upload_dir = __DIR__ . '/../assets/uploads/ca/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $file_ext = pathinfo($_FILES['cat_image']['name'], PATHINFO_EXTENSION);
                $file_name = $slug . '-' . time() . '.' . $file_ext;
                if (move_uploaded_file($_FILES['cat_image']['tmp_name'], $upload_dir . $file_name)) {
                    $hero_image = 'assets/uploads/ca/' . $file_name;
                }
            }

            if (!empty($cat_id)) {
                $pdo->prepare("UPDATE categories SET name=?, slug=?, hero_image=? WHERE id=?")->execute([$name, $slug, $hero_image, $cat_id]);
                $message = "✅ ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে!";
            } else {
                $pdo->prepare("INSERT INTO categories (name, slug, hero_image) VALUES (?, ?, ?)")->execute([$name, $slug, $hero_image]);
                $message = "✅ ক্যাটাগরি সফলভাবে যোগ করা হয়েছে!";
            }
            // If it's a success, we could redirect, but let's keep it to show the message first
            header("Location: categories.php?msg=" . urlencode($message)); exit();
        } catch (Exception $e) {
            $error = "❌ ত্রুটি: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$_GET['delete']]);
    header("Location: categories.php"); exit();
}

$adminTitle = 'ক্যাটাগরি';
include_once __DIR__ . '/includes/header.php';

$categories = [];
try {
    $categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name ASC")->fetchAll();
} catch(Exception $e) {}
?>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem; align-items: start;">
    <!-- Add/Edit Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title"><?php echo isset($_GET['edit']) ? 'ক্যাটাগরি এডিট' : 'নতুন ক্যাটাগরি তৈরি'; ?></span>
        </div>
        <div class="admin-card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php 
                $editCat = null;
                if(isset($_GET['edit'])) {
                    foreach($categories as $c) if($c['id'] == $_GET['edit']) $editCat = $c;
                }
                ?>
                <input type="hidden" name="cat_id" value="<?php echo $editCat['id'] ?? ''; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $editCat['hero_image'] ?? ''; ?>">
                
                <div style="margin-bottom:1.25rem;">
                    <label class="admin-label">ক্যাটাগরির নাম</label>
                    <input type="text" name="cat_name" required class="admin-input" placeholder="যেমন: সার" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>">
                </div>
                
                <div style="margin-bottom:1.5rem;">
                    <label class="admin-label">ক্যাটাগরি আইকন / ছবি</label>
                    <div style="display:flex; align-items:center; gap:1.5rem; background:#f8fafc; padding:1rem; border-radius:16px; border:1px solid #e2e8f0;">
                        <img id="cat-preview" src="<?php echo !empty($editCat['hero_image']) ? '../' . $editCat['hero_image'] : 'https://placehold.co/100x100/f1f5f9/94a3b8?text=Img'; ?>" 
                             style="width:70px; height:70px; border-radius:50%; object-fit:cover; border:2px solid #629d25; padding:2px; background:white;">
                        <div style="flex:1;">
                            <label class="cursor-pointer">
                                <span style="display:inline-block; background:#629d25; color:white; font-size:0.75rem; font-weight:800; padding:0.5rem 1rem; border-radius:8px; box-shadow:0 4px 10px rgba(98, 157, 37, 0.2);">ছবি পরিবর্তন</span>
                                <input type="file" name="cat_image" accept="image/*" class="hidden" onchange="document.getElementById('cat-preview').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                            <p style="font-size:0.65rem; color:#9ca3af; margin-top:0.5rem; font-weight:600;">পরামর্শ: নিখুঁত বৃত্তের জন্য বর্গাকার ছবি ব্যবহার করুন</p>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;"><?php echo $editCat ? 'আপডেট করুন' : 'যোগ করুন'; ?></button>
                    <?php if($editCat): ?>
                        <a href="categories.php" class="btn btn-ghost">বাতিল</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <span class="admin-card-title">ক্যাটাগরি পরিচালনা</span>
            <span style="font-size:0.75rem; color:#9ca3af; font-weight:700;"><?php echo count($categories); ?> টি ক্যাটাগরি</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>নাম</th>
                        <th>স্লাগ</th>
                        <th>পণ্য</th>
                        <th style="text-align:right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:3rem; color:#9ca3af;">এখনো কোন ক্যাটাগরি নেই।</td></tr>
                    <?php endif; ?>
                    <?php foreach($categories as $i => $c): ?>
                    <tr>
                        <td style="color:#d1d5db; font-weight:700;"><?php echo $i+1; ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <?php 
                                $catImg = !empty($c['hero_image']) ? '../' . $c['hero_image'] : null;
                                ?>
                                <?php if ($catImg): ?>
                                    <img src="<?php echo htmlspecialchars($catImg); ?>" 
                                         style="width:48px; height:48px; border-radius:50%; object-fit: cover; background:#f8fafc; border:1px solid #f1f5f9;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="thumbnail-fallback" style="display: <?php echo $catImg ? 'none' : 'flex'; ?>; width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, #f8fafc, #f1f5f9); border:1px solid #e2e8f0; align-items:center; justify-center; flex-shrink:0;">
                                    <i class="ph ph-folder text-slate-400 text-lg" style="margin:auto;"></i>
                                </div>
                                <div style="min-width:0;">
                                    <div style="font-weight:800; color:#111827;"><?php echo htmlspecialchars($c['name']); ?></div>
                                    <div style="font-family:monospace; font-size:0.6875rem; color:#9ca3af; margin-top:2px;">/<?php echo $c['slug']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <span style="font-weight:700; color:#629d25;"><?php echo $c['product_count']; ?></span>
                                <span style="font-size:0.6875rem; color:#9ca3af; font-weight:600;">টি</span>
                            </div>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex; gap:0.4rem;">
                                <a href="../category/<?php echo $c['slug']; ?>" target="_blank" class="btn btn-ghost" style="padding:0.35rem 0.65rem;" title="View on Site">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <a href="categories.php?edit=<?php echo $c['id']; ?>" class="btn btn-ghost" style="padding:0.35rem 0.75rem; font-size:0.75rem; font-weight:700;">এডিট</a>
                                <button onclick="if(confirm('এই ক্যাটাগরি কি মুছে ফেলতে চান?')) window.location.href='categories.php?delete=<?php echo $c['id']; ?>'" class="btn btn-danger" style="padding:0.35rem 0.75rem; font-size:0.75rem; font-weight:700;">মুছে ফেলুন</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
