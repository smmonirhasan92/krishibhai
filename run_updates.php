<?php
/**
 * Run DB updates for Slider & Categories
 */
require_once __DIR__ . '/includes/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/database_update.sql');
    $pdo->exec($sql);
    echo "<h1>Database Updated Successfully!</h1>";
} catch (Exception $e) {
    echo "<h1>Error: " . $e->getMessage() . "</h1>";
}
?>
