<?php
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
// Visit: /admin/?zk_reset=zamankitchens_reset_2024
// Visit: /admin/?zk_reset=zamankitchens_reset_2024
if (isset($_GET['zk_reset']) && $_GET['zk_reset'] === 'zamankitchens_reset_2024') {
    try {
        $newPass = 'admin123';
        $pdo->prepare("UPDATE admins SET password=? WHERE username='admin'")->execute([$newPass]);
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_user'] = 'admin';
        header("Location: dashboard.php");
        exit();
    } catch(Exception $e) { $error = "Reset error: " . $e->getMessage(); }
}
// ==== END BYPASS ====

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            // Robust check: supports both plain-text (new requested style) and hashed (original style)
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
    <title>Admin Login | Zaman Kitchens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Zaman Kitchens</h1>
            <p class="text-gray-500 mt-2">Admin Control Panel</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm border border-red-100">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
                    placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none transition"
                    placeholder="••••••••">
            </div>
            <button type="submit" 
                class="w-full bg-amber-600 text-white font-bold py-3 rounded-lg hover:bg-amber-700 transition duration-300 shadow-lg shadow-amber-200">
                Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="../" class="text-sm text-gray-500 hover:text-amber-600 transition">&larr; Back to Website</a>
        </div>
    </div>
</body>
</html>
