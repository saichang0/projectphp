<?php
session_start();
// require_once '../document_db.php';
require '../dashboard/document_db.php';

if (!isset($_GET['id'])) {
    echo "❌ No user ID provided.";
    exit;
}

$userId = intval($_GET['id']);

try {
    // Fetch current user status
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "❌ User not found.";
        exit;
    }

    $currentStatus = $user['status'];
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';

    // Update status
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $userId]);

    // Redirect back
    header("Location: /project/dashboard/setting.php");
    exit;

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    exit;
}
