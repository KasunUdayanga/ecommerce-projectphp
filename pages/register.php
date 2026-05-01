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

        .requirement {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .requirement.met {
            color: #22c55e;
        }

        .requirement.unmet {
            color: #6b7280;
        }
    </style>
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <main class="flex-1 flex items-center justify-center p-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <span class="soft-badge">Create Account</span>
                <h1 class="mt-3 text-3xl font-bold text-gray-800">Join GreenStore</h1>
                <p class="text-gray-600 mt-2">Create an account to start shopping</p>
            </div>

            <div class="form-shell">
                <?php if ($error !== ''): ?>
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                        <p class="font-semibold">✕ <?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="space-y-5">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                    <div>
                        <label class="form-label" for="name">Full Name</label>
                        <input class="form-input w-full" type="text" name="name" id="name" placeholder="Your full name" required>
                    </div>

                    <div>
                        <label class="form-label" for="email">Email Address</label>
                        <input class="form-input w-full" type="email" name="email" id="email" placeholder="your@email.com" required>
                    </div>

                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input w-full" type="password" name="password" id="password" placeholder="Create a strong password" required>
                        <div class="mt-3 space-y-1.5">
                            <p class="text-xs font-semibold text-gray-700">Password requirements:</p>
                            <div class="requirement unmet" id="length-req">
                                <span>○</span> <span>At least 6 characters</span>
                            </div>
                            <div class="requirement unmet" id="upper-req">
                                <span>○</span> <span>One uppercase letter (A-Z)</span>
                            </div>
                            <div class="requirement unmet" id="number-req">
                                <span>○</span> <span>One number (0-9)</span>
                            </div>
                            <div class="requirement unmet" id="special-req">
                                <span>○</span> <span>One special character (!@#$%...)</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="password_confirm">Confirm Password</label>
                        <input class="form-input w-full" type="password" name="password_confirm" id="password_confirm" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-500 text-white py-3 rounded-lg hover:from-green-700 hover:to-green-600 transition-all font-semibold shadow-lg hover:shadow-xl">
                        ✓ Create Account
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-700">
                        Already have an account?<br>
                        <a class="text-green-600 hover:text-green-700 font-semibold" href="login.php?redirect=<?php echo urlencode($redirect); ?>">Sign in here</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>

    <script>
        const passwordInput = document.getElementById('password');
        const lengthReq = document.getElementById('length-req');
        const upperReq = document.getElementById('upper-req');
        const numberReq = document.getElementById('number-req');
        const specialReq = document.getElementById('special-req');

        passwordInput.addEventListener('input', function() {
            const pwd = this.value;

            // Check length
            if (pwd.length >= 6) {
                lengthReq.classList.remove('unmet');
                lengthReq.classList.add('met');
                lengthReq.innerHTML = '<span>✓</span> <span>At least 6 characters</span>';
            } else {
                lengthReq.classList.remove('met');
                lengthReq.classList.add('unmet');
                lengthReq.innerHTML = '<span>○</span> <span>At least 6 characters</span>';
            }

            // Check uppercase
            if (/[A-Z]/.test(pwd)) {
                upperReq.classList.remove('unmet');
                upperReq.classList.add('met');
                upperReq.innerHTML = '<span>✓</span> <span>One uppercase letter (A-Z)</span>';
            } else {
                upperReq.classList.remove('met');
                upperReq.classList.add('unmet');
                upperReq.innerHTML = '<span>○</span> <span>One uppercase letter (A-Z)</span>';
            }

            // Check number
            if (/[0-9]/.test(pwd)) {
                numberReq.classList.remove('unmet');
                numberReq.classList.add('met');
                numberReq.innerHTML = '<span>✓</span> <span>One number (0-9)</span>';
            } else {
                numberReq.classList.remove('met');
                numberReq.classList.add('unmet');
                numberReq.innerHTML = '<span>○</span> <span>One number (0-9)</span>';
            }

            // Check special character
            if (/[\W]/.test(pwd)) {
                specialReq.classList.remove('unmet');
                specialReq.classList.add('met');
                specialReq.innerHTML = '<span>✓</span> <span>One special character (!@#$%...)</span>';
            } else {
                specialReq.classList.remove('met');
                specialReq.classList.add('unmet');
                specialReq.innerHTML = '<span>○</span> <span>One special character (!@#$%...)</span>';
            }
        });
    </script>
</body>

</html>