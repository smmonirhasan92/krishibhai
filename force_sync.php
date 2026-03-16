<?php

/**
 * OCE Force Sync Tool
 * Bypasses cPanel deployment queue to update files manually.
 */

$password = "oce_force_2026";

if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
    die("Unauthorized. Usage: force_sync.php?pass=oce_force_2026");
}

$repoPath = __DIR__ . "/../repositories/zamankitchens";
$publicHtml = __DIR__;

echo "<h2>OCE Force Sync System</h2>";
echo "1. Cleaning & Updating Repository at $repoPath...<br>";

if (!is_dir($repoPath)) {
    die("❌ Error: Repository directory not found.");
}

chdir($repoPath);
$gitOut = shell_exec("git fetch --all 2>&1 && git reset --hard origin/main 2>&1");
echo "<pre>$gitOut</pre>";

echo "<br>2. Synchronizing Files to public_html...<br>";

function syncPublic($source, $dest) {
    $excludes = ['.git', '.cpanel.yml', '.gitignore', 'node_modules', 'vendor'];
    
    if (!is_dir($dest)) mkdir($dest, 0755, true);
    
    $dir = opendir($source);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..' || in_array($file, $excludes)) continue;
        
        $srcFile = "$source/$file";
        $dstFile = "$dest/$file";
        
        if (is_dir($srcFile)) {
            syncPublic($srcFile, $dstFile);
        } else {
            if (copy($srcFile, $dstFile)) {
                // echo "Synced: $file<br>";
            }
        }
    }
    closedir($dir);
}

syncPublic($repoPath, $publicHtml);

echo "<b>✅ Force Sync Complete!</b> Files have been manually updated.<br><hr>";
echo "<a href='composer_fix.php?pass=oce_fix_2026'>Next: Run Composer Fix</a>";
