<?php
/**
 * OCE Server Inspector
 */
header('Content-Type: text/plain');
echo "OCE Server Inspection\n";
echo "Current Directory: " . __DIR__ . "\n\n";

function listRecursive($dir, $indent = "") {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = "$dir/$file";
        echo $indent . $file . (is_dir($path) ? "/" : "") . "\n";
        if (is_dir($path) && $indent === "") { // Only go 1 level deep for root
            // listRecursive($path, $indent . "  ");
        }
    }
}

echo "Root Contents:\n";
listRecursive(__DIR__);
