<?php
session_start();
require_once './document_db.php';

if (!isset($_SESSION['user_id'])) {
    $msg = "❌ User not logged in.";
    exit;
}
// Check if the user exists in the database
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $msg = "❌ User does not exist in the database.";
    exit;
}


try {
    $stmt = $pdo->query("SELECT id,name,username, email,status, profile_image FROM users");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Company Settings - Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen p-8">
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <div class="flex justify-end items-start pb-20">
        <div class="w-10/12 bg-white p-6">
            <h2 class="text-2xl font-semibold mb-4 mt-4">Encrypt Settings</h2>
            <p class="text-sm text-gray-600 mb-4">Collaborate and expand your team invite users to join your company.</p>
            <div class="flex justify-between mb-4">
                <div class="flex items-center justify-center w-3/5">
                    <input type="text" placeholder="Search..." class="px-4 py-2 w-full  rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 hover:border-orange-500" />
                </div>
                <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
                    <span class="text-xl mr-2">+</span> Invite user
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm">
                            <th class="py-3 px-4">User Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($all_users as $user): ?>
                            <tr class="border-t">
                                <!-- Profile Image & Name -->
                                <td class="py-3 px-4 flex items-center space-x-3">
                                    <img
                                        src="../login/<?= htmlspecialchars($user['profile_image']) ?>"
                                        alt="profile"
                                        class="rounded-full w-10 h-10 object-cover">
                                    <div>
                                        <div class="font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></div>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>

                                <!-- Username -->
                                <td class="py-3 px-4 text-green-600 font-medium"><?= htmlspecialchars(ucfirst($user['username'])) ?></td>

                                <!-- Status with conditional color -->
                                <td class="py-3 px-4 font-thin <?= $user['status'] === 'inactive' ? 'text-red-600' : 'text-blue-500' ?>">
                                    <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3 px-4 flex space-x-4">
                                    <!-- Edit Button -->
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-pencil-square text-gray-600 hover:text-blue-600" viewBox="0 0 16 16">
                                            <path
                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                            <path fill-rule="evenodd"
                                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    <a href="/project/delete/delete_user.php?id=<?= $user['id'] ?>" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this user?');">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                            <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</body>

</html>