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

// Fetch all categories
// Fetch all categories with IDs
$categories = $pdo->query("SELECT id, name FROM document_categories")->fetchAll();

foreach ($categories as $category) {
    $colorMap = [
        'Health Report' => 'bg-orange-100',
        'Medical Information' => 'bg-blue-100',
        'Prescription' => 'bg-purple-100',
    ];

    $id = $category['id'];
    $name = $category['name'];
    $bgColor = $colorMap[$name] ?? 'bg-gray-100';

    // Use category ID instead of name
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count, SUM(size) AS total_size FROM documents WHERE category_id = :category_id");
    $stmt->execute(['category_id' => $id]);
    $stats = $stmt->fetch();

    $fileCount = $stats['count'] ?? 0;
    $totalSizeMB = $stats['total_size'] ? round($stats['total_size'] / (1024 * 1024), 2) : 0;

    // Output your card here
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Get current user
    $stmt = $pdo->prepare("SELECT id, name, username, profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile'] = $user['profile_image'];
    } else {
        echo "❌ User not found in database.";
        exit;
    }

    // Get all other users
    $stmt = $pdo->prepare("SELECT id, name, profile_image FROM users WHERE id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $otherUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
    exit;
}

/////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = 'viewer'; // or get from request if dynamic

    if (!$userId || !$email) {
        echo "❌ Missing user session or email.";
        exit;
    }

    try {
        // Fetch all documents owned by current user
        $stmt = $pdo->prepare("SELECT id FROM documents WHERE user_id = ?");
        $stmt->execute([$userId]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$documents) {
            echo "❌ No documents found for sharing.";
            exit;
        }

        $sharedCount = 0;
        foreach ($documents as $doc) {
            $docId = $doc['id'];
            $check = $pdo->prepare("SELECT 1 FROM document_shares WHERE document_id = ? AND shared_with_email = ?");
            $check->execute([$docId, $email]);

            if (!$check->fetch()) {
                $insert = $pdo->prepare("INSERT INTO document_shares (document_id, shared_with_email, role, shared_at) VALUES (?, ?, ?, NOW())");
                $insert->execute([$docId, $email, $role]);
                $sharedCount++;
            }
        }

        echo "✅ Shared document success";
    } catch (PDOException $e) {
        echo "❌ DB Error: " . $e->getMessage();
    }
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
            <div class="flex flex-wrap gap-6 mb-6">
                <?php foreach ($categories as $category):
                    $name = $category['name'];
                    $categoryId = $category['id'];
                    $bgColor = $colorMap[$name] ?? 'bg-gray-100';

                    $stmt = $pdo->prepare("SELECT COUNT(*) AS count, SUM(size) AS total_size FROM documents WHERE category_id = :category_id");
                    $stmt->execute(['category_id' => $categoryId]);

                    $stats = $stmt->fetch();
                    if ($stats) {
                        $fileCount = $stats['count'] ?? 0;
                        $totalSizeMB = $stats['total_size'] ? round($stats['total_size'] / (1024 * 1024), 2) : 0;
                    } else {
                        $fileCount = 0;
                        $totalSizeMB = 0;
                    }
                ?>
                    <div class="<?= $bgColor ?> shadow-md rounded-xl p-4 w-64 flex flex-col items-start gap-2">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-folder-plus" viewBox="0 0 16 16">
                                <path d="m.5 3 .04.87a2 2 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2m5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19q-.362.002-.683.12L1.5 2.98a1 1 0 0 1 1-.98z" />
                                <path d="M13.5 9a.5.5 0 0 1 .5.5V11h1.5a.5.5 0 1 1 0 1H14v1.5a.5.5 0 1 1-1 0V12h-1.5a.5.5 0 0 1 0-1H13V9.5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </div>
                        <p class="text-gray-700"><?= htmlspecialchars($name) ?></p>
                        <div class="flex text-sm items-center justify-between w-full">
                            <p><?= $fileCount ?> Files</p>
                            <p><?= $totalSizeMB ?> MB</p>
                        </div>
                    </div>
                <?php endforeach; ?>

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
                                    <!-- Trigger Button -->
                                    <p class="openShareModal bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded cursor-pointer w-max" data-id="<?= $row['id'] ?>">
                                        Share
                                    </p>
                                    <a href="/project/delete/delete_document.php?id=<?php echo $doc['id']; ?>" class="bg-red-200 py-1 px-2 rounded cursor-pointer">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-between items-center border-b pb-3 mb-4">
                                <h2 class="text-xl font-semibold">Share</h2>
                                <button id="closeShareModal" class="text-gray-600 hover:text-black text-xl">&times;</button>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="flex items-center mb-3">
                                    <img src="../login/<?= $_SESSION['profile'] ?>" alt="User" class="rounded-full w-14 h-14">
                                    <div>
                                        <span class="font-bold text-gray-800"><?= htmlspecialchars($_SESSION['name']) ?></span>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <button class="border border-gray-300 px-2 py-0.5 rounded">Feed</button>
                                            <button class="border border-gray-300 px-2 py-0.5 rounded">Friends</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <textarea rows="3" placeholder="Say something about this..." class="w-full border border-gray-300 rounded px-3 py-2 resize-none mb-4"></textarea>
                            <div class="text-right mb-4">
                                <p id="shareNowBtn" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded cursor-pointer w-max openShareModal"
                                    data-document-id="<?= $doc['id'] ?>">
                                    Share
                                </p>
                                <input type="hidden" id="documentId" value="<?= $doc['id'] ?>">
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-sm font-medium text-gray-600 mb-3">Send in Other users</p>
                                <div class="flex overflow-x-auto gap-4" id="userList">
                                    <?php foreach ($otherUsers as $friend): ?>
                                        <div class="flex flex-col items-center text-xs text-center p-2 hover:bg-orange-100 cursor-pointer share-user"
                                            data-email="<?= htmlspecialchars($friend['email']) ?>">
                                            <img src="../login/<?= htmlspecialchars($friend['profile_image']) ?>"
                                                class="rounded-full w-10 h-10 mb-1"
                                                alt="profile">
                                            <p><?= htmlspecialchars($friend['name']) ?></p>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            </div>

                        </div>
                    </div>

                </table>
            </div>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let selectedEmail = null;

            document.querySelectorAll('.share-user').forEach(user => {
                user.addEventListener('click', function() {
                    selectedEmail = this.getAttribute('data-email');
                    console.log('Selected user email:', selectedEmail);
                });
            });

            document.getElementById('shareNowBtn').addEventListener('click', function() {
                const documentId = document.getElementById('documentId').value;

                if (!selectedEmail) {
                    alert("❌ Please select a user to share with.");
                    return;
                }

                fetch('document.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `email=${encodeURIComponent(selectedEmail)}`
                    })
                    .then(res => res.text())
                    .then(response => {
                        alert(response);
                        document.getElementById("shareModal").classList.add("hidden");
                    })
                    .catch(err => {
                        console.error(err);
                        alert("❌ Something went wrong.");
                    });
            });
        });
    </script>

    <script>
        const modal = document.getElementById("shareModal");
        const closeModal = document.getElementById("closeShareModal");
        let currentDocumentId = null; // We'll set this when a button is clicked

        // Handle modal open per document
        document.querySelectorAll('.openShareModal').forEach(btn => {
            btn.addEventListener("click", () => {
                currentDocumentId = btn.dataset.id;
                modal.classList.remove("hidden");
            });
        });

        // Close modal
        closeModal.addEventListener("click", () => {
            modal.classList.add("hidden");
        });

        const inviteBtn = document.getElementById("inviteBtn");
        const emailInput = document.getElementById("shareEmail");
        const roleSelect = document.getElementById("shareRole");
        const sharedList = document.getElementById("sharedList");

        inviteBtn.addEventListener("click", () => {
            const email = emailInput.value.trim();
            const role = roleSelect.value;

            if (!email) return alert("Please enter a valid email.");
            if (!currentDocumentId) return alert("No document selected!");

            // Send the data to PHP using fetch
            fetch('share_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `document_id=${encodeURIComponent(currentDocumentId)}&email=${encodeURIComponent(email)}&role=${encodeURIComponent(role)}`
                })
                .then(res => res.text())
                .then(result => {
                    alert(result); // Show response from PHP
                    modal.classList.add("hidden");
                });

            // Optionally: show shared list entry
            const item = document.createElement("div");
            item.className = "flex items-center justify-between border border-gray-200 rounded px-3 py-2";
            item.innerHTML = `
            <div>
                <p class="font-semibold">${email}</p>
                <p class="text-sm text-gray-500">${email}</p>
            </div>
            <select class="border border-gray-300 rounded px-2 py-1">
                <option ${role === 'editor' ? 'selected' : ''}>Editor</option>
                <option ${role === 'viewer' ? 'selected' : ''}>Viewer</option>
            </select>
        `;
            sharedList.appendChild(item);
            emailInput.value = '';
        });
    </script>


    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this document?")) {
                window.location.href = "delete_document.php?id=" + id;
            }
        }
    </script>

</body>

</html>