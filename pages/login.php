<?php
session_start();
require_once '../includes/functions.php';

if (isUserLoggedIn()) {
    $redirect = $_GET['redirect'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';
$isLoggedIn = false;
$userName = '';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($redirect);
$logoutUrl = 'logout.php';
$showHero = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? $redirect;

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your email/username and password.';
    } elseif (attemptUserLogin($identifier, $password)) {
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Invalid login details.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md border border-gray-200 rounded-2xl shadow-xl p-6 bg-white">
            <h1 class="text-2xl font-bold mb-2 text-center">Welcome Back</h1>
            <p class="text-gray-600 text-center mb-6">Sign in to add products to your cart.</p>
            <?php if ($error): ?>
                <p class="mb-4 text-red-600 text-center"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="login.php" class="space-y-4">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                <div>
                    <label class="block mb-1 font-semibold" for="identifier">Email or Username</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="text" name="identifier" id="identifier" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="password">Password</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">Sign In</button>
            </form>
            <p class="text-sm text-gray-500 mt-4 text-center">Demo user: customer@example.com / customer123</p>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>