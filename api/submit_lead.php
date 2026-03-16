<?php
/**
 * Zaman Kitchens - Lead Submission API
 */
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO leads (name, phone, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $message]);
            
            // Redirect back with success
            header("Location: ../index.php?inquiry=success#inquiry");
            exit();
        } catch (Exception $e) {
            header("Location: ../index.php?inquiry=error#inquiry");
            exit();
        }
    }
}
header("Location: ../index.php");
exit();
