<?php
/**
 * Krishibhai - Advanced Barcode Scanner
 * Live camera scanning with AJAX product lookup.
 */
$adminTitle = 'বারকোড স্ক্যানার';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900">ইনভেন্টরি স্ক্যানার</h1>
            <p class="text-sm text-slate-500">পণ্যের স্টক তথ্য দ্রুত দেখতে বা আপডেট করতে বারকোড স্ক্যান করুন।</p>
        </div>
        <div id="connection-status" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-green-500">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> সিস্টেম প্রস্তুত
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Scanner Module -->
        <div class="space-y-6">
            <div class="admin-card overflow-hidden">
                <div class="admin-card-header bg-slate-900 border-none">
                    <span class="admin-card-title text-white flex items-center gap-2">
                        <i class="ph ph-camera text-green-500"></i>
                        লাইভ স্ক্যানার
                    </span>
                    <button onclick="toggleScanner()" id="scanner-toggle" class="text-[10px] font-black text-slate-400 hover:text-white uppercase tracking-widest">স্ক্যানার বন্ধ করুন</button>
                </div>
                <div id="reader" style="width: 100%; border:none; background: #000;"></div>
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <select id="camera-select" class="text-xs font-bold bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none"></select>
                    </div>
                    <div class="text-[10px] font-black text-slate-400 uppercase">অটো-ফোকাস ইনাবল্ড</div>
                </div>
            </div>

            <div class="admin-card">
                <div class="p-4">
                    <label class="admin-label">ম্যানুয়ালি এন্ট্রি</label>
                    <div class="flex gap-2">
                        <input type="text" id="manual-barcode" class="admin-input" placeholder="বারকোড নম্বর লিখুন...">
                        <button onclick="lookupBarcode(document.getElementById('manual-barcode').value)" class="btn btn-primary px-6">খুঁজুন</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Module -->
        <div id="scan-result-container" class="space-y-6">
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center h-full flex flex-col items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                    <i class="ph ph-barcode text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-lg font-black text-slate-400">শুরু করতে একটি পণ্য স্ক্যান করুন</h3>
                <p class="text-sm text-slate-400 mt-2">বারকোডের দিকে ক্যামেরা ধরুন অথবা উপরে ম্যানুয়ালি এন্টার করুন।</p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrCode;
const scannerConfig = { fps: 20, qrbox: { width: 250, height: 150 } };

async function startScanner() {
    try {
        const devices = await Html5Qrcode.getCameras();
        const cameraSelect = document.getElementById('camera-select');
        
        if (devices && devices.length) {
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label;
                cameraSelect.appendChild(option);
            });

            html5QrCode = new Html5Qrcode("reader");
            await html5QrCode.start(
                devices[devices.length - 1].id, // Use back camera if available
                scannerConfig,
                onScanSuccess
            );
        }
    } catch (err) {
        console.error("Scanner Error:", err);
        document.getElementById('reader').innerHTML = `<div class="p-8 text-center text-rose-500 font-bold">ত্রুটি: ক্যামেরা পারমিশন নেই বা পাওয়া যায়নি।</div>`;
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Beep sound
    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3');
    audio.play();
    
    // Stop scanner briefly to prevent multiple scans
    html5QrCode.pause();
    lookupBarcode(decodedText);
}

async function lookupBarcode(barcode) {
    if(!barcode) return;
    
    document.getElementById('scan-result-container').innerHTML = `
        <div class="admin-card animate-pulse">
            <div class="p-12 text-center text-slate-400 font-bold">ডেটাবেসে খোঁজা হচ্ছে...</div>
        </div>
    `;

    try {
        const response = await fetch(`api-lookup.php?barcode=${barcode}`);
        const data = await response.json();

        if (data.success) {
            renderProductResult(data.product);
        } else {
            renderNotFound(barcode);
        }
    } catch (err) {
        console.error("API Error:", err);
    }
    
    // Resume scanner after 2 seconds
    setTimeout(() => {
        if(html5QrCode && html5QrCode.getState() === 3) html5QrCode.resume();
    }, 2000);
}

