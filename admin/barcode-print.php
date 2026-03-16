<?php
/**
 * Krishibhai - Barcode Printing Label Generator
 * Supports single and bulk printing.
 */
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

$productId = $_GET['id'] ?? null;
$qty = $_GET['qty'] ?? 1;

$products = [];
if ($productId) {
    $stmt = $pdo->prepare("SELECT name, price, barcode FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $p = $stmt->fetch();
    if ($p) {
        for ($i=0; $i<$qty; $i++) $products[] = $p;
    }
} else {
    // If no ID, maybe show a selection UI (not implemented yet)
    die("প্রিন্ট করার জন্য পণ্যের আইডি প্রয়োজন।");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>বারকোড প্রিন্ট করুন - কৃষিভাই</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; background: #f4f6f9; }
        .print-controls { 
            background: white; padding: 15px; border-radius: 12px; margin-bottom: 30px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; border: none; }
        .btn-primary { background: #629d25; color: white; }
        
        /* Label Sheet Grid */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50mm, 1fr));
            gap: 10px;
            background: #fff;
            padding: 20px;
            min-height: 297mm; /* A4 height */
        }
        
        /* Individual Label */
        .barcode-label {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #ddd;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 5px;
            overflow: hidden;
            background: white;
        }
        .label-brand { font-size: 8px; font-weight: 900; color: #629d25; text-transform: uppercase; margin-bottom: 2px; }
        .label-name { font-size: 9px; font-weight: 700; color: #111; max-height: 2.4em; overflow: hidden; margin-bottom: 2px; line-height: 1.1; }
        .label-price { font-size: 11px; font-weight: 900; color: #000; margin-bottom: 2px; }
        canvas { max-width: 100%; height: auto; }

        @media print {
            .print-controls { display: none; }
            body { padding: 0; background: white; }
            .label-grid { padding: 0; gap: 0; grid-template-columns: repeat(4, 1fr); }
            .barcode-label { border: 1px solid #eee; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <div>
        <h2 style="margin:0;">বারকোড লেবেল প্রিন্ট</h2>
        <p style="margin:5px 0 0; color:#666; font-size:13px;"><b><?php echo htmlspecialchars($products[0]['name'] ?? ''); ?></b> এর জন্য <b><?php echo count($products); ?></b> টি লেবেল প্রিন্ট হচ্ছ</p>
    </div>
    <div style="display:flex; gap:10px;">
        <input type="number" id="qtyInput" value="<?php echo $qty; ?>" min="1" max="100" style="width:60px; padding:8px; border-radius:8px; border:1px solid #ddd;">
        <button onclick="window.location.href='?id=<?php echo $productId; ?>&qty='+document.getElementById('qtyInput').value" class="btn" style="background:#eee;">পরিমাণ পরিবর্তন করুন</button>
        <button onclick="window.print()" class="btn btn-primary">এখনই প্রিন্ট করুন</button>
    </div>
</div>

<div class="label-grid">
    <?php foreach($products as $p): ?>
    <div class="barcode-label">
        <div class="label-brand">কৃষিভাই</div>
        <div class="label-name"><?php echo htmlspecialchars($p['name']); ?></div>
        <div class="label-price">৳ <?php echo number_format($p['price']); ?></div>
        <?php if(!empty($p['barcode'])): ?>
            <canvas class="barcode" data-value="<?php echo htmlspecialchars($p['barcode']); ?>"></canvas>
        <?php else: ?>
            <div style="font-size:8px; color:#999; margin-top:5px;">বারকোড বরাদ্দ নেই</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
    document.querySelectorAll('.barcode').forEach(function(canvas) {
        JsBarcode(canvas, canvas.getAttribute('data-value'), {
            format: "CODE128",
            width: 1.5,
            height: 35,
            displayValue: true,
            fontSize: 10,
            margin: 5
        });
    });
</script>

</body>
</html>
