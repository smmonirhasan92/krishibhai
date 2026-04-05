<?php
ob_start();
require_once __DIR__ . '/../includes/db.php';

$allowed = ['Pending', 'Processing', 'Delivered', 'Cancelled'];

// ===== AJAX: Status Update =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status_update') {
    header('Content-Type: application/json');
    $orderId   = (int)$_POST['order_id'];
    $newStatus = $_POST['status'] ?? '';
    if (!in_array($newStatus, $allowed)) { echo json_encode(['success'=>false]); exit(); }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT status, stock_reduced FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if ($order) {
            if ($newStatus === 'Cancelled' && $order['stock_reduced'] == 1) {
                $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $items->execute([$orderId]);
                foreach ($items->fetchAll() as $item)
                    $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                $pdo->prepare("UPDATE orders SET status = ?, stock_reduced = 0 WHERE id = ?")->execute([$newStatus, $orderId]);
            } elseif ($newStatus !== 'Cancelled' && $order['status'] === 'Cancelled' && $order['stock_reduced'] == 0) {
                $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $items->execute([$orderId]);
                foreach ($items->fetchAll() as $item)
                    $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                $pdo->prepare("UPDATE orders SET status = ?, stock_reduced = 1 WHERE id = ?")->execute([$newStatus, $orderId]);
            } else {
                $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

// ===== Delete Order =====
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$_GET['delete']]);
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$_GET['delete']]);
    } catch(Exception $e) {}
    header("Location: orders.php"); exit();
}

// ===== Old POST handler (form submissions for compat) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status']) && !isset($_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    if (in_array($newStatus, $allowed)) {
        try {
            $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
        } catch(Exception $e) {}
    }
    header("Location: orders.php"); exit();
}

$adminTitle = 'অর্ডার ম্যানেজমেন্ট';
include_once __DIR__ . '/includes/header.php';

