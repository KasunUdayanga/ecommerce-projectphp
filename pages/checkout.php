<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = getLoggedInUserName(); // Ensure this is set
$cartItems = getCartItems();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($cartItems)) {
        $_SESSION['checkout_error'] = 'Your cart is empty.';
        header('Location: cart.php');
        exit;
    }

    // Create the order
    $orderId = createOrder($userId, $cartItems);

    // Clear the cart
    clearCart();

    // Redirect to order confirmation page
    header("Location: order_confirmation.php?order_id=$orderId");
    exit;
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

        <form method="POST">
            <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
            <ul class="mb-4 space-y-2">
                <?php foreach ($cartItems as $item): ?>
                    <li class="flex justify-between border-b border-gray-200 pb-2">
                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                        <span>LKR<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="mt-4 font-semibold">Total: LKR<?php echo number_format(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartItems)), 2); ?></p>
            <button type="submit" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Place Order</button>
        </form>
    </div>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>