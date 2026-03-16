<?php
$pageTitle = "অ্যাকাউন্ট তৈরি করুন";
require_once __DIR__ . '/includes/header.php';

if (isset($_SESSION['user_id'])) {
    echo '<script>window.location.href="profile.php";</script>';
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "সবগুলো তথ্য প্রদান করা আবশ্যক।";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "এই ইমেইলটি ইতিপূর্বেই নিবন্ধিত হয়েছে।";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hash]);
                $success = "অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে! আপনি এখন <a href='login.php' class='underline font-bold text-green-700'>এখানে লগইন</a> করতে পারেন।";
            }
        } catch (Exception $e) {
            $error = "রেজিস্ট্রেশন ব্যর্থ হয়েছে। আবার চেষ্টা করুন।";
        }
    }
}
?>

<div class="min-h-[80vh] flex items-center justify-center bg-gray-50 py-12 px-4 relative overflow-hidden">
    <!-- Decorative Blurs -->
    <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-96 h-96 bg-green-400/20 rounded-full blur-3xl -z-10"></div>
    <div class="absolute top-1/3 right-1/4 -translate-y-1/2 w-80 h-80 bg-emerald-400/10 rounded-full blur-3xl -z-10"></div>

    <div class="max-w-md w-full glass-card p-10 rounded-[2.5rem] shadow-2xl border border-white bg-white/70 backdrop-blur-xl relative z-10 my-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">কৃষিভাই-এ যোগ দিন</h2>
            <p class="text-slate-500 mt-2 font-medium leading-relaxed">আপনার প্রোফাইল এবং অর্ডারগুলো ট্র্যাক করতে আজই একটি অ্যাকাউন্ট তৈরি করুন।</p>
        </div>

        <?php if($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 text-sm font-bold border border-rose-100 flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="bg-emerald-50 text-emerald-600 p-6 rounded-2xl mb-6 text-sm text-center border border-emerald-100 font-medium">
            <div class="text-4xl mb-4">🎉</div>
            <?php echo $success; ?>
        </div>
        <?php else: ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">পুরো নাম</label>
                <input type="text" name="name" required class="w-full px-5 py-4 bg-white/50 border border-white rounded-2xl outline-none focus:border-green-400 focus:bg-white transition-all text-slate-900 font-medium shadow-sm placeholder-slate-300" placeholder="যেমন: রহিম আহমেদ">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">ইমেইল অ্যাড্রেস</label>
                <input type="email" name="email" required class="w-full px-5 py-4 bg-white/50 border border-white rounded-2xl outline-none focus:border-green-400 focus:bg-white transition-all text-slate-900 font-medium shadow-sm placeholder-slate-300" placeholder="hello@example.com">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 pl-1">পাসওয়ার্ড</label>
                <input type="password" name="password" required class="w-full px-5 py-4 bg-white/50 border border-white rounded-2xl outline-none focus:border-green-400 focus:bg-white transition-all text-slate-900 font-medium shadow-sm placeholder-slate-300" placeholder="একটি শক্তিশালী পাসওয়ার্ড তৈরি করুন">
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl transition-all hover:-translate-y-1 shadow-xl hover:shadow-2xl shadow-green-900/20 mt-6 flex items-center justify-center gap-2">
                অ্যাকাউন্ট তৈরি করুন
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
        <?php endif; ?>

        <div class="mt-8 text-center text-sm text-slate-500 font-medium">
            ইতিমধ্যেই অ্যাকাউন্ট আছে? <a href="login.php" class="text-green-600 hover:text-green-700 font-bold hover:underline transition">লগইন করুন</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
