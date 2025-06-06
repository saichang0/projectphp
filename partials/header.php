<?php
// session_start();
$dsn = "mysql:host=127.0.0.1;port=3307;dbname=document_db;charset=utf8mb4";
$user = "root";
$pass = "";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in.";
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

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
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 9999px;
        }
    </style>
</head>
<header class="fixed top-0 left-0 right-0 h-16 bg-white shadow flex items-center justify-between px-6 z-10">
    <div>
        <div class="flex p-6 font-bold text-xl">🔐 Encrypt</div>
    </div>
    <div class="flex items-center justify-center w-8/12">
        <input type="text" placeholder="Search..." class="px-4 py-2 w-3/5 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500" />
    </div>
    <div class="relative">
        <p class="text-red-500 absolute  -top-3 left-5">0</p>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
        </svg>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="flex items-center justify-center space-x-4">
            <div id="menu-icon" class="mt-5">
                <img src="../login/<?= $_SESSION['profile'] ?>" alt="User" class="rounded-full w-10 h-10">
                <div class="py-3 px-4 text-xl relative">
                    <div id="dropdown" class="py-3 w-20 text-sm px-4 flex flex-col hidden absolute top-3 right-0 gap-2">
                        <p class="bg-blue-200 py-1 px-2 rounded cursor-pointer w-28">View Profile</p>
                        <a href="/project/login/signIn.php" class="bg-yellow-100 py-1 px-2 rounded cursor-pointer w-28 text-center block">
                            Log Out
                        </a>
                    </div>
                </div>
            </div>
            <div class="font-semibold">
                <p><?= htmlspecialchars($_SESSION['name']) ?></p>
                <span class="font-thin text-gray-400"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const menuIcon = document.getElementById("menu-icon");
        const dropdown = document.getElementById("dropdown");

        menuIcon.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdown.classList.toggle("hidden");
        });

        document.addEventListener("click", () => {
            dropdown.classList.add("hidden");
        });

        dropdown.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    </script>

</header>
</body>