// ===== Filters =====
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$sql    = "SELECT * FROM orders WHERE 1=1";
$params = [];
if ($statusFilter && in_array($statusFilter, $allowed)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($search) {
    $sql .= " AND (customer_name LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";

$orders = $pdo->prepare($sql);
$orders->execute($params);
$orders = $orders->fetchAll();

$bnStatus = ['Pending'=>'পেন্ডিং','Processing'=>'প্রসেসিং','Delivered'=>'ডেলিভারড','Cancelled'=>'বাতিল'];
$statusColors = [
    'pending'    => ['bg'=>'rgba(251,191,36,0.1)', 'color'=>'#b45309'],
    'processing' => ['bg'=>'rgba(59,130,246,0.1)', 'color'=>'#1d4ed8'],
    'delivered'  => ['bg'=>'rgba(34,197,94,0.1)',  'color'=>'#16a34a'],
    'cancelled'  => ['bg'=>'rgba(239,68,68,0.1)',  'color'=>'#dc2626'],
];

$whatsapp = str_replace(['+','-',' '], '', get_setting('site_whatsapp', ''));
?>

<!-- ===== SEARCH BAR ===== -->
<form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap; align-items:center;">
    <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>"><?php endif; ?>
    <div style="position:relative; flex:1; min-width:220px;">
        <i class="ph ph-magnifying-glass" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:1rem;"></i>
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="গ্রাহকের নাম বা ফোন নম্বর খুঁজুন..."
               class="admin-input" style="padding-left:2.25rem; margin:0;">
    </div>
    <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">🔍 খুঁজুন</button>
    <?php if ($search || $statusFilter): ?>
    <a href="orders.php" class="btn btn-ghost" style="padding:0.5rem 0.875rem;">✕ রিসেট</a>
    <?php endif; ?>
</form>

<!-- ===== STATUS FILTER PILLS ===== -->
<div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <?php
    // Count totals for all statuses
    $allOrders = $pdo->query("SELECT status FROM orders")->fetchAll();
    $counts = array_count_values(array_column($allOrders, 'status'));
    ?>
    <a href="orders.php<?php echo $search ? '?q='.urlencode($search) : ''; ?>" style="padding:0.375rem 1rem; border-radius:8px; font-size:0.8125rem; font-weight:700; text-decoration:none;
       <?php echo !$statusFilter ? 'background:linear-gradient(135deg,#629d25,#4a771c);color:white;' : 'background:#f3f4f6;color:#374151;'; ?>">
        সব <span style="opacity:0.7;">(<?php echo array_sum($counts); ?>)</span>
    </a>
    <?php foreach($allowed as $s): ?>
    <a href="orders.php?status=<?php echo $s; ?><?php echo $search ? '&q='.urlencode($search) : ''; ?>"
       style="padding:0.375rem 1rem; border-radius:8px; font-size:0.8125rem; font-weight:700; text-decoration:none;
              <?php echo $statusFilter===$s ? 'background:linear-gradient(135deg,#629d25,#4a771c);color:white;' : 'background:#f3f4f6;color:#374151;'; ?>">
        <?php echo $bnStatus[$s] ?? $s; ?>
        <?php if(isset($counts[$s])): ?><span style="opacity:0.7;">(<?php echo $counts[$s]; ?>)</span><?php endif; ?>
    </a>
    <?php endforeach; ?>
    <span style="margin-left:auto; font-size:0.8125rem; color:#9ca3af; font-weight:600;"><?php echo count($orders); ?> টি অর্ডার</span>
</div>

<!-- ===== ORDERS TABLE ===== -->
<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>অর্ডার</th>
                    <th>গ্রাহক</th>
                    <th>ঠিকানা</th>
                    <th>মোট</th>
                    <th>অবস্থা</th>
                    <th>তারিখ</th>
                    <th style="text-align:right;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem; color:#9ca3af;">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;">📭</div>
                    কোন অর্ডার পাওয়া যায়নি।
                </td></tr>
                <?php endif; ?>
                <?php foreach($orders as $o):
                    $sc = strtolower($o['status']);
                    $sColor = $statusColors[$sc] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                ?>
                <tr>
                    <td>
                        <button onclick="openOrderModal(<?php echo $o['id']; ?>)"
                                style="font-weight:800; color:#629d25; background:none; border:none; cursor:pointer; font-size:0.9375rem; padding:0; text-decoration:underline dotted;">
                            #<?php echo str_pad($o['id'],4,'0',STR_PAD_LEFT); ?>
                        </button>
                    </td>
                    <td>
                        <div style="font-weight:700; color:#111827;"><?php echo htmlspecialchars($o['customer_name']); ?></div>
                        <a href="tel:<?php echo $o['phone']; ?>" style="font-size:0.75rem; color:#629d25; font-weight:700; text-decoration:none;"><?php echo htmlspecialchars($o['phone']); ?></a>
                    </td>
                    <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#6b7280; font-size:0.8125rem;">
                        <?php echo htmlspecialchars($o['address']); ?>
                    </td>
                    <td style="font-weight:800; color:#111827;">৳ <?php echo number_format($o['total_amount']); ?></td>
                    <td>
                        <!-- Inline Status Dropdown (AJAX) -->
                        <select onchange="updateStatus(<?php echo $o['id']; ?>, this)"
                                style="font-size:0.75rem; font-weight:700; color:<?php echo $sColor['color']; ?>; background:<?php echo $sColor['bg']; ?>; border:none; border-radius:20px; padding:0.25rem 0.625rem; cursor:pointer; outline:none;">
                            <?php foreach($allowed as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $o['status']===$s ? 'selected' : ''; ?>>
                                <?php echo $bnStatus[$s] ?? $s; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td style="color:#9ca3af; font-size:0.8125rem; white-space:nowrap;">
                        <?php echo date('d M, Y', strtotime($o['created_at'])); ?>
                        <div style="font-size:0.7rem;"><?php echo date('h:i A', strtotime($o['created_at'])); ?></div>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:0.35rem; align-items:center;">
                            <!-- Detail -->
                            <button onclick="openOrderModal(<?php echo $o['id']; ?>)" class="btn btn-ghost" style="padding:0.4rem; width:30px; height:30px; justify-content:center;" title="বিস্তারিত">
                                <i class="ph ph-eye" style="font-size:1rem;"></i>
                            </button>
                            <!-- WhatsApp -->
                            <a href="https://wa.me/<?php echo $whatsapp ?: $o['phone']; ?>?text=সালাম+<?php echo urlencode($o['customer_name']); ?>+ভাই,+আপনার+অর্ডার+%23<?php echo $o['id']; ?>+সম্পর্কে+জানাতে+চাইছিলাম।"
                               target="_blank" class="btn btn-ghost" style="padding:0.4rem; width:30px; height:30px; justify-content:center; color:#25d366;" title="WhatsApp">
                                <i class="ph ph-whatsapp-logo" style="font-size:1rem;"></i>
                            </a>
                            <!-- Invoice -->
                            <a href="invoice.php?id=<?php echo $o['id']; ?>" target="_blank" class="btn btn-ghost" style="padding:0.4rem; width:30px; height:30px; justify-content:center;" title="ইনভয়েস">
                                <i class="ph ph-printer" style="font-size:1rem;"></i>
                            </a>
                            <!-- Delete -->
                            <button onclick="if(confirm('অর্ডার #<?php echo $o['id']; ?> মুছে ফেলতে চান?')) window.location.href='orders.php?delete=<?php echo $o['id']; ?>'"
                                    class="btn btn-danger" style="padding:0.4rem; width:30px; height:30px; justify-content:center;" title="মুছুন">
                                <i class="ph ph-trash" style="font-size:1rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== ORDER DETAIL MODAL ===== -->
<div id="order-modal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:white; border-radius:24px; max-width:560px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 24px 80px rgba(0,0,0,0.25);">
        <div style="padding:1.5rem 1.5rem 1rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:white; border-radius:24px 24px 0 0;">
            <h3 style="font-weight:800; color:#111827; margin:0;" id="modal-title">অর্ডার বিস্তারিত</h3>
            <button onclick="closeOrderModal()" style="background:#f3f4f6; border:none; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:1.1rem; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>
        <div id="modal-body" style="padding:1.5rem;">
            <div style="text-align:center; padding:2rem; color:#9ca3af;">লোড হচ্ছে...</div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" style="display:none; position:fixed; bottom:1.5rem; right:1.5rem; background:#111827; color:#f0fdf4; font-size:0.8rem; font-weight:700; padding:0.65rem 1.25rem; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); z-index:9999; transition:opacity 0.3s;"></div>

<script>
// ===== TOAST =====
let toastTimer;
function showToast(msg, ok=true) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = ok ? '#111827' : '#dc2626';
    t.style.display = 'block';
    t.style.opacity = '1';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        t.style.opacity = '0';
        setTimeout(() => t.style.display = 'none', 300);
    }, 2500);
}

