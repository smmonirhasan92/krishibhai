<!-- ===========================
     FOOTER
=========================== -->
<footer class="bg-gray-900 text-gray-300 pt-16 pb-6 mt-10">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">

            <!-- Brand -->
            <div class="col-span-2 md:col-span-1">
                <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2 mb-4 hover:opacity-80 transition">
                    <div class="w-9 h-9 bg-green-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-extrabold text-sm">KB</span>
                    </div>
                    <span class="font-extrabold text-white text-lg">কৃষিভাই</span>
                </a>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">বাংলাদেশের অন্যতম বিশ্বস্ত কৃষি পণ্য ও সরঞ্জাম বিপণনকারী প্রতিষ্ঠান।</p>
                <!-- Social Links -->
                <div class="flex gap-3">
                    <a href="<?php echo SITE_FB; ?>" target="_blank" class="w-10 h-10 bg-white/10 hover:bg-green-600 rounded-xl flex items-center justify-center transition text-white border border-white/5" title="Facebook">
                        <i class="ph-bold ph-facebook-logo text-lg"></i>
                    </a>
                    <a href="<?php echo SITE_YT; ?>" target="_blank" class="w-10 h-10 bg-white/10 hover:bg-green-600 rounded-xl flex items-center justify-center transition text-white border border-white/5" title="YouTube">
                        <i class="ph-bold ph-youtube-logo text-lg"></i>
                    </a>
                    <a href="<?php echo SITE_INS; ?>" target="_blank" class="w-10 h-10 bg-white/10 hover:bg-green-600 rounded-xl flex items-center justify-center transition text-white border border-white/5" title="Instagram">
                        <i class="ph-bold ph-instagram-logo text-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="text-white font-bold mb-4 text-sm tracking-wide uppercase">ক্যাটাগরি</h4>
                <ul class="space-y-2 text-sm">
                    <?php
                    try {
                        $footerCats = $pdo->query("SELECT name, slug FROM categories LIMIT 6")->fetchAll();
                        foreach ($footerCats as $c):
                    ?>
                    <li><a href="<?php echo SITE_URL; ?>/category/<?php echo $c['slug']; ?>" class="hover:text-red-500 transition"><?php echo htmlspecialchars($c['name']); ?></a></li>
                    <?php endforeach; } catch(Exception $e){} ?>
                </ul>
            </div>

            <!-- Information -->
            <div>
                <h4 class="text-white font-bold mb-4 text-sm tracking-wide uppercase">তথ্য</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-green-500 transition">আমাদের সম্পর্কে</a></li>
                    <li><a href="privacy.php" class="hover:text-green-500 transition">প্রাইভেসী পলিসি</a></li>
                    <li><a href="terms.php" class="hover:text-green-500 transition">শর্তাবলী</a></li>
                    <li><a href="admin/" class="hover:text-green-500 transition">অ্যাডমিন প্যানেল</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-white font-bold mb-4 text-sm tracking-wide uppercase">যোগাযোগ</h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5">📍</span>
                        <span class="text-gray-400"><?php echo SITE_ADDRESS; ?></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span>📞</span>
                        <a href="tel:<?php echo SITE_PHONE_RAW; ?>" class="hover:text-green-500 transition"><?php echo SITE_PHONE; ?></a>
                    </li>
                    <li class="flex items-center gap-2">
                        <span>💬</span>
                        <a href="https://wa.me/<?php echo SITE_WHATSAPP; ?>" target="_blank" class="hover:text-green-500 transition">হোয়াটসঅ্যাপ চ্যাট</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-2 text-xs text-gray-500">
            <span>&copy; <?php echo date('Y'); ?> কৃষিভাই - সর্বস্বত্ব সংরক্ষিত।</span>
        </div>
    </div>
</footer>

<!-- ===========================
     FLOATING WHATSAPP / CALL BUTTON
=========================== -->
<div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 items-end">
    <!-- WhatsApp -->
    <a href="https://wa.me/<?php echo SITE_WHATSAPP; ?>?text=Hello%2C%20I%20am%20interested%20in%20your%20products." target="_blank"
        class="relative flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold px-4 py-3 rounded-full shadow-xl transition group wa-pulse">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 32 32" fill="currentColor">
            <path d="M16.003 3C9.375 3 4 8.373 4 15.003c0 2.147.573 4.16 1.567 5.896L4 29l8.265-1.543A11.94 11.94 0 0016.003 27C22.63 27 28 21.627 28 15.003 28 8.373 22.63 3 16.003 3zm6.085 16.543c-.255.717-1.493 1.366-2.044 1.44-.55.074-1.074.35-3.62-.756-3.05-1.32-5.02-4.443-5.17-4.647-.15-.204-1.24-1.649-1.24-3.145s.79-2.23 1.07-2.536c.28-.305.61-.38.813-.38h.58c.19 0 .444-.07.7.532.255.603.865 2.098.94 2.252.075.153.125.333.025.533-.1.2-.15.323-.3.495-.15.17-.317.38-.453.511-.15.144-.307.3-.132.59.175.29.78 1.28 1.672 2.073 1.148 1.024 2.116 1.34 2.42 1.49.305.15.48.127.655-.077.175-.204.747-.872.947-1.172.2-.3.4-.25.674-.15.273.1 1.733.816 2.03.965.3.15.498.226.572.35.073.126.073.727-.18 1.444z"/>
        </svg>
        <span class="text-sm">Order on WhatsApp</span>
    </a>

    <!-- Call Button (Mobile Only) -->
    <a href="tel:<?php echo SITE_PHONE_RAW; ?>" class="md:hidden flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold w-14 h-14 rounded-full shadow-xl transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        </svg>
    </a>
</div>

<!-- Scripts -->
<script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({behavior:'smooth'}); }
        });
    });
</script>
</body>
</html>
