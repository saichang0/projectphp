<?php
session_start();
include("../dashboard/document_db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['email'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['user_id'] = $row['id'];
            header('Location: /project/dashboard/overview.php');
            exit();
        } else {
            $msg = "Incorrect email or password!";
        }
    } else {
        $msg = "Database connection failed.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login</title>
</head>

<body>
    <div class="min-h-screen flex">
        <div class="w-1/2 bg-gray-100 hidden lg:flex items-center justify-center p-10">
            <img src="https://media.istockphoto.com/id/1206750602/vector/young-people-group-reading-books-study-learning-knowledge-and-education-vector-concept.jpg?s=612x612&w=0&k=20&c=8WotuURJr3LSN7vNs1ponstKZVtE_ySXzlyrp4B1kHw=" alt="" />
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-10 bg-white">
            <form method="post" class="w-full max-w-md space-y-6">
                <h2 class="text-3xl font-bold text-gray-800">Welcome Back!</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="mt-1 w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                </div>

                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />

                    <svg id="eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="absolute right-3 top-10 cursor-pointer text-gray-600" viewBox="0 0 16 16">
                        <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z" />
                        <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829" />
                        <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z" />
                    </svg>
                    <!-- Eye Icon (Show) -->
                    <svg id="eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="absolute right-3 top-10 cursor-pointer text-gray-600 hidden" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                        <path d="M8 5.5a2.5 2.5 0=" 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0="1 1 7 0 3.5 3.5 0 0 1-7 0" />
                    </svg>

                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="form-checkbox" />
                        Remember me
                    </label>
                    <a href="#" class="text-blue-500 hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-500 transition">
                    Login
                </button>

                <hr />
                <p>Don't have an account? <a href="signUp.php" class="font-bold text-gray-800">Sign Up</a></p>
            </form>
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

    <?php if (!empty($msg)): ?>
        <script>
            alert("<?php echo $msg; ?>");
        </script>
    <?php endif; ?>

</body>

</html>