<?php
$pageTitle = "লগইন";
require_once __DIR__ . '/includes/header.php';

if (isset($_SESSION['user_id'])) {
    echo '<script>window.location.href="profile.php";</script>';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "অনুগ্রহ করে ইমেল এবং পাসওয়ার্ড উভয়ই লিখুন।";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                echo '<script>window.location.href="profile.php";</script>';
                exit;
            } else {
                $error = "ভুল ইমেল বা পাসওয়ার্ড।";
            }
        } catch (Exception $e) {
            $error = "সিস্টেম ত্রুটির কারণে লগইন ব্যর্থ হয়েছে।";
        }
    }
}
?>

<div class="min-h-[70vh] flex items-center justify-center bg-gray-50 py-12 px-4 relative overflow-hidden">
    <!-- Decorative Blurs -->
    <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-96 h-96 bg-green-400/20 rounded-full blur-3xl -z-10"></div>
    <div class="absolute top-1/3 right-1/4 -translate-y-1/2 w-80 h-80 bg-emerald-400/10 rounded-full blur-3xl -z-10"></div>

    <div class="max-w-md w-full glass-card p-10 rounded-[2.5rem] shadow-2xl border border-white bg-white/70 backdrop-blur-xl relative z-10">
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl mx-auto mb-6 flex items-center justify-center shadow-lg shadow-green-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
            </div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">স্বাগতম</h2>
            <p class="text-slate-500 mt-2 font-medium">আপনার প্রোফাইল এবং অর্ডার পরিচালনা করতে লগইন করুন।</p>
        </div>

        <?php if($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 text-sm font-bold border border-rose-100 flex items-center gap-3 animate-fade-in whitespace-pre-wrap">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">ইমেইল অ্যাড্রেস</label>
                <input type="email" name="email" required class="w-full px-5 py-4 bg-white/50 border border-white rounded-2xl outline-none focus:border-green-400 focus:bg-white transition-all text-slate-900 font-medium shadow-sm placeholder-slate-300" placeholder="hello@example.com">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">পাসওয়ার্ড</label>
                <input type="password" name="password" required class="w-full px-5 py-4 bg-white/50 border border-white rounded-2xl outline-none focus:border-green-400 focus:bg-white transition-all text-slate-900 font-medium shadow-sm placeholder-slate-300" placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between text-sm pt-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" class="w-4 h-4 text-green-600 rounded-md border-slate-300 focus:ring-green-500 transition">
                    <span class="text-slate-500 font-medium group-hover:text-slate-700 transition">আমাকে মনে রাখুন</span>
                </label>
                <a href="#" class="text-green-600 hover:text-green-700 font-bold hover:underline transition">পাসওয়ার্ড ভুলে গেছেন?</a>
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl transition-all hover:-translate-y-1 shadow-xl hover:shadow-2xl shadow-green-900/20 mt-4 flex items-center justify-center gap-2">
                লগইন করুন
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-slate-500 font-medium">
            অ্যাকাউন্ট নেই? <a href="register.php" class="text-green-600 hover:text-green-700 font-bold hover:underline transition">নতুন অ্যাকাউন্ট তৈরি করুন</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
