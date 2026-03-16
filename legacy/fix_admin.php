<?php
/**
 * Direct DB admin password reset - NO GIT NEEDED
 * Runs directly via web browser on the live server
 * DELETE AFTER USE
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direct DB connection (bypasses config.php)
try {
    $db = new PDO('mysql:host=localhost;dbname=zamankitchens_db;charset=utf8mb4', 'zamankitchens_admin', 'Sir@@@admin123');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $newPass = 'admin123';
    
    // Check if admin exists
    $count = $db->query("SELECT COUNT(*) FROM admins WHERE username='admin'")->fetchColumn();
    
    if ($count > 0) {
        $db->prepare("UPDATE admins SET password=? WHERE username='admin'")->execute([$newPass]);
        $msg = "✅ Password updated!";
    } else {
        $db->prepare("INSERT INTO admins (username,password) VALUES ('admin',?)")->execute([$newPass]);
        $msg = "✅ Admin account created!";
    }
    
    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:60px;'>";
    echo "<h1 style='color:green;'>$msg</h1>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p style='color:red;'>⚠️ Delete this file via cPanel File Manager immediately!</p>";
    echo "<a href='/admin/' style='display:inline-block;margin-top:20px;background:#d97706;color:#fff;padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:900;font-size:16px;'>Login to Admin →</a>";
    echo "</body></html>";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
