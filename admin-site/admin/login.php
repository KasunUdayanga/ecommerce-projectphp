<?php
require_once __DIR__ . '/../../shared-core/includes/admin.php';

if (isAdminLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (attemptAdminLogin($username, $password)) {
        header('Location: ../index.php');
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
    <link rel="stylesheet" href="/ecommerce-projectphp/shared-core/assets/css/styles.css">
    <style>
        .form-input {
            border: 1px solid #e5e7eb;
            border-radius: 0.8rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color .3s ease, box-shadow .3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #86efac;
            box-shadow: 0 0 0 3px rgba(134, 239, 172, .1);
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-green-50 to-green-100 text-black min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-green-700 mb-2">GreenStore Admin</h1>
                <p class="text-gray-600">Manage your product inventory</p>
            </div>

            <!-- Login Card -->
            <div class="border border-green-200 rounded-2xl p-8 shadow-xl bg-white">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Admin Login</h2>

                <?php if ($error): ?>
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                        <p class="font-semibold">✕ <?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-5">
                    <div>
                        <label class="form-label" for="username">Username</label>
                        <input class="form-input w-full" type="text" name="username" id="username" placeholder="Enter your username" required autofocus>
                    </div>

                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input w-full" type="password" name="password" id="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-500 text-white py-3 rounded-lg hover:from-green-700 hover:to-green-600 transition-all font-semibold shadow-lg hover:shadow-xl">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-700">
                        <span class="font-semibold">Demo Credentials:</span><br>
                        Username: <code class="bg-blue-100 px-2 py-1 rounded">admin</code><br>
                        Password: <code class="bg-blue-100 px-2 py-1 rounded">admin123</code>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    <a href=" /ecommerce-projectphp/user-site/index.php" class="text-green-600 hover:text-green-700 font-semibold">← Back to Store</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>