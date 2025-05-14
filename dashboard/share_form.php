<?php
session_start();
require_once './document_db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in.";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->prepare("SELECT id, name, username, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile'] = $user['profile_image'];
    } else {
        echo "❌ User not found in database.";
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, document_id, shared_with_email, shared_at, role FROM document_shares");
    $stmt->execute();
    $shares = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
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

    <?php include '../partials/header.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <div class="ml-64 p-6 bg-gray-50 mt-16 max-h-screen">
        <div class="flex flex-col items-start justify-between gap-4">
            <div class="flex items-center justify-center w-3/5">
                <input type="text" placeholder="Search..." class="px-4 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 hover:border-orange-500" />
            </div>

            <div class="w-full flex flex-col bg-white shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Files</h3>
                <table class="min-w-full text-sm text-left">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Document ID</th>
                            <th>Shared With Email</th>
                            <th>Shared At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shares as $row): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['document_id']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['shared_with_email'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['shared_at'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4 flex gap-4">
                                    <button class="text-blue-600 bg-blue-100 p-2 rounded hover:underline view-file-btn" data-doc-id="<?= $row['document_id'] ?>">
                                        View
                                    </button>
                                    <button class="text-yellow-600 bg-yellow-100 p-2 rounded hover:underline view-file-btn">
                                        Edit
                                    </button>
                                    <button class="text-green-600 bg-green-100 rounded p-2 hover:underline view-file-btn">
                                        Download
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Modal -->
                <div id="fileModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white p-4 rounded-lg max-w-3xl w-full relative">
                        <button id="closeModalBtn" class="absolute top-2 right-2 text-red-500 font-bold">✕</button>
                        <div id="fileContent" class="mt-4 h-[500px] overflow-auto text-center">
                            <!-- File will be shown here -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('fileModal');
            const fileContent = document.getElementById('fileContent');
            const closeModalBtn = document.getElementById('closeModalBtn');

            document.querySelectorAll('.view-file-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const docId = button.getAttribute('data-doc-id');

                    fetch(`get-file-info.php?doc_id=${docId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                fileContent.innerHTML = `<p class="text-red-600">${data.message}</p>`;
                            } else {
                                const fileUrl = data.file_url;
                                const ext = fileUrl.split('.').pop().toLowerCase();

                                if (['pdf'].includes(ext)) {
                                    fileContent.innerHTML = `<iframe src="${fileUrl}" class="w-full h-full" frameborder="0"></iframe>`;
                                } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                    fileContent.innerHTML = `<img src="${fileUrl}" class="mx-auto max-h-[480px]" alt="Document">`;
                                } else {
                                    fileContent.innerHTML = `<a href="${fileUrl}" target="_blank" class="text-blue-600 underline">Download File</a>`;
                                }

                                modal.classList.remove('hidden');
                            }
                        });
                });
            });

            closeModalBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                fileContent.innerHTML = '';
            });
        });
    </script>

</body>

</html>