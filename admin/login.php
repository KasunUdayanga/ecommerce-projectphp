<?php
require_once __DIR__ . '/../includes/admin.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (attemptAdminLogin($username, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md border border-gray-300 rounded-lg p-6 shadow-lg bg-white">
            <h1 class="text-2xl font-bold mb-4 text-center">Admin Login</h1>
            <?php if ($error): ?>
                <p class="mb-4 text-red-600 text-center"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="login.php" class="space-y-4">
                <div>
                    <label class="block mb-1 font-semibold" for="username">Username</label>
                    <input class="w-full border border-gray-300 rounded p-2" type="text" name="username" id="username" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="password">Password</label>
                    <input class="w-full border border-gray-300 rounded p-2" type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Sign In</button>
            </form>
        </div>
    </div>
</body>

</html>