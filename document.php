<?php
session_start();
require_once './document_db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in.";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username']; // Don't overwrite this

$documents = [];

try {
    // ✅ Create PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // ✅ Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userExists = $stmt->fetch();

    if (!$userExists) {
        echo "❌ User does not exist in the database.";
        exit;
    }

    // ✅ Fetch all documents
    $stmt = $pdo->query("
        SELECT 
            documents.*,
            users.username AS uploaded_by
        FROM documents
        LEFT JOIN users ON documents.uploaded_by = users.id
    ");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 font-sans">

    <?php include './partials/header.php'; ?>
    <?php include './partials/sidebar.php'; ?>

    <div class="ml-64 p-6 bg-gray-50 mt-16 max-h-screen">
        <div class="flex flex-col items-start justify-between gap-4">
            <div class="flex flex-wrap gap-6 mb-6">
                <div class="bg-orange-100 shadow-md rounded-xl p-4 w-64 flex flex-col items-center gap-2">
                    <h2 class="text-3xl font-bold">28</h2>
                    <p class="text-gray-500">Workspaces</p>
                </div>
                <div class="bg-blue-100 shadow-md rounded-xl p-4 w-64 flex flex-col items-center gap-2">
                    <h2 class="text-3xl font-bold">170</h2>
                    <p class="text-gray-500">Documents</p>
                </div>
                <div class="bg-purple-100 shadow-md rounded-xl p-4 w-64 flex flex-col items-center gap-2">
                    <h2 class="text-3xl font-bold">32</h2>
                    <p class="text-gray-500">Members</p>
                </div>
            </div>

            <div class="flex items-center justify-center w-3/5">
                <input type="text" placeholder="Search..." class="px-4 py-2 w-full  rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 hover:border-orange-500" />
            </div>

            <div class="w-full flex flex-col bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Files</h3>
                <table class="min-w-full text-sm text-left">
                    <thead class="text-gray-600 uppercase bg-gray-100">
                        <tr>
                            <th class="py-3 px-4">File name</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Uploaded</th>
                            <th class="py-3 px-4">Uploaded_By</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($documents as $doc): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4"><?= htmlspecialchars($doc['file_name']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($doc['file_type']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($doc['uploaded_by']) ?></td>
                                <td class="py-3 px-4 text-green-600"><?= htmlspecialchars($doc['status']) ?></td>
                                <td class="py-3 px-4 flex gap-2">
                                    <p class="bg-blue-200 py-1 px-2 rounded cursor-pointer">Edit</p>
                                    <a href="delete_document.php?id=<?= $doc['id'] ?>" class="bg-red-200 py-1 px-2 rounded cursor-pointer">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>


    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this document?")) {
                window.location.href = "delete_document.php?id=" + id;
            }
        }
    </script>

</body>

</html>