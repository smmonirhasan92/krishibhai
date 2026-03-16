<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Info</h1>";
echo "PHP Version: " . phpversion() . "<br>";

require_once __DIR__ . '/config.php';
echo "Config Loaded<br>";

try {
    require_once __DIR__ . '/includes/db.php';
    echo "Database File Included<br>";
    
    if (isset($pdo)) {
        echo "PDO Object Found<br>";
        $stmt = $pdo->query("SELECT 1");
        echo "Database Query Success<br>";
    } else {
        echo "PDO Object NOT Found<br>";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

echo "Done.";
?>
