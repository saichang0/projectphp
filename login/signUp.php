<?php
session_start();

$servername = "localhost";
$usernameDB = "root";
$passwordDB = "";
$dbname = "document_db";
$port = 3307;


$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $targetDir = "uploads/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $targetFile = $targetDir . basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (getimagesize($_FILES["profile_image"]["tmp_name"])) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                echo "Sorry, there was an error uploading your file.";
                exit();
            }
        } else {
            echo "File is not an image.";
            exit();
        }
    } else {
        echo "No image file uploaded or there was an error.";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (name,username, email, password, profile_image) VALUES (?, ?, ?, ?,?)");
    $stmt->bind_param("sssss",$name, $username, $email, $hashedPassword, $imagePath);

    if ($stmt->execute()) {
        header("Location: signIn.php");
        exit(); 
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Register</title>
</head>

<body>
    <div class="min-h-screen flex">
        <div class="w-full lg:w-1/2 flex items-center justify-center p-10 bg-white">
            <form method="POST" enctype="multipart/form-data" class="w-full max-w-md space-y-6">
                <h2 class="text-3xl font-bold text-gray-800">Welcome To Register Page</h2>

                 <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="mt-1 w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">UserName</label>
                    <input type="text" name="username" class="mt-1 w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="mt-1 w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Profile Image</label>
                    <input type="file" name="profile_image" accept="image/*" class="mt-1 w-full p-3 border rounded-lg shadow-sm" />
                </div>

                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />

                    <svg id="eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="absolute right-3 top-10 cursor-pointer text-gray-600" viewBox="0 0 16 16">
                        <path
                            d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z" />
                        <path
                            d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829" />
                        <path
                            d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z" />
                    </svg>
                    <svg id="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="absolute right-3 top-10 cursor-pointer text-gray-600 hidden" viewBox="0 0 16 16">
                        <path
                            d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                        <path
                            d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                    </svg>
                </div>

                <button type="submit"
                    class="w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 transition">Register</button>

                <hr />
                <p>
                    I have an account already
                    <a href="signIn.php" class="font-bold text-gray-800">Sign In</a>
                </p>
            </form>
        </div>
        <div class="w-1/2 bg-gray-100 hidden lg:flex items-center justify-center p-10">
            <img src="https://img.freepik.com/premium-vector/teenagers-learning-with-books_24640-76265.jpg" alt="Register Illustration" />
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.getElementById("eye");
        const eyeSlashIcon = document.getElementById("eye-slash");

        eyeIcon.addEventListener("click", () => {
            passwordInput.type = "password";
            eyeIcon.classList.add("hidden");
            eyeSlashIcon.classList.remove("hidden");
        });

        eyeSlashIcon.addEventListener("click", () => {
            passwordInput.type = "text";
            eyeSlashIcon.classList.add("hidden");
            eyeIcon.classList.remove("hidden");
        });
    </script>
</body>

</html>