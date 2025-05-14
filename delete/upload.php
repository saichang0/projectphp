<?php
// Assuming the user is authenticated and their ID is available in $_SESSION
session_start();
$userId = $_SESSION['user_id']; // get the logged-in user ID

// Fetch user status from the database
$conn = new mysqli("localhost", "root", "", "your_database");
$sql = "SELECT status FROM users WHERE id = $userId";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['status'] == 0) {
    // If the user is inactive, show an error message or redirect them
    echo "You are inactive and cannot upload files.";
    exit;
}

// Proceed with file upload logic
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Handle the file upload
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "File is valid, and was successfully uploaded.";
    } else {
        echo "File upload failed.";
    }
}
?>
