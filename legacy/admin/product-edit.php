<?php
/**
 * Zaman Kitchens - Pro Product Management (Add/Edit)
 * Features: Variations, Meta Data, Gallery, Specs
 */
require_once __DIR__ . '/../includes/db.php';

$adminTitle = 'Edit Product';
include_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$product = null;
$message = "";
$error = "";

// Fetch Product for editing
if ($id && is_numeric($id)) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // Decode JSON fields
    if ($product) {
        $product['variations'] = json_decode($product['variations'] ?? '[]', true) ?: [];
        $product['specifications'] = json_decode($product['specifications'] ?? '[]', true) ?: [];
        $product['gallery_images'] = json_decode($product['gallery_images'] ?? '[]', true) ?: [];
    }

    // Fetch Price Rules
    $priceRules = $pdo->prepare("SELECT * FROM price_rules WHERE product_id = ? ORDER BY min_qty ASC");
    $priceRules->execute([$id]);
    $priceRules = $priceRules->fetchAll();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $stock_status = $_POST['stock_status'] ?? 'In Stock';
    $description = $_POST['description'] ?? '';
    $meta_title = $_POST['meta_title'] ?? '';
    $meta_description = $_POST['meta_description'] ?? '';
    
    // Process Variations
    $var_names = $_POST['var_name'] ?? [];
    $var_values = $_POST['var_value'] ?? [];
    $variations = [];
    for($i=0; $i < count($var_names); $i++) {
        if(!empty($var_names[$i])) {
            $variations[] = ['name' => $var_names[$i], 'value' => $var_values[$i]];
        }
    }
    
    // Process Specs
    $spec_labels = $_POST['spec_label'] ?? [];
    $spec_values = $_POST['spec_value'] ?? [];
    $specifications = [];
    for($i=0; $i < count($spec_labels); $i++) {
        if(!empty($spec_labels[$i])) {
            $specifications[] = ['label' => $spec_labels[$i], 'value' => $spec_values[$i]];
        }
    }

    $main_image = $_POST['existing_image'] ?? '';
    
    // Main Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = $slug . '-' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
            $main_image = 'assets/uploads/products/' . $file_name;
        }
    }

    if ($name) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, purchase_price = ?, stock_status = ?, main_image = ?, meta_title = ?, meta_description = ?, variations = ?, specifications = ? WHERE id = ?");
                $stmt->execute([$category_id, $name, $slug, $description, $price, $purchase_price, $stock_status, $main_image, $meta_title, $meta_description, json_encode($variations), json_encode($specifications), $id]);
                $message = "Product updated successfully!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, purchase_price, stock_status, main_image, meta_title, meta_description, variations, specifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $slug, $description, $price, $purchase_price, $stock_status, $main_image, $meta_title, $meta_description, json_encode($variations), json_encode($specifications)]);
                $id = $pdo->lastInsertId();
                $message = "Product added successfully!";
            }

            // Save Price Rules
            $pdo->prepare("DELETE FROM price_rules WHERE product_id = ?")->execute([$id]);
            $min_qtys = $_POST['rule_min_qty'] ?? [];
            $rule_types = $_POST['rule_type'] ?? [];
            $rule_values = $_POST['rule_value'] ?? [];
            
            for($i=0; $i < count($min_qtys); $i++) {
                if(!empty($min_qtys[$i]) && $rule_values[$i] > 0) {
                    $insertRule = $pdo->prepare("INSERT INTO price_rules (product_id, min_qty, discount_type, value, is_active) VALUES (?, ?, ?, ?, 1)");
                    $insertRule->execute([$id, $min_qtys[$i], $rule_types[$i], $rule_values[$i]]);
                }
            }

            // Refresh product data
            header("Location: product-edit.php?id=$id&msg=".urlencode($message));
            exit();
        } catch(Exception $e) { $error = "Error: " . $e->getMessage(); }
    }
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$msg = $_GET['msg'] ?? '';
?>

