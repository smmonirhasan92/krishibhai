<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// ==== ONE-TIME BYPASS TOKEN (Delete after first use) ====
if (isset($_GET['kb_reset']) && $_GET['kb_reset'] === 'krishibhai_reset_2026') {
    try {
        $newPass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admins SET password=? WHERE username='admin'")->execute([$newPass]);
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_user'] = 'admin';
        header("Location: dashboard.php");
        exit();
    } catch(Exception $e) { $error = "Reset error: " . $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            $is_authenticated = false;
            if ($admin) {
                if ($password === $admin['password']) {
                    $is_authenticated = true;
                } elseif (password_verify($password, $admin['password'])) {
                    $is_authenticated = true;
                }
            }

            if ($is_authenticated) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ম্যানেজমেন্ট পোর্টাল | কৃষিভাই</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .shining-button {
            background: linear-gradient(135deg, #629d25 0%, #4a771c 100%);
            box-shadow: 0 0 20px rgba(98, 157, 37, 0.3);
            transition: all 0.3s ease;
        }
        .shining-button:hover {
            box-shadow: 0 0 30px rgba(98, 157, 37, 0.5);
            transform: translateY(-2px);
        }
        .input-dark {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .input-dark:focus {
            border-color: #629d25;
            background: rgba(15, 23, 42, 0.9);
            outline: none;
            box-shadow: 0 0 0 4px rgba(98, 157, 37, 0.1);
        }
        .vibrant-bg {
            background: radial-gradient(circle at top left, rgba(98, 157, 37, 0.15), transparent 40%),
                        radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.1), transparent 40%);
        }
    </style>
</head>
<body class="vibrant-bg flex items-center justify-center min-h-screen p-6">
    
    <!-- Animated Blobs -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-[10%] left-[10%] w-[30rem] h-[30rem] bg-green-600/10 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[25rem] h-[25rem] bg-indigo-600/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-md w-full">
        <!-- Logo/Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600/20 rounded-2xl border border-green-500/30 mb-6 group">
                <i class="ph ph-shield-check-bold text-3xl text-green-500 group-hover:scale-110 transition duration-300"></i>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">ম্যানেজমেন্ট পোর্টাল</h1>
            <p class="text-slate-400 font-medium mt-2">কৃষিভাই প্রো এডমিন</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-[2.5rem] p-10 shadow-2xl relative overflow-hidden">
            <!-- Decorative Accent -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-green-500 to-transparent opacity-50"></div>

            <?php if($error): ?>
                <div class="bg-red-500/10 text-red-400 p-4 rounded-2xl mb-8 text-sm font-bold border border-red-500/20 flex items-center gap-3">
                    <i class="ph ph-warning-circle-bold text-lg"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-1">ইউজারনেম</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="ph ph-user-bold text-lg"></i>
                        </span>
                        <input type="text" name="username" required 
                            class="input-dark w-full pl-12 pr-4 py-4 rounded-2xl font-bold"
                            placeholder="ইউজারনেম লিখুন">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-1">নিরাপদ পাসওয়ার্ড</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="ph ph-lock-key-bold text-lg"></i>
                        </span>
                        <input type="password" name="password" required 
                            class="input-dark w-full pl-12 pr-4 py-4 rounded-2xl font-bold"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="shining-button w-full text-white font-black py-4 rounded-2xl flex items-center justify-center gap-2 group">
                        প্রবেশ করুন
                        <i class="ph ph-arrow-right-bold group-hover:translate-x-1 transition"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-10 text-center">
            <a href="../" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-white transition">
                <i class="ph ph-arrow-left-bold"></i>
                ওয়েবসাইটে ফিরে যান
            </a>
        </div>
    </div>

</body>
</html>
