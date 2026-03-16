<?php
/**
 * Bootstrap deploy: git pull + reset admin password
 * DELETE THIS FILE AFTER USE
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>";

// Try git pull
$gitOut = shell_exec("cd /home/zamankitchens/public_html && git pull origin main 2>&1");
echo "Git Pull:\n$gitOut\n";

// Reset admin password
require_once '/home/zamankitchens/public_html/includes/db.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

if ($stmt->rowCount() === 0) {
    $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)")->execute([$hash]);
    echo "Admin user CREATED.\n";
} else {
    echo "Admin password UPDATED.\n";
}

echo "\nDone! Username: admin | Password: admin123\n";
echo "⚠️ Delete deploy.php from your server now!";
echo "</pre>";
?>