// ===== AJAX STATUS UPDATE =====
const statusColors = {
    pending:    { bg:'rgba(251,191,36,0.15)', color:'#b45309' },
    processing: { bg:'rgba(59,130,246,0.15)', color:'#1d4ed8' },
    delivered:  { bg:'rgba(34,197,94,0.15)',  color:'#16a34a' },
    cancelled:  { bg:'rgba(239,68,68,0.15)',  color:'#dc2626' },
};
function updateStatus(orderId, sel) {
    const newStatus = sel.value;
    fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=status_update&order_id=${orderId}&status=${newStatus}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const sc = newStatus.toLowerCase();
            const col = statusColors[sc] || {};
            sel.style.color = col.color || '#374151';
            sel.style.background = col.bg || '#f3f4f6';
            showToast('✅ অবস্থা আপডেট হয়েছে!');
        } else {
            showToast('❌ আপডেট ব্যর্থ!', false);
        }
    });
}

// ===== ORDER DETAIL MODAL =====
function openOrderModal(id) {
    const modal = document.getElementById('order-modal');
    const body  = document.getElementById('modal-body');
    document.getElementById('modal-title').textContent = 'অর্ডার #' + String(id).padStart(4,'0');
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:#9ca3af;">লোড হচ্ছে...</div>';
    modal.style.display = 'flex';

    fetch('api/order-detail.php?id=' + id)
    .then(r => r.json())
    .then(data => {
        if (!data.success) { body.innerHTML = '<p style="color:red;">লোড ব্যর্থ</p>'; return; }
        const o = data.order;
        const items = data.items;
        const bnStatus = {Pending:'পেন্ডিং',Processing:'প্রসেসিং',Delivered:'ডেলিভারড',Cancelled:'বাতিল'};

        let itemsHtml = items.map(item => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:700;color:#111827;">${item.product_name}</div>
                    <div style="font-size:0.75rem;color:#9ca3af;">${item.quantity} × ৳${parseInt(item.price).toLocaleString('en')}</div>
                </div>
                <div style="font-weight:800;color:#111827;">৳${(item.quantity * item.price).toLocaleString('en')}</div>
            </div>
        `).join('');

        body.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.25rem;">
                <div style="background:#f8fafc;border-radius:12px;padding:0.875rem;">
                    <div style="font-size:0.65rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:0.375rem;">গ্রাহক</div>
                    <div style="font-weight:800;color:#111827;">${o.customer_name}</div>
                    <a href="tel:${o.phone}" style="font-size:0.8125rem;color:#629d25;font-weight:700;">${o.phone}</a>
                </div>
                <div style="background:#f8fafc;border-radius:12px;padding:0.875rem;">
                    <div style="font-size:0.65rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:0.375rem;">অবস্থা</div>
                    <div style="font-weight:800;color:#111827;">${bnStatus[o.status] || o.status}</div>
                    <div style="font-size:0.75rem;color:#9ca3af;">${new Date(o.created_at).toLocaleDateString('bn-BD')}</div>
                </div>
                <div style="background:#f8fafc;border-radius:12px;padding:0.875rem;grid-column:1/-1;">
                    <div style="font-size:0.65rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:0.375rem;">ঠিকানা</div>
                    <div style="font-weight:700;color:#374151;">${o.address}</div>
                    ${o.note ? `<div style="font-size:0.8125rem;color:#6b7280;margin-top:0.375rem;">💬 ${o.note}</div>` : ''}
                </div>
            </div>
            <h4 style="font-weight:800;color:#111827;margin-bottom:0.75rem;font-size:0.875rem;">পণ্য তালিকা</h4>
            <div style="border:1px solid #f1f5f9;border-radius:12px;padding:0 0.75rem;">
                ${itemsHtml || '<div style="padding:1rem;color:#9ca3af;text-align:center;">আইটেম পাওয়া যায়নি</div>'}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.875rem 0 0.125rem;border-top:2px solid #f1f5f9;margin-top:0.25rem;">
                    <span style="font-weight:800;color:#111827;">মোট</span>
                    <span style="font-weight:900;color:#629d25;font-size:1.1rem;">৳${parseInt(o.total_amount).toLocaleString('en')}</span>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;margin-top:1.25rem;">
                <a href="invoice.php?id=${o.id}" target="_blank" class="btn btn-ghost" style="flex:1;text-align:center;">🖨️ ইনভয়েস</a>
                <a href="https://wa.me/${o.phone.replace(/[^0-9]/g,'')}?text=সালাম+${encodeURIComponent(o.customer_name)}+ভাই,+আপনার+অর্ডার+%23${o.id}+আপডেট।" target="_blank" class="btn btn-primary" style="flex:1;text-align:center;background:#25d366;">💬 WhatsApp</a>
            </div>
        `;
    })
    .catch(() => body.innerHTML = '<p style="color:red;text-align:center;">সংযোগ সমস্যা</p>');
}

function closeOrderModal() {
    document.getElementById('order-modal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('order-modal').addEventListener('click', function(e) {
    if (e.target === this) closeOrderModal();
});

// Close on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOrderModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
