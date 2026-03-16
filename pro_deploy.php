<?php

/**
 * OCE (Omni-Commerce Engine) - Pro Deploy Tool
 * Use this tool to sync public assets from the core engine to the live public_html directory.
 * Best for: Shared Hosting & Sellable distributions.
 */

$password = "oce_deploy_2026"; // Simple security, change this.

if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
    die("Unauthorized access. Usage: pro_deploy.php?pass=YOUR_PASSWORD");
}

$corePath = __DIR__ . '/../repositories/zamankitchens/public'; 
$publicHtmlPath = __DIR__; // Assuming this file is in public_html

function syncFolder($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($source . '/' . $file)) {
                syncFolder($source . '/' . $file, $dest . '/' . $file);
            } else {
                copy($source . '/' . $file, $dest . '/' . $file);
                echo "Synced: $file <br>";
            }
        }
    }
    closedir($dir);
}

echo "<h2>OCE Pro Deploy System</h2>";
echo "Syncing from: $corePath <br>";
echo "To: $publicHtmlPath <br><hr>";

if (is_dir($corePath)) {
    syncFolder($corePath, $publicHtmlPath);
    echo "<br><b>✅ Deployment Successful!</b> Content from public/ synced to public_html.";
} else {
    echo "<b>❌ Error:</b> Core public directory not found at $corePath";
}
