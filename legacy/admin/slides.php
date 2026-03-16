<?php
/**
 * Zaman Kitchens - Manage Hero Slides
 * Features: List, Add, Edit, Delete slides with Image Upload
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
        $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Slide deleted successfully!";
    } catch(Exception $e) { $error = "Error deleting slide: " . $e->getMessage(); }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $button_text = trim($_POST['button_text'] ?? 'Shop Now');
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
                // Update
                $stmt = $pdo->prepare("UPDATE hero_slides SET title = ?, subtitle = ?, image_path = ?, button_text = ?, button_link = ?, order_index = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $image_path, $button_text, $button_link, $order_index, $is_active, $id]);
                $message = "Slide updated!";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, image_path, button_text, button_link, order_index, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $image_path, $button_text, $button_link, $order_index, $is_active]);
                $message = "Slide added!";
            }
        } catch(Exception $e) { $error = "Database Error: " . $e->getMessage(); }
    } else {
        $error = "An image is required for the slide.";
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
?>
<?php 
$adminTitle = 'Manage Slider';
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">
    
    <!-- Form Section -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
            <h2 class="text-xl font-extrabold mb-6"><?php echo $editSlide ? 'Edit Slide' : 'Add New Slide'; ?></h2>
            
            <?php if($message): ?> <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-4 text-sm font-medium border border-green-100">✅ <?php echo $message; ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm font-medium border border-red-100">❌ <?php echo $error; ?></div> <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $editSlide['id'] ?? ''; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $editSlide['image_path'] ?? ''; ?>">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Slide Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($editSlide['title'] ?? ''); ?>"
                        placeholder="e.g. Dream Kitchen Sinks"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Subtitle/Description</label>
                    <textarea name="subtitle" rows="2"
                        placeholder="e.g. Modern designs for your dream kitchen."
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm resize-none"><?php echo htmlspecialchars($editSlide['subtitle'] ?? ''); ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Button Text</label>
                        <input type="text" name="button_text" value="<?php echo htmlspecialchars($editSlide['button_text'] ?? 'Shop Now'); ?>"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Order Index</label>
                        <input type="number" name="order_index" value="<?php echo $editSlide['order_index'] ?? 0; ?>"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Button Link</label>
                    <input type="text" name="button_link" value="<?php echo htmlspecialchars($editSlide['button_link'] ?? '#'); ?>"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-400 focus:bg-white transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Slide Image (1600x600 recommended)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                    <?php if(!empty($editSlide['image_path'])): ?>
                        <img src="../<?php echo $editSlide['image_path']; ?>" class="mt-2 w-full h-24 rounded-lg object-cover border border-gray-100 shadow-sm">
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2 py-2">
                    <input type="checkbox" name="is_active" id="is_active" <?php echo ($editSlide['is_active'] ?? 1) ? 'checked' : ''; ?> class="w-4 h-4 text-amber-600 rounded">
                    <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Published / Active</label>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-amber-200">
                        <?php echo $editSlide ? 'Update Slide' : 'Save Slide'; ?>
                    </button>
                    <?php if($editSlide): ?>
                        <a href="slides.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List Section -->
    <div class="md:col-span-2 space-y-4">
        <?php if(empty($slides)): ?>
            <div class="bg-white rounded-2xl p-10 text-center border-2 border-dashed border-gray-100">
                <div class="text-4xl mb-2">🖼️</div>
                <h3 class="font-bold text-gray-400">No slides found. Add your first promotional slide!</h3>
            </div>
        <?php else: ?>
            <?php foreach ($slides as $slide): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row h-full group">
                <div class="md:w-1/3 relative h-48 md:h-auto">
                    <img src="../<?php echo $slide['image_path']; ?>" class="w-full h-full object-cover">
                    <div class="absolute top-2 left-2 px-2 py-1 rounded bg-black/50 text-white text-[10px] font-bold"><?php echo $slide['order_index']; ?>. Index</div>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-lg text-gray-900"><?php echo htmlspecialchars($slide['title']); ?></h3>
                            <?php if($slide['is_active']): ?>
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">ACTIVE</span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded-full">DRAFT</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                        <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">Link: <?php echo htmlspecialchars($slide['button_link']); ?></div>
                    </div>
                    <div class="flex gap-4 mt-6">
                        <a href="slides.php?edit=<?php echo $slide['id']; ?>" class="flex-1 text-center bg-gray-50 hover:bg-blue-50 text-blue-600 font-bold py-2 rounded-lg border border-gray-100 hover:border-blue-200 transition">Edit</a>
                        <a href="slides.php?delete=<?php echo $slide['id']; ?>" 
                           onclick="return confirm('Delete this slide?')"
                           class="flex-1 text-center bg-gray-50 hover:bg-red-50 text-red-500 font-bold py-2 rounded-lg border border-gray-100 hover:border-red-200 transition">Delete</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
