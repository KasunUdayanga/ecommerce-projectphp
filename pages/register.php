<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (isUserLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '../index.php';
$isLoggedIn = false;
$userName = '';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = '../index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($redirect);
$logoutUrl = 'logout.php';
$showHero = false;

if (!empty($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $redirect = $_POST['redirect'] ?? $redirect;

    if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
        $error = 'Password must include at least one uppercase letter, one number, and one special character.';
    }

    if ($error !== '') {
        $_SESSION['register_error'] = $error;
        header('Location: register.php?redirect=' . urlencode($redirect));
        exit;
    }

    $created = registerUser($name, $email, $password);
    if (!$created) {
        $_SESSION['register_error'] = 'Email already registered or server error.';
        header('Location: register.php?redirect=' . urlencode($redirect));
        exit;
    }

    $_SESSION['user_id'] = $created;
    $_SESSION['user_name'] = $name;

    header('Location: ' . $redirect);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md border border-gray-200 rounded-2xl shadow-xl p-6 bg-white">
            <h1 class="text-2xl font-bold mb-2 text-center">Create Your Account</h1>
            <p class="text-gray-600 text-center mb-6">Join us to start adding products to your cart.</p>
            <?php if ($error !== ''): ?>
                <p class="mb-4 text-red-600 text-center"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="register.php" class="space-y-4">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                <div>
                    <label class="block mb-1 font-semibold" for="name">Full Name</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="text" name="name" id="name" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="email">Email</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="email" name="email" id="email" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="password">Password</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="password" name="password" id="password" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="password_confirm">Confirm Password</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2" type="password" name="password_confirm" id="password_confirm" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">Create Account</button>
            </form>
            <p class="text-sm text-gray-500 mt-4 text-center">Already have an account? <a class="text-green-600 hover:underline" href="<?php echo htmlspecialchars($loginUrl); ?>">Sign in</a></p>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>