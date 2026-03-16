<?php
/**
 * Admin Password Reset - DELETE THIS FILE AFTER USE
 */
require_once __DIR__ . '/includes/db.php';

$newPassword = 'admin123';

try {
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$newPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "<div style='font-family:sans-serif;padding:40px;text-align:center;'>";
        echo "<h2 style='color:green;'>✅ Admin password reset successfully!</h2>";
        echo "<p>Username: <strong>admin</strong></p>";
        echo "<p>Password: <strong>$newPassword</strong></p>";
        echo "<p style='color:red;font-weight:bold;'>⚠️ Delete this file immediately after logging in!</p>";
        echo "<a href='/admin/' style='display:inline-block;margin-top:20px;background:#d97706;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;'>Go to Admin Login</a>";
        echo "</div>";
    } else {
        // Admin user might not exist, insert it
        $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)")->execute([$newPassword]);
        echo "<div style='font-family:sans-serif;padding:40px;text-align:center;'>";
        echo "<h2 style='color:green;'>✅ Admin account created!</h2>";
        echo "<p>Username: <strong>admin</strong> | Password: <strong>$newPassword</strong></p>";
        echo "<a href='/admin/' style='display:inline-block;margin-top:20px;background:#d97706;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;'>Go to Admin Login</a>";
        echo "</div>";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
