<?php
require_once __DIR__ . '/includes/db.php';
try {
    $stmt = $pdo->query("DESCRIBE products");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
} catch(Exception $e) { echo $e->getMessage(); }
