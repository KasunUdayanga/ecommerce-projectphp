<?php
session_start();
require_once '../includes/functions.php';

if (!isUserLoggedIn()) {
    $redirect = 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/pages/checkout.php');
    header('Location: ' . $redirect);
    exit;
}

$userName = getLoggedInUserName();
$isLoggedIn = true;
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/pages/checkout.php');
$logoutUrl = 'logout.php';
$showHero = false;

$cartItems = getCartItems();
$totalPrice = calculateTotalPrice($cartItems);
$success = false;
$error = '';
$orderItems = [];

$formData = [
    'name' => '',
    'email' => '',
    'address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['address'] = trim($_POST['address'] ?? '');

    if (empty($cartItems)) {
        $error = 'Your cart is empty.';
    } elseif ($formData['name'] === '' || $formData['email'] === '' || $formData['address'] === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $orderItems = $cartItems;
        clearCart();
        $cartItems = [];
        $totalPrice = 0;
        $success = true;
    }
}

function calculateTotalPrice($cartItems)
{
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <title>Checkout</title>
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="container mx-auto p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h1 class="text-2xl font-bold">Checkout</h1>
            <span class="text-sm text-gray-500">Signed in as <?php echo htmlspecialchars($userName); ?></span>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded border border-green-300 bg-green-50 p-4">
                <p class="text-green-700 font-semibold">Order placed successfully!</p>
                <p class="text-sm text-green-700">We have received your order and will process it soon.</p>
            </div>
            <?php if (!empty($orderItems)): ?>
                <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
                <ul class="mb-4 space-y-2">
                    <?php foreach ($orderItems as $item): ?>
                        <li class="flex justify-between border-b border-gray-200 pb-2">
                            <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="flex gap-3">
                <a href="index.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Continue Shopping</a>
                <a href="cart.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">View Cart</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <p class="mb-4 text-red-600"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <p class="text-gray-600">Your cart is empty. Add some products first.</p>
                <a href="index.php" class="inline-block mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Browse Products</a>
            <?php else: ?>
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
                        <ul class="space-y-2">
                            <?php foreach ($cartItems as $item): ?>
                                <li class="flex justify-between border-b border-gray-200 pb-2">
                                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                                    <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="mt-4 font-semibold">Total: $<?php echo number_format($totalPrice, 2); ?></p>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Shipping Details</h2>
                        <form method="POST" action="checkout.php" class="space-y-4">
                            <div>
                                <label class="block mb-1 font-semibold" for="name">Full Name</label>
                                <input class="w-full border border-gray-300 rounded p-2" type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold" for="email">Email</label>
                                <input class="w-full border border-gray-300 rounded p-2" type="email" name="email" id="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                            </div>
                            <div>
                                <label class="block mb-1 font-semibold" for="address">Address</label>
                                <textarea class="w-full border border-gray-300 rounded p-2" name="address" id="address" rows="4" required><?php echo htmlspecialchars($formData['address']); ?></textarea>
                            </div>
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Place Order</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>