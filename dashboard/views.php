<?php
session_start();
require_once './document_db.php'; 

$docId = $_GET['doc_id'] ?? null;

if (!$docId) {
    echo "❌ No document ID provided.";
    exit;
}

$stmt = $pdo->prepare("SELECT file_path, file_name FROM documents WHERE id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    echo "❌ Document not found.";
    exit;
}

$filePath = '../uploads/' . $doc['file_path']; 

if (!file_exists($filePath)) {
    echo "❌ File does not exist.";
    exit;
}

$mime = mime_content_type($filePath);
header("Content-Type: $mime");
readfile($filePath);
exit;
