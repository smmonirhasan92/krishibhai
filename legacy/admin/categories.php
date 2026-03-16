<?php
/**
 * Zaman Kitchens - Manage Categories
 * Features: List, Add, Edit, Delete categories with Image Upload
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

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
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Category deleted successfully!";
    } catch(Exception $e) { $error = "Error deleting category: " . $e->getMessage(); }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    $image_path = $_POST['existing_image'] ?? '';

    // Handle File Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/categories/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = $slug . '-' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'assets/uploads/categories/' . $file_name;
        }
    }

    if ($name) {
        try {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $image_path, $id]);
                $message = "Category updated!";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $image_path]);
                $message = "Category added!";
            }
        } catch(Exception $e) { $error = "Database Error: " . $e->getMessage(); }
    }
}

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch single category for editing
$editCat = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>
<?php 
$adminTitle = 'Manage Categories';
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="px-12 py-10">
    <div class="flex items-center justify-between mb-12">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Category Manager</h1>
            <p class="text-slate-500 font-medium">Organize your shop collection by professional categories.</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-10">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-[2.5rem] shadow-sm p-10 border border-white/40 sticky top-32">
                <h2 class="text-xl font-black text-slate-900 mb-8"><?php echo $editCat ? 'Edit Category' : 'Create New'; ?></h2>
                
                <?php if($message): ?> <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-6 text-xs font-bold border border-emerald-100 uppercase tracking-widest">✅ <?php echo $message; ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="bg-rose-50 text-rose-700 p-4 rounded-2xl mb-6 text-xs font-bold border border-rose-100 uppercase tracking-widest">❌ <?php echo $error; ?></div> <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?php echo $editCat['id'] ?? ''; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo $editCat['image'] ?? ''; ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Gallery Name</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>"
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none transition font-bold text-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Custom Slug</label>
                        <input type="text" name="slug" value="<?php echo htmlspecialchars($editCat['slug'] ?? ''); ?>"
                            placeholder="e.g. kitchen-cabinet"
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none transition font-bold text-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Cover Media</label>
                        <div class="relative group mt-2">
                            <input type="file" name="image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                            <div class="w-full py-10 border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center bg-slate-50/50 group-hover:bg-amber-50 group-hover:border-amber-200 transition-all">
                                <span class="text-4xl mb-2 opacity-20">📸</span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Upload Icon</span>
                            </div>
                        </div>
                        <?php if(!empty($editCat['image'])): ?>
                            <img src="../<?php echo $editCat['image']; ?>" class="mt-4 w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-xl">
                        <?php endif; ?>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-black py-4 rounded-2xl transition shadow-xl shadow-amber-200 uppercase tracking-widest text-xs">
                            <?php echo $editCat ? 'Update' : 'Create'; ?>
                        </button>
                        <?php if($editCat): ?>
                            <a href="categories.php" class="bg-slate-100 hover:bg-slate-200 text-slate-500 font-black px-6 py-4 rounded-2xl transition uppercase tracking-widest text-xs">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Section -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-[2.5rem] shadow-sm overflow-hidden border border-white/40">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Preview</th>
                                <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Collection</th>
                                <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Slug Reference</th>
                                <th class="px-10 py-5 text-right font-bold text-slate-400 uppercase tracking-widest text-[10px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-amber-50/20 transition group">
                                <td class="px-10 py-6">
                                    <img src="../<?php echo !empty($cat['image']) ? $cat['image'] : 'https://placehold.co/400x400/f5f5f5/aaa?text='.$cat['name']; ?>" 
                                         class="w-14 h-14 rounded-2xl object-cover bg-white shadow-sm group-hover:scale-110 transition-transform duration-500">
                                </td>
                                <td class="px-10 py-6 font-black text-slate-900 group-hover:text-amber-600 transition-colors"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td class="px-10 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]"><?php echo htmlspecialchars($cat['slug']); ?></td>
                                <td class="px-10 py-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-blue-500 flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow-sm">
                                            <i class="ph ph-note-pencil text-lg"></i>
                                        </a>
                                        <a href="categories.php?delete=<?php echo $cat['id']; ?>" 
                                           onclick="return confirm('Permanent delete?')"
                                           class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-rose-500 flex items-center justify-center hover:bg-rose-600 hover:text-white transition shadow-sm">
                                            <i class="ph ph-trash text-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
</body>
</html>

</body>
</html>