function renderProductResult(p) {
    const container = document.getElementById('scan-result-container');
    const img = p.image ? `../${p.image}` : 'https://placehold.co/400x400/f8fafc/94a3b8?text=No+Image';
    
    container.innerHTML = `
        <div class="admin-card overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="p-6 bg-slate-900">
                <div class="flex items-center gap-4">
                    <img src="${img}" class="w-16 h-16 rounded-xl object-cover border-2 border-slate-800">
                    <div>
                        <h2 class="text-lg font-black text-white leading-tight">${p.name}</h2>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Barcode: ${p.barcode}</span>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl">
                        <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">বিক্রয় মূল্য</span>
                        <div class="text-xl font-black text-slate-900">৳ \${new Intl.NumberFormat().format(p.price)}</div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl">
                        <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">স্টক অবস্থা</span>
                        <div class="text-xl font-black \${p.stock_status === 'In Stock' ? 'text-emerald-600' : 'text-rose-600'}">\${p.stock_status === 'In Stock' ? 'স্টকে আছে' : 'স্টকে নেই'}</div>
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
                    <h4 class="text-xs font-black text-emerald-800 uppercase mb-3">কুইক আপডেট</h4>
                    <form onsubmit="updateProduct(event, \${p.id})" class="flex gap-2">
                        <div class="flex-1">
                            <label class="text-[9px] font-black text-emerald-700 uppercase mb-1 block">নতুন মূল্য</label>
                            <input type="number" id="update-price" value="\${p.price}" class="w-full bg-white border border-emerald-200 rounded-lg px-3 py-2 text-sm font-bold outline-none focus:border-emerald-500">
                        </div>
                        <div class="flex-1">
                            <label class="text-[9px] font-black text-emerald-700 uppercase mb-1 block">অবস্থা</label>
                            <select id="update-status" class="w-full bg-white border border-emerald-200 rounded-lg px-3 py-2 text-sm font-bold outline-none focus:border-emerald-500">
                                <option value="In Stock" \${p.stock_status === 'In Stock' ? 'selected' : ''}>স্টকে আছে</option>
                                <option value="Out of Stock" \${p.stock_status === 'Out of Stock' ? 'selected' : ''}>স্টকে নেই</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn btn-primary py-2.5">সেভ করুন</button>
                        </div>
                    </form>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <a href="product-edit.php?id=\${p.id}" class="flex-1 btn btn-ghost justify-center py-3">বিস্তারিত এডিট</a>
                    <a href="barcode-print.php?id=\${p.id}" target="_blank" class="flex-1 btn btn-ghost justify-center py-3">লেবেল প্রিন্ট</a>
                </div>
            </div>
        </div>
    `;
}

function renderNotFound(barcode) {
    document.getElementById('scan-result-container').innerHTML = `
        <div class="bg-rose-50 border-2 border-dashed border-rose-200 rounded-2xl p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-warning-circle text-4xl text-rose-500"></i>
            </div>
            <h3 class="text-lg font-black text-rose-900">বারকোড পাওয়া যায়নি</h3>
            <p class="text-sm text-rose-700 mt-2 mb-6">বারকোড "\${barcode}" কোনো পণ্যের সাথে নিবন্ধিত নয়।</p>
            <a href="product-edit.php?barcode=\${barcode}" class="btn btn-primary inline-flex">নতুন পণ্য যোগ করুন</a>
        </div>
    `;
}

async function updateProduct(e, id) {
    e.preventDefault();
    const price = document.getElementById('update-price').value;
    const status = document.getElementById('update-status').value;
    
    const submitBtn = e.target.querySelector('button');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';

    try {
        const response = await fetch('api-update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, price, stock_status: status })
        });
        const data = await response.json();
        if(data.success) {
            alert('সফলভাবে আপডেট করা হয়েছে!');
            lookupBarcode(data.barcode); // Refresh view
        }
    } catch (err) { alert('আপডেট করতে ব্যর্থ হয়েছে।'); }
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Save';
}

function toggleScanner() {
    const btn = document.getElementById('scanner-toggle');
    if (html5QrCode.getState() === 2) {
        html5QrCode.stop();
        btn.innerHTML = 'স্ক্যানার চালু করুন';
        btn.classList.replace('text-slate-400', 'text-green-500');
    } else {
        startScanner();
        btn.innerHTML = 'স্ক্যানার বন্ধ করুন';
        btn.classList.replace('text-green-500', 'text-slate-400');
    }
}

// Start on load
document.addEventListener('DOMContentLoaded', startScanner);
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
