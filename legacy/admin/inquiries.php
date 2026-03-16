<?php
$adminTitle = 'Leads & Inquiries';
include_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/db.php';

$leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
?>

<div class="px-12 py-10">
    <div class="flex items-center justify-between mb-12">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Customer Inquiries</h1>
            <p class="text-slate-500 font-medium">Direct leads collected from your website.</p>
        </div>
    </div>

    <div class="glass-card rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Name</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Contact</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Message</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Date</th>
                        <th class="px-10 py-5 text-left font-bold text-slate-400 uppercase tracking-widest text-[10px]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($leads)): ?>
                    <tr><td colspan="5" class="px-10 py-20 text-center">
                        <div class="text-5xl mb-4 opacity-10">📧</div>
                        <div class="text-slate-400 font-bold uppercase tracking-widest text-xs">No inquiries yet</div>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($leads as $lead): ?>
                    <tr class="hover:bg-amber-50/20 transition">
                        <td class="px-10 py-6 font-bold text-slate-900"><?php echo htmlspecialchars($lead['name']); ?></td>
                        <td class="px-10 py-6">
                            <div class="font-bold text-slate-800"><?php echo htmlspecialchars($lead['phone']); ?></div>
                            <div class="text-[10px] text-slate-400 font-bold"><?php echo htmlspecialchars($lead['email']); ?></div>
                        </td>
                        <td class="px-10 py-6 text-slate-600 max-w-xs truncate"><?php echo htmlspecialchars($lead['message']); ?></td>
                        <td class="px-10 py-6 text-[10px] text-slate-400 font-bold uppercase"><?php echo date('d M, Y', strtotime($lead['created_at'])); ?></td>
                        <td class="px-10 py-6">
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-wider"><?php echo $lead['status']; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</body>
</html>
