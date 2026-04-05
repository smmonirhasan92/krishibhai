<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

// ===== AJAX: Drag & Drop Reorder =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    header('Content-Type: application/json');
    $order = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($order)) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
            foreach ($order as $idx => $id) {
                $stmt->execute([$idx, (int)$id]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    }
    exit();
}

// Handle Add/Edit/Delete Category
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_name'])) {
    $name = trim($_POST['cat_name']);
    $cat_id = $_POST['cat_id'] ?? null;
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
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
                $pdo->prepare("UPDATE categories SET name=?, slug=?, hero_image=?, parent_id=?, sort_order=? WHERE id=?")->execute([$name, $slug, $hero_image, $parent_id, $sort_order, $cat_id]);
                $message = "✅ ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে!";
            } else {
                $pdo->prepare("INSERT INTO categories (name, slug, hero_image, parent_id, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$name, $slug, $hero_image, $parent_id, $sort_order]);
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
    $categories = $pdo->query("SELECT c.*, p.name as parent_name, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                               FROM categories c 
                               LEFT JOIN categories p ON c.parent_id = p.id 
                               ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();
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

                <div style="margin-bottom:1.25rem;">
                    <label class="admin-label">প্যারেন্ট ক্যাটাগরি</label>
                    <select name="parent_id" class="admin-input">
                        <option value="">কোনটিই নয় (মেইন ক্যাটাগরি)</option>
                        <?php foreach($categories as $pc): 
                            if(isset($editCat) && $editCat['id'] == $pc['id']) continue; // Can't be own parent
                            if($pc['parent_id'] != null) continue; // Only 1 level for now to keep it simple, or remove this for multi-level
                            ?>
                            <option value="<?php echo $pc['id']; ?>" <?php echo (isset($editCat) && $editCat['parent_id'] == $pc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pc['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label class="admin-label">সাজানোর ক্রম (Sort Order)</label>
                    <input type="number" name="sort_order" class="admin-input" placeholder="0" value="<?php echo $editCat['sort_order'] ?? '0'; ?>">
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
        <!-- Drag hint -->
        <div id="sort-toast" style="display:none; position:fixed; bottom:1.5rem; right:1.5rem; background:#111827; color:#f0fdf4; font-size:0.8rem; font-weight:700; padding:0.65rem 1.25rem; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); z-index:9999; transition:opacity 0.3s;">✅ ক্রম সংরক্ষিত হয়েছে!</div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:32px;"></th>
                        <th>নাম</th>
                        <th>পণ্য</th>
                        <th style="text-align:right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody id="sortable-categories">
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:3rem; color:#9ca3af;">এখনো কোন ক্যাটাগরি নেই।</td></tr>
                    <?php endif; ?>
                    <?php foreach($categories as $i => $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>" style="cursor:grab;">
                        <td style="color:#d1d5db; text-align:center;">
                            <span class="drag-handle" title="টেনে সাজান" style="cursor:grab; color:#d1d5db; font-size:1.1rem; user-select:none;">⣿</span>
                        </td>
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
                                <div class="thumbnail-fallback" style="display: <?php echo $catImg ? 'none' : 'flex'; ?>; width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, #f8fafc, #f1f5f9); border:1px solid #e2e8f0; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="ph ph-folder" style="color:#94a3b8; font-size:1.25rem;"></i>
                                </div>
                                <div style="min-width:0;">
                                    <div style="font-weight:800; color:#111827;">
                                        <?php echo htmlspecialchars($c['name']); ?>
                                        <?php if($c['parent_name']): ?>
                                            <span style="font-size:0.65rem; color:#629d25; background:#f0fdf4; padding:2px 6px; border-radius:4px; margin-left:4px;">
                                                <i class="ph ph-caret-left"></i> <?php echo htmlspecialchars($c['parent_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
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

        <p style="font-size:0.72rem; color:#9ca3af; font-weight:600; padding:0.75rem 1rem 0;">💡 টেনে ধরে উপরে বা নিচে সরিয়ে ক্যাটাগরির ক্রম পরিবর্তন করুন — স্বয়ংক্রিয়ভাবে সংরক্ষিত হবে।</p>
    </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('sortable-categories');
    if (!tbody) return;

    const toast = document.getElementById('sort-toast');
    let toastTimer;

    function showToast() {
        toast.style.display = 'block';
        toast.style.opacity = '1';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 300);
        }, 2500);
    }

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 180,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function () {
            const rows = tbody.querySelectorAll('tr[data-id]');
            const order = Array.from(rows).map(r => r.dataset.id);

            fetch('categories.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(order))
            })
            .then(r => r.json())
            .then(data => { if (data.success) showToast(); })
            .catch(console.error);
        }
    });
});
</script>

<style>
.sortable-ghost  { opacity: 0.35; background: #f0fdf4; }
.sortable-chosen { box-shadow: 0 4px 20px rgba(98,157,37,0.18); background: #fff; }
.sortable-drag   { box-shadow: 0 8px 32px rgba(98,157,37,0.22); opacity: 1 !important; }
.drag-handle:hover { color: #629d25 !important; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
