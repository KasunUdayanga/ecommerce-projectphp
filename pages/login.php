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
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .soft-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .7rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            color: #166534;
            background: #dcfce7;
        }

        .form-shell {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 2rem;
        }

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

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <main class="flex-1 flex items-center justify-center p-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <span class="soft-badge">Sign In</span>
                <h1 class="mt-3 text-3xl font-bold text-gray-800">Welcome Back</h1>
                <p class="text-gray-600 mt-2">Sign in to add products to your cart</p>
            </div>

            <div class="form-shell">
                <?php if ($error): ?>
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                        <p class="font-semibold">✕ <?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-5">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                    <div>
                        <label class="form-label" for="identifier">Email or Username</label>
                        <input class="form-input w-full" type="text" name="identifier" id="identifier" placeholder="your@email.com" required autofocus>
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input w-full" type="password" name="password" id="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-500 text-white py-3 rounded-lg hover:from-green-700 hover:to-green-600 transition-all font-semibold shadow-lg hover:shadow-xl">
                        ✓ Sign In
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-4">
                        <span class="font-semibold">Demo Credentials:</span><br>
                        Email: <code class="bg-gray-100 px-2 py-1 rounded text-xs">customer@example.com</code><br>
                        Password: <code class="bg-gray-100 px-2 py-1 rounded text-xs">customer123</code>
                    </p>
                </div>
            </div>

            <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200 text-center">
                <p class="text-sm text-gray-700">
                    Don't have an account?<br>
                    <a class="text-green-600 hover:text-green-700 font-semibold" href="register.php?redirect=<?php echo urlencode($redirect); ?>">Create one now</a>
                </p>
            </div>
        </div>
    </main>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>