<?php
session_start();
require_once '../document_db.php';

if (!isset($_GET['id'])) {
    echo "❌ No document ID provided.";
    exit;
}

$docId = intval($_GET['id']);

try {
    // Fetch file path
    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        echo "❌ Document not found.";
        exit;
    }

    $filePath = $doc['file_path'];

    // Delete file from filesystem
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$docId]);

    header("Location: /project/document.php"); // redirect back
    exit;

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    exit;
}

