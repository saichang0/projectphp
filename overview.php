<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// File upload handling
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    // Required user ID from session
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    // Optional category from form
    $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;

    if ($userId === null) {
        $msg = "❌ User not logged in.";
    } else {
        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        $targetDir = "uploads/"; // Ensure this directory exists and is writable
        $targetPath = $targetDir . basename($fileName);

        $targetDir = "uploads/";
        $targetPath = $targetDir . basename($fileName);

        // Ensure variables are defined
        $userId = $_SESSION['user_id'];
        $categoryId = $_POST['category_id'] ?? null;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpName, $targetPath)) {
            $stmt = $pdo->prepare("
                INSERT INTO documents 
                    (user_id, file_name, file_path, file_type, file_size, uploaded_by, category_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $fileName,
                $targetPath,
                $fileType,
                $fileSize,
                $userId,
                $categoryId
            ]);
            $msg = "File uploaded successfully.";
        } else {
            $msg = "File upload failed.";
        }
    }
}

// Fetch recent documents
try {
    $stmt = $pdo->query("
        SELECT 
            documents.id,
            documents.file_name,
            documents.file_path,
            documents.file_type,
            documents.uploaded_at,
            documents.status,
            users.username AS uploaded_by
        FROM documents
        LEFT JOIN users ON documents.uploaded_by = users.id
         ORDER BY documents.uploaded_at DESC
        LIMIT 2
    ");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// Calculate user percentage change
$query = "SELECT COUNT(*) AS total_users FROM users";
$result = $pdo->query($query);

$total_users = 0;
if ($result) {
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $total_users = $row['total_users'];
}
$previous_total_users = 100;
if ($previous_total_users > 0) {
    $percent_change = (($total_users - $previous_total_users) / $previous_total_users) * 100;
} else {
    $percent_change = 0;
}
$percent_class = $percent_change >= 0 ? 'text-green-500' : 'text-red-500';
$percent_sign = $percent_change >= 0 ? '+' : '';

// Calculate document change
$query = "SELECT COUNT(*) AS total_document FROM documents";
$result = $pdo->query($query);

$total_document = 0;
if ($result) {
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $total_document = $row['total_document'];
}
$previous_total_documents = 100;
if ($previous_total_documents > 0) {
    $percent_change = (($total_document - $previous_total_documents) / $previous_total_documents) * 100;
} else {
    $percent_change = 0;
}
$percent_class = $percent_change >= 0 ? 'text-green-500' : 'text-red-500';
$percent_document = $percent_change >= 0 ? '+' : '';

// Fetch all users with profile images
try {
    $stmt = $pdo->query("SELECT profile_image FROM users WHERE profile_image IS NOT NULL AND profile_image != '' LIMIT 3");
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
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 font-sans">

    <?php include './partials/header.php'; ?>
    <?php include './partials/sidebar.php'; ?>

    <div class="ml-64 p-6 bg-gray-50 mt-16 max-h-screen">
        <div class="flex items-start justify-between">
            <div class="flex gap-5">
                <div class="flex flex-wrap gap-6 mb-6">
                    <div class="bg-white shadow-md rounded-xl p-4 w-64 flex flex-col gap-2">
                        <h2 class="text-3xl font-bold">28</h2>
                        <p class="text-gray-500">Workspaces</p>
                        <div class="flex items-end justify-between">
                            <div class="flex gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-graph-up-arrow text-green-500" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5" />
                                </svg>
                                <span class="text-green-500 text-sm">+5.8%</span>
                            </div>
                            <div class="p-3 bg-orange-500 border w-fit rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box text-white" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white shadow-md rounded-xl p-4 w-64 flex flex-col gap-2">
                        <h2 class="text-3xl font-bold"><?php echo $total_document; ?></h2>
                        <p class="text-gray-500">Documents</p>
                        <div class="flex items-end justify-between">
                            <div class="flex gap-2 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-graph-up-arrow <?= $percent_class ?>" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5" />
                                </svg>
                                <span class="<?= $percent_class ?> text-sm">
                                    <?= $percent_document . number_format($percent_change, 1) ?>%
                                </span>
                            </div>
                            <div class="p-3 bg-blue-600 border w-fit rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder2 text-white" viewBox="0 0 16 16">
                                    <path d="M1 3.5A1.5 1.5 0 0 1 2.5 2h2.764c.958 0 1.76.56 2.311 1.184C7.985 3.648 8.48 4 9 4h4.5A1.5 1.5 0 0 1 15 5.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 12.5zM2.5 3a.5.5 0 0 0-.5.5V6h12v-.5a.5.5 0 0 0-.5-.5H9c-.964 0-1.71-.629-2.174-1.154C6.374 3.334 5.82 3 5.264 3zM14 7H2v5.5a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white shadow-md rounded-xl p-4 w-64 flex flex-col gap-2">
                        <h2 class="text-3xl font-bold"><?php echo $total_users; ?></h2>
                        <p class="text-gray-500">Members</p>
                        <div class="flex items-end justify-between">
                            <div class="flex gap-2 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-graph-up-arrow <?= $percent_class ?>" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5" />
                                </svg>
                                <span class="<?= $percent_class ?> text-sm">
                                    <?= $percent_sign . number_format($percent_change, 1) ?>%
                                </span>
                            </div>
                            <div class="flex gap-1">
                                <?php foreach ($all_users as $user): ?>
                                    <div class="flex items-center">
                                        <img
                                            src="login/<?= htmlspecialchars($user['profile_image']) ?>"
                                            alt="profile"
                                            class="rounded-full w-8 h-8">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="p-3 bg-purple-600 border w-fit rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-person-add text-white" viewBox="0 0 16 16">
                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                    <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow rounded-xl p-6 mb-6 w-full">
                        <div class="flex justify-start items-center gap-40">
                            <h3 class="text-lg font-semibold mb-4">Upload Files</h3>
                            <?php if (isset($msg)) : ?>
                                <p id="flash-message" class="text-sm mt-4 <?= str_starts_with($msg, '✅') ? 'text-red-600' : 'text-green-600' ?>">
                                    <?= htmlspecialchars($msg) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="w-full" action="">
                            <label class="flex flex-col border-dashed border-2 border-orange-500 rounded-xl p-10 text-center justify-center items-center w-full bg-white shadow cursor-pointer">
                                <svg for="file-upload" xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-orange-500 mb-4" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708z" />
                                    <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383m.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z" />
                                </svg>
                                <p>Drop files here or <span class="text-orange-600 underline cursor-pointer">Browse</span></p>
                                <input id="file-upload" name="file" type="file" class="hidden" onchange="this.form.submit()" />
                            </label>
                        </form>
                    </div>

                    <div class="bg-white shadow rounded-xl p-6 w-full">
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="">
                    <div class="bg-white p-6 rounded-lg shadow-md max-w-sm mx-auto">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Documents Stats</h2>
                            <div class="text-sm text-gray-500 bg-gray-200 px-3 py-1 rounded-md">
                                6 Months
                            </div>
                        </div>
                        <div id="gaugeChart"></div>
                        <div class="flex justify-between text-sm mt-4">
                            <div class="text-orange-500 font-semibold">Completed<br><span class="text-xl">222</span></div>
                            <div class="text-gray-400 font-semibold">Remaining<br><span class="text-xl">68</span></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md max-w-sm mx-auto mt-6">
                        <h2 class="text-lg font-semibold">Total Budget Spent</h2>
                        <p class="text-2xl font-bold mt-2">$12,048.06 <span class="text-green-500 text-sm">+5.8%</span></p>
                        <div id="lineChart" class="mt-4"></div>
                        <p class="text-sm text-gray-400 text-center mt-2">From Jan 1, 2022 to May 28, 2022</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'partials/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            setTimeout(() => {
                const msg = document.getElementById('flash-message');
                if (msg) msg.style.display = 'none';
            }, 800);
        </script>

        <script>
            // Gauge Chart
            var options = {
                chart: {
                    type: 'radialBar',
                    offsetY: -20
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            size: '70%',
                        },
                        dataLabels: {
                            name: {
                                show: false
                            },
                            value: {
                                fontSize: '24px',
                                show: true,
                                formatter: function(val) {
                                    return val + "%";
                                }
                            }
                        }
                    }
                },
                series: [62],
                labels: ['Completed'],
                colors: ['#f97316'],
            };

            var chart1 = new ApexCharts(document.querySelector("#gaugeChart"), options);
            chart1.render();

            // Line Chart
            var lineOptions = {
                chart: {
                    type: 'line',
                    height: 200
                },
                series: [{
                    name: 'Budget',
                    data: [7000, 8500, 10000, 12000, 11500]
                }],
                colors: ['#f97316'],
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May']
                }
            };

            var chart2 = new ApexCharts(document.querySelector("#lineChart"), lineOptions);
            chart2.render();
        </script>

</body>

</html>