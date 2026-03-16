<?php

/**
 * OCE Composer Runner
 * Use this to run 'composer install' on shared hosting.
 */

$password = "oce_fix_2026"; // Security check

if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
    die("Unauthorized. Usage: composer_fix.php?pass=oce_fix_2026");
}

// Paths
$corePath = __DIR__ . '/../repositories/zamankitchens';
$composerPhar = __DIR__ . '/composer.phar';

if (!file_exists($composerPhar)) {
    echo "composer.phar not found. Attempting to download...<br>";
    $composerUrl = 'https://getcomposer.org/composer.phar';
    if (file_put_contents($composerPhar, file_get_contents($composerUrl))) {
        echo "✅ composer.phar downloaded successfully.<br>";
    } else {
        die("❌ Error: Failed to download composer.phar. Please upload it manually to " . __DIR__);
    }
}

if (!is_dir($corePath)) {
    die("Error: Core directory not found at $corePath");
}

echo "<h2>OCE Composer Fix</h2>";
echo "Running 'php composer.phar install' in $corePath...<br><br>";

// Set memory limit and time limit for long process
ini_set('memory_limit', '512M');
set_time_limit(300);

// Fix "The HOME or COMPOSER_HOME environment variable must be set" error
putenv('HOME=' . __DIR__);

// Command to run
$cmd = "cd $corePath && php $composerPhar install --no-dev 2>&1";

echo "Executing: $cmd <br><hr><pre>";
$output = shell_exec($cmd);
echo $output;
echo "</pre><hr>";

if (file_exists($corePath . '/vendor/autoload.php')) {
    echo "<b>✅ Success!</b> Vendor directory created and autoload.php is ready.";
} else {
    echo "<b>❌ Failed.</b> Please check the output above or contact support.";
}