<div class="max-w-7xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-extrabold"><?php echo $product ? 'Edit Product' : 'Add New Product'; ?></h1>
            <p class="text-sm text-gray-500">Manage pro-grade listings with variations and meta tags.</p>
        </div>
        <a href="products.php" class="text-gray-500 hover:text-amber-600 font-bold">&larr; Back to Products</a>
    </div>

    <?php if($msg): ?> <div class="bg-green-50 text-green-700 p-4 rounded-2xl mb-6 font-bold border border-green-100">✅ <?php echo htmlspecialchars($msg); ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="bg-red-50 text-red-700 p-4 rounded-2xl mb-6 font-bold border border-red-100">❌ <?php echo $error; ?></div> <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-8">
        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2 mt-0">
                    <span class="w-2 h-6 bg-amber-500 rounded-full"></span> 
                    Basic Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Product Title</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition text-lg font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Description</label>
                        <textarea name="description" rows="6" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Variations & specifications -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6 mt-0">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <span class="w-2 h-6 bg-indigo-500 rounded-full"></span> 
                        Product Variations (Size, Color, etc.)
                    </h3>
                    <button type="button" onclick="addVariation()" class="text-indigo-600 font-bold text-xs hover:underline">+ ADD VARIATION</button>
                </div>
                <div id="variation-container" class="space-y-3">
                    <?php 
                    $vars = $product['variations'] ?? [['name'=>'', 'value'=>'']];
                    foreach($vars as $v): ?>
                    <div class="flex gap-3 variation-row">
                        <input type="text" name="var_name[]" value="<?php echo htmlspecialchars($v['name']); ?>" placeholder="Name (e.g. Size)" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        <input type="text" name="var_value[]" value="<?php echo htmlspecialchars($v['value']); ?>" placeholder="Value (e.g. Medium)" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center justify-between mb-6 mt-10">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span> 
                        Technical Specifications
                    </h3>
                    <button type="button" onclick="addSpec()" class="text-blue-600 font-bold text-xs hover:underline">+ ADD SPEC</button>
                </div>
                <div id="spec-container" class="space-y-3">
                    <?php 
                    $specs = $product['specifications'] ?? [['label'=>'', 'value'=>'']];
                    foreach($specs as $s): ?>
                    <div class="flex gap-3 spec-row">
                        <input type="text" name="spec_label[]" value="<?php echo htmlspecialchars($s['label']); ?>" placeholder="Label (e.g. Material)" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        <input type="text" name="spec_value[]" value="<?php echo htmlspecialchars($s['value']); ?>" placeholder="Value (e.g. Stainless Steel)" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Wholesale Pricing Rules -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6 mt-0">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <span class="w-2 h-6 bg-pink-500 rounded-full"></span> 
                        Wholesale Price Rules (Tiered Pricing)
                    </h3>
                    <button type="button" onclick="addPriceRule()" class="text-pink-600 font-bold text-xs hover:underline">+ ADD RULE</button>
                </div>
                <div id="price-rule-container" class="space-y-3">
                    <?php 
                    $rules = $priceRules ?? [];
                    foreach($rules as $r): ?>
                    <div class="flex gap-3 rule-row">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">Min Qty</label>
                            <input type="number" name="rule_min_qty[]" value="<?php echo htmlspecialchars($r['min_qty']); ?>" placeholder="10" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        </div>
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">Type</label>
                            <select name="rule_type[]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                                <option value="fixed" <?php echo $r['discount_type'] == 'fixed' ? 'selected' : ''; ?>>Fixed Price</option>
                                <option value="percentage" <?php echo $r['discount_type'] == 'percentage' ? 'selected' : ''; ?>>% Discount</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">Value (৳ or %)</label>
                            <input type="number" step="0.01" name="rule_value[]" value="<?php echo htmlspecialchars($r['value']); ?>" placeholder="150" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                        </div>
                        <div class="flex-none pt-6">
                            <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-[10px] text-gray-400 mt-4 italic">* If "Fixed Price", it becomes the unit price. If "% Discount", it's deducted from the sale price.</p>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2 mt-0">
                    <span class="w-2 h-6 bg-green-500 rounded-full"></span> 
                    SEO & Meta Data
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="<?php echo htmlspecialchars($product['meta_title'] ?? ''); ?>" placeholder="SEO Title" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3" placeholder="SEO Description" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none"><?php echo htmlspecialchars($product['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Publishing -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-2xl mb-4 transition shadow-lg shadow-amber-200">
                    <?php echo $product ? 'Save Changes' : 'Publish Product'; ?>
                </button>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Category</label>
                        <select name="category_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Sale Price (৳)</label>
                        <input type="number" name="price" value="<?php echo $product['price'] ?? 0; ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-bold text-amber-600">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Purchase Price / Cost (৳)</label>
                        <input type="number" step="0.01" name="purchase_price" value="<?php echo $product['purchase_price'] ?? 0; ?>" class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl outline-none font-bold text-indigo-600">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Inventory Status</label>
                        <select name="stock_status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none font-semibold">
                            <option value="In Stock" <?php echo ($product['stock_status'] ?? '') == 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="Out of Stock" <?php echo ($product['stock_status'] ?? '') == 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Main Image -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Main Image</h4>
                <div class="border-2 border-dashed border-gray-100 rounded-2xl p-4 text-center">
                    <?php if(!empty($product['main_image'])): ?>
                        <img src="../<?php echo $product['main_image']; ?>" class="w-full h-48 object-contain rounded-xl mb-4 bg-gray-50">
                    <?php endif; ?>
                    <label class="cursor-pointer block">
                        <span class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold px-4 py-2 rounded-full inline-block transition">Change Image</span>
                        <input type="file" name="image" accept="image/*" class="hidden">
                    </label>
                    <input type="hidden" name="existing_image" value="<?php echo $product['main_image'] ?? ''; ?>">
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function addVariation() {
    const container = document.getElementById('variation-container');
    const div = document.createElement('div');
    div.className = 'flex gap-3 variation-row';
    div.innerHTML = `
        <input type="text" name="var_name[]" placeholder="Name" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        <input type="text" name="var_value[]" placeholder="Value" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
    `;
    container.appendChild(div);
}
function addSpec() {
    const container = document.getElementById('spec-container');
    const div = document.createElement('div');
    div.className = 'flex gap-3 spec-row';
    div.innerHTML = `
        <input type="text" name="spec_label[]" placeholder="Label" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        <input type="text" name="spec_value[]" placeholder="Value" class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
    `;
    container.appendChild(div);
}
function addPriceRule() {
    const container = document.getElementById('price-rule-container');
    const div = document.createElement('div');
    div.className = 'flex gap-3 rule-row';
    div.innerHTML = `
        <div class="flex-1">
            <label class="block text-[10px] font-bold text-gray-400 mb-1">Min Qty</label>
            <input type="number" name="rule_min_qty[]" placeholder="10" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        </div>
        <div class="flex-1">
            <label class="block text-[10px] font-bold text-gray-400 mb-1">Type</label>
            <select name="rule_type[]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
                <option value="fixed">Fixed Price</option>
                <option value="percentage">% Discount</option>
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-[10px] font-bold text-gray-400 mb-1">Value</label>
            <input type="number" step="0.01" name="rule_value[]" placeholder="150" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl outline-none">
        </div>
        <div class="flex-none pt-6">
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-400 p-2 hover:text-red-600">✕</button>
        </div>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
