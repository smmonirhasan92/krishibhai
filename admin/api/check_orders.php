<?php
/**
 * AJAX endpoint: Check for new orders since last_id
 * Returns JSON: { new_count: N, latest_id: X }
 */
// Prevent direct file access
if (!defined('ADMIN_CONTEXT')) {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
}

require_once __DIR__ . '/../../includes/db.php';

$lastId = max(0, (int)($_GET['last_id'] ?? 0));

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(MAX(id), 0) as latest_id FROM orders WHERE id > ?");
    $stmt->execute([$lastId]);
    $row = $stmt->fetch();

    header('Content-Type: application/json');
    echo json_encode([
        'new_count' => (int)$row['cnt'],
        'latest_id' => (int)$row['latest_id']
    ]);
} catch(Exception $e) {
    echo json_encode(['new_count' => 0, 'latest_id' => $lastId]);
}
?>
